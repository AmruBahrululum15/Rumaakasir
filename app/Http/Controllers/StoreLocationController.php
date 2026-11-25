<?php
namespace App\Http\Controllers;
use App\Models\Business;
use Illuminate\Http\Request;
class StoreLocationController extends Controller {
    public function update(Request $r){
        abort_unless(!$r->user()->hasRole('Super Admin')&&$r->user()->hasRole('Atasan')&&$r->user()->can('settings.edit'),403);
        if ($r->exists('maps_url')) {
            $d = $r->validate(['maps_url' => 'nullable|string|max:4096|url:https']);
            $url = trim($d['maps_url'] ?? '');
            $coordinates = $url === '' ? ['latitude' => null, 'longitude' => null] : app(\App\Services\GoogleMapsLocation::class)->coordinates($url);
            \Illuminate\Support\Facades\DB::transaction(function () use ($r, $coordinates, $url) {
                Business::findOrFail($r->user()->business_id)->forceFill($coordinates)->save();
                \App\Models\Setting::setValue('store_maps_url', $url);
            });
        } else {
            $d=$r->validate(['latitude'=>'nullable|required_with:longitude|numeric|between:-90,90','longitude'=>'nullable|required_with:latitude|numeric|between:-180,180']);
            Business::findOrFail($r->user()->business_id)->forceFill(['latitude'=>$d['latitude']??null,'longitude'=>$d['longitude']??null])->save();
            \App\Models\Setting::setValue('store_maps_url', '');
        }
        return back()->with('success','Lokasi toko diperbarui.');
    }
}
