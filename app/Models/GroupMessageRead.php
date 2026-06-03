<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMessageRead extends Model
{
    protected $fillable = [
        'group_message_id',
        'user_id',
        'read_at',
    ];
}