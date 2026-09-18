<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToBusiness;

class Setting extends Model
{
    use BelongsToBusiness;
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Ambil nilai setting berdasarkan key.
     */
    public static function getValue(
        string $key,
        mixed $default = null
    ): mixed {
        return static::where('key', $key)
            ->value('value') ?? $default;
    }

    /**
     * Simpan atau update setting.
     */
    public static function setValue(
        string $key,
        mixed $value
    ): static {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Ambil seluruh setting sebagai array.
     */
    public static function allAsArray(): array
    {
        return static::pluck('value', 'key')
            ->toArray();
    }
}
