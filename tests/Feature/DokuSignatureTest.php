<?php

namespace Tests\Feature;

use App\Services\DokuService;
use Tests\TestCase;

class DokuSignatureTest extends TestCase
{
    public function test_guzzle_transport_receives_the_exact_signed_body_and_single_headers(): void
    {
        config(['doku.env'=>'sandbox', 'doku.base_url'=>'https://api-sandbox.doku.com',
            'doku.client_id'=>'transport-client', 'doku.secret_key'=>'transport-secret']);
        $user = new \App\Models\User(['name'=>'Owner / Toko & Café', 'email'=>'owner@example.test']);
        $user->id=1; $user->setRelation('business', new \App\Models\Business());
        $order = new \App\Models\SubscriptionOrder(['order_id'=>'SUB-TRANSPORT-TEST', 'request_id'=>'transport-request',
            'plan_id'=>1, 'plan_name'=>'Premium', 'billing_cycle'=>'monthly', 'amount'=>49000]);
        $captured = [];
        $handler = function (\Psr\Http\Message\RequestInterface $request, array $options) use (&$captured, $order) {
            $captured[]=$request;
            return \GuzzleHttp\Promise\Create::promiseFor(new \GuzzleHttp\Psr7\Response(200, ['Content-Type'=>'application/json'], json_encode(['response'=>[
                'order'=>['invoice_number'=>$order->order_id,'amount'=>49000],
                'payment'=>['url'=>'https://sandbox.doku.com/checkout-link-v2/test','expired_date'=>now()->addHour()->setTimezone('Asia/Jakarta')->format('YmdHis')],
            ]])));
        };
        // Run Laravel/Guzzle's real request preparation, replacing only network I/O.
        \Illuminate\Support\Facades\Http::globalOptions(['handler'=>\GuzzleHttp\HandlerStack::create($handler)]);
        app(DokuService::class)->createPayment($order,$user);
        $this->assertCount(1,$captured);
        $request=$captured[0];$raw=(string)$request->getBody();
        $this->assertSame('https://api-sandbox.doku.com/checkout/v1/payment',(string)$request->getUri());
        $this->assertSame('application/json',$request->getHeaderLine('Content-Type'));
        foreach(['Client-Id','Request-Id','Request-Timestamp','Signature'] as $header) $this->assertCount(1,$request->getHeader($header));
        $this->assertSame($user->name,json_decode($raw,true)['customer']['name']);
        $canonical='Client-Id:transport-client'."\nRequest-Id:transport-request\nRequest-Timestamp:".$request->getHeaderLine('Request-Timestamp')
            ."\nRequest-Target:/checkout/v1/payment\nDigest:".base64_encode(hash('sha256',$raw,true));
        $this->assertSame('HMACSHA256='.base64_encode(hash_hmac('sha256',$canonical,'transport-secret',true)),$request->getHeaderLine('Signature'));
    }

    public function test_fixed_checkout_vector_matches_independent_python_hmac(): void
    {
        // DOKU non-SNAP sample algorithm and public example credentials;
        // expected output independently calculated with Python hashlib/hmac.
        config(['doku.client_id' => 'yourClientId', 'doku.secret_key' => 'secret-key-from-jokul-back-office']);
        $service = app(DokuService::class);
        $signature = $service->signature('{}', 'yourRequestId', '2020-10-21T03:38:28Z', '/checkout/v1/payment');
        $this->assertSame('HMACSHA256=ORkZj+5D/dB1n88u2+B9V8QQHtJiGd3lJkSlIwioLYw=', $signature);
        $this->assertNotSame($signature, $service->signature("{}\n", 'yourRequestId', '2020-10-21T03:38:28Z', '/checkout/v1/payment'));
        config(['doku.secret_key' => 'secret-key-from-jokul-back-office ']);
        $this->assertNotSame($signature, $service->signature('{}', 'yourRequestId', '2020-10-21T03:38:28Z', '/checkout/v1/payment'));
    }
}
