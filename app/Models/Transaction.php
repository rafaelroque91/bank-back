<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'amount',
        'type',
        'user_id',
        'status',
        'filename',
        'due_date'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
