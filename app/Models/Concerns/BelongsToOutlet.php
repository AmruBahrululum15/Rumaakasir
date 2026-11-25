<?php
namespace App\Models\Concerns;
use App\Tenancy\OutletContext;
use Illuminate\Database\Eloquent\Builder;
trait BelongsToOutlet {
    public static function bootBelongsToOutlet():void {
        static::addGlobalScope('outlet',function(Builder $q){if($id=app(OutletContext::class)->id())$q->where($q->getModel()->getTable().'.outlet_id',$id);});
        static::creating(function($model){$id=app(OutletContext::class)->id();if($id)$model->outlet_id=$id;});
        static::updating(function($model){if($model->isDirty('outlet_id'))throw new \LogicException('Outlet transaksi tidak dapat dipindahkan.');});
    }
}
