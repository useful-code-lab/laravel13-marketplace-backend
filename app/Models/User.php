<?php

namespace App\Models;

use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Table('users')]
#[Fillable(['name', 'email', 'password', 'role'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->email === 'admin@example.com';
    }

    /**
     * Стандартный метод для кастинга типов в Laravel
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Автоматическое хеширование пароля при сохранении
        ];
    }

    /**
     * Быстрая проверка роли Пользователя
     */
    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }
}
