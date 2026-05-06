<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User
 *
 * Authentication model for DLimiter.
 *
 * Notes
 * - Uses Sanctum tokens for issuing personal access tokens.
 * - Password is hashed by casting rule in casts().
 * - is_admin (if present in DB) is used for admin-only pages (users/events).
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Fields allowed for mass assignment.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Hidden when converting model to array/JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Type casts.
     * - password: hashed means Laravel hashes it automatically on set.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}