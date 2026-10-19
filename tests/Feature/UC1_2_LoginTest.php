<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\NguoiDung;
use App\Models\KhachHang;
use App\Models\NhanVien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

/**
 * Suite kiểm thử tự động Backend (Feature Test) cho Use Case UC1.2 – Đăng nhập
 * Học phần: Đánh giá và kiểm định chất lượng phần mềm
 * Sinh viên thực hiện: Nguyễn Kiều Trang (23010495)
 * Căn cứ: SRS UC1.2 (BR-1 đến BR-17), Test Plan Mục 5.2, Workbook sheet func_UC1.2
 */
class UC1_2_LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Chỉ định các migration cần thiết cho môi trường test SQLite in-memory.
     * Bỏ qua các migration không tương thích hoặc lỗi của module khác.
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

    /**
     * Helper tạo tài khoản người dùng thử nghiệm
     */
    private function createTestUser(string $username, string $password, string $role): NguoiDung
    {
        $user = NguoiDung::create([
            'ten_dang_nhap' => $username,
            'mat_khau'      => $password, // Mutator trong NguoiDung sẽ tự bcrypt
            'vai_tro'       => $role,
        ]);

        if ($role === 'KHACH_HANG') {
            KhachHang::create([
                'ma_khach_hang'  => $user->ma_nguoi_dung,
                'ten_khach_hang' => 'Khách Hàng Test',
                'so_dien_thoai'  => '0987654321',
                'email'          => $username . '@example.com',
                'dia_chi'        => 'Hà Nội',
                'diem_tich_luy'  => 0,
            ]);
        } elseif (in_array($role, ['ADMIN', 'NHAN_VIEN', 'SHIPPER'])) {
            NhanVien::create([
                'ma_nhan_vien'  => $user->ma_nguoi_dung,
                'ten_nhan_vien' => 'Nhân Viên Test ' . $role,
                'email'         => $username . '@example.com',
                'so_dien_thoai' => '0912345678',
                'chuc_vu'       => $role === 'SHIPPER' ? 'SHIPPER' : 'CSKH',
            ]);
        }

        return $user;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 0. GIAO DIỆN FORM ĐĂNG NHẬP
    // ─────────────────────────────────────────────────────────────────────────

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        $response->assertSee('ten_dang_nhap');
        $response->assertSee('password');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. NHÓM UC1.2_FUNC_001: ĐĂNG NHẬP HỢP LỆ VÀ ĐIỀU HƯỚNG THEO 4 VAI TRÒ
    // Căn cứ SRS: BR-1, BR-4, BR-6, BR-16
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * TC: UC1.2_FUNC_001_01
     * Đăng nhập hợp lệ bằng tài khoản Khách hàng -> Chuyển đến Trang chủ Khách hàng
     */
    public function test_UC1_2_FUNC_001_01_customer_can_login_and_redirects_to_customer_dashboard(): void
    {
        $this->createTestUser('khachhang_test', '123456', 'KHACH_HANG');

        $response = $this->post('/login', [
            'ten_dang_nhap' => 'khachhang_test',
            'password'      => '123456',
        ]);

        $this->assertAuthenticated();
        $this->assertEquals('KHACH_HANG', Auth::user()->vai_tro);
        $response->assertRedirect(route('customer.dashboard'));
    }

    /**
     * TC: UC1.2_FUNC_001_02
     * Đăng nhập thành công bằng tài khoản Admin -> Chuyển đến Trang quản trị của Admin
     */
    public function test_UC1_2_FUNC_001_02_admin_can_login_and_redirects_to_admin_dashboard(): void
    {
        $this->createTestUser('admin_test', '123456', 'ADMIN');

        $response = $this->post('/login', [
            'ten_dang_nhap' => 'admin_test',
            'password'      => '123456',
        ]);

        $this->assertAuthenticated();
        $this->assertEquals('ADMIN', Auth::user()->vai_tro);
        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * TC: UC1.2_FUNC_001_03
     * Đăng nhập thành công bằng tài khoản Nhân viên -> Chuyển đến Trang quản trị của Nhân viên
     */
    public function test_UC1_2_FUNC_001_03_staff_can_login_and_redirects_to_admin_dashboard(): void
    {
        $this->createTestUser('nhanvien_test', '123456', 'NHAN_VIEN');

        $response = $this->post('/login', [
            'ten_dang_nhap' => 'nhanvien_test',
            'password'      => '123456',
        ]);

        $this->assertAuthenticated();
        $this->assertEquals('NHAN_VIEN', Auth::user()->vai_tro);
        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * TC: UC1.2_FUNC_001_04
     * Đăng nhập thành công bằng tài khoản Shipper -> Chuyển đến Trang chủ Shipper
     */
    public function test_UC1_2_FUNC_001_04_shipper_can_login_and_redirects_to_shipper_dashboard(): void
    {
        $this->createTestUser('shipper_test', '123456', 'SHIPPER');

        $response = $this->post('/login', [
            'ten_dang_nhap' => 'shipper_test',
            'password'      => '123456',
        ]);

        $this->assertAuthenticated();
        $this->assertEquals('SHIPPER', Auth::user()->vai_tro);
        $response->assertRedirect(route('shipper.dashboard'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. NHÓM UC1.2_FUNC_002: BỎ TRỐNG DỮ LIỆU ĐẦU VÀO (VALIDATION)
    // Căn cứ SRS: Luồng thay thế A2, BR-2
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * TC: UC1.2_FUNC_002_01
     * Đăng nhập khi bỏ trống cả 2 trường tên đăng nhập và mật khẩu
     */
    public function test_UC1_2_FUNC_002_01_login_fails_when_both_fields_empty(): void
    {
        $response = $this->post('/login', [
            'ten_dang_nhap' => '',
            'password'      => '',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['ten_dang_nhap', 'password']);
    }

    /**
     * TC: UC1.2_FUNC_002_02
     * Đăng nhập khi bỏ trống tên đăng nhập
     */
    public function test_UC1_2_FUNC_002_02_login_fails_when_username_is_empty(): void
    {
        $response = $this->post('/login', [
            'ten_dang_nhap' => '',
            'password'      => '123456',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['ten_dang_nhap']);
    }

    /**
     * TC: UC1.2_FUNC_002_03
     * Đăng nhập khi bỏ trống mật khẩu
     */
    public function test_UC1_2_FUNC_002_03_login_fails_when_password_is_empty(): void
    {
        $response = $this->post('/login', [
            'ten_dang_nhap' => 'khachhang_test',
            'password'      => '',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['password']);
    }

    /**
     * TC: UC1.2_FUNC_002_04
     * Đăng nhập với tên đăng nhập chỉ chứa dấu cách / khoảng trắng
     */
    public function test_UC1_2_FUNC_002_04_login_fails_when_username_contains_only_whitespace(): void
    {
        $response = $this->post('/login', [
            'ten_dang_nhap' => '     ',
            'password'      => '123456',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['ten_dang_nhap']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. NHÓM UC1.2_FUNC_003: THÔNG TIN XÁC THỰC KHÔNG HỢP LỆ
    // Căn cứ SRS: Luồng thay thế A3, BR-3, BR-5 (Thông báo chung: "Tên đăng nhập hoặc mật khẩu không đúng.")
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * TC: UC1.2_FUNC_003_01
     * Đăng nhập bằng tên tài khoản không tồn tại
     */
    public function test_UC1_2_FUNC_003_01_login_fails_when_username_does_not_exist(): void
    {
        $response = $this->from('/login')->post('/login', [
            'ten_dang_nhap' => 'user_khong_ton_tai_9999',
            'password'      => '123456',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'ten_dang_nhap' => 'Tên đăng nhập hoặc mật khẩu không đúng.',
        ]);
    }

    /**
     * TC: UC1.2_FUNC_003_02
     * Đăng nhập bằng tên tài khoản đúng nhưng mật khẩu sai
     */
    public function test_UC1_2_FUNC_003_02_login_fails_when_password_is_incorrect(): void
    {
        $this->createTestUser('khachhang_valid', '123456', 'KHACH_HANG');

        $response = $this->from('/login')->post('/login', [
            'ten_dang_nhap' => 'khachhang_valid',
            'password'      => 'mat_khau_sai_hoan_toan',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'ten_dang_nhap' => 'Tên đăng nhập hoặc mật khẩu không đúng.',
        ]);
    }

    /**
     * TC: UC1.2_FUNC_003_03
     * Đăng nhập bằng Email thay cho Tên đăng nhập
     * BR-3: Không mặc định coi email là thông tin thay thế tên đăng nhập
     */
    public function test_UC1_2_FUNC_003_03_login_fails_when_using_email_instead_of_username(): void
    {
        $this->createTestUser('khachhang_email_test', '123456', 'KHACH_HANG');

        $response = $this->from('/login')->post('/login', [
            'ten_dang_nhap' => 'khachhang_email_test@example.com',
            'password'      => '123456',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'ten_dang_nhap' => 'Tên đăng nhập hoặc mật khẩu không đúng.',
        ]);
    }

    /**
     * TC: UC1.2_FUNC_003_04
     * Đăng nhập bằng Số điện thoại thay cho Tên đăng nhập
     * BR-3: Không coi số điện thoại là thông tin đăng nhập thay thế
     */
    public function test_UC1_2_FUNC_003_04_login_fails_when_using_phone_instead_of_username(): void
    {
        $this->createTestUser('khachhang_phone_test', '123456', 'KHACH_HANG');

        $response = $this->from('/login')->post('/login', [
            'ten_dang_nhap' => '0987654321',
            'password'      => '123456',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'ten_dang_nhap' => 'Tên đăng nhập hoặc mật khẩu không đúng.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. NHÓM UC1.2_FUNC_004: QUẢN LÝ PHIÊN ĐĂNG NHẬP VÀ ĐĂNG XUẤT
    // Căn cứ SRS: Luồng thay thế A4, A7, BR-11, BR-12
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * TC: UC1.2_FUNC_004_01
     * Truy cập trực tiếp trang cần xác thực khi chưa đăng nhập -> Chuyển hướng sang /login
     */
    public function test_UC1_2_FUNC_004_01_unauthenticated_user_cannot_access_protected_route(): void
    {
        // Truy cập giỏ hàng hoặc trang cá nhân yêu cầu auth
        $response = $this->get('/customer/gio-hang');

        $response->assertRedirect(route('login'));
    }

    /**
     * TC: UC1.2_FUNC_004_02
     * Đăng xuất khỏi hệ thống: hủy phiên làm việc, không thể tiếp tục truy cập trang bảo vệ
     */
    public function test_UC1_2_FUNC_004_02_authenticated_user_can_logout_and_session_invalidated(): void
    {
        $user = $this->createTestUser('user_logout_test', '123456', 'KHACH_HANG');

        // Đăng nhập người dùng vào session
        $this->actingAs($user);
        $this->assertAuthenticated();

        // Thực hiện POST /logout
        $response = $this->post('/logout');

        // Phiên đăng nhập bị hủy
        $this->assertGuest();
        $response->assertRedirect(route('customer.dashboard'));

        // Cố truy cập lại trang bảo vệ bằng phiên này -> bị chặn, chuyển hướng sang /login
        $followUp = $this->get('/customer/gio-hang');
        $followUp->assertRedirect(route('login'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. NHÓM UC1.2_FUNC_005: PHÂN QUYỀN TRUY CẬP THEO VAI TRÒ (RBAC - 403)
    // Căn cứ SRS: Luồng thay thế A5, BR-7, BR-8, BR-9, BR-10, BR-14
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * TC: UC1.2_FUNC_005_01
     * Khách hàng truy cập trang báo cáo doanh thu chỉ dành cho Admin -> Bị từ chối (403 Forbidden)
     */
    public function test_UC1_2_FUNC_005_01_customer_cannot_access_admin_revenue_report(): void
    {
        $customer = $this->createTestUser('customer_rbac_1', '123456', 'KHACH_HANG');

        $response = $this->actingAs($customer)->get('/bao-cao/doanh-thu');

        $response->assertStatus(403);
    }

    /**
     * TC: UC1.2_FUNC_005_02
     * Khách hàng truy cập chức năng Admin Dashboard -> Bị từ chối (403 Forbidden)
     */
    public function test_UC1_2_FUNC_005_02_customer_cannot_access_admin_dashboard(): void
    {
        $customer = $this->createTestUser('customer_rbac_2', '123456', 'KHACH_HANG');

        $response = $this->actingAs($customer)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /**
     * TC: UC1.2_FUNC_005_03
     * Khách hàng truy cập chức năng của Shipper Dashboard -> Bị từ chối (403 Forbidden)
     */
    public function test_UC1_2_FUNC_005_03_customer_cannot_access_shipper_dashboard(): void
    {
        $customer = $this->createTestUser('customer_rbac_3', '123456', 'KHACH_HANG');

        $response = $this->actingAs($customer)->get('/shipper/dashboard');

        $response->assertStatus(403);
    }

    /**
     * TC: UC1.2_FUNC_005_04
     * Nhân viên (NHAN_VIEN) truy cập chức năng chỉ dành riêng cho Quản trị viên (/nguoi-dung) -> Bị từ chối (403 Forbidden)
     * Căn cứ SRS: R1.4, BR-9 (Nhân viên không được thực hiện chức năng chỉ dành cho quản trị viên)
     */
    public function test_UC1_2_FUNC_005_04_staff_cannot_access_admin_only_routes(): void
    {
        $staff = $this->createTestUser('staff_rbac_4', '123456', 'NHAN_VIEN');

        $response = $this->actingAs($staff)->get('/nguoi-dung');

        $response->assertStatus(403);
    }

    /**
     * TC: UC1.2_FUNC_005_05
     * Shipper truy cập chức năng quản trị Admin Dashboard -> Bị từ chối (403 Forbidden)
     */
    public function test_UC1_2_FUNC_005_05_shipper_cannot_access_admin_dashboard(): void
    {
        $shipper = $this->createTestUser('shipper_rbac_5', '123456', 'SHIPPER');

        $response = $this->actingAs($shipper)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. KIỂM THỬ ĐẶC THÙ HỆ THỐNG: GIỮ NGUYÊN GIỎ HÀNG KHI ĐĂNG NHẬP
    // ─────────────────────────────────────────────────────────────────────────

    public function test_cart_session_is_preserved_across_session_regeneration_upon_login(): void
    {
        $this->createTestUser('user_with_cart', '123456', 'KHACH_HANG');

        // Giả lập giỏ hàng có sẵn trước khi người dùng đăng nhập
        $guestCart = [
            1 => ['ma_san_pham' => 1, 'so_luong' => 2, 'gia' => 150000],
        ];

        $response = $this->withSession(['gio_hang' => $guestCart])
            ->post('/login', [
                'ten_dang_nhap' => 'user_with_cart',
                'password'      => '123456',
            ]);

        $this->assertAuthenticated();
        $response->assertSessionHas('gio_hang', $guestCart);
    }
}
