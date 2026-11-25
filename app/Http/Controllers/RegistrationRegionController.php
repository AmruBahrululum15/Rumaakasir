<?php

namespace App\Http\Controllers;

use App\Exceptions\RegionServiceUnavailable;
use App\Services\IndonesiaRegions;
use Illuminate\Http\Request;

class RegistrationRegionController extends Controller
{
    public function __invoke(Request $request, IndonesiaRegions $regions)
    {
        $data = $request->validate(['level'=>'required|in:province,city,district,village', 'parent'=>'nullable|string|max:20']);
        try {
            return response()->json(['data'=>$regions->listing($data['level'], $data['parent'] ?? null)]);
        } catch (RegionServiceUnavailable $exception) {
            return response()->json(['message'=>'Data wilayah sedang tidak tersedia. Coba lagi atau gunakan input manual.', 'manual_available'=>true], 503);
        }
    }
}
