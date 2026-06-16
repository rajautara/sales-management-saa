<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'registration_no',
        'tin',
        'sst_registration_no',
        'msic_code',
        'business_activity_desc',
        'address',
        'address_city',
        'address_postcode',
        'address_state_code',
        'address_country_code',
        'phone',
        'email',
        'logo_path',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
