<?php

namespace Tests\Feature;

use App\Models\StoreRegistration;
use App\Services\IndonesiaRegions;
use Illuminate\Support\Facades\{Cache, Http, Mail, Notification};
use Illuminate\Support\Str;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class RegistrationRegionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests(); Mail::fake(); Notification::fake(); Cache::flush();
        config(['indonesia_regions.base_url'=>'https://regions.example.test/v2']);
    }

    private function fakeRegions(?string $postal = '16820'): void
    {
        Http::fake([
            '*/provinces.json'=>Http::response(['data'=>[['id'=>'32','name'=>'Jawa Barat'],['id'=>'31','name'=>'DKI Jakarta']]]),
            '*/regencies/32.json'=>Http::response(['data'=>[['id'=>'32.01','name'=>'Kabupaten Bogor']]]),
            '*/districts/32.01.json'=>Http::response(['data'=>[['id'=>'32.01.07','name'=>'Cileungsi']]]),
            '*/villages/32.01.07.json'=>Http::response(['data'=>[['id'=>'32.01.07.2001','name'=>'Pasirangin','postal_code'=>$postal]]]),
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'submission_token'=>(string)Str::uuid(), 'store_name'=>'Toko Alamat', 'business_type'=>'retail',
            'store_phone'=>'081234567890', 'store_email'=>'store@example.test', 'address'=>'Jalan Toko 123, RT 01 RW 02',
            'address_mode'=>'structured', 'province_id'=>'32', 'city_id'=>'32.01', 'district_id'=>'32.01.07', 'village_id'=>'32.01.07.2001',
            'province'=>'Forged Province', 'city'=>'Forged City', 'district'=>'Forged District', 'village'=>'Forged Village', 'postal_code'=>'16820',
            'owner_name'=>'Pemilik Baru', 'owner_username'=>'pemilik_baru', 'owner_email'=>'owner@example.test', 'owner_phone'=>'081234567891', 'requested_plan'=>'free', 'terms'=>'1',
        ], $overrides);
    }

    private function submit(array $data)
    {
        return $this->withSession(['store_registration_token'=>$data['submission_token']])->post(route('store-registration.store'), $data);
    }

    public function test_dependent_endpoints_supply_names_and_postal_codes_and_cache_results(): void
    {
        $this->fakeRegions();
        foreach ([['province',null,'32'],['city','32','32.01'],['district','32.01','32.01.07'],['village','32.01.07','32.01.07.2001']] as [$level,$parent,$id]) {
            $this->getJson(route('store-registration.regions', array_filter(compact('level','parent'))))->assertOk()->assertJsonPath('data.0.id',$id);
        }
        $this->getJson(route('store-registration.regions',['level'=>'village','parent'=>'32.01.07']))->assertOk()->assertJsonPath('data.0.postal_codes.0','16820');
        Http::assertSentCount(4);
        $this->getJson(route('store-registration.regions',['level'=>'city','parent'=>'../secret']))->assertUnprocessable();
        Http::assertSentCount(4);
        $this->get(route('store-registration.create'))->assertOk()->assertSee('registration-address.js')->assertSee('Kelurahan / Desa');
    }

    public function test_structured_submission_resolves_names_on_server_and_fills_known_postal_code(): void
    {
        $this->fakeRegions();
        $this->submit($this->payload(['postal_code'=>null]))->assertRedirect(route('store-registration.success'));
        $registration = StoreRegistration::firstOrFail();
        $this->assertSame('Jawa Barat',$registration->province);
        $this->assertSame('Kabupaten Bogor',$registration->city);
        $this->assertSame('Cileungsi',$registration->district);
        $this->assertSame('Pasirangin',$registration->village);
        $this->assertSame('32.01.07.2001',$registration->village_id);
        $this->assertSame('16820',$registration->postal_code);
        $this->assertSame('Jalan Toko 123, RT 01 RW 02',$registration->address);
        $this->assertTrue($registration->address_verified);
        $this->assertSame('pending',$registration->status);
        $this->assertNull($registration->business_id);
    }

    public function test_invalid_region_relationships_and_wrong_postal_code_are_rejected(): void
    {
        $this->fakeRegions();
        foreach (['city_id'=>'31.01','district_id'=>'32.02.07','village_id'=>'32.01.08.2001','postal_code'=>'99999'] as $field=>$value) {
            $this->submit($this->payload([$field=>$value]))->assertSessionHasErrors($field);
        }
        $this->assertDatabaseCount('store_registrations',0);
    }

    public function test_unavailable_api_allows_manual_submission_without_trusted_ids(): void
    {
        Http::fake(['*'=>Http::response('Unavailable',503)]);
        $this->getJson(route('store-registration.regions',['level'=>'province']))->assertStatus(503)->assertJsonPath('manual_available',true);
        $this->submit($this->payload())->assertSessionHasErrors('address_mode');
        $manual = $this->payload(['address_mode'=>'manual','province_id'=>null,'city_id'=>null,'district_id'=>null,'village_id'=>null,
            'province'=>'Jawa Barat','city'=>'Kabupaten Bogor','district'=>'Cileungsi','village'=>'Pasirangin']);
        $this->submit($manual)->assertRedirect(route('store-registration.success'));
        $registration = StoreRegistration::firstOrFail();
        $this->assertFalse($registration->address_verified);
        $this->assertNull($registration->province_id);
        $this->assertNull($registration->village_id);
        $this->assertSame('Pasirangin',$registration->village);
    }

    public function test_manual_mode_cannot_smuggle_ids_or_verification_and_postal_requires_five_digits(): void
    {
        $this->submit($this->payload(['address_mode'=>'manual','address_verified'=>true,'postal_code'=>'123']))
            ->assertSessionHasErrors(['province_id','city_id','district_id','village_id','address_verified','postal_code']);
        $this->assertDatabaseCount('store_registrations',0);
        Http::assertNothingSent();
    }

    public function test_missing_postal_reference_accepts_manual_five_digit_code(): void
    {
        $this->fakeRegions(null);
        $this->submit($this->payload(['postal_code'=>'16821']))->assertRedirect(route('store-registration.success'));
        $this->assertSame('16821',StoreRegistration::firstOrFail()->postal_code);
    }

    public function test_malformed_upstream_response_is_not_cached_and_fails_gracefully(): void
    {
        Http::fakeSequence()->push(['data'=>[['id'=>'32','name'=>'Jawa Barat']]])
            ->push(['data'=>[['id'=>'31.01','name'=>'Wrong Parent']]])
            ->push(['data'=>[['id'=>'32.01','name'=>'Kabupaten Bogor']]]);
        app(IndonesiaRegions::class)->listing('province');
        $this->getJson(route('store-registration.regions',['level'=>'city','parent'=>'32']))->assertStatus(503);
        $this->getJson(route('store-registration.regions',['level'=>'city','parent'=>'32']))->assertOk()->assertJsonPath('data.0.id','32.01');
        Http::assertSentCount(3);
    }
}
