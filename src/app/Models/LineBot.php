<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineBot extends Model
{
    use HasFactory;

    protected $fillable = ['channel_access_token', 'channel_secret'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
