<?php
namespace App\Notifications;
use Illuminate\Notifications\Notification;
class SaleApprovalRequested extends Notification {
    public function __construct(public int $approvalId,public string $saleCode){}
    public function via(object $notifiable): array{return ['database'];}
    public function toArray(object $notifiable): array{return ['title'=>'Permintaan Refund / Void','message'=>'Transaksi '.$this->saleCode.' menunggu persetujuan.','approval_id'=>$this->approvalId,'url'=>route('sale-approvals.index')];}
}
