<?php

namespace Database\Factories;

use App\Models\HoaDon;
use App\Models\NguoiDung;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HoaDon>
 */
class HoaDonFactory extends Factory
{
    protected $model = HoaDon::class;

    /**
     * TODO: bổ sung cột NOT NULL nếu migration hoa_don yêu cầu.
     */
    public function definition(): array
    {
        return [
            'shipper_id' => NguoiDung::factory()->state([
                'vai_tro' => 'SHIPPER',
            ]),
            'trang_thai'     => 'Chờ lấy',
            'ten_khach_hang' => $this->faker->name(),
            'dia_chi_giao'   => $this->faker->address(),
        ];
    }
}