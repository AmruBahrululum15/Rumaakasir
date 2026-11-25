<?php
namespace App\Http\Controllers;
use App\Models\{CustomerAccount,CustomerAddress};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Storage};
use Illuminate\Validation\Rule;
class CustomerProfileController extends Controller {
    public function show(){return view('portal.profile',['account'=>auth('customer')->user()->load('addresses')]);}
    public function update(Request $r){
        $a=auth('customer')->user();$rules=['required','string','max:30'];
        if($r->phone!==$a->phone)$rules[]=app(\App\Services\CustomerIdentity::class)->phoneRule($a->id);
        if(is_string($r->email))$r->merge(['email'=>strtolower(trim($r->email))]);
        $d=$r->validate(['name'=>'required|string|max:120','phone'=>$rules,'email'=>['sometimes','required','email','max:190',Rule::unique('customer_accounts')->ignore($a->id)],'birth_date'=>'nullable|date_format:Y-m-d|before_or_equal:today','avatar'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:2048']);
        unset($d['avatar']);if(isset($d['email'])&&$d['email']!==$a->email)$d['email_verified_at']=null;
        if($r->hasFile('avatar'))$d['avatar_path']=$r->file('avatar')->store('customer-avatars/'.$a->id,'local');
        $a->forceFill($d)->save();return back()->with('success','Profil diperbarui.');
    }
    public function avatar(){ $a=auth('customer')->user();abort_unless($a->avatar_path&&Storage::disk('local')->exists($a->avatar_path),404);return response()->file(Storage::disk('local')->path($a->avatar_path),['Cache-Control'=>'private, no-store'])->setPrivate(); }
    public function address(Request $r,?int $address=null){
        $d=$r->validate(['label'=>['required',Rule::in(['Rumah','Kantor','Lainnya'])],'recipient_name'=>'required|string|max:120','phone'=>'required|string|max:30','address'=>'required|string|max:1000','province'=>'required|string|max:100','city'=>'required|string|max:100','district'=>'nullable|string|max:100','village'=>'nullable|string|max:100','postal_code'=>'nullable|string|max:15','notes'=>'nullable|string|max:500','latitude'=>'nullable|required_with:longitude|numeric|between:-90,90','longitude'=>'nullable|required_with:latitude|numeric|between:-180,180','is_default'=>'sometimes|boolean']);
        DB::transaction(function()use($d,$address){
            $a=CustomerAccount::whereKey(auth('customer')->id())->lockForUpdate()->firstOrFail();
            $row=$address?$a->addresses()->findOrFail($address):$a->addresses()->make();
            $d['is_default']=($d['is_default']??false)||!$a->addresses()->exists()||$row->is_default;
            if($d['is_default'])$a->addresses()->update(['is_default'=>false]);
            $row->fill($d)->save();
        });return back()->with('success','Alamat disimpan.');
    }
}
