<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\NguoiDung;
use App\Models\KhachHang;
use App\Models\NhanVien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * ============================================================================
 * KIỂM THỬ TỰ ĐỘNG CHỨC NĂNG (FEATURE TEST) — UC1.2: ĐĂNG NHẬP (AUTHENTICATION)
 * ============================================================================
 * Học phần    : Đánh giá và Kiểm định chất lượng phần mềm
 * Giảng viên  : TS. Mai Xuân Tráng
 * Đề tài      : Đánh giá chất lượng phần mềm quản lý và bán hoa trực tuyến FlowerStore
 * Sinh viên   : Nguyễn Kiều Trang (23010495) — Phụ trách UC1.2
 *
 * Căn cứ kỹ thuật và tài liệu:
 * 1. SRS UC1.2: Luồng chính, các luồng thay thế A1–A8, ngoại lệ và Business Rules BR-1 đến BR-17.
 * 2. Test Plan: Mục 5.2 (Phạm vi UC1.2), Mục 6.2 (Chiến lược kiểm thử), Mục 7 (Tiêu chí chấp nhận).
 * 3. Test Case Workbook: Sheet `func_UC1.2` (19 Functional Test Cases chuẩn hóa) và ma trận RTM.
 * 4. Tiêu chuẩn thiết kế: Tuân thủ cấu trúc chuẩn kịch bản (Scenario-based Testing)
 *    đồng bộ hoàn toàn với bộ test kiểm định của nhóm (UC4_2_ShipperTest).
 *
 * Quy chuẩn triển khai:
 * - Môi trường: SQLite in-memory cô lập hoàn toàn, không can thiệp database MySQL Aiven dùng chung.
 * - Mô hình kiểm thử: AAA (Arrange - Act - Assert) phân tách rõ ràng bằng đường phân cách trực quan.
 * - Đa tầng xác minh: HTTP status/redirect, Session errors/flash, CSDL integrity, bảo mật thông tin (không rò rỉ stack trace).
 * ============================================================================
 */
