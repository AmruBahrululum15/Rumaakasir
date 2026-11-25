<?php
namespace App\Http\Controllers;
use App\Models\{Outlet,OutletStock,OutletStockTransfer,Product};
use App\Services\OutletInventory;
use Illuminate\Http\Request;
class OutletInventoryController extends Controller {
    public function index(){return view('premium.outlet-inventory',['outlets'=>Outlet::where('is_active',true)->get(),'products'=>Product::orderBy('name')->get(),'stocks'=>OutletStock::with(['product','outlet'])->paginate(30),'transfers'=>OutletStockTransfer::latest('id')->limit(30)->get()]);}
    public function store(Request $r,OutletInventory $s){$d=$r->validate(['product_id'=>'required|integer','from_outlet_id'=>'nullable|integer','to_outlet_id'=>'nullable|integer','quantity'=>'required|integer|min:1|max:1000000','request_key'=>'required|uuid']);$s->transfer($r->user(),$d);return back()->with('success','Transfer stok dicatat.');}
}
