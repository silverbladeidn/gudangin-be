<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_email',
        'cc_emails',
        'request_notifications',
        'low_stock_notifications',
        'low_stock_threshold'
    ];

    protected $casts = [
        'cc_emails' => 'array',
        'request_notifications' => 'boolean',
        'low_stock_notifications' => 'boolean',
    ];

    // app/Models/EmailSettings.php
    public static function getSettings()
    {
        $settings = self::first();
        if (!$settings) {
            $settings = self::create([
                'admin_email' => 'aribiya@gmail.com',
                'cc_emails' => [],
                'request_notifications' => true,
                'low_stock_notifications' => true,
                'low_stock_threshold' => 10
            ]);
        }
        return $settings;
    }
}
