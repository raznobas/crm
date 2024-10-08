<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'director_id',
        'user_sender_id',
        'task_date',
        'task_description',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function userSender()
    {
        return $this->belongsTo(User::class, 'user_sender_id');
    }
}
