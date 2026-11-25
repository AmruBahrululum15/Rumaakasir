<?php
namespace App\Http\Controllers;
use App\Models\{Outlet,Sale,User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class OutletController extends Controller {
    public function index(){return view('premium.outlets',['outlets'=>Outlet::orderBy('name')->get(),'users'=>User::whereDoesntHave('roles',fn($q)=>$q->where('name','Super Admin'))->orderBy('name')->get(),'summary'=>Sale::withoutGlobalScope('outlet')->where('payment_status','PAID')->select('outlet_id')->selectRaw('COUNT(*) AS transactions, SUM(total) AS revenue')->groupBy('outlet_id')->get()->keyBy('outlet_id'),'selected'=>app(\App\Tenancy\OutletContext::class)->id()]);}
    private function data(Request $r):array{return $r->validate(['name'=>'required|string|max:150','address'=>'nullable|string|max:1000','phone'=>'nullable|string|max:30','is_active'=>'required|boolean']);}
    public function store(Request $r){Outlet::create($this->data($r));return back()->with('success','Outlet dibuat. Data lama tidak dipindahkan.');}
    public function update(Request $r,Outlet $outlet){$d=$this->data($r);if(!$d['is_active'] && User::where('outlet_id',$outlet->id)->exists())return back()->withErrors(['is_active'=>'Lepaskan penugasan pengguna sebelum menonaktifkan outlet.']);$outlet->update($d);return back()->with('success','Outlet diperbarui.');}
    public function select(Request $r){$d=$r->validate(['outlet_id'=>'nullable|integer']);$id=$d['outlet_id']??null;if($id)Outlet::where('is_active',true)->findOrFail($id);$r->session()->put('outlet.'.$r->user()->business_id,$id);return redirect()->route('outlets.index');}
    public function assign(Request $r,User $user){
        abort_if($user->hasRole('Super Admin'),403);$d=$r->validate(['outlet_id'=>'nullable|integer']);$id=$d['outlet_id']??null;
        DB::transaction(function()use($id,$user){if($id)Outlet::where('is_active',true)->lockForUpdate()->findOrFail($id);$user->forceFill(['outlet_id'=>$id])->save();});return back()->with('success','Penugasan outlet disimpan.');
    }
}
