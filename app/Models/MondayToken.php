<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MondayToken extends Model
{
    use HasFactory;

    protected $table = 'monday_tokens';

    protected $fillable = [
        'access_token',
    ];

}
