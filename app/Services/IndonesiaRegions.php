<?php

namespace App\Services;

use App\Exceptions\RegionServiceUnavailable;
use Illuminate\Support\Facades\{Cache, Http};
use Illuminate\Validation\ValidationException;

class IndonesiaRegions
{
    public const PATTERNS = [
        'province' => '/^\d{2}$/D', 'city' => '/^\d{2}\.\d{2}$/D',
        'district' => '/^\d{2}\.\d{2}\.\d{2}$/D', 'village' => '/^\d{2}\.\d{2}\.\d{2}\.\d{4}$/D',
    ];

    public function listing(string $level, ?string $parent = null): array
    {
        $parents = ['city'=>'province', 'district'=>'city', 'village'=>'district'];
        if (!isset(self::PATTERNS[$level]) || ($level !== 'province' && !preg_match(self::PATTERNS[$parents[$level]], $parent ?? ''))) {
            throw ValidationException::withMessages(['parent'=>'Wilayah induk tidak valid.']);
        }
        $path = match ($level) {
            'province'=>'provinces.json', 'city'=>"regencies/{$parent}.json",
            'district'=>"districts/{$parent}.json", 'village'=>"villages/{$parent}.json",
        };
        $base = rtrim(config('indonesia_regions.base_url'), '/');
        $key = 'indonesia-regions:v2:'.hash('sha256', $base.'/'.$path);
        return Cache::remember($key, config('indonesia_regions.cache_seconds'), function () use ($base, $path, $level, $parent) {
            try {
                $response = Http::acceptJson()->connectTimeout(3)->timeout(8)->withoutRedirecting()->get($base.'/'.$path);
                if (!$response->successful()) throw new RegionServiceUnavailable();
                $rows = $response->json('data');
                if (!is_array($rows) || !array_is_list($rows) || !$rows) throw new RegionServiceUnavailable();
                $result = [];
                foreach ($rows as $row) {
                    $id = $row['id'] ?? null; $name = $row['name'] ?? null;
                    if (!is_string($id) || !preg_match(self::PATTERNS[$level], $id) || !is_string($name) || trim($name)==='' || mb_strlen($name)>255
                        || ($parent !== null && !str_starts_with($id, $parent.'.'))) throw new RegionServiceUnavailable();
                    $codes = $row['postal_code'] ?? [];
                    $codes = is_array($codes) ? $codes : [$codes];
                    $codes = array_values(array_unique(array_map('strval', array_filter($codes, fn($code)=>is_scalar($code) && preg_match('/^\d{5}$/D', (string)$code)))));
                    $result[] = ['id'=>$id, 'name'=>$name, 'postal_codes'=>$codes];
                }
                return $result;
            } catch (\Illuminate\Http\Client\ConnectionException | \JsonException $exception) {
                throw new RegionServiceUnavailable('Data wilayah belum tersedia.', 0, $exception);
            }
        });
    }

    /** Resolve names from the provider, never from submitted display labels. */
    public function resolve(array $input): array
    {
        $result = []; $parent = null; $village = null;
        foreach (array_keys(self::PATTERNS) as $level) {
            $selected = collect($this->listing($level, $parent))->firstWhere('id', $input[$level.'_id'] ?? null);
            if (!$selected) throw ValidationException::withMessages([$level.'_id'=>'Pilihan wilayah tidak sesuai dengan wilayah induknya.']);
            $result[$level.'_id'] = $selected['id'];
            $result[$level] = $selected['name'];
            $parent = $selected['id']; $village = $selected;
        }
        $postalCode = $input['postal_code'] ?? null;
        if ($village['postal_codes']) {
            if (!$postalCode && count($village['postal_codes'])===1) $postalCode = $village['postal_codes'][0];
            if (!in_array($postalCode, $village['postal_codes'], true)) {
                throw ValidationException::withMessages(['postal_code'=>'Kode pos tidak sesuai kelurahan/desa yang dipilih.']);
            }
        }
        return [...$result, 'postal_code'=>$postalCode, 'address_verified'=>true];
    }
}
