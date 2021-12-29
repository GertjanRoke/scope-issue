<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence(),
        ];
    }

    public function withSorting()
    {
        return $this->afterCreating(function (Item $item) {
            $item->sortable()->create(['left' => 1]);
        });
    }
}
