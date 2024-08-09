<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XeroToken extends Model
{
    use HasFactory;

    protected $table = 'xero_tokens';

    protected $fillable = [
        'app_name',
        'access_token',
        'refresh_token',
    ];
}
