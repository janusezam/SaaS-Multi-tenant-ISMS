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
        'use_custom_theme',
        'branding_logo_path',
        'login_brand_badge',
        'login_brand_heading',
        'login_brand_description',
        'login_brand_feature_1',
        'login_brand_feature_2',
        'login_brand_feature_3',
        'privacy_message',
    ];
}
