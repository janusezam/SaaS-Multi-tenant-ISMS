<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class TenantSetting extends Model
{
    use CentralConnection;

    protected $fillable = [
        'tenant_id',
        'brand_primary_color',
        'brand_secondary_color',
        'theme_preference',
        'privacy_message',
    ];
}
