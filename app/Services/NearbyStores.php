<?php
namespace App\Services;
use App\Models\Business;
class NearbyStores {
    public static function distance(float $lat,float $lng,float $otherLat,float $otherLng):float {
        $a=sin(deg2rad($otherLat-$lat)/2)**2+cos(deg2rad($lat))*cos(deg2rad($otherLat))*sin(deg2rad($otherLng-$lng)/2)**2;
        return 6371*2*asin(sqrt(min(1,max(0,$a))));
    }
    public function search(array $d=[]){
        $stores=Business::where('is_active',true)->whereNotNull('business_type')->where('subscription_plan','premium')->where('subscription_status','active')
            ->where(fn($q)=>$q->whereNull('subscription_ends_at')->orWhere('subscription_ends_at','>',now()))
            ->when($d['q']??null,function($q,$v){$q->where(function($q)use($v){$q->where('name','like','%'.$v.'%')->orWhereExists(fn($p)=>$p->selectRaw('1')->from('products')->whereColumn('products.business_id','businesses.id')->where('products.is_active',true)->where('products.name','like','%'.$v.'%'))->orWhereExists(fn($p)=>$p->selectRaw('1')->from('business_services')->whereColumn('business_services.business_id','businesses.id')->where('business_services.is_active',true)->where('business_services.name','like','%'.$v.'%'));});})
            ->when($d['type']??null,fn($q,$v)=>$q->where('business_type',$v))->when($d['city']??null,fn($q,$v)=>$q->where('address','like','%'.$v.'%'))->orderBy('name')->get()->filter(fn($b)=>app(CustomerPortal::class)->active($b));
        if(isset($d['latitude'],$d['longitude'])){
            foreach($stores as $s)$s->distance_km=$s->latitude!==null&&$s->longitude!==null?self::distance($d['latitude'],$d['longitude'],$s->latitude,$s->longitude):null;
            $stores=$stores->filter(fn($s)=>!isset($d['radius'])||($s->distance_km!==null&&$s->distance_km<=$d['radius']))->sortBy(fn($s)=>$s->distance_km??INF);
        }
        return $stores->values();
    }
    public function markers($stores):array { return $stores->filter(fn($b)=>$b->latitude!==null&&$b->longitude!==null)->map(fn($b)=>['name'=>$b->name,'type'=>config('business_modules.types.'.$b->business_type.'.label',ucfirst($b->business_type)),'address'=>$b->address,'latitude'=>(float)$b->latitude,'longitude'=>(float)$b->longitude,'distance'=>$b->distance_km!==null?round($b->distance_km,2):null,'url'=>route('customer.store',$b->slug)])->values()->all(); }
}
