<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * School-wide key/value settings.
 *
 * Reads go through a single cached map rather than a query per key, because the
 * payment panel asks for half a dozen keys on one page render.
 */
class SchoolSetting extends Model
{
    use Auditable;

    private const CACHE_KEY = 'school_settings.all';

    protected $fillable = ['key', 'value'];

    /** Payment-instruction keys, with the labels the admin form uses. */
    public const PAYMENT_KEYS = [
        'payment_bank_name'           => 'Bank name',
        'payment_bank_account_name'   => 'Account name',
        'payment_bank_account_number' => 'Account number',
        'payment_bank_branch'         => 'Branch / sort code',
        'payment_momo_mtn'            => 'MTN Mobile Money number',
        'payment_momo_airtel'         => 'Airtel Money number',
        'payment_note'                => 'Note to parents',
    ];

    public static function all_settings(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = self::all_settings()[$key] ?? null;

        return filled($value) ? $value : $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        Cache::forget(self::CACHE_KEY);
    }

    /** True when at least one payment channel has been configured. */
    public static function hasPaymentDetails(): bool
    {
        foreach (['payment_bank_account_number', 'payment_momo_mtn', 'payment_momo_airtel'] as $key) {
            if (filled(self::get($key))) {
                return true;
            }
        }

        return false;
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }
}
