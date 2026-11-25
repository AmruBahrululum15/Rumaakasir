<?php
namespace App\Http\Controllers;
use App\Models\{BusinessAuditEntry,Sale,SaleApproval};
use App\Services\SaleApprovalService;
use Illuminate\Http\Request;
class SaleApprovalController extends Controller {
    public function index() {return view('premium.sale-approvals',['requests'=>SaleApproval::whereIn('sale_id',Sale::select('id'))->with(['sale','requester'])->latest()->paginate(20),'sales'=>Sale::where('payment_method','cash')->whereIn('payment_status',['PAID','PENDING','CASH_PENDING'])->latest()->limit(100)->get(),'audit'=>BusinessAuditEntry::where('entity','sale')->whereIn('entity_id',Sale::select('id'))->latest('id')->limit(30)->get()]);}
    public function store(Request $request,SaleApprovalService $service) {
        $d=$request->validate(['sale_id'=>'required|integer','type'=>'required|in:refund,void','reason'=>'required|string|min:5|max:2000']);
        $service->request($request->user(),$d['sale_id'],$d['type'],$d['reason']);return back()->with('success','Pengajuan dikirim.');
    }
    public function review(Request $request,SaleApproval $approval,SaleApprovalService $service) {
        $d=$request->validate(['decision'=>'required|in:approve,reject','review_note'=>'required|string|min:5|max:2000','cash_returned'=>'required_if:decision,approve|accepted_if:decision,approve']);
        $service->review($request->user(),$approval->id,$d['decision']==='approve',$d['review_note']);return back()->with('success','Pengajuan diproses.');
    }
}
