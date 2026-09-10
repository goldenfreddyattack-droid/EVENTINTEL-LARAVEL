<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierService extends Model
{
    protected $table = 'supplier_services';
    protected $primaryKey = 'service_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'category',
        'style',
        'name',
        'description',
        'price',
        'address',
        'capacity',
        'latitude',
        'longitude',
        'service_pic',
        'service_pic1',
        'service_pic2',
        'service_pic3',
        'service_pic4',
        'service_pic5',
        'venue_add_ons',
        'rating',
        'created_at',
    ];

    protected $casts = [
        'venue_add_ons' => 'array',
        'service_pic' => 'binary',
        'service_pic1' => 'binary',
        'service_pic2' => 'binary',
        'service_pic3' => 'binary',
        'service_pic4' => 'binary',
        'service_pic5' => 'binary',
    ];
}
