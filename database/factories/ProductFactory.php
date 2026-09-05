<?php

namespace Database\Factories;

use App\Models\User;
use Domain\Products\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    // Явно указываем нашу DDD модель, так как она лежит вне app/Models
    protected $model = Product::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);

        return [
            'id' => (string) Str::uuid(),
            // Привязываем к случайному продавцу (создадим его в сидере)
            'vendor_id' => User::where('role', 'vendor')->inRandomOrder()->first()?->id ?? User::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->paragraph(),
            'price_cents' => $this->faker->numberBetween(1000, 50000), // от $10.00 до $500.00
            'stock' => $this->faker->numberBetween(5, 100),
            'status' => 'published',
        ];
    }
}