class UC1_2_LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Chỉ chạy các migration cần thiết cho môi trường test SQLite in-memory.
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
     * Helper: Tạo tài khoản người dùng và hồ sơ đi kèm theo vai trò (Customer hoặc Staff/Shipper).
     * Đảm bảo toàn vẹn khóa ngoại (Foreign Key) trong mô hình cơ sở dữ liệu.
     */
    private function makeUserWithRole(string $username, string $role, string $password = '123456'): NguoiDung
    {
        $user = NguoiDung::create([
            'ten_dang_nhap' => $username,
            'mat_khau'      => $password, // Mutator trong NguoiDung sẽ tự động bcrypt
            'vai_tro'       => $role,
        ]);

        if ($role === 'KHACH_HANG') {
            KhachHang::create([
                'ma_khach_hang'  => $user->ma_nguoi_dung,
                'ten_khach_hang' => 'Khách Hàng ' . $username,
                'so_dien_thoai'  => '0987654' . random_int(100, 999),
                'email'          => $username . '@example.test',
                'dia_chi'        => 'Hà Nội',
                'diem_tich_luy'  => 0,
            ]);
        } elseif (in_array($role, ['ADMIN', 'NHAN_VIEN', 'SHIPPER'])) {
            NhanVien::create([
                'ma_nhan_vien'  => $user->ma_nguoi_dung,
                'ten_nhan_vien' => 'Nhân Viên ' . $username,
                'email'         => $username . '@flowerstore.test',
                'so_dien_thoai' => '0912345' . random_int(100, 999),
                'chuc_vu'       => $role === 'SHIPPER' ? 'SHIPPER' : 'CSKH',
                'cong_viec'     => 'Nhiệm vụ kiểm thử',
                'luong'         => 8000000,
            ]);
        }

        return $user;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SCENARIO 0: KHỞI TẠO VÀ HIỂN THỊ MÀN HÌNH ĐĂNG NHẬP
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Test Case : TS-AUTH-000 (Khởi tạo giao diện đăng nhập)
     * Mục đích  : Màn hình /login hiển thị đầy đủ các trường nhập liệu, token CSRF,
     *             tùy chọn ghi nhớ và liên kết điều hướng sang trang đăng ký.
     *             Không phát sinh lỗi runtime hoặc rò rỉ mã nguồn.
     */
    public function test_TS_AUTH_000_render_login_screen_with_all_required_elements(): void
    {
        // ───────────── ARRANGE ─────────────
        $expectedAction = route('login');
        $expectedRegisterLink = route('register');

        // ───────────── ACT ─────────────
        $response = $this->get('/login');

        // ───────────── ASSERT ─────────────
        // ASSERT 1: Phản hồi HTTP 200 và render đúng view auth.login.
        $response->assertOk();
        $response->assertViewIs('auth.login');

        // ASSERT 2: Form chứa các trường nhập liệu cốt lõi theo đặc tả.
        $response->assertSee('name="ten_dang_nhap"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="remember"', false);
        $response->assertSee('action="' . $expectedAction . '"', false);
        $response->assertSee('href="' . $expectedRegisterLink . '"', false);

        // ASSERT 3: Không rò rỉ lỗi hệ thống / stack trace.
        $response->assertDontSee('Stack trace', false);
        $response->assertDontSee('SQLSTATE', false);
        $response->assertDontSee('vendor/laravel', false);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SCENARIO 1: ĐĂNG NHẬP HỢP LỆ VÀ ĐIỀU HƯỚNG THEO 4 VAI TRÒ
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Data provider cho TS-AUTH-001: 4 vai trò hợp lệ trong hệ thống.
     * Mỗi entry: [vai_tro, route_dieu_huong_mong_doi].
     */
    public static function validRoleLoginProvider(): array
    {
        return [
            '001_01_Khách hàng'              => ['KHACH_HANG', 'customer.dashboard'],
            '001_02_Quản trị viên (Admin)'   => ['ADMIN',      'admin.dashboard'],
            '001_03_Nhân viên cửa hàng'      => ['NHAN_VIEN',  'admin.dashboard'],
            '001_04_Nhân viên giao hàng'     => ['SHIPPER',    'shipper.dashboard'],
        ];
    }

    /**
     * Test Case : TS-AUTH-001 (bao phủ UC1.2_FUNC_001_01, 001_02, 001_03, 001_04)
     * Scenario  : TS-AUTH-001 – Xác thực thành công và phân luồng điều hướng vai trò
     * Mục đích  : Xác thực đúng tên đăng nhập + mật khẩu của cả 4 nhóm người dùng,
     *             hệ thống thiết lập phiên đăng nhập hợp lệ, gán đúng vai trò
     *             và điều hướng chính xác đến bảng điều khiển tương ứng.
     * Căn cứ    : SRS UC1.2 Luồng chính, Quy tắc nghiệp vụ BR-1, BR-4, BR-6, BR-16.
     */
    #[DataProvider('validRoleLoginProvider')]
    public function test_TS_AUTH_001_valid_login_and_role_based_redirection(
        string $role,
        string $expectedRoute
    ): void {
        // ───────────── ARRANGE ─────────────
        $username = 'user_auth_001_' . strtolower($role);
        $password = 'MatKhauChuan@123';
        $user = $this->makeUserWithRole($username, $role, $password);

        $hashBefore = $user->mat_khau;
        $usersCountBefore = NguoiDung::count();

        // ───────────── ACT ─────────────
        $response = $this->post('/login', [
            'ten_dang_nhap' => $username,
            'password'      => $password,
        ]);

        // ───────────── ASSERT ─────────────
        // ASSERT 1: Người dùng đã được xác thực thành công trong hệ thống.
        $this->assertAuthenticatedAs($user);

        // ASSERT 2: Phiên làm việc mang đúng vai trò được cấp quyền.
        $this->assertEquals(
            $role,
            Auth::user()->vai_tro,
            "Vai trò của người dùng xác thực phải là {$role}."
        );

        // ASSERT 3: Điều hướng đúng route tương ứng với quyền hạn.
        $response->assertRedirect(route($expectedRoute));

        // ASSERT 4: Mật khẩu mã hóa trong CSDL được bảo toàn, không bị biến đổi.
        $hashAfter = NguoiDung::find($user->ma_nguoi_dung)->mat_khau;
        $this->assertEquals(
            $hashBefore,
            $hashAfter,
            'Mật khẩu mã hóa trong CSDL không được thay đổi sau khi đăng nhập thành công.'
        );

        // ASSERT 5: Không làm biến động số lượng bản ghi người dùng.
        $this->assertEquals(
            $usersCountBefore,
            NguoiDung::count(),
            'Quá trình đăng nhập không được thêm hoặc xóa bản ghi trong bảng người dùng.'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SCENARIO 2: KIỂM SOÁT DỮ LIỆU ĐẦU VÀO RỖNG VÀ KHOẢNG TRẮNG (VALIDATION)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Data provider cho TS-AUTH-002: Các trường hợp bỏ trống và ký tự trắng.
     * Mỗi entry: [username, password, mảng_trường_báo_lỗi].
     */
    public static function emptyAndWhitespaceInputProvider(): array
    {
        return [
            '002_01_bỏ trống cả 2 trường'        => ['', '', ['ten_dang_nhap', 'password']],
            '002_02_bỏ trống tên đăng nhập'      => ['', '123456', ['ten_dang_nhap']],
            '002_03_bỏ trống mật khẩu'           => ['khachhang_test', '', ['password']],
            '002_04_username toàn khoảng trắng'  => ['     ', '123456', ['ten_dang_nhap']],
        ];
    }

    /**
     * Test Case : TS-AUTH-002 (bao phủ UC1.2_FUNC_002_01, 002_02, 002_03, 002_04)
     * Scenario  : TS-AUTH-002 – Kiểm tra tính toàn vẹn và bắt buộc của dữ liệu đầu vào
     * Mục đích  : Bỏ trống một hoặc cả hai trường, hoặc nhập toàn ký tự trắng
     *             phải bị từ chối ngay tại tầng validation, trả về lỗi tương ứng
     *             và tuyệt đối không tạo phiên đăng nhập.
     * Căn cứ    : SRS Luồng thay thế A2, Quy tắc nghiệp vụ BR-2.
     */
    #[DataProvider('emptyAndWhitespaceInputProvider')]
    public function test_TS_AUTH_002_reject_empty_and_whitespace_inputs(
        string $username,
        string $password,
        array $expectedErrors
    ): void {
        // ───────────── ARRANGE ─────────────
        $usersCountBefore = NguoiDung::count();

        // ───────────── ACT ─────────────
        $response = $this->from('/login')->post('/login', [
            'ten_dang_nhap' => $username,
            'password'      => $password,
        ]);

        // ───────────── ASSERT ─────────────
        // ASSERT 1: Người dùng vẫn ở trạng thái khách (Guest), không tạo session auth.
        $this->assertGuest();

        // ASSERT 2: Chuyển hướng quay lại trang form đăng nhập.
        $response->assertRedirect('/login');

        // ASSERT 3: Session chứa đúng các trường bị lỗi validation.
        $response->assertSessionHasErrors($expectedErrors);

        // ASSERT 4: CSDL nguyên vẹn.
        $this->assertEquals(
            $usersCountBefore,
            NguoiDung::count(),
            'Số lượng tài khoản trong CSDL không đổi.'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SCENARIO 3: THÔNG TIN XÁC THỰC KHÔNG ĐÚNG & BẢO MẬT CHỐNG DÒ QUÉT TÀI KHOẢN
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Data provider cho TS-AUTH-003: Các trường hợp thông tin xác thực sai lệch.
     */
    public static function invalidCredentialsProvider(): array
    {
        return [
            '003_01_tài khoản không tồn tại'      => ['user_hoan_toan_khong_ton_tai', '123456'],
            '003_02_đúng tài khoản nhưng sai pass' => ['khachhang_real_003', 'mat_khau_hoan_toan_sai'],
            '003_03_thử dùng email thay username' => ['khachhang_real_003@example.test', '123456'],
            '003_04_thử dùng SĐT thay username'   => ['0987654321', '123456'],
        ];
    }

    /**
     * Test Case : TS-AUTH-003 (bao phủ UC1.2_FUNC_003_01, 003_02, 003_03, 003_04)
     * Scenario  : TS-AUTH-003 – Xử lý thông tin không hợp lệ và tuân thủ chuẩn an toàn thông tin
     * Mục đích  : Khi thông tin xác thực sai, hệ thống từ chối đăng nhập và
     *             CHỈ hiển thị một thông báo chung: "Tên đăng nhập hoặc mật khẩu không đúng."
     *             Không phân biệt lỗi để phòng chống tấn công dò quét tài khoản (Username Enumeration).
     *             Mật khẩu không được gửi ngược lại giao diện.
     * Căn cứ    : SRS Luồng thay thế A3, Quy tắc nghiệp vụ BR-3, BR-5, chuẩn OWASP Authentication.
     */
    #[DataProvider('invalidCredentialsProvider')]
    public function test_TS_AUTH_003_reject_invalid_credentials_with_generic_security_message(
        string $loginInput,
        string $passwordInput
    ): void {
        // ───────────── ARRANGE ─────────────
        // Khởi tạo tài khoản thực tế để phục vụ các ca test sai mật khẩu/nhập nhầm email.
        $this->makeUserWithRole('khachhang_real_003', 'KHACH_HANG', '123456');

        // ───────────── ACT ─────────────
        $response = $this->from('/login')->post('/login', [
            'ten_dang_nhap' => $loginInput,
            'password'      => $passwordInput,
        ]);

        // ───────────── ASSERT ─────────────
        // ASSERT 1: Từ chối đăng nhập, trạng thái người dùng vẫn là Guest.
        $this->assertGuest();

        // ASSERT 2: Chuyển hướng quay lại trang đăng nhập.
        $response->assertRedirect('/login');

        // ASSERT 3: Thông báo lỗi đồng nhất theo đúng quy định tại SRS BR-5.
        $response->assertSessionHasErrors([
            'ten_dang_nhap' => 'Tên đăng nhập hoặc mật khẩu không đúng.',
        ]);

        // ASSERT 4: Bảo mật mật khẩu — không flash mật khẩu ngược lại session (onlyInput ten_dang_nhap).
        $this->assertFalse(
            session()->hasOldInput('password'),
            'Mật khẩu đã nhập tuyệt đối không được lưu lại trong old input của session.'
        );

        // ASSERT 5: Không hiển thị các thông báo nhạy cảm gây lộ diện tài khoản.
        $response->assertDontSee('User not found', false);
        $response->assertDontSee('Tài khoản không tồn tại', false);
        $response->assertDontSee('Sai mật khẩu', false);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SCENARIO 4: QUẢN LÝ VÒNG ĐỜI PHIÊN LÀM VIỆC VÀ ĐĂNG XUẤT AN TOÀN
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Test Case : TS-AUTH-004 (bao phủ UC1.2_FUNC_004_01, 004_02)
     * Scenario  : TS-AUTH-004 – Vòng đời phiên làm việc, kiểm soát truy cập và đăng xuất
     * Mục đích  :
     *   1. Khách chưa đăng nhập truy cập trang cần bảo vệ -> bị chuyển hướng về /login.
     *   2. Khi người dùng đăng xuất (POST /logout), hệ thống hủy phiên đăng nhập,
     *      thu hồi quyền và ngăn chặn việc tái sử dụng phiên cũ.
     * Căn cứ    : SRS Luồng thay thế A4, A7, Quy tắc nghiệp vụ BR-11, BR-12, BR-17.
     */
    public function test_TS_AUTH_004_session_lifecycle_unauthenticated_guard_and_secure_logout(): void
    {
        // ───────────── ARRANGE ─────────────
        $customer = $this->makeUserWithRole('customer_session_004', 'KHACH_HANG', '123456');

        // ───────────── ACT & ASSERT 1: Chưa đăng nhập cố truy cập trang bảo vệ ─────────────
        $unauthResponse = $this->get('/customer/gio-hang');
        $unauthResponse->assertRedirect(route('login'));

        // ───────────── ACT & ASSERT 2: Đăng nhập thành công và sử dụng chức năng ─────────────
        $this->actingAs($customer);
        $this->assertAuthenticatedAs($customer);

        $authResponse = $this->get('/customer/gio-hang');
        $authResponse->assertOk();

        // ───────────── ACT & ASSERT 3: Thực hiện đăng xuất (POST /logout) ─────────────
        $logoutResponse = $this->post('/logout');

        // ASSERT 3.1: Trạng thái người dùng chuyển thành Guest.
        $this->assertGuest();

        // ASSERT 3.2: Điều hướng về trang chủ cửa hàng theo thiết kế hệ thống.
        $logoutResponse->assertRedirect(route('customer.dashboard'));

        // ───────────── ACT & ASSERT 4: Thử truy cập lại bằng phiên đã đăng xuất ─────────────
        $reAccessResponse = $this->get('/customer/gio-hang');
        $reAccessResponse->assertRedirect(route('login'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SCENARIO 5: KIỂM SOÁT PHÂN QUYỀN TRUY CẬP CHÉO VAI TRÒ (RBAC - 403 FORBIDDEN)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Data provider cho TS-AUTH-005: Các tình huống truy cập vượt quyền hạn.
     * Mỗi entry: [vai_tro_nguoi_dung, url_thu_truy_cap, http_status_mong_doi].
     */
    public static function crossRoleAccessProvider(): array
    {
        return [
            '005_01_khách hàng vào báo cáo doanh thu admin' => ['KHACH_HANG', '/bao-cao/doanh-thu', 403],
            '005_02_khách hàng vào admin dashboard'          => ['KHACH_HANG', '/admin/dashboard',   403],
            '005_03_khách hàng vào shipper dashboard'        => ['KHACH_HANG', '/shipper/dashboard', 403],
            '005_04_nhân viên vào quản lý người dùng admin'  => ['NHAN_VIEN',  '/nguoi-dung',         403],
            '005_05_shipper vào admin dashboard'             => ['SHIPPER',    '/admin/dashboard',   403],
        ];
    }

    /**
     * Test Case : TS-AUTH-005 (bao phủ UC1.2_FUNC_005_01, 005_02, 005_03, 005_04, 005_05)
     * Scenario  : TS-AUTH-005 – Kiểm soát phân quyền truy cập chéo vai trò (RBAC)
     * Mục đích  : Người dùng dù đã đăng nhập thành công nhưng cố tình truy cập các
     *             tuyến chức năng không thuộc vai trò được cấp quyền đều phải bị
     *             Middleware CheckRole từ chối với mã trạng thái HTTP 403 Forbidden.
     *             Không phát sinh lỗi 500, không làm rò rỉ dữ liệu nhạy cảm.
     * Căn cứ    : SRS Luồng thay thế A5, Quy tắc nghiệp vụ BR-7, BR-8, BR-9, BR-10, BR-14, BR-15.
     */
    #[DataProvider('crossRoleAccessProvider')]
    public function test_TS_AUTH_005_role_based_access_control_rejects_unauthorized_roles(
        string $userRole,
        string $targetUri,
        int $expectedStatus
    ): void {
        // ───────────── ARRANGE ─────────────
        $user = $this->makeUserWithRole('user_rbac_' . strtolower($userRole) . '_' . uniqid(), $userRole);

        // ───────────── ACT ─────────────
        $response = $this->actingAs($user)->get($targetUri);

        // ───────────── ASSERT ─────────────
        // ASSERT 1: Trả về chính xác mã trạng thái 403 Forbidden.
        $response->assertStatus($expectedStatus);

        // ASSERT 2: Không phát sinh mã lỗi 500 hoặc sập ứng dụng.
        $this->assertLessThan(
            500,
            $response->getStatusCode(),
            'Truy cập sai vai trò không bao giờ được gây ra lỗi máy chủ nội bộ 500.'
        );

        // ASSERT 3: Không làm lộ stack trace hoặc cấu trúc thư mục máy chủ.
        $response->assertDontSee('Stack trace', false);
        $response->assertDontSee('SQLSTATE', false);
        $response->assertDontSee('vendor/laravel', false);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SCENARIO 6: BẢO TOÀN DỮ LIỆU GIỎ HÀNG QUA PHIÊN ĐĂNG NHẬP (SESSION INTEGRITY)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Test Case : TS-AUTH-006 (Kiểm thử tính liên tục của trải nghiệm người dùng)
     * Mục đích  : Khi khách vãng lai đã thêm hoa vào giỏ hàng trước khi đăng nhập,
     *             lệnh regenerate session ID (để chống tấn công Session Fixation)
     *             vẫn phải bảo toàn nguyên vẹn dữ liệu giỏ hàng sau khi đăng nhập.
     * Căn cứ    : Logic xử lý tại AuthenticatedSessionController::store().
     */
    public function test_TS_AUTH_006_cart_session_is_preserved_across_session_regeneration(): void
    {
        // ───────────── ARRANGE ─────────────
        $user = $this->makeUserWithRole('customer_cart_006', 'KHACH_HANG', '123456');

        // Giả lập dữ liệu giỏ hàng của khách vãng lai trước khi đăng nhập
        $guestCart = [
            101 => ['ma_san_pham' => 101, 'so_luong' => 3, 'gia_ban' => 250000],
            102 => ['ma_san_pham' => 102, 'so_luong' => 1, 'gia_ban' => 500000],
        ];

        // ───────────── ACT ─────────────
        $response = $this->withSession(['gio_hang' => $guestCart])
            ->post('/login', [
                'ten_dang_nhap' => 'customer_cart_006',
                'password'      => '123456',
            ]);

        // ───────────── ASSERT ─────────────
        // ASSERT 1: Đăng nhập thành công và điều hướng đúng.
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('customer.dashboard'));

        // ASSERT 2: Session giỏ hàng được phục hồi chính xác từng phần tử.
        $response->assertSessionHas('gio_hang', $guestCart);
        $this->assertEquals(
            $guestCart,
            session('gio_hang'),
            'Dữ liệu giỏ hàng trước đăng nhập phải được khôi phục nguyên vẹn sau khi đăng nhập.'
        );
    }
}
