<?php

namespace Database\Factories;

use App\Models\ListMember;
use App\Models\ListModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ListMemberFactory extends Factory
{
    protected $model = ListMember::class;

    public function definition(): array
    {
        return [
            'list_id' => ListModel::factory(),
            'user_id' => User::factory(),
            'role' => 'member',
        ];
    }

    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'owner',
        ]);
    }
}
