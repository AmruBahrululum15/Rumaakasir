<?php

namespace App\Notifications;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTableOrderNotification extends Notification
{
    use Queueable;

    /**
     * Data transaksi.
     */
    public Sale $sale;

    /**
     * Create a new notification instance.
     */
    public function __construct(Sale $sale)
    {
        $this->sale = $sale;
    }

    /**
     * Channel notifikasi.
     */
    public function via(object $notifiable): array
    {
        return [
            'database',
        ];
    }

    /**
     * Data yang disimpan ke database.
     */
    public function toDatabase(object $notifiable): array
    {
        $tableName = $this->getSaleNote(
            'TABLE_NAME'
        );

        $customerName = $this->getSaleNote(
            'CUSTOMER_NAME'
        );

        $paymentMethod = $this->getSaleNote(
            'PAYMENT_METHOD'
        );

        return [
            'type' => 'new_table_order',

            'title' => 'Pesanan Baru dari Meja',

            'message' =>
                'Pesanan baru dari meja '
                . ($tableName ?: '-')
                . ' atas nama '
                . ($customerName ?: 'Pelanggan'),

            'sale_id' => $this->sale->id,

            'sale_code' => $this->sale->code,

            'table_name' => $tableName,

            'customer_name' => $customerName,

            'payment_method' => $paymentMethod,

            'total' => (float) $this->sale->total,

            'url' => route(
                'sales.show',
                $this->sale->id
            ),

            'created_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Fallback untuk broadcast jika dibutuhkan.
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    /**
     * Ambil value dari notes transaksi.
     */
    private function getSaleNote(
        string $key
    ): ?string {

        $notes = $this->sale->notes ?? '';

        if (trim($notes) === '') {
            return null;
        }

        $lines = preg_split(
            '/\r\n|\r|\n/',
            $notes
        );

        foreach ($lines as $line) {

            $line = trim($line);

            if (
                str_starts_with(
                    $line,
                    $key . '='
                )
            ) {

                return trim(
                    substr(
                        $line,
                        strlen($key) + 1
                    )
                );
            }
        }

        return null;
    }
}