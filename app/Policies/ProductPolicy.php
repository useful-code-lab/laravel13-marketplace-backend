<?php

namespace App\Policies;

use App\Models\User;
use Domain\Products\Models\Product;

class ProductPolicy
{
    /**
     * Определяет, может ли пользователь создавать продукты.
     */
    public function create(User $user): bool
    {
        // Только пользователи с ролью 'vendor' имеют право создавать продукты
        return $user->isVendor();
    }
}
