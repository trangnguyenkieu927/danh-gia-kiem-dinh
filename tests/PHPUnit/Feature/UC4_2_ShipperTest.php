<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\NguoiDung;
use App\Models\NhanVien;
use App\Models\KhachHang;
use App\Models\HoaDon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use App\Http\Controllers\Shipper\ShipperThongBaoController;



class UC4_2_ShipperTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Chỉ chạy các migration cần thiết cho test shipper.
     * Không chạy migration bao_cao_hang_hong đang lỗi.
     */
    protected function migrateFreshUsing(): array
    {
        return [
            '--path' => [
                'database/migrations/2026_04_15_100100_create_nguoi_dung_table.php',
                'database/migrations/2026_04_15_100200_create_khach_hang_table.php',
                'database/migrations/2026_04_15_100300_create_nhan_vien_table.php',
                'database/migrations/2026_04_15_100400_create_san_pham_table.php',
                'database/migrations/2026_04_15_100500_create_hoa_don_table.php',
                'database/migrations/2026_04_15_100700_create_phieu_nhap_table.php',
                'database/migrations/2026_04_15_100800_create_chi_tiet_nhap_table.php',
                'database/migrations/2026_04_15_100900_create_chi_tiet_hoa_don_table.php',
            ],
            '--seed' => false,
        ];
    }

    public function test_UC4_2_FUNC_005_01_shipper_cannot_view_other_shippers_order(): void
    {
        // Tạo hai tài khoản shipper.
        $userA = NguoiDung::factory()->create([
            'ten_dang_nhap' => 'shipper_test_A',
            'vai_tro' => 'SHIPPER',
        ]);

        $userB = NguoiDung::factory()->create([
            'ten_dang_nhap' => 'shipper_test_B',
            'vai_tro' => 'SHIPPER',
        ]);

        // Mỗi tài khoản tương ứng với một nhân viên giao hàng.
        NhanVien::create([
            'ma_nhan_vien' => $userA->ma_nguoi_dung,
            'ten_nhan_vien' => 'Shipper Test A',
            'chuc_vu' => 'SHIPPER',
        ]);

        NhanVien::create([
            'ma_nhan_vien' => $userB->ma_nguoi_dung,
            'ten_nhan_vien' => 'Shipper Test B',
            'chuc_vu' => 'SHIPPER',
        ]);

        // Tạo khách hàng bằng tài khoản riêng.
        $customerUser = NguoiDung::factory()->create([
            'ten_dang_nhap' => 'customer_test_B',
            'vai_tro' => 'KHACH_HANG',
        ]);

        $customer = KhachHang::create([
            'ma_khach_hang' => $customerUser->ma_nguoi_dung,
            'ten_khach_hang' => 'PRIVATE_CUSTOMER_TS_SHIP_005',
            'so_dien_thoai' => '0912345678',
            'email' => 'private_ship005@example.test',
            'dia_chi' => 'Dia chi rieng cua khach hang B',
            'diem_tich_luy' => 0,
        ]);

        // Tạo hóa đơn thuộc shipper B.
        $orderOfB = HoaDon::create([
            'ma_khach_hang' => $customer->ma_khach_hang,
            'ngay_dat' => now(),
            'tong_tien' => 250000,
            'trang_thai' => 'SHIPPING',
            'trang_thai_thanh_toan' => 'CHUA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'dia_chi_giao' => 'Dia chi rieng cua khach hang B',
            'so_dien_thoai' => '0912345678',
            'ma_nhan_vien_giao' => $userB->ma_nguoi_dung,
        ]);

        $orderId = $orderOfB->ma_hoa_don;
        $statusBefore = $orderOfB->trang_thai;
        $shipperBefore = $orderOfB->ma_nhan_vien_giao;

        // Shipper A cố truy cập hóa đơn của shipper B.
        $response = $this->actingAs($userA)
            ->get("/shipper/don-hang/{$orderId}/chi-tiet");

        // ASSERT 1: Không cho phép truy cập hóa đơn của người khác.
        // Controller hiện tại dùng findOrFail() sau khi lọc shipper,
        // nên kết quả mong đợi là 404.
        $response->assertNotFound();

        // ASSERT 2: Không tiết lộ thông tin riêng của khách hàng B.
        $response->assertDontSee('PRIVATE_CUSTOMER_TS_SHIP_005');
        $response->assertDontSee('private_ship005@example.test');
        $response->assertDontSee('0912345678');
        $response->assertDontSee('Dia chi rieng cua khach hang B');

        // ASSERT 3: Hóa đơn vẫn giữ nguyên trạng thái và shipper được giao.
        $this->assertDatabaseHas('hoa_don', [
            'ma_hoa_don' => $orderId,
            'trang_thai' => $statusBefore,
            'ma_nhan_vien_giao' => $shipperBefore,
        ]);
    }
    /**
     * Test Case : UC4.2_FUNC_006_01
     * Scenario  : TS-SHIP-006
     * Mục đích  : Shipper nhận đơn Chờ lấy (CONFIRMED) → chuyển sang
     *             Đang giao (SHIPPING), ghi nhận thời điểm bắt đầu giao
     *             (Cache) và gửi thông báo đến đúng khách hàng của đơn.
     */
    public function test_UC4_2_FUNC_006_01_shipper_can_start_delivery_and_notify_customer(): void
    {
        // ───────────── ARRANGE ─────────────
        // Shipper sở hữu đơn.
        $shipperUser = NguoiDung::factory()->create([
            'ten_dang_nhap' => 'shipper_test_006',
            'vai_tro'       => 'SHIPPER',
        ]);

        NhanVien::create([
            'ma_nhan_vien'  => $shipperUser->ma_nguoi_dung,
            'ten_nhan_vien' => 'Shipper Test 006',
            'chuc_vu'       => 'SHIPPER',
        ]);

        // Khách hàng sở hữu đơn.
        $customerUser = NguoiDung::factory()->create([
            'ten_dang_nhap' => 'customer_test_006',
            'vai_tro'       => 'KHACH_HANG',
        ]);

        $customer = KhachHang::create([
            'ma_khach_hang'  => $customerUser->ma_nguoi_dung,
            'ten_khach_hang' => 'Khach Hang Test 006',
            'so_dien_thoai'  => '0912345678',
            'email'          => 'customer006@example.test',
            'dia_chi'        => 'Dia chi test 006',
            'diem_tich_luy'  => 0,
        ]);

        // Hóa đơn "Chờ lấy" thuộc shipper.
        $order = HoaDon::create([
            'ma_khach_hang'          => $customer->ma_khach_hang,
            'ngay_dat'               => now(),
            'tong_tien'              => 300000,
            'trang_thai'             => 'CONFIRMED',
            'trang_thai_thanh_toan'  => 'CHUA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'dia_chi_giao'           => 'Dia chi test 006',
            'so_dien_thoai'          => '0912345678',
            'ma_nhan_vien_giao'      => $shipperUser->ma_nguoi_dung,
        ]);

        $orderId = $order->ma_hoa_don;
        $maKhach = (int) $customer->ma_khach_hang;

        // Dọn Cache để test độc lập (không ảnh hưởng test khác chạy trước).
        Cache::forget("shipping_start_{$orderId}");
        Cache::forget("thong_bao_khach_{$maKhach}");

        $timeBefore = now();

        // ───────────── ACT ─────────────
        $response = $this->actingAs($shipperUser)
            ->patchJson("/shipper/don-hang/{$orderId}/cap-nhat");

        // ───────────── ASSERT ─────────────

        // ASSERT 1: Controller trả JSON thành công, new_status = SHIPPING.
        $response->assertOk();
        $response->assertJson([
            'success'    => true,
            'new_status' => 'SHIPPING',
        ]);

        // ASSERT 2: CSDL cập nhật trạng thái đơn sang "Đang giao".
        $this->assertDatabaseHas('hoa_don', [
            'ma_hoa_don' => $orderId,
            'trang_thai' => 'SHIPPING',
        ]);

        // ASSERT 3: Thời điểm bắt đầu giao được ghi nhận (Cache), >= thời điểm trước thao tác.
        $startTime = Cache::get("shipping_start_{$orderId}");
        $this->assertNotNull(
            $startTime,
            'Thời điểm bắt đầu giao phải được ghi nhận sau khi nhận hàng.'
        );

        $startCarbon = \Carbon\Carbon::parse($startTime);
        $this->assertTrue(
            $startCarbon->greaterThanOrEqualTo($timeBefore->copy()->subSecond()),
            'Thời điểm bắt đầu giao phải không nhỏ hơn thời điểm ngay trước thao tác.'
        );

        // ASSERT 4: Khách hàng sở hữu đơn nhận đúng 1 thông báo nhận hàng.
        $thongBaos = Cache::get("thong_bao_khach_{$maKhach}", []);
        $this->assertCount(
            1,
            $thongBaos,
            'Khách hàng sở hữu đơn phải nhận đúng 1 thông báo.'
        );

        $noiDung = $thongBaos[0]['noi_dung'] ?? '';
        $this->assertStringContainsString(
            '#HD-',
            $noiDung,
            'Nội dung thông báo phải chứa mã đơn.'
        );
        $this->assertStringContainsString(
            'đang trên đường giao',
            $noiDung,
            'Nội dung thông báo phải khớp mẫu SRS (đang trên đường giao).'
        );

        // ASSERT 5: Khách hàng khác không nhận thông báo.
        $otherCustomerKey = 'thong_bao_khach_' . ($maKhach + 99999);
        $this->assertEmpty(
            Cache::get($otherCustomerKey, []),
            'Khách hàng không sở hữu đơn không được nhận thông báo.'
        );
    }
        /**
     * Test Case : UC4.2_FUNC_007_01
     * Scenario  : TS-SHIP-007
     * Mục đích  : Khi shipper nhấn "Đã đến điểm giao" trên đơn đang
     *             SHIPPING, hệ thống phải gửi thông báo cho đúng khách
     *             hàng với mã đơn, địa chỉ giao, và KHÔNG đổi trạng thái
     *             đơn trong CSDL.
     */
    public function test_UC4_2_FUNC_007_01_shipper_can_notify_customer_arrived(): void
    {
        // Cache là storage không bị RefreshDatabase reset — dọn thủ công
        // để test chạy độc lập, không phụ thuộc thứ tự chạy.
        Cache::flush();

        // ───────────── ARRANGE ─────────────
        $shipperUser = NguoiDung::factory()->create([
            'ten_dang_nhap' => 'shipper_test_007',
            'vai_tro'       => 'SHIPPER',
        ]);

        NhanVien::create([
            'ma_nhan_vien'  => $shipperUser->ma_nguoi_dung,
            'ten_nhan_vien' => 'Shipper Test 007',
            'chuc_vu'       => 'SHIPPER',
        ]);

        $customerUser = NguoiDung::factory()->create([
            'ten_dang_nhap' => 'customer_test_007',
            'vai_tro'       => 'KHACH_HANG',
        ]);

        $customer = KhachHang::create([
            'ma_khach_hang'  => $customerUser->ma_nguoi_dung,
            'ten_khach_hang' => 'Khach Hang Test 007',
            'so_dien_thoai'  => '0912345679',
            'email'          => 'customer007@example.test',
            'dia_chi'        => 'Dia chi khach 007',
            'diem_tich_luy'  => 0,
        ]);

        $diaChiGiao = 'So 99 Duong Test 007, Quan 1';

        // Hóa đơn đang "Đang giao" (SHIPPING) — đủ điều kiện gửi thông báo.
        $order = HoaDon::create([
            'ma_khach_hang'          => $customer->ma_khach_hang,
            'ngay_dat'               => now(),
            'tong_tien'              => 400000,
            'trang_thai'             => 'SHIPPING',
            'trang_thai_thanh_toan'  => 'CHUA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'dia_chi_giao'           => $diaChiGiao,
            'so_dien_thoai'          => '0912345679',
            'ma_nhan_vien_giao'      => $shipperUser->ma_nguoi_dung,
        ]);

        $orderId       = $order->ma_hoa_don;
        $maKhach       = (int) $customer->ma_khach_hang;
        $statusBefore  = $order->trang_thai;
        $paymentBefore = $order->trang_thai_thanh_toan;
        $ngayGiaoBefore = $order->ngay_giao;

        // ───────────── ACT ─────────────
        $response = $this->actingAs($shipperUser)
            ->postJson("/shipper/don-hang/{$orderId}/da-den-noi");

        // ───────────── ASSERT ─────────────

        // ASSERT 1: HTTP 200 + JSON success = true.
        $response->assertOk();
        $response->assertJson(['success' => true]);

        // ASSERT 2: Khách hàng sở hữu đơn nhận đúng 1 thông báo.
        $thongBaos = Cache::get("thong_bao_khach_{$maKhach}", []);
        $this->assertCount(
            1,
            $thongBaos,
            'Khách hàng sở hữu đơn phải nhận đúng 1 thông báo.'
        );

        // ASSERT 3: Nội dung thông báo khớp template SRS.
        $noiDung = $thongBaos[0]['noi_dung'] ?? '';
        $maHD    = '#HD-' . str_pad($orderId, 4, '0', STR_PAD_LEFT);

        $this->assertStringContainsString(
            $maHD,
            $noiDung,
            'Nội dung phải chứa mã đơn dạng #HD-xxxx.'
        );
        $this->assertStringContainsString(
            'đã đến địa điểm giao',
            $noiDung,
            'Nội dung phải chứa cụm "đã đến địa điểm giao".'
        );
        $this->assertStringContainsString(
            $diaChiGiao,
            $noiDung,
            'Nội dung phải chứa đúng địa chỉ giao của đơn.'
        );
        $this->assertStringContainsString(
            'xuống nhận hàng',
            $noiDung,
            'Nội dung phải chứa lời nhắc "xuống nhận hàng".'
        );

        // ASSERT 4: CSDL không đổi (bao phủ luôn một phần UC4.2_FUNC_007_02).
        $this->assertDatabaseHas('hoa_don', [
            'ma_hoa_don'            => $orderId,
            'trang_thai'            => $statusBefore,       // vẫn SHIPPING
            'trang_thai_thanh_toan' => $paymentBefore,      // vẫn CHUA_THANH_TOAN
            'ngay_giao'             => $ngayGiaoBefore,     // vẫn null
        ]);

        // ASSERT 5: Khách hàng khác không nhận thông báo.
        $keyKhac = 'thong_bao_khach_' . ($maKhach + 99999);
        $this->assertEmpty(
            Cache::get($keyKhac, []),
            'Khách hàng không sở hữu đơn không được nhận thông báo.'
        );
    }
        /**
     * Test Case : UC4.2_FUNC_008_01
     * Scenario  : TS-SHIP-008
     * Mục đích  : Shipper hoàn thành đơn COD đang "Đang giao" (SHIPPING)
     *             → trạng thái chuyển sang "Đã giao" (DELIVERED), đồng
     *             thời ghi nhận ngay_giao và cập nhật thanh toán theo
     *             luồng đặc tả.
     *
     * Phạm vi  : Chỉ kiểm tra 4 bước đầu của TC 008_01 (trạng thái +
     *             thời gian + thanh toán). Việc gửi thông báo xác nhận
     *             thuộc TC 008_06.
     */
    public function test_UC4_2_FUNC_008_01_shipper_can_complete_cod_order(): void
    {
        // ───────────── ARRANGE ─────────────
        $shipperUser = NguoiDung::factory()->create([
            'ten_dang_nhap' => 'shipper_test_008',
            'vai_tro'       => 'SHIPPER',
        ]);

        NhanVien::create([
            'ma_nhan_vien'  => $shipperUser->ma_nguoi_dung,
            'ten_nhan_vien' => 'Shipper Test 008',
            'chuc_vu'       => 'SHIPPER',
        ]);

        $customerUser = NguoiDung::factory()->create([
            'ten_dang_nhap' => 'customer_test_008',
            'vai_tro'       => 'KHACH_HANG',
        ]);

        $customer = KhachHang::create([
            'ma_khach_hang'  => $customerUser->ma_nguoi_dung,
            'ten_khach_hang' => 'Khach Hang Test 008',
            'so_dien_thoai'  => '0912345680',
            'email'          => 'customer008@example.test',
            'dia_chi'        => 'Dia chi khach 008',
            'diem_tich_luy'  => 0,
        ]);

        // Hóa đơn COD đang SHIPPING, chưa thanh toán — đúng đầu vào TC 008_01.
        $order = HoaDon::create([
            'ma_khach_hang'          => $customer->ma_khach_hang,
            'ngay_dat'               => now(),
            'tong_tien'              => 500000,
            'trang_thai'             => 'SHIPPING',
            'trang_thai_thanh_toan'  => 'CHUA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'dia_chi_giao'           => 'Dia chi giao 008',
            'so_dien_thoai'          => '0912345680',
            'ma_nhan_vien_giao'      => $shipperUser->ma_nguoi_dung,
            'ngay_giao'              => null,
        ]);

        $orderId    = $order->ma_hoa_don;
        $timeBefore = now()->subSecond();

        // ───────────── ACT ─────────────
        $response = $this->actingAs($shipperUser)
            ->patchJson("/shipper/don-hang/{$orderId}/cap-nhat");

        // ───────────── ASSERT ─────────────

        // ASSERT 1: Response 200 JSON, success=true, new_status=DELIVERED.
        $response->assertOk();
        $response->assertJson([
            'success'    => true,
            'new_status' => 'DELIVERED',
        ]);

        // ASSERT 2: CSDL — trạng thái đơn chuyển sang "Đã giao".
        $this->assertDatabaseHas('hoa_don', [
            'ma_hoa_don' => $orderId,
            'trang_thai' => 'DELIVERED',
        ]);

        // ASSERT 3: CSDL — ngay_giao được ghi nhận và >= thời điểm thao tác.
        $order->refresh();

        $this->assertNotNull(
            $order->ngay_giao,
            'Thời gian hoàn thành (ngay_giao) phải được ghi nhận.'
        );

        $ngayGiao = \Carbon\Carbon::parse($order->ngay_giao);
        $this->assertTrue(
            $ngayGiao->greaterThanOrEqualTo($timeBefore),
            'Thời gian hoàn thành phải >= thời điểm ngay trước thao tác.'
        );

        // ASSERT 4: CSDL — trạng thái thanh toán cập nhật theo luồng đặc tả.
        // (Controller hiện tại: khi DELIVERED luôn set DA_THANH_TOAN.)
        $this->assertEquals(
            'DA_THANH_TOAN',
            $order->trang_thai_thanh_toan,
            'Đơn COD hoàn thành phải được đánh dấu DA_THANH_TOAN.'
        );

        // ASSERT 5: Không thay đổi shipper được giao — chỉ cập nhật trạng thái.
        $this->assertEquals(
            $shipperUser->ma_nguoi_dung,
            $order->ma_nhan_vien_giao,
            'Shipper được giao không được thay đổi khi hoàn thành đơn.'
        );
    }
        /**
     * Test Case : UC4.2_FUNC_009_01
     * Scenario  : TS-SHIP-009
     * Mục đích  : Khi đơn đã "Đã giao" (DELIVERED), shipper bấm
     *             "Hoàn thành đơn hàng" từ màn hình cũ → hệ thống phải
     *             từ chối với thông báo "Trạng thái hiện tại không thể
     *             cập nhật." và không thay đổi bất kỳ dữ liệu hoàn thành.
     */
    public function test_UC4_2_FUNC_009_01_reject_complete_on_delivered_order(): void
    {
        // ───────────── ARRANGE ─────────────
        $shipperUser = NguoiDung::factory()->create([
            'ten_dang_nhap' => 'shipper_test_009',
            'vai_tro'       => 'SHIPPER',
        ]);

        NhanVien::create([
            'ma_nhan_vien'  => $shipperUser->ma_nguoi_dung,
            'ten_nhan_vien' => 'Shipper Test 009',
            'chuc_vu'       => 'SHIPPER',
        ]);

        $customerUser = NguoiDung::factory()->create([
            'ten_dang_nhap' => 'customer_test_009',
            'vai_tro'       => 'KHACH_HANG',
        ]);

        $customer = KhachHang::create([
            'ma_khach_hang'  => $customerUser->ma_nguoi_dung,
            'ten_khach_hang' => 'Khach Hang Test 009',
            'so_dien_thoai'  => '0912345681',
            'email'          => 'customer009@example.test',
            'dia_chi'        => 'Dia chi khach 009',
            'diem_tich_luy'  => 0,
        ]);

        // Đơn đã DELIVERED từ trước (mô phỏng tab 2 đã hoàn thành xong).
        $ngayGiaoCu = now()->subMinutes(5);
        $order = HoaDon::create([
            'ma_khach_hang'          => $customer->ma_khach_hang,
            'ngay_dat'               => now()->subHour(),
            'tong_tien'              => 600000,
            'trang_thai'             => 'DELIVERED',
            'trang_thai_thanh_toan'  => 'DA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'dia_chi_giao'           => 'Dia chi giao 009',
            'so_dien_thoai'          => '0912345681',
            'ma_nhan_vien_giao'      => $shipperUser->ma_nguoi_dung,
            'ngay_giao'              => $ngayGiaoCu,
        ]);

        $orderId           = $order->ma_hoa_don;
        $ngayGiaoTruoc     = $order->ngay_giao;
        $thanhToanTruoc    = $order->trang_thai_thanh_toan;
        $trangThaiTruoc    = $order->trang_thai;

        // ───────────── ACT ─────────────
        // Shipper bấm "Hoàn thành đơn hàng" lần nữa (tab cũ).
        $response = $this->actingAs($shipperUser)
            ->patchJson("/shipper/don-hang/{$orderId}/cap-nhat");

        // ───────────── ASSERT ─────────────

        // ASSERT 1: Bị từ chối với HTTP 422 và success=false.
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Trạng thái hiện tại không thể cập nhật.',
        ]);

        // ASSERT 2: Trạng thái đơn không thay đổi.
        $this->assertDatabaseHas('hoa_don', [
            'ma_hoa_don' => $orderId,
            'trang_thai' => $trangThaiTruoc,
        ]);

        // ASSERT 3: ngay_giao không bị ghi đè.
        $order->refresh();
        $this->assertEquals(
            $ngayGiaoTruoc,
            $order->ngay_giao,
            'Thời gian hoàn thành cũ không được ghi đè.'
        );

        // ASSERT 4: trạng thái thanh toán không đổi.
        $this->assertEquals(
            $thanhToanTruoc,
            $order->trang_thai_thanh_toan,
            'Trạng thái thanh toán không được thay đổi khi request bị từ chối.'
        );
    }
        /**
     * Data provider cho TS-SHIP-010: các cặp chuyển trạng thái KHÔNG hợp lệ.
     *
     * @return array<string, array{string, string}>
     */
    public static function invalidTransitionsProvider(): array
    {
        return [
            'Chờ lấy → Đã giao (bỏ bước)'         => ['CONFIRMED', 'DELIVERED'],
            'Đang giao → Chờ lấy (quay ngược)'    => ['SHIPPING',  'CONFIRMED'],
        ];
    }
        /**
     * Helper: tạo shipper + khách + hóa đơn với trạng thái cho trước.
     * Trả về [shipperUser, order].
     */
    private function makeShipperWithOrder(
        string $username,
        string $status,
        string $paymentStatus = 'CHUA_THANH_TOAN'
    ): array {
        $shipperUser = NguoiDung::factory()->create([
            'ten_dang_nhap' => $username,
            'vai_tro'       => 'SHIPPER',
        ]);

        NhanVien::create([
            'ma_nhan_vien'  => $shipperUser->ma_nguoi_dung,
            'ten_nhan_vien' => 'Shipper ' . $username,
            'chuc_vu'       => 'SHIPPER',
        ]);

        $customerUser = NguoiDung::factory()->create([
            'ten_dang_nhap' => 'cust_' . $username,
            'vai_tro'       => 'KHACH_HANG',
        ]);

        $customer = KhachHang::create([
            'ma_khach_hang'  => $customerUser->ma_nguoi_dung,
            'ten_khach_hang' => 'Khach ' . $username,
            'so_dien_thoai'  => '0912345' . random_int(100, 999),
            'email'          => 'cust_' . $username . '@example.test',
            'dia_chi'        => 'Dia chi ' . $username,
            'diem_tich_luy'  => 0,
        ]);

        $order = HoaDon::create([
            'ma_khach_hang'          => $customer->ma_khach_hang,
            'ngay_dat'               => now(),
            'tong_tien'              => 300000,
            'trang_thai'             => $status,
            'trang_thai_thanh_toan'  => $paymentStatus,
            'phuong_thuc_thanh_toan' => 'COD',
            'dia_chi_giao'           => 'Dia chi giao ' . $username,
            'so_dien_thoai'          => '0912345' . random_int(100, 999),
            'ma_nhan_vien_giao'      => $shipperUser->ma_nguoi_dung,
        ]);

        return [$shipperUser, $order];
    }
    /**
     * Test Case : TS-SHIP-010 (bao phủ UC4.2_FUNC_010_01 và 010_02)
     * Scenario  : Cập nhật đơn ở trạng thái không cho phép giao tiếp.
     *
     * Mục đích  : Với mọi cặp (trạng thái nguồn → trạng thái đích) không
     *             nằm trong luồng hợp lệ, hệ thống phải từ chối và giữ
     *             nguyên trạng thái nguồn.
     *
     * Kỳ vọng theo SRS:
     *   - Response: 422 (từ chối).
     *   - DB: `trang_thai` giữ nguyên giá trị ban đầu.
     *   - Không phát sinh thông báo cho khách (đối với nhánh CONFIRMED).
     *
   
     */
     #[DataProvider('invalidTransitionsProvider')]

   public function test_TS_SHIP_010_reject_invalid_status_transition(
    string $from,
    string $to
): void {
        // ───────────── ARRANGE ─────────────
        Cache::flush();

        $username = 'shipper_010_' . strtolower($from) . '_' . strtolower($to);
        [$shipper, $order] = $this->makeShipperWithOrder($username, $from);

        $orderId   = $order->ma_hoa_don;
        $maKhach   = (int) $order->ma_khach_hang;
        $statusTruoc = $order->trang_thai;               // = $from
        $ngayGiaoTruoc = $order->ngay_giao;              // null ở cả 2 case
        $thanhToanTruoc = $order->trang_thai_thanh_toan;

        // ───────────── ACT ─────────────
        // Shipper gửi yêu cầu chuyển sang trạng thái đích không hợp lệ.
        $response = $this->actingAs($shipper)
            ->patchJson("/shipper/don-hang/{$orderId}/cap-nhat", [
                'status' => $to,
            ]);

        // ───────────── ASSERT ─────────────

        // ASSERT 1: Hệ thống phải từ chối (422).
        $response->assertStatus(
            422,
            "Chuyển {$from} → {$to} phải bị từ chối với HTTP 422."
        );

        // ASSERT 2: Trạng thái đơn trong CSDL giữ nguyên giá trị nguồn.
        $this->assertDatabaseHas('hoa_don', [
            'ma_hoa_don' => $orderId,
            'trang_thai' => $statusTruoc,
        ]);

        // ASSERT 3: Các dữ liệu hoàn thành/thanh toán không bị thay đổi.
        $order->refresh();
        $this->assertEquals(
            $ngayGiaoTruoc,
            $order->ngay_giao,
            'ngay_giao không được thay đổi khi transition bị từ chối.'
        );
        $this->assertEquals(
            $thanhToanTruoc,
            $order->trang_thai_thanh_toan,
            'trang_thai_thanh_toan không được thay đổi khi transition bị từ chối.'
        );

        // ASSERT 4: Không phát sinh thông báo cho khách trong nhánh bỏ bước.
        if ($from === 'CONFIRMED') {
            $this->assertEmpty(
                Cache::get("thong_bao_khach_{$maKhach}", []),
                'Nhánh CONFIRMED → DELIVERED không được phát sinh thông báo.'
            );
            $this->assertNull(
                Cache::get("shipping_start_{$orderId}"),
                'Không được ghi nhận thời điểm bắt đầu giao khi bị từ chối.'
            );
        }
    }
        /**
     * Test Case : TS-SHIP-011 (bao phủ UC4.2_FUNC_011_01)
     * Mục đích  : Khi shipper hoàn thành đơn COD đang giao:
     *             1. Số tiền phải thu trên chi tiết đơn = tong_tien.
     *             2. Trạng thái đơn → DELIVERED, thanh toán → DA_THANH_TOAN.
     *             3. Tổng COD cần nộp trên dashboard tăng đúng bằng
     *                tong_tien của đơn đó.
     *
     * Phạm vi   : Chỉ xác minh dữ liệu phần mềm, không chứng minh giao
     *             nhận tiền mặt thực tế.
     */
    public function test_TS_SHIP_011_cod_collected_and_reflected_in_dashboard(): void
    {
        // ───────────── ARRANGE ─────────────
        [$shipper, $order] = $this->makeShipperWithOrder('shipper_011', 'SHIPPING');

        // Đặt tổng tiền cụ thể cho đơn COD.
        $order->update(['tong_tien' => 600000]);
        $order->refresh();

        // ── Trước khi hoàn thành ──
        // Dashboard: chưa có đơn DELIVERED → COD cần nộp = 0.
        $before = $this->actingAs($shipper)->get('/shipper/dashboard');
        $before->assertOk();
        $before->assertViewHas(
            'tienCanTra',
            fn ($v) => (float) $v === 0.0,
            'COD cần nộp ban đầu phải bằng 0 khi chưa có đơn DELIVERED.'
        );

        // Chi tiết đơn: số tiền phải thu = tong_tien, phương thức COD.
        $detail = $this->actingAs($shipper)
            ->get("/shipper/don-hang/{$order->ma_hoa_don}/chi-tiet");
        $detail->assertOk();
        $detail->assertViewHas('hoaDon', function ($h) {
            return (float) $h->tong_tien === 600000.0
                && $h->phuong_thuc_thanh_toan === 'COD';
        });

        // ───────────── ACT ─────────────
        $response = $this->actingAs($shipper)
            ->patchJson("/shipper/don-hang/{$order->ma_hoa_don}/cap-nhat");

        // ───────────── ASSERT ─────────────

        // ASSERT 1: response 200, new_status = DELIVERED.
        $response->assertOk();
        $response->assertJson([
            'success'    => true,
            'new_status' => 'DELIVERED',
        ]);

        // ASSERT 2: DB — trạng thái đơn và thanh toán đúng.
        $this->assertDatabaseHas('hoa_don', [
            'ma_hoa_don'            => $order->ma_hoa_don,
            'trang_thai'            => 'DELIVERED',
            'trang_thai_thanh_toan' => 'DA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
        ]);

        // ASSERT 3: Dashboard sau khi hoàn thành — COD cần nộp = 600000.
        $after = $this->actingAs($shipper)->get('/shipper/dashboard');
        $after->assertOk();
        $after->assertViewHas(
            'tienCanTra',
            fn ($v) => (float) $v === 600000.0,
            'Tổng COD cần nộp phải tăng đúng bằng tong_tien của đơn vừa hoàn thành.'
        );

        // ASSERT 4: Số đơn COD còn nợ = 1.
        $after->assertViewHas(
            'soDonConNo',
            fn ($v) => (int) $v === 1,
            'Số đơn COD chưa nộp phải bằng 1 sau khi hoàn thành 1 đơn COD.'
        );
    }
        /**
     * Test Case : TS-SHIP-012 (bao phủ UC4.2_FUNC_012_01 và 012_02)
     * Mục đích  : Đơn đã thanh toán bằng chuyển khoản (NGAN_HANG):
     *             1. Không yêu cầu shipper thu tiền mặt (không vào tổng COD).
     *             2. Hoàn thành bình thường → DELIVERED.
     *             3. Tổng COD cần nộp và số đơn COD chưa nộp trên dashboard
     *                KHÔNG thay đổi sau khi hoàn thành.
     */
    public function test_TS_SHIP_012_bank_transfer_order_not_counted_in_cod(): void
    {
        // ───────────── ARRANGE ─────────────
        // Shipper có sẵn 1 đơn COD đã giao, chưa nộp (600k) làm baseline.
        [$shipper, $codOrder] = $this->makeShipperWithOrder('shipper_012', 'SHIPPING');
        $codOrder->update([
            'tong_tien'              => 600000,
            'trang_thai'             => 'DELIVERED',
            'trang_thai_thanh_toan'  => 'DA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'ngay_giao'              => now()->subMinutes(5),
        ]);

        // Tạo thêm 1 khách hàng và 1 đơn NGAN_HANG cho cùng shipper đó.
        $customerUser2 = NguoiDung::factory()->create([
            'ten_dang_nhap' => 'cust_012_bank',
            'vai_tro'       => 'KHACH_HANG',
        ]);

        $customer2 = KhachHang::create([
            'ma_khach_hang'  => $customerUser2->ma_nguoi_dung,
            'ten_khach_hang' => 'Khach 012 Bank',
            'so_dien_thoai'  => '0912345900',
            'email'          => 'cust_012_bank@example.test',
            'dia_chi'        => 'Dia chi bank 012',
            'diem_tich_luy'  => 0,
        ]);

        $bankOrder = HoaDon::create([
            'ma_khach_hang'          => $customer2->ma_khach_hang,
            'ngay_dat'               => now(),
            'tong_tien'              => 1480000,
            'trang_thai'             => 'SHIPPING',
            'trang_thai_thanh_toan'  => 'DA_THANH_TOAN',   // đã trả qua ngân hàng
            'phuong_thuc_thanh_toan' => 'NGAN_HANG',
            'dia_chi_giao'           => 'Dia chi giao 012',
            'so_dien_thoai'          => '0912345900',
            'ma_nhan_vien_giao'      => $shipper->ma_nguoi_dung,
        ]);

        // ── Kiểm tra dashboard TRƯỚC thao tác ──
        $before = $this->actingAs($shipper)->get('/shipper/dashboard');
        $before->assertOk();
        $before->assertViewHas(
            'tienCanTra',
            fn ($v) => (float) $v === 600000.0,
            'Tổng COD trước thao tác phải chỉ gồm đơn COD baseline.'
        );
        $before->assertViewHas(
            'soDonConNo',
            fn ($v) => (int) $v === 1,
            'Chỉ đếm 1 đơn COD baseline.'
        );

        // ── Kiểm tra chi tiết đơn NGAN_HANG: không yêu cầu thu tiền mặt ──
        $detail = $this->actingAs($shipper)
            ->get("/shipper/don-hang/{$bankOrder->ma_hoa_don}/chi-tiet");
        $detail->assertOk();
        $detail->assertViewHas('hoaDon', function ($h) {
            return $h->phuong_thuc_thanh_toan === 'NGAN_HANG'
                && $h->trang_thai_thanh_toan === 'DA_THANH_TOAN';
        });

        // ───────────── ACT ─────────────
        $response = $this->actingAs($shipper)
            ->patchJson("/shipper/don-hang/{$bankOrder->ma_hoa_don}/cap-nhat");

        // ───────────── ASSERT ─────────────

        // ASSERT 1: Hoàn thành đơn thành công, new_status=DELIVERED.
        $response->assertOk();
        $response->assertJson([
            'success'    => true,
            'new_status' => 'DELIVERED',
        ]);

        // ASSERT 2: DB — đơn NGAN_HANG chuyển DELIVERED.
        $this->assertDatabaseHas('hoa_don', [
            'ma_hoa_don'             => $bankOrder->ma_hoa_don,
            'trang_thai'             => 'DELIVERED',
            'phuong_thuc_thanh_toan' => 'NGAN_HANG',
        ]);

        // ASSERT 3: Tổng COD cần nộp KHÔNG đổi (vẫn 600k, không cộng 1.48tr).
        $after = $this->actingAs($shipper)->get('/shipper/dashboard');
        $after->assertOk();
        $after->assertViewHas(
            'tienCanTra',
            fn ($v) => (float) $v === 600000.0,
            'Đơn NGAN_HANG không được cộng vào tổng COD cần nộp.'
        );

        // ASSERT 4: Số đơn COD chưa nộp KHÔNG đổi (vẫn 1).
        $after->assertViewHas(
            'soDonConNo',
            fn ($v) => (int) $v === 1,
            'Đơn NGAN_HANG không được tính là đơn COD chưa nộp.'
        );
    }
        /**
     * Helper: tạo shipper + khách đi kèm.
     */
    private function makeShipperAndCustomer(string $username): array
    {
        $shipperUser = NguoiDung::factory()->create([
            'ten_dang_nhap' => $username,
            'vai_tro'       => 'SHIPPER',
        ]);

        NhanVien::create([
            'ma_nhan_vien'  => $shipperUser->ma_nguoi_dung,
            'ten_nhan_vien' => 'Shipper ' . $username,
            'chuc_vu'       => 'SHIPPER',
        ]);

        $customerUser = NguoiDung::factory()->create([
            'ten_dang_nhap' => 'cust_' . $username,
            'vai_tro'       => 'KHACH_HANG',
        ]);

        $customer = KhachHang::create([
            'ma_khach_hang'  => $customerUser->ma_nguoi_dung,
            'ten_khach_hang' => 'Khach ' . $username,
            'so_dien_thoai'  => '0912345' . random_int(100, 999),
            'email'          => 'cust_' . $username . '@example.test',
            'dia_chi'        => 'Dia chi ' . $username,
            'diem_tich_luy'  => 0,
        ]);

        return [$shipperUser, $customer];
    }

    /**
     * Helper: tạo hóa đơn với cột mặc định + override.
     */
    private function createOrderFor(NguoiDung $shipperUser, KhachHang $customer, array $attrs = []): HoaDon
    {
        return HoaDon::create(array_merge([
            'ma_khach_hang'          => $customer->ma_khach_hang,
            'ngay_dat'               => now(),
            'tong_tien'              => 100000,
            'trang_thai'             => 'SHIPPING',
            'trang_thai_thanh_toan'  => 'CHUA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'dia_chi_giao'           => 'Dia chi giao',
            'so_dien_thoai'          => '0912345678',
            'ma_nhan_vien_giao'      => $shipperUser->ma_nguoi_dung,
        ], $attrs));
    }

    /**
     * Test Case : TS-SHIP-013
     * Mục đích  : Tổng COD cần nộp trên dashboard CHỈ gồm các đơn:
     *             - thuộc shipper đang đăng nhập
     *             - trạng thái DELIVERED
     *             - phương thức COD
     *             - trang_thai_thanh_toan = DA_THANH_TOAN (đã thu khách, chưa nộp)
     *
     *             KHÔNG tính: COD chưa giao (CONFIRMED/SHIPPING), COD DA_NOP,
     *             NGAN_HANG, và đơn của shipper khác.
     */
    public function test_TS_SHIP_013_cod_total_only_eligible_own_orders(): void
    {
        // ───────────── ARRANGE ─────────────
        [$shipperA, $custA] = $this->makeShipperAndCustomer('shipper_013a');
        [$shipperB, $custB] = $this->makeShipperAndCustomer('shipper_013b');

        // ─── Đơn của Shipper A ───
        // A1: COD đang giao (SHIPPING) → KHÔNG tính
        $this->createOrderFor($shipperA, $custA, [
            'trang_thai'             => 'SHIPPING',
            'trang_thai_thanh_toan'  => 'CHUA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'tong_tien'              => 500000,
        ]);

        // A2: COD chờ lấy (CONFIRMED) → KHÔNG tính
        $this->createOrderFor($shipperA, $custA, [
            'trang_thai'             => 'CONFIRMED',
            'trang_thai_thanh_toan'  => 'CHUA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'tong_tien'              => 300000,
        ]);

        // A3: COD đã giao, đã thu khách, CHƯA nộp → TÍNH (600.000)
        $this->createOrderFor($shipperA, $custA, [
            'trang_thai'             => 'DELIVERED',
            'trang_thai_thanh_toan'  => 'DA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'tong_tien'              => 600000,
            'ngay_giao'              => now()->subMinutes(10),
        ]);

        // A4: COD đã giao, ĐÃ NỘP (DA_NOP) → KHÔNG tính (700.000)
        $this->createOrderFor($shipperA, $custA, [
            'trang_thai'             => 'DELIVERED',
            'trang_thai_thanh_toan'  => 'DA_NOP',
            'phuong_thuc_thanh_toan' => 'COD',
            'tong_tien'              => 700000,
            'ngay_giao'              => now()->subMinutes(20),
        ]);

        // A5: NGAN_HANG đã giao → KHÔNG tính (2.135.500)
        $this->createOrderFor($shipperA, $custA, [
            'trang_thai'             => 'DELIVERED',
            'trang_thai_thanh_toan'  => 'DA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'NGAN_HANG',
            'tong_tien'              => 2135500,
            'ngay_giao'              => now()->subMinutes(30),
        ]);

        // ─── Đơn của Shipper B ───
        // B1: COD đã giao chưa nộp → TÍNH cho B (2.142.000), KHÔNG tính cho A
        $this->createOrderFor($shipperB, $custB, [
            'trang_thai'             => 'DELIVERED',
            'trang_thai_thanh_toan'  => 'DA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'tong_tien'              => 2142000,
            'ngay_giao'              => now()->subMinutes(5),
        ]);

        // ───────────── ACT + ASSERT — Shipper A ─────────────
        $responseA = $this->actingAs($shipperA)->get('/shipper/dashboard');
        $responseA->assertOk();

        // Chỉ A3 (600k) được tính.
        $responseA->assertViewHas(
            'tienCanTra',
            fn ($v) => (float) $v === 600000.0,
            'Tổng COD của A chỉ gồm đơn COD DELIVERED + DA_THANH_TOAN của chính A.'
        );
        $responseA->assertViewHas(
            'soDonConNo',
            fn ($v) => (int) $v === 1,
            'A phải có đúng 1 đơn COD chưa nộp.'
        );

        // ───────────── ASSERT — Shipper B ─────────────
        $responseB = $this->actingAs($shipperB)->get('/shipper/dashboard');
        $responseB->assertOk();

        // Chỉ B1 (2.142.000) được tính — không lẫn với đơn của A.
        $responseB->assertViewHas(
            'tienCanTra',
            fn ($v) => (float) $v === 2142000.0,
            'B chỉ thấy COD của chính mình.'
        );
        $responseB->assertViewHas(
            'soDonConNo',
            fn ($v) => (int) $v === 1,
            'B có đúng 1 đơn COD chưa nộp.'
        );
    }
        /**
     * Test Case : TS-SHIP-014 (bao phủ UC4.2_FUNC_014_01, 014_02, 014_03)
     * Mục đích  : Shipper KHÔNG có quyền tự xác nhận đã nộp COD.
     *             - Gửi request set trang_thai_thanh_toan = DA_NOP qua
     *               route shipper → không có hiệu lực.
     *             - Trạng thái thanh toán và tổng COD cần nộp không đổi.
     *             - Không route nào của shipper cho phép self-confirm.
     */
    public function test_TS_SHIP_014_shipper_cannot_self_confirm_cod_remittance(): void
    {
        // ───────────── ARRANGE ─────────────
        [$shipper, $customer] = $this->makeShipperAndCustomer('shipper_014');

        // Đơn COD đã giao, đã thu khách, CHƯA nộp (DA_THANH_TOAN).
        $order = $this->createOrderFor($shipper, $customer, [
            'trang_thai'             => 'DELIVERED',
            'trang_thai_thanh_toan'  => 'DA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'tong_tien'              => 600000,
            'ngay_giao'              => now()->subMinutes(10),
        ]);

        $orderId       = $order->ma_hoa_don;
        $paymentBefore = $order->trang_thai_thanh_toan;   // DA_THANH_TOAN
        $statusBefore  = $order->trang_thai;              // DELIVERED

        // ───────────── ACT 1 ─────────────
        // Shipper cố gửi request "xác nhận đã nộp COD" qua route shipper
        // (cách duy nhất shipper có thể chạm vào đơn của mình).
        $response = $this->actingAs($shipper)
            ->patchJson("/shipper/don-hang/{$orderId}/cap-nhat", [
                'trang_thai_thanh_toan' => 'DA_NOP',
            ]);

        // ───────────── ASSERT 1 ─────────────
        // Đơn đã DELIVERED → không nằm trong $map → bị từ chối 422.
        $response->assertStatus(
            422,
            'Shipper không được phép xác nhận nộp COD; request phải bị từ chối.'
        );

        // ASSERT 2: CSDL — trạng thái thanh toán KHÔNG đổi.
        $this->assertDatabaseHas('hoa_don', [
            'ma_hoa_don'             => $orderId,
            'trang_thai'             => $statusBefore,
            'trang_thai_thanh_toan'  => $paymentBefore,   // vẫn DA_THANH_TOAN
        ]);

        // ASSERT 3: dashboard — tổng COD cần nộp KHÔNG đổi.
        $dashboard = $this->actingAs($shipper)->get('/shipper/dashboard');
        $dashboard->assertOk();
        $dashboard->assertViewHas(
            'tienCanTra',
            fn ($v) => (float) $v === 600000.0,
            'Tổng COD cần nộp phải không đổi sau khi request bị từ chối.'
        );
        $dashboard->assertViewHas(
            'soDonConNo',
            fn ($v) => (int) $v === 1,
            'Đơn vẫn được tính là COD chưa nộp (không tự đánh dấu đã nộp).'
        );

        // ───────────── ACT 2 + ASSERT 4 ─────────────
        // Kiểm tra không tồn tại route nào của shipper cho phép self-confirm.
        // Chỉ dựa vào danh sách route thực tế, không giả định.
        $suspiciousRoutes = collect(\Route::getRoutes())->filter(function ($route) {
            $uri        = strtolower($route->uri());
            $middleware = implode('|', $route->gatherMiddleware());

            // Route thuộc nhóm shipper + có từ khoá liên quan xác nhận nộp
            return str_starts_with($uri, 'shipper/')
                && (str_contains($uri, 'nop')
                    || str_contains($uri, 'xac-nhan')
                    || str_contains($uri, 'da-nop'));
        });

        $this->assertCount(
            0,
            $suspiciousRoutes,
            'Không được tồn tại route cho shipper tự xác nhận nộp COD. '
            . 'Các route tìm thấy: ' . $suspiciousRoutes->pluck('uri')->implode(', ')
        );
    }
        /**
     * Test Case : TS-SHIP-016 (bao phủ UC4.2_FUNC_016_01 → 016_05)
     * Mục đích  : Shipper cập nhật họ tên + SĐT + email hợp lệ:
     *             - Lưu thành công (flash success_info, redirect edit).
     *             - CSDL bảng nhan_vien cập nhật đúng 3 trường.
     *             - Các trường khác (chuc_vu) không đổi.
     *             - Dữ liệu bền vững khi đăng xuất / đăng nhập lại.
     */
    public function test_TS_SHIP_016_update_profile_with_valid_data(): void
    {
        // ───────────── ARRANGE ─────────────
        [$shipper, $customer] = $this->makeShipperAndCustomer('shipper_016');

        $nhanVien = NhanVien::findOrFail($shipper->ma_nguoi_dung);
        $nhanVien->update([
            'ten_nhan_vien' => 'Nguyen Van Cu',
            'so_dien_thoai' => '0900000000',
            'email'         => 'old.016@example.test',
        ]);

        $chucVuBefore = $nhanVien->chuc_vu;

        $payload = [
            'ten_nhan_vien' => 'Nguyen Van Binh',
            'so_dien_thoai' => '0987123456',
            'email'         => 'binh.shipper@example.test',
        ];

        // ───────────── ACT ─────────────
        $response = $this->actingAs($shipper)
            ->patch('/shipper/profile', $payload);

        // ───────────── ASSERT ─────────────

        // ASSERT 1: Redirect về trang chỉnh sửa + flash thành công.
        $response->assertRedirect(route('shipper.profile.edit'));
        $response->assertSessionHas('success_info');

        // ASSERT 2: CSDL — cả 3 trường được lưu đúng giá trị mới.
        $this->assertDatabaseHas('nhan_vien', [
            'ma_nhan_vien'  => $shipper->ma_nguoi_dung,
            'ten_nhan_vien' => $payload['ten_nhan_vien'],
            'so_dien_thoai' => $payload['so_dien_thoai'],
            'email'         => $payload['email'],
        ]);

        // ASSERT 3: Các trường không cho phép sửa vẫn giữ nguyên.
        $nhanVien->refresh();
        $this->assertEquals(
            $chucVuBefore,
            $nhanVien->chuc_vu,
            'Chức vụ không được thay đổi khi chỉ cập nhật hồ sơ.'
        );

        // ASSERT 4: Dữ liệu bền vững sau đăng xuất → đăng nhập lại.
        $this->post('/logout');
        $this->assertGuest();

        $this->actingAs($shipper);
        $reloaded = NhanVien::findOrFail($shipper->ma_nguoi_dung);

        $this->assertEquals('Nguyen Van Binh',   $reloaded->ten_nhan_vien);
        $this->assertEquals('0987123456',        $reloaded->so_dien_thoai);
        $this->assertEquals('binh.shipper@example.test', $reloaded->email);
    }
        /**
     * Data provider cho TS-SHIP-017: các payload không hợp lệ.
     * Mỗi entry: [payload, các trường kỳ vọng có lỗi].
     */
    public static function invalidProfileProvider(): array
    {
        return [
            '017_01_họ tên rỗng' => [
                ['ten_nhan_vien' => '',        'so_dien_thoai' => '0901234567', 'email' => 'a@b.test'],
                ['ten_nhan_vien'],
            ],
            '017_02_sđt rỗng' => [
                ['ten_nhan_vien' => 'A',       'so_dien_thoai' => '',            'email' => 'a@b.test'],
                ['so_dien_thoai'],
            ],
            '017_03_sđt 9 số' => [
                ['ten_nhan_vien' => 'A',       'so_dien_thoai' => '090123456',   'email' => 'a@b.test'],
                ['so_dien_thoai'],
            ],
            '017_04_sđt 11 số' => [
                ['ten_nhan_vien' => 'A',       'so_dien_thoai' => '09012345678', 'email' => 'a@b.test'],
                ['so_dien_thoai'],
            ],
            '017_05_sđt có chữ' => [
                ['ten_nhan_vien' => 'A',       'so_dien_thoai' => '09012abc67',  'email' => 'a@b.test'],
                ['so_dien_thoai'],
            ],
            '017_05b_sđt ký tự đặc biệt' => [
                ['ten_nhan_vien' => 'A',       'so_dien_thoai' => '090-123-456', 'email' => 'a@b.test'],
                ['so_dien_thoai'],
            ],
            '017_07a_email thiếu @' => [
                ['ten_nhan_vien' => 'A',       'so_dien_thoai' => '0901234567',  'email' => 'shipper.flowerstore.vn'],
                ['email'],
            ],
            '017_07b_email thiếu miền' => [
                ['ten_nhan_vien' => 'A',       'so_dien_thoai' => '0901234567',  'email' => 'shipper@'],
                ['email'],
            ],
            '017_08_nhiều lỗi cùng lúc' => [
                ['ten_nhan_vien' => '',        'so_dien_thoai' => '12345',       'email' => 'abc'],
                ['ten_nhan_vien', 'so_dien_thoai', 'email'],
            ],
        ];
    }

    /**
     * Test Case : TS-SHIP-017 (bao phủ UC4.2_FUNC_017_01 → 017_05, 017_07, 017_08)
     * Mục đích  : Mọi payload hồ sơ không hợp lệ phải bị từ chối:
     *             - Redirect về trang hồ sơ.
     *             - Có lỗi validation ở đúng trường.
     *             - CSDL giữ nguyên giá trị cũ.
     */
    #[DataProvider('invalidProfileProvider')]
    public function test_TS_SHIP_017_rejects_invalid_profile_and_preserves_db(
        array $payload,
        array $errorFields
    ): void {
        // ───────────── ARRANGE ─────────────
        [$shipper, $customer] = $this->makeShipperAndCustomer('shipper_017_' . uniqid());

        $nhanVien = NhanVien::findOrFail($shipper->ma_nguoi_dung);
        $nhanVien->update([
            'ten_nhan_vien' => 'Original Name',
            'so_dien_thoai' => '0900000000',
            'email'         => 'original@example.test',
        ]);

        $original = [
            'ten_nhan_vien' => 'Original Name',
            'so_dien_thoai' => '0900000000',
            'email'         => 'original@example.test',
        ];

        // ───────────── ACT ─────────────
        $response = $this->actingAs($shipper)
            ->from('/shipper/profile')
            ->patch('/shipper/profile', $payload);

        // ───────────── ASSERT ─────────────

        // ASSERT 1: Redirect về trang hồ sơ.
        $response->assertRedirect('/shipper/profile');

        // ASSERT 2: Có lỗi ở các trường kỳ vọng.
        $response->assertSessionHasErrors($errorFields);

        // ASSERT 3: CSDL giữ nguyên giá trị cũ.
        $this->assertDatabaseHas('nhan_vien', array_merge(
            ['ma_nhan_vien' => $shipper->ma_nguoi_dung],
            $original
        ));
    }
        /**
     * Test Case : TS-SHIP-018 (bao phủ UC4.2_FUNC_018_01, 018_02, 018_03)
     * Mục đích  : Shipper gửi request cập nhật hồ sơ kèm trường chỉ đọc
     *             (chức vụ, lương cơ bản, mô tả công việc) → hệ thống:
     *             1. Không báo lỗi 500.
     *             2. Vẫn cập nhật các trường được phép (ten, sđt, email).
     *             3. Bỏ qua / từ chối các trường chỉ đọc; CSDL giữ nguyên.
     */
    public function test_TS_SHIP_018_readonly_fields_cannot_be_modified(): void
    {
        // ───────────── ARRANGE ─────────────
        [$shipper, $customer] = $this->makeShipperAndCustomer('shipper_018');

        // Chỉ kiểm tra những cột thực tồn tại trong bảng nhan_vien.
        $columns = Schema::getColumnListing('nhan_vien');

        $readOnlyCandidates = [
            'chuc_vu'          => 'SHIPPER',
            'luong_co_ban'     => 8000000,
            'mo_ta_cong_viec'  => 'Nhan vien giao hang',
        ];
        $readOnly = array_intersect_key($readOnlyCandidates, array_flip($columns));

        $this->assertNotEmpty(
            $readOnly,
            'Không tìm thấy cột chỉ đọc nào trong bảng nhan_vien. '
            . 'Cột hiện có: ' . implode(', ', $columns)
        );

        // Ghi nhận giá trị gốc của các trường chỉ đọc + trường cho phép sửa.
        $nhanVien = NhanVien::findOrFail($shipper->ma_nguoi_dung);
        $nhanVien->update($readOnly);
        $originalReadOnly = array_intersect_key($nhanVien->fresh()->toArray(), $readOnly);

        // ───────────── ACT ─────────────
        // Payload gồm cả trường hợp lệ (để request đi qua validate) và
        // trường chỉ đọc bị ghi đè (để kiểm tra mass assignment).
        $payload = [
            'ten_nhan_vien'   => 'Changed Name 018',
            'so_dien_thoai'   => '0901234567',
            'email'           => 'changed018@example.test',
            'chuc_vu'         => 'Quản lý',
            'luong_co_ban'    => 99000000,
            'mo_ta_cong_viec' => 'Toàn quyền quản lý',
        ];

        $response = $this->actingAs($shipper)
            ->from('/shipper/profile')
            ->patch('/shipper/profile', $payload);

        // ───────────── ASSERT ─────────────

        // ASSERT 1: Không lỗi hệ thống 500.
        $this->assertLessThan(
            500,
            $response->getStatusCode(),
            'Gửi kèm trường chỉ đọc không được gây lỗi 500.'
        );

        // ASSERT 2: Request đi qua bình thường, trường cho phép sửa được lưu.
        $response->assertRedirect('/shipper/profile');
        $response->assertSessionHas('success_info');
        $this->assertDatabaseHas('nhan_vien', [
            'ma_nhan_vien'  => $shipper->ma_nguoi_dung,
            'ten_nhan_vien' => 'Changed Name 018',
            'so_dien_thoai' => '0901234567',
            'email'         => 'changed018@example.test',
        ]);

        // ASSERT 3: Các trường chỉ đọc giữ nguyên giá trị gốc.
        $nhanVien->refresh();
        foreach ($originalReadOnly as $col => $origValue) {
            $this->assertEquals(
                $origValue,
                $nhanVien->{$col},
                "Trường chỉ đọc '{$col}' không được phép thay đổi."
            );
        }
    }
        /**
     * Test Case : TS-SHIP-019
     * Mục đích  : Shipper đổi mật khẩu với:
     *             - mật khẩu hiện tại đúng
     *             - mật khẩu mới 6–255 chữ số, KHÁC mật khẩu cũ
     *             - xác nhận khớp
     *             → Mật khẩu được cập nhật thành công; CSDL lưu dạng hash;
     *               mật khẩu cũ không còn hiệu lực.
     */
    public function test_TS_SHIP_019_change_password_with_valid_current_password(): void
    {
        // ───────────── ARRANGE ─────────────
        [$shipper, $customer] = $this->makeShipperAndCustomer('shipper_019');

        $matKhauCu  = '123456';
        $matKhauMoi = '654321';

        $shipperModel = NguoiDung::findOrFail($shipper->ma_nguoi_dung);
        $shipperModel->mat_khau = $matKhauCu;
        $shipperModel->save();

        // ───────────── ACT ─────────────
        $response = $this->actingAs($shipper)
            ->from('/shipper/profile')
            ->patch('/shipper/profile/password', [
                'mat_khau_cu'              => $matKhauCu,
                'mat_khau_moi'             => $matKhauMoi,
                'mat_khau_moi_confirmation'=> $matKhauMoi,
            ]);

        // ───────────── ASSERT ─────────────

        // ASSERT 1: Redirect về trang hồ sơ + flash thành công.
        $response->assertRedirect(route('shipper.profile.edit'));
        $response->assertSessionHas('success_password');

        // ASSERT 2: CSDL — mật khẩu mới được lưu, hash đúng, không plain text.
        $updated = NguoiDung::findOrFail($shipper->ma_nguoi_dung);

        $this->assertNotEquals(
            $matKhauMoi,
            $updated->mat_khau,
            'Mật khẩu phải được hash trước khi lưu CSDL, không lưu plain text.'
        );
        $this->assertTrue(
            Hash::check($matKhauMoi, $updated->mat_khau),
            'Mật khẩu mới phải xác thực được qua Hash::check.'
        );

        // ASSERT 3: Mật khẩu cũ không còn hiệu lực.
        $this->assertFalse(
            Hash::check($matKhauCu, $updated->mat_khau),
            'Mật khẩu cũ không được dùng được sau khi đổi.'
        );
    }
        /**
     * Test Case : TS-SHIP-020 (bao phủ UC4.2_FUNC_020_01 và 020_02)
     * Mục đích  : Shipper đổi mật khẩu với mật khẩu hiện tại SAI (hoặc
     *             để trống) → hệ thống phải từ chối, báo lỗi ở trường
     *             mat_khau_cu, và KHÔNG thay đổi mật khẩu trong CSDL.
     */
    public function test_TS_SHIP_020_change_password_with_wrong_current_password(): void
    {
        // ───────────── ARRANGE ─────────────
        [$shipper, $customer] = $this->makeShipperAndCustomer('shipper_020');

        // Ghi nhận hash mật khẩu hiện tại để so sánh sau.
        $shipperModel = NguoiDung::findOrFail($shipper->ma_nguoi_dung);
        $shipperModel->mat_khau = '123456';   // setup bất kỳ — không quan trọng đúng hash hay không
        $shipperModel->save();
        $hashBefore = $shipperModel->fresh()->mat_khau;

        $validNewPassword = '654321';

        // ───────────── ACT 1 — mật khẩu hiện tại SAI ─────────────
        $response1 = $this->actingAs($shipper)
            ->from('/shipper/profile')
            ->patch('/shipper/profile/password', [
                'mat_khau_cu'              => '999999',
                'mat_khau_moi'             => $validNewPassword,
                'mat_khau_moi_confirmation'=> $validNewPassword,
            ]);

        // ASSERT 1: Bị từ chối với lỗi ở mat_khau_cu, không có flash thành công.
        $response1->assertRedirect('/shipper/profile');
        $response1->assertSessionHasErrors(['mat_khau_cu']);
        $response1->assertSessionMissing('success_password');

        // ASSERT 2: Mật khẩu trong CSDL KHÔNG thay đổi.
        $hashAfter1 = NguoiDung::findOrFail($shipper->ma_nguoi_dung)->mat_khau;
        $this->assertEquals(
            $hashBefore,
            $hashAfter1,
            'Mật khẩu không được thay đổi khi mật khẩu hiện tại sai.'
        );

        // ───────────── ACT 2 — mật khẩu hiện tại TRỐNG ─────────────
        $response2 = $this->actingAs($shipper)
            ->from('/shipper/profile')
            ->patch('/shipper/profile/password', [
                'mat_khau_cu'              => '',
                'mat_khau_moi'             => $validNewPassword,
                'mat_khau_moi_confirmation'=> $validNewPassword,
            ]);

        // ASSERT 3: Bị từ chối ở mat_khau_cu (required rule).
        $response2->assertRedirect('/shipper/profile');
        $response2->assertSessionHasErrors(['mat_khau_cu']);
        $response2->assertSessionMissing('success_password');

        // ASSERT 4: Mật khẩu trong CSDL vẫn không thay đổi sau lần 2.
        $hashAfter2 = NguoiDung::findOrFail($shipper->ma_nguoi_dung)->mat_khau;
        $this->assertEquals(
            $hashBefore,
            $hashAfter2,
            'Mật khẩu không được thay đổi khi để trống mật khẩu hiện tại.'
        );
    }
        /**
     * Data provider cho TS-SHIP-021: các mật khẩu mới không hợp lệ.
     * Mỗi entry: [nhãn, mật khẩu mới].
     */
    public static function invalidNewPasswordProvider(): array
    {
        return [
            '021_01_5 chữ số (dưới biên)'      => ['12345'],
            '021_02_256 chữ số (trên biên)'    => [str_repeat('1', 256)],
            '021_03_chứa chữ cái'              => ['abc123'],
            '021_04a_chứa khoảng trắng'        => ['123 456'],
            '021_04b_chứa ký tự đặc biệt'      => ['12345@'],
        ];
    }

    /**
     * Test Case : TS-SHIP-021 (bao phủ UC4.2_FUNC_021_01 → 021_04)
     * Mục đích  : Mật khẩu mới vi phạm digits_between:6,255 → hệ thống
     *             từ chối, báo lỗi ở trường mat_khau_moi, không đổi
     *             mật khẩu trong CSDL.
     */
    #[DataProvider('invalidNewPasswordProvider')]
    public function test_TS_SHIP_021_reject_invalid_new_password(string $newPassword): void
    {
        // ───────────── ARRANGE ─────────────
        [$shipper, $customer] = $this->makeShipperAndCustomer('shipper_021_' . uniqid());

        // Ghi nhận hash mật khẩu hiện tại để so sánh sau.
        $shipperModel = NguoiDung::findOrFail($shipper->ma_nguoi_dung);
        $shipperModel->mat_khau = '123456';
        $shipperModel->save();
        $hashBefore = $shipperModel->fresh()->mat_khau;

        // ───────────── ACT ─────────────
        // "mat_khau_cu" chỉ cần là chuỗi không rỗng — validate mat_khau_moi
        // sẽ fail trước, không chạy đến Hash::check.
        $response = $this->actingAs($shipper)
            ->from('/shipper/profile')
            ->patch('/shipper/profile/password', [
                'mat_khau_cu'              => '123456',
                'mat_khau_moi'             => $newPassword,
                'mat_khau_moi_confirmation'=> $newPassword,
            ]);

        // ───────────── ASSERT ─────────────

        // ASSERT 1: Bị từ chối ở trường mat_khau_moi.
        $response->assertRedirect('/shipper/profile');
        $response->assertSessionHasErrors(['mat_khau_moi']);

        // ASSERT 2: Không có flash thành công.
        $response->assertSessionMissing('success_password');

        // ASSERT 3: Hash mật khẩu trong CSDL không đổi.
        $hashAfter = NguoiDung::findOrFail($shipper->ma_nguoi_dung)->mat_khau;
        $this->assertEquals(
            $hashBefore,
            $hashAfter,
            'Mật khẩu cũ phải giữ nguyên khi mật khẩu mới không hợp lệ.'
        );
    }
        /**
     * Data provider cho TS-SHIP-022: mật khẩu mới hợp lệ nhưng xác nhận khác.
     * Mỗi entry: [nhãn, mật khẩu mới, xác nhận].
     */
    public static function mismatchConfirmationProvider(): array
    {
        return [
            '022_01_khác hoàn toàn' => ['654321', '111111'],
            '022_02_lệch 1 ký tự'   => ['654321', '654322'],
        ];
    }

    /**
     * Test Case : TS-SHIP-022 (bao phủ UC4.2_FUNC_022_01 và 022_02)
     * Mục đích  : Xác nhận mật khẩu không khớp (khác hoàn toàn hoặc
     *             lệch 1 ký tự) → hệ thống từ chối, báo lỗi ở trường
     *             mat_khau_moi, không đổi mật khẩu trong CSDL.
     */
    #[DataProvider('mismatchConfirmationProvider')]
    public function test_TS_SHIP_022_reject_password_confirmation_mismatch(
        string $newPassword,
        string $confirmation
    ): void {
        // ───────────── ARRANGE ─────────────
        [$shipper, $customer] = $this->makeShipperAndCustomer('shipper_022_' . uniqid());

        $shipperModel = NguoiDung::findOrFail($shipper->ma_nguoi_dung);
        $shipperModel->mat_khau = '123456';
        $shipperModel->save();
        $hashBefore = $shipperModel->fresh()->mat_khau;

        // ───────────── ACT ─────────────
        $response = $this->actingAs($shipper)
            ->from('/shipper/profile')
            ->patch('/shipper/profile/password', [
                'mat_khau_cu'              => '123456',
                'mat_khau_moi'             => $newPassword,
                'mat_khau_moi_confirmation'=> $confirmation,
            ]);

        // ───────────── ASSERT ─────────────

        // ASSERT 1: Bị từ chối ở trường mat_khau_moi (rule `confirmed`).
        $response->assertRedirect('/shipper/profile');
        $response->assertSessionHasErrors(['mat_khau_moi']);

        // ASSERT 2: Không có flash thành công.
        $response->assertSessionMissing('success_password');

        // ASSERT 3: Hash mật khẩu trong CSDL không đổi.
        $hashAfter = NguoiDung::findOrFail($shipper->ma_nguoi_dung)->mat_khau;
        $this->assertEquals(
            $hashBefore,
            $hashAfter,
            'Mật khẩu cũ phải giữ nguyên khi xác nhận không khớp.'
        );
    }
        /**
     * Test Case : TS-SHIP-023 (bao phủ UC4.2_FUNC_023_01, 023_02, 023_03)
     * Mục đích  : Mọi truy cập với mã đơn KHÔNG TỒN TẠI hoặc SAI ĐỊNH DẠNG
     *             đều phải trả 404 (không lộ 500 / stack trace).
     *             - GET chi tiết với id không tồn tại / số âm / ký tự
     *             - PATCH cập nhật trạng thái với id không tồn tại
     *             Không phát sinh bản ghi mới, không gửi thông báo.
     */
    public function test_TS_SHIP_023_not_found_and_invalid_order_id(): void
    {
        // ───────────── ARRANGE ─────────────
        Cache::flush();

        [$shipper, $customer] = $this->makeShipperAndCustomer('shipper_023');

        $hoaDonTruoc  = HoaDon::count();
        $khachHangTruoc = KhachHang::count();
        $thongBaoTruoc = Cache::get("thong_bao_khach_{$customer->ma_khach_hang}", []);

        // ───────────── ACT + ASSERT — GET chi tiết ─────────────

        // 023_01: id không tồn tại
        $r1 = $this->actingAs($shipper)->get('/shipper/don-hang/999999/chi-tiet');
        $r1->assertNotFound();
        $r1->assertDontSee('Stack trace', false);
        $r1->assertDontSee('SQLSTATE', false);
        $r1->assertDontSee('vendor/laravel', false);

        // 023_02a: id số âm
        $r2 = $this->actingAs($shipper)->get('/shipper/don-hang/-1/chi-tiet');
        $this->assertTrue(
            in_array($r2->getStatusCode(), [404, 400]),
            'GET với id âm phải trả 404/400, nhận: ' . $r2->getStatusCode()
        );
        $this->assertLessThan(
            500,
            $r2->getStatusCode(),
            'GET với id âm không được gây lỗi 500.'
        );

        // 023_02b: id chữ cái
        $r3 = $this->actingAs($shipper)->get('/shipper/don-hang/abc/chi-tiet');
        $this->assertTrue(
            in_array($r3->getStatusCode(), [404, 400]),
            'GET với id chữ phải trả 404/400, nhận: ' . $r3->getStatusCode()
        );
        $this->assertLessThan(
            500,
            $r3->getStatusCode(),
            'GET với id chữ không được gây lỗi 500.'
        );

        // ───────────── ACT + ASSERT — PATCH cập nhật ─────────────

        // 023_03: gửi request cập nhật cho id không tồn tại
        $r4 = $this->actingAs($shipper)
            ->patchJson('/shipper/don-hang/999999/cap-nhat');

        $this->assertTrue(
            in_array($r4->getStatusCode(), [404, 400]),
            'PATCH với id không tồn tại phải trả 404/400, nhận: ' . $r4->getStatusCode()
        );
        $this->assertLessThan(
            500,
            $r4->getStatusCode(),
            'PATCH với id không tồn tại không được gây lỗi 500.'
        );

        // ───────────── ASSERT cuối — không phát sinh dữ liệu ─────────────

        $this->assertEquals(
            $hoaDonTruoc,
            HoaDon::count(),
            'Không có bản ghi hóa đơn nào được tạo.'
        );
        $this->assertEquals(
            $khachHangTruoc,
            KhachHang::count(),
            'Không có khách hàng nào bị ảnh hưởng.'
        );
        $this->assertEquals(
            $thongBaoTruoc,
            Cache::get("thong_bao_khach_{$customer->ma_khach_hang}", []),
            'Không được phát sinh thông báo cho khách hàng.'
        );
    }
        /**
     * Test Case : TS-SHIP-024 (bao phủ UC4.2_FUNC_024_01, 024_02, 024_03)
     * Mục đích  : Khi xảy ra lỗi xử lý/kết nối trong lúc cập nhật trạng thái:
     *             1. Response KHÔNG được là HTTP 500 (phải là thông báo lỗi
     *                chuẩn "Có lỗi xảy ra, vui lòng thử lại." theo SRS).
     *             2. Dữ liệu CSDL phải được rollback hoàn toàn.
     *             3. Cache (shipping_start, thông báo khách) không bị ghi
     *                nửa vời.
     *             4. Sau khi hệ thống phục hồi, thao tác lại thành công.
     */
    public function test_TS_SHIP_024_error_rollback_on_status_update(): void
    {
        // ───────────── ARRANGE ─────────────
        Cache::flush();

        [$shipper, $customer] = $this->makeShipperAndCustomer('shipper_024');

        $order = $this->createOrderFor($shipper, $customer, [
            'trang_thai'             => 'CONFIRMED',
            'trang_thai_thanh_toan'  => 'CHUA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'tong_tien'              => 600000,
        ]);

        $orderId = $order->ma_hoa_don;
        $maKhach = (int) $customer->ma_khach_hang;

        // ── Mô phỏng lỗi: mọi lần lưu HoaDon đều throw exception ──
        // Đăng ký SAU khi đã tạo xong dữ liệu test.
        $listener = function () {
            throw new \RuntimeException('Simulated DB connection failure');
        };
        Event::listen('eloquent.saving: ' . HoaDon::class, $listener);

        // ───────────── ACT 1 — lỗi khi nhận hàng ─────────────
        $responseFail = $this->actingAs($shipper)
            ->patchJson("/shipper/don-hang/{$orderId}/cap-nhat");

        // ───────────── ASSERT — phân tích bug ─────────────

        // ASSERT 1: Response không được là 500.
        // → Dự kiến FAIL: controller không try/catch → default handler 500.
        $this->assertLessThan(
            500,
            $responseFail->getStatusCode(),
            'Lỗi xử lý không được trả về HTTP 500 (SRS yêu cầu thông báo "Có lỗi xảy ra, vui lòng thử lại.").'
        );

        // ASSERT 2: DB trạng thái giữ nguyên (điểm duy nhất code làm đúng).
        $this->assertDatabaseHas('hoa_don', [
            'ma_hoa_don' => $orderId,
            'trang_thai' => 'CONFIRMED',
        ]);

        // ASSERT 3: Cache shipping_start KHÔNG được ghi nửa vời.
        // → Dự kiến FAIL: Cache::put chạy TRƯỚC update DB, không rollback.
        $this->assertNull(
            Cache::get("shipping_start_{$orderId}"),
            'Cache shipping_start không được ghi khi DB update thất bại (rollback toàn phần).'
        );

        // ASSERT 4: Không được phát thông báo cho khách.
        // → Dự kiến FAIL: cùng lý do trên.
        $this->assertEmpty(
            Cache::get("thong_bao_khach_{$maKhach}", []),
            'Khách không được nhận thông báo khi thao tác thất bại.'
        );

        // ───────────── ACT 2 — phục hồi và thử lại ─────────────
        // Gỡ listener để hệ thống hoạt động lại bình thường.
        Event::forget('eloquent.saving: ' . HoaDon::class);

        $responseOk = $this->actingAs($shipper)
            ->patchJson("/shipper/don-hang/{$orderId}/cap-nhat");

        // ASSERT 5: Sau phục hồi, thao tác thành công, trạng thái chuyển SHIPPING.
        $responseOk->assertOk();
        $responseOk->assertJson([
            'success'    => true,
            'new_status' => 'SHIPPING',
        ]);
        $this->assertDatabaseHas('hoa_don', [
            'ma_hoa_don' => $orderId,
            'trang_thai' => 'SHIPPING',
        ]);
    }
        /**
     * Data provider cho TS-SHIP-025 (phần backend mốc 3h).
     * Mỗi entry: [số giây đã trôi qua từ lúc nhận hàng, kỳ vọng status].
     * null = chưa set shipping_start.
     */
    public static function threeHourTimerProvider(): array
    {
        return [
            '025_02a_chưa nhận hàng'      => [null,   'no_start_time'],
            '025_03a_còn 2h'              => [3600,   'ok'],
            '025_03b_còn ~1 phút'         => [10740,  'ok'],
            '025_04_đúng mốc 3h'          => [10805,  'penalty_applied'],
            '025_05_quá 3h 1 phút'        => [10860,  'penalty_applied'],
        ];
    }

    /**
     * Test Case : TS-SHIP-025 (backend, bao phủ logic mốc 3h)
     * Mục đích  : Endpoint check-timeout phản ánh đúng trạng thái theo
     *             mốc thời gian đã trôi qua từ lúc shipper nhận hàng:
     *             - Chưa có mốc → no_start_time
     *             - < 3h → ok + remaining
     *             - >= 3h → penalty_applied (đúng quy tắc đã chốt)
     */
    #[DataProvider('threeHourTimerProvider')]
    public function test_TS_SHIP_025_timeout_endpoint_respects_3h_threshold(
        ?int $elapsedSeconds,
        string $expectedStatus
    ): void {
        // ───────────── ARRANGE ─────────────
        Cache::flush();

        [$shipper, $customer] = $this->makeShipperAndCustomer('shipper_025_' . uniqid());

        $order = $this->createOrderFor($shipper, $customer, [
            'trang_thai'             => 'SHIPPING',
            'trang_thai_thanh_toan'  => 'CHUA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'tong_tien'              => 300000,
        ]);

        $orderId = $order->ma_hoa_don;

        // Chỉ set shipping_start nếu case yêu cầu (elapsed != null).
        if ($elapsedSeconds !== null) {
            Cache::put(
                "shipping_start_{$orderId}",
                now()->subSeconds($elapsedSeconds)->toIso8601String(),
                now()->addHours(24)
            );
        }

        // ───────────── ACT ─────────────
        $response = $this->actingAs($shipper)
            ->postJson("/shipper/don-hang/{$orderId}/check-timeout");

        // ───────────── ASSERT ─────────────
        $response->assertOk();
        $response->assertJsonFragment(['status' => $expectedStatus]);

        // Nếu chưa đến 3h: trạng thái đơn không đổi, không phát thông báo phạt.
        if (in_array($expectedStatus, ['ok', 'no_start_time'], true)) {
            $this->assertDatabaseHas('hoa_don', [
                'ma_hoa_don' => $orderId,
                'trang_thai' => 'SHIPPING',
            ]);
        }

        // Nếu đã quá 3h: đơn vẫn SHIPPING (không bị chặn), nhưng có thông báo
        // phạt cho shipper và cộng điểm tích lũy cho khách.
        if ($expectedStatus === 'penalty_applied') {
            $this->assertDatabaseHas('hoa_don', [
                'ma_hoa_don' => $orderId,
                'trang_thai' => 'SHIPPING',
            ]);

            // Khách được cộng 15 điểm.
            $customer->refresh();
            $this->assertEquals(
                15,
                (int) $customer->diem_tich_luy,
                'Khách phải được cộng đúng 15 điểm khi đơn quá 3h.'
            );

            // Shipper nhận thông báo phạt 15.000 đ trong Cache.
            $shipperNotifs = Cache::get("thong_bao_shipper_{$shipper->ma_nguoi_dung}", []);
            $this->assertNotEmpty(
                $shipperNotifs,
                'Shipper phải nhận thông báo phạt khi đơn quá 3h.'
            );
        }
    }
        /**
     * Test Case : TS-SHIP-026 (bao phủ UC4.2_FUNC_026_01, 026_02, 026_03)
     * Mục đích  : Đơn đã ở trạng thái CANCELLED ("Đã hủy"):
     *             1. Shipper không cập nhật được trạng thái (không sập 500).
     *             2. Trạng thái DB giữ nguyên CANCELLED.
     *             3. Không ghi shipping_start, không phát thông báo khách,
     *                không cộng COD.
     *             4. Gửi request "đã đến điểm giao" cũng bị từ chối.
     *
     * Ghi chú   : SRS E2 ghi tình huống này "chưa được hỗ trợ đầy đủ trong
     *             phiên bản hiện tại" — test chỉ chứng minh phòng vệ đúng.
     */
    public function test_TS_SHIP_026_cancelled_order_cannot_be_updated(): void
    {
        // ───────────── ARRANGE ─────────────
        Cache::flush();

        [$shipper, $customer] = $this->makeShipperAndCustomer('shipper_026');

        $order = $this->createOrderFor($shipper, $customer, [
            'trang_thai'             => 'CANCELLED',
            'trang_thai_thanh_toan'  => 'CHUA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'tong_tien'              => 600000,
        ]);

        $orderId        = $order->ma_hoa_don;
        $maKhach        = (int) $customer->ma_khach_hang;
        $statusTruoc    = $order->trang_thai;             // CANCELLED
        $thanhToanTruoc = $order->trang_thai_thanh_toan;
        $ngayGiaoTruoc  = $order->ngay_giao;

        // ───────────── ACT 1 — cập nhật trạng thái ─────────────
        $r1 = $this->actingAs($shipper)
            ->patchJson("/shipper/don-hang/{$orderId}/cap-nhat");

        // ───────────── ASSERT 1 ─────────────
        // Phải bị từ chối (4xx), KHÔNG được 500.
        $this->assertLessThan(
            500,
            $r1->getStatusCode(),
            'Cập nhật đơn CANCELLED không được gây lỗi 500.'
        );
        $this->assertTrue(
            in_array($r1->getStatusCode(), [403, 404, 422], true),
            'Cập nhật đơn CANCELLED phải bị từ chối (403/404/422), nhận: '
            . $r1->getStatusCode()
        );

        // ───────────── ASSERT 2 — DB giữ nguyên ─────────────
        $order->refresh();
        $this->assertEquals($statusTruoc,    $order->trang_thai,           'Trạng thái không đổi.');
        $this->assertEquals($thanhToanTruoc, $order->trang_thai_thanh_toan,'Thanh toán không đổi.');
        $this->assertEquals($ngayGiaoTruoc,  $order->ngay_giao,            'ngay_giao không đổi.');

        // ───────────── ASSERT 3 — không side effect ─────────────
        $this->assertNull(
            Cache::get("shipping_start_{$orderId}"),
            'Không được ghi shipping_start cho đơn CANCELLED.'
        );
        $this->assertEmpty(
            Cache::get("thong_bao_khach_{$maKhach}", []),
            'Khách không được nhận thông báo nào cho đơn CANCELLED.'
        );

        // ───────────── ACT 2 — "đã đến điểm giao" ─────────────
        $r2 = $this->actingAs($shipper)
            ->postJson("/shipper/don-hang/{$orderId}/da-den-noi");

        // ───────────── ASSERT 4 ─────────────
        // Cũng phải bị từ chối, không sập.
        $this->assertLessThan(
            500,
            $r2->getStatusCode(),
            'Gửi "đã đến điểm giao" cho đơn CANCELLED không được gây lỗi 500.'
        );
        $this->assertTrue(
            in_array($r2->getStatusCode(), [403, 404, 422], true),
            'Gửi "đã đến điểm giao" cho đơn CANCELLED phải bị từ chối, nhận: '
            . $r2->getStatusCode()
        );

        // DB vẫn giữ nguyên sau lần 2.
        $order->refresh();
        $this->assertEquals($statusTruoc, $order->trang_thai);
        $this->assertEmpty(
            Cache::get("thong_bao_khach_{$maKhach}", []),
            'Không được phát thông báo "đã đến điểm giao" cho đơn CANCELLED.'
        );
    }
        /**
     * Test Case : TS-SHIP-027 (bao phủ UC4.2_FUNC_027_01, 027_02, 027_03)
     * Phạm vi   : Chỉ verify phía shipper — dashboard liệt kê đơn được giao,
     *             notification Cache được push đúng người, không lẫn sang
     *             shipper khác. Hành động phân công phía nhân viên chưa
     *             được test (cần controller).
     */
    public function test_TS_SHIP_027_new_assignment_visible_and_notified(): void
    {
        // ───────────── ARRANGE ─────────────
        Cache::flush();

        [$shipperA, $customerA] = $this->makeShipperAndCustomer('shipper_027a');
        [$shipperB, $customerB] = $this->makeShipperAndCustomer('shipper_027b');

        // Baseline: shipper A đã có 1 đơn cần giao.
        $existingOrder = $this->createOrderFor($shipperA, $customerA, [
            'trang_thai'             => 'CONFIRMED',
            'phuong_thuc_thanh_toan' => 'COD',
        ]);

        // Dashboard A trước khi phân công: đếm = 1.
        $beforeA = $this->actingAs($shipperA)->get('/shipper/dashboard');
        $beforeA->assertOk();
        $beforeA->assertViewHas(
            'donHangCanShip',
            fn ($list) => $list->count() === 1,
            'Trước phân công, shipper A chỉ có 1 đơn cần giao.'
        );

        // ───────────── ACT — mô phỏng phân công 2 đơn liên tiếp cho A ─────────────
        // (Khi có controller phân công thật, thay 2 block dưới bằng
        //  $this->actingAs($nhanVien)->post('/nhan-vien/phan-cong', ...))
        $assigned1 = $this->createOrderFor($shipperA, $customerA, [
            'trang_thai'             => 'CONFIRMED',
            'phuong_thuc_thanh_toan' => 'COD',
            'tong_tien'              => 100000,
        ]);
        ShipperThongBaoController::push(
            (int) $shipperA->ma_nguoi_dung,
            "Đơn hàng #HD-" . str_pad($assigned1->ma_hoa_don, 4, '0', STR_PAD_LEFT) . " vừa được phân công cho bạn.",
            'assignment'
        );

        $assigned2 = $this->createOrderFor($shipperA, $customerA, [
            'trang_thai'             => 'CONFIRMED',
            'phuong_thuc_thanh_toan' => 'COD',
            'tong_tien'              => 200000,
        ]);
        ShipperThongBaoController::push(
            (int) $shipperA->ma_nguoi_dung,
            "Đơn hàng #HD-" . str_pad($assigned2->ma_hoa_don, 4, '0', STR_PAD_LEFT) . " vừa được phân công cho bạn.",
            'assignment'
        );

        // Đơn cho B — để chắc chắn B không thấy đơn của A.
        $this->createOrderFor($shipperB, $customerB, [
            'trang_thai'             => 'CONFIRMED',
            'phuong_thuc_thanh_toan' => 'COD',
        ]);

        // ───────────── ASSERT — dashboard shipper A ─────────────
        $afterA = $this->actingAs($shipperA)->get('/shipper/dashboard');
        $afterA->assertOk();
        $afterA->assertViewHas(
            'donHangCanShip',
            function ($list) use ($existingOrder, $assigned1, $assigned2) {
                $ids = collect($list)->pluck('ma_hoa_don')->all();
                return in_array($existingOrder->ma_hoa_don, $ids)
                    && in_array($assigned1->ma_hoa_don, $ids)
                    && in_array($assigned2->ma_hoa_don, $ids)
                    && count($ids) === 3;
            },
            'Danh sách đơn cần giao của shipper A phải gồm 1 baseline + 2 đơn vừa phân công.'
        );

        // ASSERT — notification Cache của shipper A có đúng 2 thông báo mới
        $notifsA = Cache::get("thong_bao_shipper_{$shipperA->ma_nguoi_dung}", []);
        $this->assertCount(
            2,
            $notifsA,
            'Shipper A phải nhận đúng 2 thông báo phân công.'
        );

        // Nội dung thông báo chứa đúng mã đơn.
        $noiDungAll = collect($notifsA)->pluck('noi_dung')->implode(' | ');
        $this->assertStringContainsString(
            '#HD-' . str_pad($assigned1->ma_hoa_don, 4, '0', STR_PAD_LEFT),
            $noiDungAll
        );
        $this->assertStringContainsString(
            '#HD-' . str_pad($assigned2->ma_hoa_don, 4, '0', STR_PAD_LEFT),
            $noiDungAll
        );

        // ASSERT — dashboard shipper B chỉ có 1 đơn, không lẫn của A
        $afterB = $this->actingAs($shipperB)->get('/shipper/dashboard');
        $afterB->assertOk();
        $afterB->assertViewHas(
            'donHangCanShip',
            fn ($list) => $list->count() === 1,
            'Shipper B chỉ thấy đơn của mình.'
        );

        // ASSERT — notification shipper B trống (không nhận thông báo lây).
        $notifsB = Cache::get("thong_bao_shipper_{$shipperB->ma_nguoi_dung}", []);
        $this->assertEmpty(
            $notifsB,
            'Shipper B không được nhận thông báo phân công cho shipper A.'
        );
    }
        /**
     * Test Case : TS-SHIP-028 (bao phủ UC4.2_FUNC_028_01, 028_02, 028_03)
     * Mục đích  : Shipper A không được cập nhật / hoàn thành / gửi thông báo
     *             "đã đến điểm giao" cho đơn thuộc shipper B.
     *             - updateStatus: 403 + message "Bạn không có quyền cập nhật đơn hàng này."
     *             - guiThongBao: từ chối (404 do controller filter theo owner)
     *             Dữ liệu đơn và thông báo khách không thay đổi.
     */
    public function test_TS_SHIP_028_shipper_cannot_update_other_shippers_order(): void
    {
        // ───────────── ARRANGE ─────────────
        Cache::flush();

        [$shipperA, $customerA] = $this->makeShipperAndCustomer('shipper_028a');
        [$shipperB, $customerB] = $this->makeShipperAndCustomer('shipper_028b');

        // Đơn của B — trạng thái CONFIRMED (Chờ lấy).
        $orderB = $this->createOrderFor($shipperB, $customerB, [
            'trang_thai'             => 'CONFIRMED',
            'trang_thai_thanh_toan'  => 'CHUA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'tong_tien'              => 500000,
        ]);

        $orderId        = $orderB->ma_hoa_don;
        $maKhachB       = (int) $customerB->ma_khach_hang;
        $statusTruoc    = $orderB->trang_thai;
        $shipperTruoc   = $orderB->ma_nhan_vien_giao;
        $thanhToanTruoc = $orderB->trang_thai_thanh_toan;

        // ───────────── ACT 1 + ASSERT — 028_01: nhận hàng đơn của B ─────────────
        $r1 = $this->actingAs($shipperA)
            ->patchJson("/shipper/don-hang/{$orderId}/cap-nhat");

        $r1->assertStatus(403);
        $r1->assertJsonFragment([
            'success' => false,
            'message' => 'Bạn không có quyền cập nhật đơn hàng này.',
        ]);

        // Không side effect sau 028_01.
        $orderB->refresh();
        $this->assertEquals($statusTruoc,  $orderB->trang_thai);
        $this->assertEquals($shipperTruoc, $orderB->ma_nhan_vien_giao);
        $this->assertNull(
            Cache::get("shipping_start_{$orderId}"),
            'Không được ghi shipping_start cho đơn shipper khác.'
        );
        $this->assertEmpty(
            Cache::get("thong_bao_khach_{$maKhachB}", []),
            'Khách B không được nhận thông báo từ shipper A.'
        );

        // ───────────── ACT 2 + ASSERT — 028_02: hoàn thành đơn của B ─────────────
        // Set đơn B sang SHIPPING để kiểm nhánh DELIVERED.
        $orderB->update(['trang_thai' => 'SHIPPING']);
        $orderB->refresh();
        $statusShipping = $orderB->trang_thai;

        $r2 = $this->actingAs($shipperA)
            ->patchJson("/shipper/don-hang/{$orderId}/cap-nhat");

        $r2->assertStatus(403);
        $r2->assertJsonFragment([
            'success' => false,
            'message' => 'Bạn không có quyền cập nhật đơn hàng này.',
        ]);

        // Đơn B vẫn SHIPPING — không bị chuyển sang DELIVERED.
        $orderB->refresh();
        $this->assertEquals($statusShipping,           $orderB->trang_thai);
        $this->assertEquals($thanhToanTruoc,           $orderB->trang_thai_thanh_toan);
        $this->assertNull($orderB->ngay_giao,          'ngay_giao không được ghi.');
        $this->assertNull($orderB->fresh()->ngay_giao, 'ngay_giao không được ghi (double check).');

        // ───────────── ACT 3 + ASSERT — 028_03: gửi "đã đến điểm giao" ─────────────
        $r3 = $this->actingAs($shipperA)
            ->postJson("/shipper/don-hang/{$orderId}/da-den-noi");

        // Controller dùng findOrFail sau khi filter owner → 404.
        $this->assertTrue(
            in_array($r3->getStatusCode(), [403, 404], true),
            'Gửi "đã đến điểm giao" cho đơn shipper khác phải bị từ chối, nhận: '
            . $r3->getStatusCode()
        );
        $this->assertLessThan(
            500,
            $r3->getStatusCode(),
            'Không được trả HTTP 500.'
        );

        // Khách B vẫn không nhận thông báo nào.
        $this->assertEmpty(
            Cache::get("thong_bao_khach_{$maKhachB}", []),
            'Khách B không được nhận thông báo "đã đến điểm giao" từ shipper A.'
        );

        // ───────────── ASSERT cuối — shipper A cũng không nhận gì ─────────────
        $this->assertEmpty(
            Cache::get("thong_bao_shipper_{$shipperA->ma_nguoi_dung}", []),
            'Shipper A không được nhận thông báo phát sinh từ đơn của B.'
        );
    }
        /**
     * Test Case : TS-SHIP-029 (bao phủ UC4.2_FUNC_029_01 → 029_03)
     * Phạm vi   : Chỉ verify dashboard shipper phản ứng đúng với state DB
     *             ở 3 mốc: trước xác nhận (DA_THANH_TOAN), sau xác nhận 1 đơn,
     *             sau xác nhận tất cả (DA_NOP). Không test controller admin.
     */
    public function test_TS_SHIP_029_cod_totals_before_and_after_admin_confirmation(): void
    {
        // ───────────── ARRANGE ─────────────
        Cache::flush();

        [$shipper, $customer] = $this->makeShipperAndCustomer('shipper_029');

        // 2 đơn COD đã giao, đã thu khách, chưa nộp — 600k và 240k.
        $order1 = $this->createOrderFor($shipper, $customer, [
            'trang_thai'             => 'DELIVERED',
            'trang_thai_thanh_toan'  => 'DA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'tong_tien'              => 600000,
            'ngay_giao'              => now()->subMinutes(10),
        ]);

        $order2 = $this->createOrderFor($shipper, $customer, [
            'trang_thai'             => 'DELIVERED',
            'trang_thai_thanh_toan'  => 'DA_THANH_TOAN',
            'phuong_thuc_thanh_toan' => 'COD',
            'tong_tien'              => 240000,
            'ngay_giao'              => now()->subMinutes(20),
        ]);

        // ───────────── 029_01 — Trước xác nhận ─────────────
        $before = $this->actingAs($shipper)->get('/shipper/dashboard');
        $before->assertOk();

        // Tổng COD = 600k + 240k = 840k; số đơn = 2.
        $before->assertViewHas(
            'tienCanTra',
            fn ($v) => (float) $v === 840000.0,
            'Trước xác nhận: tổng COD phải = 840.000 (2 đơn DA_THANH_TOAN).'
        );
        $before->assertViewHas(
            'soDonConNo',
            fn ($v) => (int) $v === 2,
            'Trước xác nhận: số đơn COD chưa nộp phải = 2.'
        );

        // ───────────── 029_02 — Mô phỏng Admin xác nhận đơn 1 ─────────────
        // Ghi trực tiếp DB (thay cho thao tác admin).
        $order1->update(['trang_thai_thanh_toan' => 'DA_NOP']);

        $after1 = $this->actingAs($shipper)->get('/shipper/dashboard');
        $after1->assertOk();

        // Tổng COD = 240.000 (chỉ còn đơn 2); số đơn = 1.
        $after1->assertViewHas(
            'tienCanTra',
            fn ($v) => (float) $v === 240000.0,
            'Sau xác nhận 1 đơn: tổng COD phải = 240.000 (loại đơn DA_NOP).'
        );
        $after1->assertViewHas(
            'soDonConNo',
            fn ($v) => (int) $v === 1,
            'Sau xác nhận 1 đơn: số đơn chưa nộp phải = 1.'
        );

        // ───────────── 029_03 — Mô phỏng Admin xác nhận đơn 2 ─────────────
        $order2->update(['trang_thai_thanh_toan' => 'DA_NOP']);

        $after2 = $this->actingAs($shipper)->get('/shipper/dashboard');
        $after2->assertOk();

        // Tổng COD = 0; số đơn = 0.
        $after2->assertViewHas(
            'tienCanTra',
            fn ($v) => (float) $v === 0.0,
            'Sau khi tất cả đơn đã nộp: tổng COD phải = 0.'
        );
        $after2->assertViewHas(
            'soDonConNo',
            fn ($v) => (int) $v === 0,
            'Sau khi tất cả đơn đã nộp: số đơn chưa nộp phải = 0.'
        );

        // ───────────── ASSERT cuối — DB đúng state ─────────────
        $this->assertDatabaseHas('hoa_don', [
            'ma_hoa_don'            => $order1->ma_hoa_don,
            'trang_thai_thanh_toan' => 'DA_NOP',
        ]);
        $this->assertDatabaseHas('hoa_don', [
            'ma_hoa_don'            => $order2->ma_hoa_don,
            'trang_thai_thanh_toan' => 'DA_NOP',
        ]);
    }
        /**
     * Test Case : TS-SHIP-030 (bao phủ UC4.2_FUNC_030_01 → 030_04)
     * Mục đích  : Mô phỏng lỗi tại 2 nhóm thao tác (đổi mật khẩu + gửi
     *             thông báo "đã đến điểm giao"), kiểm tra:
     *             1. Response KHÔNG được là 500 (SRS E3 yêu cầu thông báo
     *                "Có lỗi xảy ra, vui lòng thử lại.").
     *             2. Dữ liệu trước thao tác được bảo toàn.
     *             3. Sau khi phục hồi, thao tác lại thành công.
     */
    public function test_TS_SHIP_030_error_handling_and_retry_for_password_and_notification(): void
    {
        // ───────────── SETUP ─────────────
        Cache::flush();

        [$shipper, $customer] = $this->makeShipperAndCustomer('shipper_030');

        // Setup mật khẩu hiện tại.
        $shipperModel = NguoiDung::findOrFail($shipper->ma_nguoi_dung);
        $shipperModel->mat_khau = '123456';
        $shipperModel->save();
        $hashBefore = $shipperModel->fresh()->mat_khau;

        // Setup đơn SHIPPING cho phần gửi thông báo.
        $order = $this->createOrderFor($shipper, $customer, [
            'trang_thai'             => 'SHIPPING',
            'phuong_thuc_thanh_toan' => 'COD',
            'tong_tien'              => 300000,
        ]);
        $maKhach = (int) $customer->ma_khach_hang;

        // ═══════════════════════════════════════════════════════════
        // PHASE 1 — Đổi mật khẩu gặp lỗi DB
        // ═══════════════════════════════════════════════════════════
        Event::listen(
            'eloquent.saving: ' . NguoiDung::class,
            fn () => throw new \RuntimeException('Simulated DB failure')
        );

        $r1 = $this->actingAs($shipper)
            ->from('/shipper/profile')
            ->patch('/shipper/profile/password', [
                'mat_khau_cu'              => '123456',
                'mat_khau_moi'             => '654321',
                'mat_khau_moi_confirmation'=> '654321',
            ]);

        Event::forget('eloquent.saving: ' . NguoiDung::class);

        // ASSERT 1a: không được 500 (SRS E3).
        $this->assertLessThan(
            500,
            $r1->getStatusCode(),
            'PHASE 1: Lỗi DB khi đổi mật khẩu không được trả HTTP 500 (SRS yêu cầu "Có lỗi xảy ra, vui lòng thử lại.").'
        );

        // ASSERT 1b: hash mật khẩu trong CSDL không đổi.
        $hashAfterFail = NguoiDung::findOrFail($shipper->ma_nguoi_dung)->mat_khau;
        $this->assertEquals(
            $hashBefore,
            $hashAfterFail,
            'PHASE 1: Mật khẩu cũ phải được bảo toàn khi đổi thất bại.'
        );

        // ═══════════════════════════════════════════════════════════
        // PHASE 2 — Retry đổi mật khẩu sau phục hồi
        // ═══════════════════════════════════════════════════════════
        $r2 = $this->actingAs($shipper)
            ->from('/shipper/profile')
            ->patch('/shipper/profile/password', [
                'mat_khau_cu'              => '123456',
                'mat_khau_moi'             => '654321',
                'mat_khau_moi_confirmation'=> '654321',
            ]);

        $r2->assertRedirect(route('shipper.profile.edit'));
        $r2->assertSessionHas('success_password');

        $this->assertTrue(
            Hash::check('654321', NguoiDung::findOrFail($shipper->ma_nguoi_dung)->mat_khau),
            'PHASE 2: Mật khẩu mới phải có hiệu lực sau khi retry thành công.'
        );

        // ═══════════════════════════════════════════════════════════
        // PHASE 3 — Gửi thông báo "đã đến điểm giao" gặp lỗi Cache
        // ═══════════════════════════════════════════════════════════
        // Mock Cache facade: cho phép đọc trả về rỗng, put throw.
        // Bắt buộc phải mock sau khi đã dùng Cache thật cho SETUP.
        Cache::shouldReceive('get')->andReturn([]);
        Cache::shouldReceive('put')->andThrow(new \RuntimeException('Cache store down'));

        $r3 = $this->actingAs($shipper)
            ->postJson("/shipper/don-hang/{$order->ma_hoa_don}/da-den-noi");

        // Khôi phục Cache facade thật để verify sau.
        Cache::swap(app('cache')->store());

        // ASSERT 3a: không được 500.
        $this->assertLessThan(
            500,
            $r3->getStatusCode(),
            'PHASE 3: Lỗi gửi thông báo không được trả HTTP 500 (SRS yêu cầu "Có lỗi xảy ra, vui lòng thử lại.").'
        );

        // ASSERT 3b: khách không nhận thông báo.
        $this->assertEmpty(
            Cache::get("thong_bao_khach_{$maKhach}", []),
            'PHASE 3: Khách không được nhận thông báo khi thao tác thất bại.'
        );

        // ASSERT 3c: đơn vẫn SHIPPING, dữ liệu không đổi.
        $order->refresh();
        $this->assertEquals(
            'SHIPPING',
            $order->trang_thai,
            'PHASE 3: Trạng thái đơn không được thay đổi khi gửi thông báo thất bại.'
        );

        // ═══════════════════════════════════════════════════════════
        // PHASE 4 — Retry gửi thông báo sau phục hồi
        // ═══════════════════════════════════════════════════════════
        $r4 = $this->actingAs($shipper)
            ->postJson("/shipper/don-hang/{$order->ma_hoa_don}/da-den-noi");

        $r4->assertOk();
        $r4->assertJson(['success' => true]);

        $notifs = Cache::get("thong_bao_khach_{$maKhach}", []);
        $this->assertCount(
            1,
            $notifs,
            'PHASE 4: Khách phải nhận đúng 1 thông báo sau retry (không bị nhân đôi).'
        );
    }
}
