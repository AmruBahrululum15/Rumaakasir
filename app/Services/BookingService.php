<?php
namespace App\Services;
use App\Models\{Booking,Business,BusinessAuditEntry,BusinessService,Customer,User};
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class BookingService {
    public const STATUSES=['pending'=>'Menunggu Konfirmasi','awaiting_payment'=>'Menunggu Pembayaran','confirmed'=>'Dikonfirmasi','in_progress'=>'Sedang Berlangsung','completed'=>'Selesai','cancelled'=>'Dibatalkan','no_show'=>'Tidak Hadir'];
    public function save(User $actor,array $data,?int $id=null):Booking {
        return $this->saveForBusiness($actor->business,$data,$id,$actor);
    }
    public function saveForBusiness(Business $business,array $data,?int $id=null,?User $actor=null):Booking {
        app(PackageAccess::class)->ensureFeature('booking',$business);
        return DB::transaction(function()use($business,$actor,$data,$id){
            // Serialize scheduling within this tenant, including initially empty calendars.
            Business::whereKey($business->id)->lockForUpdate()->firstOrFail();
            $booking=$id ? Booking::where('business_id',$business->id)->lockForUpdate()->findOrFail($id) : new Booking;
            if($id && (int)$booking->customer_id!==(int)$data['customer_id'])throw ValidationException::withMessages(['customer_id'=>'Pelanggan reservasi existing tidak dapat diganti.']);
            if($id && !in_array($booking->status,['pending','awaiting_payment','confirmed']))throw ValidationException::withMessages(['booking'=>'Booking selesai/dibatalkan tidak dapat diubah.']);
            $service=BusinessService::where('business_id',$business->id)->where('kind','appointment')->where('is_active',true)->findOrFail($data['service_id']);
            Customer::where('business_id',$business->id)->findOrFail($data['customer_id']);
            $staff=$data['staff_id'] ?? null;
            if($staff)User::withoutGlobalScopes()->where('business_id',$business->id)->where('is_active',true)->findOrFail($staff);
            $start=CarbonImmutable::parse($data['booking_date'].' '.$data['start_time']);
            $units=(int)($data['capacity_units']??1);$resource=$data['resource_id']??null;
            [$end,$until]=app(ReservationSchedule::class)->check($business,$service,$start,$staff,$resource,$units,$id);
            if($id && (float)$booking->paid>0 && ((int)$booking->service_id!==(int)$service->id || (int)$booking->capacity_units!==$units))throw ValidationException::withMessages(['service_id'=>'Layanan/jumlah tidak dapat diganti setelah pembayaran.']);
            if(!$id || (!(float)$booking->paid && ((int)$booking->service_id!==(int)$service->id || (int)$booking->capacity_units!==$units))){
                $settings=app(ReservationSchedule::class)->settings($business);$total=round((float)$service->price*$units,2);$required=match($settings['payment_mode']){'full'=>$total,'deposit'=>round($total*$settings['deposit_percent']/100,2),default=>0};
                $customer=Customer::where('business_id',$business->id)->findOrFail($data['customer_id']);
                $booking->fill(['code'=>$booking->code??('RSV-'.\Illuminate\Support\Str::ulid()),'total'=>$total,'required_payment'=>$required,'payment_mode'=>$settings['payment_mode'],'payment_status'=>$total>0?'unpaid':'paid','status'=>$settings['auto_confirm']?($required>0?'awaiting_payment':'confirmed'):'pending','customer_name'=>$data['customer_name']??$customer->name,'customer_phone'=>$data['customer_phone']??$customer->phone,'customer_email'=>$data['customer_email']??null]);
            }
            $booking->fill(['customer_id'=>$data['customer_id'],'staff_id'=>$staff,'service_id'=>$service->id,'service_name'=>$service->name,'resource_id'=>$resource,'capacity_units'=>$units,'starts_at'=>$start,'ends_at'=>$end,'reserved_until'=>$until,'notes'=>$data['notes'] ?? null])->save();
            $bookingId=$booking->id;DB::afterCommit(fn()=>app(BookingNotifications::class)->send($bookingId,$id?'rescheduled':'created'));
            if ($actor) $this->audit($actor,$booking,$id?'updated':'created');return $booking;
        });
    }
    public function status(User $actor,int $id,string $status):void {
        app(PackageAccess::class)->ensureFeature('booking',$actor->business);
        DB::transaction(function()use($actor,$id,$status){
            Business::whereKey($actor->business_id)->lockForUpdate()->firstOrFail();$booking=Booking::where('business_id',$actor->business_id)->lockForUpdate()->findOrFail($id);
            $allowed=['pending'=>['confirmed','awaiting_payment','cancelled','no_show'],'awaiting_payment'=>['confirmed','cancelled','no_show'],'confirmed'=>['in_progress','completed','cancelled','no_show'],'in_progress'=>['completed','cancelled']];
            if(!in_array($status,$allowed[$booking->status]??[],true))throw ValidationException::withMessages(['status'=>'Perubahan status tidak diizinkan.']);
            if(in_array($status,['completed','no_show']) && $booking->starts_at->isFuture())throw ValidationException::withMessages(['status'=>'Jadwal belum dimulai.']);
            if($status==='in_progress' && $booking->starts_at->isFuture())throw ValidationException::withMessages(['status'=>'Jadwal belum dimulai.']);
            if($status==='confirmed' && (float)$booking->paid<(float)$booking->required_payment)throw ValidationException::withMessages(['status'=>'Pembayaran wajib belum terpenuhi.']);
            if($status==='completed' && $booking->payment_mode!=='free' && (float)$booking->paid<(float)$booking->total)throw ValidationException::withMessages(['status'=>'Lunasi sisa tagihan sebelum menyelesaikan reservasi.']);
            $booking->update(['status'=>$status]+($status==='in_progress'?['checked_in_at'=>now()]:[]));$this->audit($actor,$booking,$status);
            DB::afterCommit(fn()=>app(BookingNotifications::class)->send($id,$status));
        });
    }
    private function audit(User $actor,Booking $booking,string $action):void {BusinessAuditEntry::create(['user_id'=>$actor->id,'action'=>'booking.'.$action,'entity'=>'booking','entity_id'=>$booking->id,'metadata'=>['starts_at'=>$booking->starts_at->toDateTimeString(),'ends_at'=>$booking->ends_at->toDateTimeString(),'status'=>$booking->status],'created_at'=>now()]);}
}

