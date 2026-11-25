<?php
namespace App\Http\Controllers;
use App\Models\{Business,Product};
use App\Services\{CustomerPortal,PortalOrderService};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class PortalCartController extends Controller
{
    private function key(Business $business):string {return 'portal_cart.'.auth('customer')->id().'.'.$business->id;}
    public function index(Request $r,Business $business,CustomerPortal $portal)
    {
        $portal->requireActive($business);$key=$this->key($business);
        $cart=$r->session()->get($key.'.items',[]);$token=$r->session()->get($key.'.token');
        if(!$token){$token=(string)Str::uuid();$r->session()->put($key.'.token',$token);}
        $products=Product::withoutGlobalScopes()->where('business_id',$business->id)->where('is_active',true)->whereIn('id',array_keys($cart))->get();
        $methods=app(\App\Services\CustomerPaymentMethods::class)->available($business);
        return view('portal.cart',compact('business','cart','products','token','methods'));
    }
    public function add(Request $r,Business $business,CustomerPortal $portal)
    {
        $portal->requireActive($business);abort_unless(in_array($business->business_type,PortalOrderService::TYPES),403);
        $d=$r->validate(['product_id'=>'required|integer','quantity'=>'required|integer|min:0|max:100']);
        $product=Product::withoutGlobalScopes()->where('business_id',$business->id)->where('is_active',true)->findOrFail($d['product_id']);
        $key=$this->key($business);$cart=$r->session()->get($key.'.items',[]);
        if($d['quantity']==0)unset($cart[$product->id]);else $cart[$product->id]=(int)$d['quantity'];
        abort_if(count($cart)>50,422);$r->session()->put($key.'.items',$cart);$r->session()->put($key.'.token',(string)Str::uuid());
        return redirect()->route('customer.cart',$business->slug);
    }
    public function checkout(Request $r,Business $business,PortalOrderService $orders)
    {
        $d=$r->validate(['token'=>'required|uuid','voucher'=>'nullable|string|max:60','payment_method'=>'sometimes|required|in:cash,qris,va']);$key=$this->key($business);
        abort_unless(hash_equals((string)$r->session()->get($key.'.token'),$d['token']),419);
        $sale=$orders->create($business,auth('customer')->user(),$r->session()->get($key.'.items',[]),$d['token'],$d['voucher']??null,$d['payment_method']??'cash');
        $r->session()->forget($key.'.items');
        return redirect()->route('customer.orders.show',$sale->id)->with('success','Pesanan diterima. Ikuti petunjuk pembayaran di bawah.');
    }
}
