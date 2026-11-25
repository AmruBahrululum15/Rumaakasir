<?php
namespace App\Tenancy;
use App\Models\{Business,Outlet};
use App\Services\PackageAccess;
use Illuminate\Http\Request;
class OutletContext {
    private ?int $outletId=null;
    public function id():?int{return $this->outletId;}
    public function clear():void{$this->outletId=null;}
    public function resolve(Request $request,Business $business):void {
        $this->clear();if($business->business_type===null)return;
        $user=$request->user();
        // Assigned staff retain their data boundary even when Premium expires.
        $id=$user->hasRole('Atasan') ? null : $user->outlet_id;
        if(!$id && $user->hasRole('Atasan') && app(PackageAccess::class)->allows('multi_outlet',$business))$id=$request->session()->get('outlet.'.$business->id);
        if($id){
            $outlet=Outlet::where('business_id',$business->id)->where('is_active',true)->find($id);
            if(!$outlet && $user->hasRole('Atasan')){$request->session()->forget('outlet.'.$business->id);return;}
            abort_unless($outlet,403,'Outlet tidak aktif atau tidak tersedia.');$this->outletId=(int)$outlet->id;
        }
    }
}
