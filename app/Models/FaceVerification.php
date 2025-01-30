<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaceVerification extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'initial_face_data',
        'action_sequence',
        'flash_sequence',
        'verification_data',
        'status',
        'expires_at'
    ];

    protected $casts = [
        'initial_face_data' => 'array',
        'action_sequence' => 'array',
        'flash_sequence' => 'array',
        'verification_data' => 'array',
        'expires_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
