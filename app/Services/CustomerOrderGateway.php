<?php
namespace App\Services;
use App\Models\{Business,CustomerOrderPayment,Sale};
use Illuminate\Support\Facades\{DB,Http};
use Illuminate\Validation\ValidationException;
class CustomerOrderGateway {
    private function client(CustomerOrderPayment $payment){return Http::acceptJson()->withoutRedirecting()->withBasicAuth($payment->server_key,'')->connectTimeout(10)->timeout(25);}
    public function start(Sale $sale):void {
        $payment=CustomerOrderPayment::where('business_id',$sale->business_id)->where('sale_id',$sale->id)->firstOrFail();
        abort_unless($payment->provider==='midtrans',422);
        $claimed=DB::transaction(function()use($sale,$payment){
            $locked=Sale::withoutGlobalScopes()->lockForUpdate()->findOrFail($sale->id);
            abort_unless($locked->payment_status==='PENDING' && $locked->order_status!=='cancelled',422);
            return CustomerOrderPayment::whereKey($payment->id)->where('gateway_state','new')->update(['gateway_state'=>'creating','updated_at'=>now()]);
        });
        if(!$claimed)return;
        try {
            $host=$payment->production?'https://app.midtrans.com':'https://app.sandbox.midtrans.com';
            $account=\App\Models\CustomerAccount::findOrFail($sale->customer_account_id);
            $response=$this->client($payment)->withHeaders(['X-Override-Notification'=>route('customer-payment.webhook',$sale->business_id)])
                ->post($host.'/snap/v1/transactions',[
                    'transaction_details'=>['order_id'=>$sale->code,'gross_amount'=>(int)round($sale->total)],
                    'customer_details'=>['first_name'=>$account->name,'email'=>$account->email,'phone'=>$account->phone],
                    'enabled_payments'=>$payment->enabled_payments,
                    'callbacks'=>['finish'=>route('customer.orders.show',$sale->id)],
                ]);
            $url=$response->json('redirect_url');
            if(!$response->successful()||!is_string($url)||parse_url($url,PHP_URL_SCHEME)!=='https'||parse_url($url,PHP_URL_HOST)!==parse_url($host,PHP_URL_HOST)||parse_url($url,PHP_URL_USER)||parse_url($url,PHP_URL_PASS))throw new \RuntimeException('Gateway response unavailable.');
            $payment->update(['redirect_url'=>$url,'gateway_state'=>'ready']);
        }catch(\Throwable $e){
            // A timeout may still have created the remote transaction. Never retry blindly.
            $payment->update(['gateway_state'=>'uncertain']);
            throw ValidationException::withMessages(['payment'=>'Pembayaran belum dapat ditampilkan. Pesanan tersimpan; tunggu konfirmasi gateway atau hubungi toko. Jangan membuat pembayaran ulang.']);
        }
    }
    public function verifiedStatus(CustomerOrderPayment $payment,Sale $sale,array $payload):array {
        $signature=hash('sha512',$payload['order_id'].$payload['status_code'].$payload['gross_amount'].$payment->server_key);
        abort_unless(hash_equals($signature,$payload['signature_key']),403);
        $host=$payment->production?'https://api.midtrans.com':'https://api.sandbox.midtrans.com';
        $response=$this->client($payment)->get($host.'/v2/'.rawurlencode($sale->code).'/status');
        abort_unless($response->successful(),503,'Status gateway belum dapat diverifikasi.');
        $status=$response->json();
        abort_unless(is_array($status)&&($status['order_id']??null)===$sale->code && is_numeric($status['gross_amount']??null) && (float)$status['gross_amount']===(float)round($sale->total) && ($status['currency']??'IDR')==='IDR',422);
        $types=$sale->payment_method==='qris'?['qris','other_qris','gopay','shopeepay']:['bank_transfer','echannel'];
        abort_unless(in_array($status['payment_type']??null,$types,true),422);
        return $status;
    }
}
