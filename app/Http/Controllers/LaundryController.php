<?php
namespace App\Http\Controllers;
use App\Models\{BusinessService,Customer,LaundryOrder,LaundryPayment,LaundryStatusHistory};
use App\Services\LaundryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class LaundryController extends Controller {
    public function index(Request $request) {
        $d=$request->validate(['status'=>['nullable',Rule::in(array_keys(LaundryService::STATUSES))],'customer_id'=>'nullable|integer']);
        return view('premium.laundry',['orders'=>LaundryOrder::with('customer')->when($d['status'] ?? null,fn($q,$v)=>$q->where('status',$v))->when($d['customer_id'] ?? null,fn($q,$v)=>$q->where('customer_id',$v))->latest()->paginate(20)->withQueryString(),'services'=>BusinessService::where('kind','laundry')->get(),'customers'=>Customer::orderBy('name')->get(),'statuses'=>LaundryService::STATUSES]);
    }
    public function service(Request $request) {
        $d=$request->validate(['name'=>'required|string|max:150','unit'=>'required|in:kg,unit','price'=>'required|numeric|min:0.01|max:100000000']);
        BusinessService::create($d+['kind'=>'laundry']);return back()->with('success','Layanan dibuat.');
    }
    public function store(Request $request,LaundryService $service) {
        $d=$request->validate(['customer_id'=>'required|integer','service_id'=>'required|integer','quantity'=>'required|numeric|min:0.001|max:100000|decimal:0,3','estimated_at'=>'required|date|after_or_equal:now','notes'=>'nullable|string|max:2000']);
        $order=$service->create($request->user(),$d);return redirect()->route('laundry.show',$order);
    }
    public function show(LaundryOrder $order) {return view('premium.laundry-show',['order'=>$order->load('customer'),'history'=>LaundryStatusHistory::where('laundry_order_id',$order->id)->orderBy('id')->get(),'payments'=>LaundryPayment::where('laundry_order_id',$order->id)->orderBy('id')->get(),'statuses'=>LaundryService::STATUSES]);}
    public function status(Request $request,LaundryOrder $order,LaundryService $service) {$d=$request->validate(['status'=>['required',Rule::in(array_keys(LaundryService::STATUSES))]]);$service->transition($request->user(),$order->id,$d['status']);return back()->with('success','Status diperbarui.');}
    public function pay(Request $request,LaundryOrder $order,LaundryService $service) {$d=$request->validate(['amount'=>'required|numeric|min:0.01|max:1000000000|decimal:0,2','request_key'=>'required|uuid']);$service->pay($request->user(),$order->id,$d['request_key'],(float)$d['amount']);return back()->with('success','Pembayaran tunai dicatat.');}
}
