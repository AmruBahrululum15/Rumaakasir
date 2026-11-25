<?php

namespace Tests\Feature;

use App\Models\{Business, Customer, Sale, User};
use App\Services\CustomerPortal;
use Illuminate\Support\Facades\{Http, Mail, Notification};
use Spatie\Permission\Models\Permission;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderListingTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests(); Mail::fake(); Notification::fake();
        $this->travelTo(now()->setDate(2026, 10, 7)->setTime(13, 0));
        $this->business = Business::create(['name'=>'Listing Store', 'subscription_plan'=>'premium', 'subscription_ends_at'=>now()->addMonth()]);
        $this->business->forceFill(['business_type'=>'retail'])->save();
        $this->staff = app(CustomerPortal::class)->within($this->business, fn()=>User::factory()->create());
        foreach (['sales.view', 'sales.create'] as $permission) {
            $this->staff->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
    }

    private function order(string $date, ?string $status = 'pending', array $attributes = [], ?Business $business = null): Sale
    {
        $business ??= $this->business;
        return app(CustomerPortal::class)->within($business, function () use ($date, $status, $attributes, $business) {
            $customer = Customer::create(['name'=>'Amru Bahrul Ulum']);
            $sale = new Sale(array_merge([
                'code'=>'WEB-'.str()->uuid(), 'sale_date'=>$date, 'customer_id'=>$customer->id,
                'user_id'=>$this->staff->id, 'total'=>25000, 'paid'=>0, 'change'=>0,
                'payment_method'=>'cash', 'payment_status'=>'CASH_PENDING',
                'order_source'=>'customer_portal', 'order_status'=>$status,
            ], $attributes));
            $sale->business_id = $business->id;
            $sale->created_at = $date; $sale->updated_at = $date;
            $sale->saveQuietly();
            return $sale;
        });
    }

    public function test_calendar_periods_include_boundaries_and_exclude_next_period(): void
    {
        $dates = ['2025-12-31 23:59:59','2026-01-01 00:00:00','2026-09-30 23:59:59',
            '2026-10-01 00:00:00','2026-10-04 23:59:59','2026-10-05 00:00:00',
            '2026-10-06 23:59:59','2026-10-07 00:00:00','2026-10-07 23:59:59',
            '2026-10-08 00:00:00','2026-10-11 23:59:59','2026-10-12 00:00:00',
            '2026-10-31 23:59:59','2026-11-01 00:00:00','2026-12-31 23:59:59','2027-01-01 00:00:00'];
        foreach ($dates as $date) $this->order($date);
        $this->actingAs($this->staff);
        foreach (['today'=>2, 'week'=>6, 'month'=>10, 'year'=>14] as $period=>$expected) {
            $this->get(route('portal-orders.index', ['period'=>$period]))->assertOk()
                ->assertViewHas('orders', fn($orders)=>$orders->total()===$expected);
        }
        $this->get(route('portal-orders.index'))->assertViewHas('orders', fn($orders)=>$orders->total()===2);
    }

    public function test_status_groups_priority_and_daily_summary_use_real_scoped_data(): void
    {
        $pending = $this->order('2026-10-07 08:00:00');
        $confirmed = $this->order('2026-10-07 09:00:00', 'confirmed');
        $processing = $this->order('2026-10-07 10:00:00', 'processing');
        $legacy = $this->order('2026-10-07 07:00:00', null);
        $done = $this->order('2026-10-07 12:00:00', 'completed', ['payment_status'=>'PAID', 'paid'=>25000]);
        $cancelled = $this->order('2026-10-07 13:00:00', 'cancelled', ['payment_status'=>'CANCELLED']);
        $this->order('2026-10-06 12:00:00', 'completed', ['payment_status'=>'PAID']);
        $this->order('2026-10-07 12:00:00', 'completed', ['order_source'=>'cashier', 'payment_status'=>'PAID']);
        $this->actingAs($this->staff)->get(route('portal-orders.index'))->assertOk()
            ->assertSee('BELUM SELESAI')->assertSee('SELESAI')->assertSee('DIBATALKAN')->assertSee('LUNAS')
            ->assertViewHas('summary', fn($s)=>$s['today']===6 && $s['unfinished']===4 && $s['completed']===1 && (float)$s['sales_today']===25000.0)
            ->assertViewHas('orders', fn($orders)=>$orders->pluck('id')->all()===[$processing->id,$confirmed->id,$pending->id,$legacy->id,$done->id,$cancelled->id]);
        foreach (['unfinished'=>4,'completed'=>1,'cancelled'=>1] as $status=>$count) {
            $this->get(route('portal-orders.index', ['status'=>$status]))->assertOk()
                ->assertViewHas('orders', fn($orders)=>$orders->total()===$count);
        }
    }

    public function test_search_and_pagination_retain_filters_and_bound_the_results(): void
    {
        for ($i=0; $i<23; $i++) $this->order('2026-10-07 10:00:00', 'pending', ['code'=>'WEB-MATCH-'.$i]);
        $this->order('2026-10-07 10:00:00', 'completed', ['code'=>'WEB-MATCH-DONE']);
        $this->order('2026-10-07 10:00:00', 'pending', ['code'=>'WEB-OTHER']);
        $filters = ['period'=>'month','status'=>'unfinished','search'=>'WEB-MATCH'];
        $this->actingAs($this->staff)->get(route('portal-orders.index', $filters))->assertOk()
            ->assertViewHas('orders', function ($orders) {
                parse_str(parse_url($orders->nextPageUrl(), PHP_URL_QUERY), $query);
                return $orders->total()===23 && $orders->count()===20 && $query['period']==='month'
                    && $query['status']==='unfinished' && $query['search']==='WEB-MATCH' && $query['page']==='2';
            });
        $this->get(route('portal-orders.index', $filters+['page'=>2]))->assertViewHas('orders', fn($orders)=>$orders->count()===3);
        $this->get(route('portal-orders.index', ['search'=>'Amru Bahrul']))->assertViewHas('orders', fn($orders)=>$orders->total()===25);
        $this->get(route('portal-orders.index', ['search'=>'Nonexistent']))->assertOk()->assertSee('Tidak ada pesanan');
    }

    public function test_tenant_scope_covers_search_summary_detail_and_receipt(): void
    {
        $own = $this->order('2026-10-07 10:00:00');
        $other = Business::create(['name'=>'Foreign Store']);
        $foreign = $this->order('2026-10-07 10:00:00', 'completed', ['code'=>'WEB-FOREIGN','payment_status'=>'PAID'], $other);
        $this->actingAs($this->staff)->get(route('portal-orders.index', ['business_id'=>$other->id,'search'=>'Amru']))
            ->assertOk()->assertSee($own->code)->assertDontSee($foreign->code)
            ->assertViewHas('summary', fn($s)=>$s['today']===1 && $s['completed']===0 && (float)$s['sales_today']===0.0);
        $this->get(route('portal-orders.index', ['search'=>'WEB-FOREIGN']))->assertViewHas('orders', fn($orders)=>$orders->total()===0);
        $this->get(route('portal-orders.show',$own->id))->assertOk()->assertSee('Detail Pesanan')->assertSee($own->code);
        $this->get(route('sales.receipt',$own->id))->assertOk()->assertSee($own->code);
        $this->get(route('portal-orders.show',$foreign->id))->assertNotFound();
        $this->get(route('sales.receipt',$foreign->id))->assertNotFound();
    }

    public function test_invalid_filter_values_are_rejected(): void
    {
        $this->actingAs($this->staff)->getJson(route('portal-orders.index', ['period'=>'invalid','status'=>'paid']))
            ->assertUnprocessable()->assertJsonValidationErrors(['period','status']);
    }
}
