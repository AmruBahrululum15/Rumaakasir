<?php
namespace App\Services;
use App\Models\{Booking,BookingResource,BookingStaffSchedule,Business,BusinessService,Setting,User};
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
class ReservationSchedule {
    public const OCCUPIED=['pending','awaiting_payment','confirmed','in_progress'];
    public function settings(Business $business):array {
        $saved=json_decode(Setting::withoutGlobalScopes()->where('business_id',$business->id)->where('key','reservation_configuration')->value('value')??'{}',true)??[];
        return array_replace(['enabled'=>true,'auto_confirm'=>false,'opens_at'=>'00:00','closes_at'=>'23:59','days'=>[0,1,2,3,4,5,6],'holidays'=>[],'slot_minutes'=>30,'payment_mode'=>'free','deposit_percent'=>30],$saved);
    }
    public function check(Business $business,BusinessService $service,CarbonImmutable $start,?int $staffId,?int $resourceId,int $units=1,?int $ignore=null):array {
        if($units<1 || (int)$service->business_id!==(int)$business->id)throw ValidationException::withMessages(['capacity_units'=>'Jumlah atau layanan reservasi tidak valid.']);
        $settings=$this->settings($business);$end=$start->addMinutes($service->duration_minutes);$until=$end->addMinutes($service->buffer_minutes??0);
        if(!$settings['enabled'])throw ValidationException::withMessages(['booking'=>'Reservasi toko sedang dinonaktifkan.']);
        if($start->isPast() || !in_array($start->dayOfWeek,$settings['days']) || in_array($start->toDateString(),$settings['holidays']) || $start->format('H:i')<$settings['opens_at'] || $until->toDateString()!==$start->toDateString() || $until->format('H:i')>$settings['closes_at'])throw ValidationException::withMessages(['start_time'=>'Jadwal di luar jam operasional atau hari libur.']);
        if($staffId){
            User::withoutGlobalScopes()->where('business_id',$business->id)->where('is_active',true)->findOrFail($staffId);
            $schedules=BookingStaffSchedule::withoutGlobalScopes()->where('business_id',$business->id)->where('staff_id',$staffId)->get();
            if($schedules->isNotEmpty()){$schedule=$schedules->firstWhere('day_of_week',$start->dayOfWeek);if(!$schedule || $start->format('H:i')<$schedule->opens_at || $until->format('H:i')>$schedule->closes_at)throw ValidationException::withMessages(['staff_id'=>'Karyawan tidak tersedia pada jadwal ini.']);}
        }
        $resource=$resourceId?BookingResource::withoutGlobalScopes()->where('business_id',$business->id)->where('is_active',true)->findOrFail($resourceId):null;
        $overlap=Booking::withoutGlobalScopes()->where('business_id',$business->id)->whereIn('status',self::OCCUPIED)->where('starts_at','<',$until)->whereRaw('COALESCE(reserved_until, ends_at) > ?',[$start->toDateTimeString()])->when($ignore,fn($q)=>$q->whereKeyNot($ignore));
        if($staffId && (clone $overlap)->where('staff_id',$staffId)->exists())throw ValidationException::withMessages(['start_time'=>'Jadwal karyawan bentrok.']);
        if($resource){$this->capacity((clone $overlap)->where('resource_id',$resource->id)->get(),$start,$until,$units,$resource->capacity);}
        elseif(!$staffId){$this->capacity((clone $overlap)->whereNull('staff_id')->whereNull('resource_id')->where('service_id',$service->id)->get(),$start,$until,$units,$service->booking_capacity??1);}
        return [$end,$until];
    }
    private function capacity($bookings,CarbonImmutable $start,CarbonImmutable $end,int $units,int $capacity):void {
        $events=[[$start->timestamp,$units],[$end->timestamp,-$units]];
        foreach($bookings as $b){$events[]=[max($start->timestamp,$b->starts_at->timestamp),$b->capacity_units];$events[]=[min($end->timestamp,($b->reserved_until??$b->ends_at)->timestamp),-$b->capacity_units];}
        usort($events,fn($a,$b)=>($a[0]<=>$b[0])?:($a[1]<=>$b[1]));$used=0;
        foreach($events as $event){$used+=$event[1];if($used>$capacity)throw ValidationException::withMessages(['start_time'=>'Slot atau kapasitas fasilitas sudah penuh.']);}
    }
    public function slots(Business $business,int $serviceId,string $date,?int $staffId=null,?int $resourceId=null,int $units=1):array {
        $service=BusinessService::withoutGlobalScopes()->where('business_id',$business->id)->where('kind','appointment')->where('is_active',true)->findOrFail($serviceId);
        $settings=$this->settings($business);$slots=[];$start=CarbonImmutable::parse($date.' '.$settings['opens_at']);
        for($i=0;$i<288 && $start->toDateString()===$date && $start->format('H:i')<$settings['closes_at'];$i++,$start=$start->addMinutes($settings['slot_minutes'])){
            try{$this->check($business,$service,$start,$staffId,$resourceId,$units);$slots[]=$start->format('H:i');}catch(ValidationException $e){}
        }return $slots;
    }
}
