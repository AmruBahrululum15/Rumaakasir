<?php
namespace App\Http\Controllers;
use App\Models\CustomerAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Password};
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\Rule;

class CustomerAuthController extends Controller
{
    public function form(Request $request) { return view('portal.auth', ['mode'=>$request->route()->defaults['mode'] ?? 'login']); }
    public function register(Request $request)
    {
        if(is_string($request->email))$request->merge(['email'=>strtolower(trim($request->email))]);
        $data=$request->validate(['name'=>'required|string|max:120','phone'=>['bail','required','string','max:30',app(\App\Services\CustomerIdentity::class)->phoneRule()],'email'=>'required|email|max:190|unique:customer_accounts,email','password'=>['required','confirmed',PasswordRule::defaults()], 'address'=>'nullable|string|max:1000', 'province'=>['nullable', \Illuminate\Validation\Rule::requiredIf(fn()=> $request->filled('address') && $request->input('address_mode') !== 'structured'), 'string','max:100'], 'city'=>['nullable', \Illuminate\Validation\Rule::requiredIf(fn()=> $request->filled('address') && $request->input('address_mode') !== 'structured'), 'string','max:100'], 'district'=>'nullable|string|max:100', 'village'=>'nullable|string|max:100', 'postal_code'=>'nullable|digits:5', 'address_mode'=>'nullable|in:structured,manual', 'province_id'=>'nullable|string|max:20', 'city_id'=>'nullable|string|max:20', 'district_id'=>'nullable|string|max:20', 'village_id'=>'nullable|string|max:20']);
        if (!empty($data['address']) && ($data['address_mode'] ?? 'manual') === 'structured') {
            try {
                $data = array_merge($data, app(\App\Services\IndonesiaRegions::class)->resolve($data));
            } catch (\App\Exceptions\RegionServiceUnavailable $e) {
                throw \Illuminate\Validation\ValidationException::withMessages(['province_id'=>'Data wilayah belum tersedia. Coba kembali atau pilih input manual.']);
            }
        }
        $account = \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $account = CustomerAccount::create(\Illuminate\Support\Arr::only($data, ['name','phone','email','password']));
            $account->forceFill(['last_login_at'=>now()])->save();
            if (!empty($data['address'])) {
                $account->addresses()->create(array_merge(
                    \Illuminate\Support\Arr::only($data, ['address','province','city','district','village','postal_code']),
                    ['label'=>'Rumah', 'recipient_name'=>$account->name, 'phone'=>$account->phone, 'is_default'=>true]
                ));
            }
            return $account;
        });
        auth('customer')->login($account); $request->session()->regenerate();
        return redirect()->to($request->session()->pull('customer.intended',route('customer.dashboard',[],false)));
    }
    public function login(Request $request)
    {
        // Keep the old customer email payload compatible; staff login is untouched.
        $request->merge(['identifier'=>$request->input('identifier',$request->input('email'))]);
        $data=$request->validate(['identifier'=>'required|string|max:190','password'=>'required|string']);
        $account=app(\App\Services\CustomerIdentity::class)->resolve($data['identifier']);
        if (!$account || !auth('customer')->attempt(['id'=>$account->id,'password'=>$data['password'],'status'=>'active'])) {
            if(!$account)Hash::check($data['password'],'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
            return back()->withErrors(['identifier'=>'Email/nomor HP atau password tidak sesuai.'])->onlyInput('identifier');
        }
        $account->forceFill(['last_login_at'=>now()])->save();
        $request->session()->regenerate(); return redirect()->to($request->session()->pull('customer.intended',route('customer.dashboard',[],false)));
    }
    public function logout(Request $request)
    {
        auth('customer')->logout(); $request->session()->regenerate(); $request->session()->regenerateToken();
        return redirect()->route('customer.login');
    }
    public function forgot(Request $request)
    {
        $request->validate(['email'=>'required|email']);
        try { Password::broker('customers')->sendResetLink($request->only('email')); }
        catch (\Throwable $e) { \Log::warning('Customer reset email delivery failed', ['exception'=>get_class($e)]); }
        return back()->with('success','Jika akun tersebut valid, instruksi reset password akan dikirim.');
    }
    public function reset(Request $request)
    {
        $data=$request->validate(['email'=>'required|email','token'=>'required|string','password'=>['required','confirmed',PasswordRule::defaults()]]);
        $status=Password::broker('customers')->reset($data, function ($account, $password) {
            $account->forceFill(['password'=>Hash::make($password),'remember_token'=>\Illuminate\Support\Str::random(60)])->save();
        });
        return $status===Password::PASSWORD_RESET ? redirect()->route('customer.login')->with('success','Password berhasil diubah.') : back()->withErrors(['email'=>'Tautan reset tidak valid atau sudah kedaluwarsa.']);
    }
    public function profile(Request $request)
    {
        if ($request->isMethod('patch')) {
            $account=auth('customer')->user();
            $phoneRules=['bail','required','string','max:30'];
            if($request->input('phone')!==$account->phone)$phoneRules[]=app(\App\Services\CustomerIdentity::class)->phoneRule($account->id);
            $data=$request->validate(['name'=>'required|string|max:120','phone'=>$phoneRules]);
            $account->update($data); return back()->with('success','Profil diperbarui.');
        }
        return view('portal.auth',['mode'=>'profile']);
    }
}
