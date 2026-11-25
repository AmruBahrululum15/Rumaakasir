<?php
namespace App\Http\Controllers;
use App\Models\{Promotion,PromotionUsage};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class PromotionController extends Controller {
    public function index() {return view('premium.promotions',['promotions'=>Promotion::latest()->paginate(20),'editing'=>new Promotion]);}
    public function edit(Promotion $promotion) {return view('premium.promotions',['promotions'=>Promotion::latest()->paginate(20),'editing'=>$promotion]);}
    private function data(Request $request,?Promotion $promotion=null): array {
        $request->merge(['code'=>filled($request->code)?strtoupper(trim($request->code)):null]);
        return $request->validate(['name'=>'required|string|max:150','code'=>['nullable','string','max:50','regex:/^[A-Z0-9_-]+$/',Rule::unique('promotions')->where('business_id',$request->user()->business_id)->ignore($promotion?->id)],
            'discount_type'=>'required|in:percentage,fixed','discount_value'=>['required','numeric','min:0.01','max:'.($request->discount_type==='percentage'?100:1000000000)],'min_transaction'=>'required|numeric|min:0|max:1000000000','max_discount'=>'nullable|numeric|min:0.01|max:1000000000',
            'start_at'=>'required|date','end_at'=>'required|date|after:start_at','is_active'=>'required|boolean','usage_limit'=>'nullable|integer|min:1|max:1000000','per_customer_limit'=>'nullable|integer|min:1|max:1000000']);
    }
    public function store(Request $request) {Promotion::create($this->data($request));return redirect()->route('promotions.index')->with('success','Promo dibuat.');}
    public function update(Request $request,Promotion $promotion) {$promotion->update($this->data($request,$promotion));return redirect()->route('promotions.index')->with('success','Promo diperbarui.');}
    public function history(Promotion $promotion) {return view('premium.promotion-history',['promotion'=>$promotion,'usages'=>PromotionUsage::where('promotion_id',$promotion->id)->latest('id')->paginate(30)]);}
}
