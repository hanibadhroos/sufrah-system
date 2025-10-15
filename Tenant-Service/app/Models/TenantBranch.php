<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantBranch extends Model
{
    use HasFactory;

    protected $table = 'tenant_branches';
    protected $fillable = ['id', 'name', 'location', 'tenant_id', 'phone'];

    protected $keyType = 'string';
    public $incrementing = false;

}
