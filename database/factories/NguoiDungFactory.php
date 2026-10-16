<?php

namespace Database\Factories;

use App\Models\NguoiDung;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NguoiDung>
 */
class NguoiDungFactory extends Factory
{
    protected $model = NguoiDung::class;

    public function definition(): array
    {
        return [
            'ten_dang_nhap' => $this->faker->unique()->userName(),
            'mat_khau'      => Hash::make('123456'),
            'vai_tro'       => 'SHIPPER',
        ];
    }
}