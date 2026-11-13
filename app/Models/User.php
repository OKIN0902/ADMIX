<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username', // Kolom legacy, tetap dipertahankan agar data lama tidak rusak.
        'nisn',     // Kolom legacy, tidak lagi diwajibkan untuk akun baru.
        'role',
        'gender',
        'avatar',   // Kolom legacy, tidak digunakan oleh UI yang baru.
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
