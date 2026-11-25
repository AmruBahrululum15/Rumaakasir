<?php
namespace App\Http\Controllers;
use App\Services\{CustomerPaymentMethods,CustomerPortal};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class BusinessPaymentSettingController extends Controller {
    private function business(Request $r){abort_unless(!$r->user()->hasRole('Super Admin')&&$r->user()->hasRole('Atasan')&&$r->user()->can('settings.edit'),403);return $r->user()->business;}
    public function edit(Request $r,CustomerPaymentMethods $methods){$business=$this->business($r);$settings=$methods->settings($business);return view('settings.customer-payments',compact('settings','business'));}
    public function update(Request $r,CustomerPaymentMethods $methods){
        $business=$this->business($r);
        $d=$r->validate(['cash_enabled'=>'required|boolean','qris_enabled'=>'required|boolean','va_enabled'=>'required|boolean','qris_mode'=>'required|in:manual,midtrans','qris_image'=>'nullable|image|mimes:png,jpg,jpeg,webp|max:4096','midtrans_server_key'=>'nullable|string|max:500','midtrans_production'=>'required|boolean','va_banks'=>'nullable|array','va_banks.*'=>Rule::in(array_keys(CustomerPaymentMethods::BANKS))]);
        $s=$methods->settings($business);
        if(!filled($d['midtrans_server_key']??null))unset($d['midtrans_server_key']);
        $d['va_banks']=$d['va_banks']??[];unset($d['qris_image']);
        if($r->hasFile('qris_image'))$d['qris_path']=$r->file('qris_image')->store('business-qris/'.$business->id,'local');
        $s->fill($d);
        if(($s->va_enabled||($s->qris_enabled&&$s->qris_mode==='midtrans'))&&!filled($s->midtrans_server_key))throw \Illuminate\Validation\ValidationException::withMessages(['midtrans_server_key'=>'Isi Server Key Midtrans milik toko ini.']);
        if($s->va_enabled&&!$s->va_banks)throw \Illuminate\Validation\ValidationException::withMessages(['va_banks'=>'Pilih bank VA yang aktif pada merchant Anda.']);
        if($s->qris_enabled&&$s->qris_mode==='manual'&&!$s->qris_path)throw \Illuminate\Validation\ValidationException::withMessages(['qris_image'=>'Unggah QRIS milik toko ini.']);
        $s->save();return back()->with('success','Pengaturan pembayaran pelanggan disimpan.');
    }
}
