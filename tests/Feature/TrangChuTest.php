<?php
namespace tests\Feature;

use Tests\TestCase;

class TrangChuTest extends TestCase
{
    public function test_khach_vang_lai_duoc_chuyen_den_dashboard_customer(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('customer.dashboard'));
    }
}
