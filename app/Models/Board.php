<?php

namespace App\Models;

use App\Events\BoardUpdated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Board extends Model
{
    use HasFactory;
    protected $dispatchesEvents = [
        'updated' => BoardUpdated::class,
    ];
    public function mark()
    {
        event(new BoardUpdated($this));
    }
    protected $fillable = [
        'board_name',
        'board_id',
        'webhookUrl_test',
        'webhookUrl_mapping',
        'webhookUrl_woocommerce',

    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
