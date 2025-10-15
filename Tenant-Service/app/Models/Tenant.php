<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'name', 'email', 'password', 'logo', 'payment_method','location', 'status', 'cancel_cutoff_minutes', 'type'];
    public $table = 'tenants';

    protected $hidden = [
        'password'
    ];
}
