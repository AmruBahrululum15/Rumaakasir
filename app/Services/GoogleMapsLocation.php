<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class GoogleMapsLocation
{
    public function coordinates(string $url): array
    {
        for ($hop = 0; $hop < 5; $hop++) {
            $this->validateUrl($url);
            $decoded = rawurldecode($url);
            parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);
            $pair = null;
            if (preg_match('/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/', $decoded, $match)) {
                $pair = [$match[1], $match[2]];
            } else {
                foreach (['query', 'q', 'll'] as $key) {
                    if (is_string($query[$key] ?? null) && preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/', $query[$key], $match)) {
                        $pair = [$match[1], $match[2]];
                        break;
                    }
                }
            }
            if (!$pair && preg_match('/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?),/', $decoded, $match)) {
                $pair = [$match[1], $match[2]];
            }
            if ($pair) {
                [$latitude, $longitude] = array_map('floatval', $pair);
                if (abs($latitude) <= 90 && abs($longitude) <= 180) {
                    return compact('latitude', 'longitude');
                }
                $this->fail();
            }
            // Never follow redirects automatically: validate each destination first.
            try {
                $response = Http::connectTimeout(3)->timeout(6)->withoutRedirecting()->get($url);
            } catch (\Throwable $e) {
                $this->fail();
            }
            $next = $response->header('Location');
            if (!$response->redirect() || !$next || !str_starts_with($next, 'https://')) {
                $this->fail();
            }
            $url = $next;
        }
        $this->fail();
    }

    private function validateUrl(string $url): void
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $allowed = ['maps.app.goo.gl', 'goo.gl', 'google.com', 'www.google.com', 'maps.google.com', 'google.co.id', 'www.google.co.id', 'maps.google.co.id'];
        $path = $parts['path'] ?? '/';
        if (!$parts || ($parts['scheme'] ?? '') !== 'https' || !in_array($host, $allowed, true)
            || isset($parts['user']) || isset($parts['pass']) || (isset($parts['port']) && $parts['port'] !== 443)
            || ($host === 'goo.gl' && !str_starts_with($path, '/maps/'))
            || (in_array($host, ['google.com', 'www.google.com', 'google.co.id', 'www.google.co.id'], true) && !str_starts_with($path, '/maps'))) {
            $this->fail();
        }
    }

    private function fail(): never
    {
        throw ValidationException::withMessages(['maps_url' => 'Lokasi belum dapat dibaca. Salin link Google Maps dari tombol Bagikan pada lokasi toko, atau gunakan link panjang yang memuat koordinat, lalu coba kembali.']);
    }
}
