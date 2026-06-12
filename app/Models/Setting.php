<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'key',
        'value',
    ];

    public static function get(string $key, mixed $default = null, ?int $companyId = null): mixed
    {
        $companyId ??= auth()->user()?->company_id;

        if (! $companyId) {
            return $default;
        }

        $setting = static::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('key', $key)
            ->first();

        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, mixed $value, ?int $companyId = null): void
    {
        $companyId ??= auth()->user()?->company_id;

        if (! $companyId) {
            return;
        }

        static::withoutGlobalScope('company')->updateOrCreate(
            ['company_id' => $companyId, 'key' => $key],
            ['value' => $value]
        );
    }
}
