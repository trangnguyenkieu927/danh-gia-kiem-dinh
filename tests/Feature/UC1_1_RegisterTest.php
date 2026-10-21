<?php

namespace Tests\Feature;

use App\Models\KhachHang;
use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UC1_1_RegisterTest extends TestCase
{
    use RefreshDatabase {
        refreshDatabase as private refreshDatabaseForTest;
    }

    /**
     * Kiểm tra môi trường trước khi chạy migration.
     */
    public function refreshDatabase(): void
    {
        $this->assertTrue(
            app()->environment('testing'),
            'Dừng test: APP_ENV phải là testing.'
        );

        $this->assertSame(
            'sqlite',
            config('database.default'),
            'Dừng test: kết nối mặc định phải là sqlite.'
        );

        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database'),
            'Dừng test: SQLite phải sử dụng :memory:.'
        );

        $this->assertEmpty(
            config('database.connections.sqlite.url'),
            'Dừng test: không được có DB_URL ghi đè kết nối SQLite.'
        );

        $this->refreshDatabaseForTest();
    }

    /**
     * Chỉ chạy hai migration phục vụ kiểm thử đăng ký.
     * Chạy riêng file test này.
     */
    protected function migrateFreshUsing(): array
    {
        return [
            '--path' => [
                'database/migrations/2026_04_15_100100_create_nguoi_dung_table.php',
                'database/migrations/2026_04_15_100200_create_khach_hang_table.php',
            ],
            '--seed' => false,
        ];
    }

    /**
     * Test Case: TC_AUTH_002_01
     * Scenario: TS-AUTH-002
     *
     * Kiểm tra đăng ký hợp lệ:
     * - Tạo đúng một tài khoản và một hồ sơ khách hàng.
     * - Lưu đúng thông tin và địa chỉ.
     * - Gán vai trò KHACH_HANG.
     * - Băm mật khẩu khi lưu.
     * - Chuyển về trang Đăng nhập theo SRS.
     *
     * Thông báo thành công trên giao diện được kiểm tra riêng.
     */
    public function test_TC_AUTH_002_01_register_valid_account(): void
    {
        // ARRANGE: chuẩn bị dữ liệu đăng ký mới.
        // Giá trị quận/huyện và xã/phường cần đối chiếu danh mục thực tế.
        $data = [
            'ten_dang_nhap' => 'test_auth00201',
            'ten_khach_hang' => 'Nguyễn Văn Kiểm Thử',
            'email' => 'test.auth00201@example.com',
            'so_dien_thoai' => '0912345601',
            'quan_huyen' => 'Hoàn Kiếm',
            'xa_phuong' => 'Hàng Bạc',
            'dia_chi_chi_tiet' => 'Số 10, ngõ 20, đường kiểm thử',
            'mat_khau' => '123456',
            'mat_khau_confirmation' => '123456',
        ];

        $expectedAddress = $data['dia_chi_chi_tiet']
            . ', ' . $data['xa_phuong']
            . ', ' . $data['quan_huyen']
            . ', Hà Nội';

        $usersBefore = NguoiDung::count();
        $customersBefore = KhachHang::count();

        // ACT: gửi yêu cầu đăng ký.
        $response = $this->post('/register', $data);

        // ASSERT 1: không có lỗi validation hoặc lỗi tạo tài khoản.
        $response->assertSessionHasNoErrors();

        // ASSERT 2: tạo đúng một tài khoản và một hồ sơ.
        $this->assertDatabaseCount(
            'nguoi_dung',
            $usersBefore + 1
        );

        $this->assertDatabaseCount(
            'khach_hang',
            $customersBefore + 1
        );

        $user = NguoiDung::where(
            'ten_dang_nhap',
            $data['ten_dang_nhap']
        )->first();

        $this->assertNotNull(
            $user,
            'Không tìm thấy tài khoản vừa đăng ký.'
        );

        // ASSERT 3: vai trò mặc định là Khách hàng.
        $this->assertSame(
            'KHACH_HANG',
            $user->vai_tro
        );

        // ASSERT 4: lưu đúng thông tin và liên kết tài khoản.
        $this->assertDatabaseHas('khach_hang', [
            'ma_khach_hang' => $user->ma_nguoi_dung,
            'ten_khach_hang' => $data['ten_khach_hang'],
            'email' => $data['email'],
            'so_dien_thoai' => $data['so_dien_thoai'],
            'dia_chi' => $expectedAddress,
        ]);

        // ASSERT 5: mật khẩu được băm và xác minh được.
        $storedPassword = $user->getRawOriginal('mat_khau');

        $this->assertNotSame(
            $data['mat_khau'],
            $storedPassword,
            'Mật khẩu đang được lưu dưới dạng văn bản rõ.'
        );

        $this->assertTrue(
            Hash::check($data['mat_khau'], $storedPassword),
            'Mật khẩu lưu trong CSDL không xác minh được với dữ liệu đầu vào.'
        );

        // ASSERT 6: giữ nguyên yêu cầu điều hướng của SRS.
        $response->assertRedirect(route('login'));
    }

    /**
     * Test Case: TC_AUTH_011_02
     * Scenario: TS-AUTH-011
     * Quy tắc: BR-5.
     *
     * Không cho phép đăng ký bằng số điện thoại đã tồn tại.
     */
    public function test_TC_AUTH_011_02_rejects_duplicate_phone(): void
    {
        // ARRANGE: tạo tài khoản A làm đối chiếu.
        $existingUser = NguoiDung::create([
            'ten_dang_nhap' => 'existing_phone_user',
            'mat_khau' => '123456',
            'vai_tro' => 'KHACH_HANG',
        ]);

        $existingCustomer = KhachHang::create([
            'ma_khach_hang' => $existingUser->ma_nguoi_dung,
            'ten_khach_hang' => 'Khách hàng đối chiếu',
            'email' => 'existing.phone@example.com',
            'so_dien_thoai' => '0987654321',
            'dia_chi' => 'Địa chỉ tài khoản đối chiếu',
            'diem_tich_luy' => 0,
        ]);

        // Đọc lại từ CSDL để snapshot trước và sau có cùng tập trường.
        $existingUser->refresh();
        $existingCustomer->refresh();

        $userBefore = $existingUser->getAttributes();
        $customerBefore = $existingCustomer->getAttributes();

        // Chuẩn hóa thứ tự khóa trước khi so sánh bằng assertSame().
        ksort($userBefore);
        ksort($customerBefore);

        $userCountBefore = NguoiDung::count();
        $customerCountBefore = KhachHang::count();

        // Tài khoản B có tên đăng nhập và email mới.
        // Chỉ số điện thoại trùng với tài khoản A.
        $data = [
            'ten_dang_nhap' => 'new_phone_user',
            'ten_khach_hang' => 'Khách hàng đăng ký mới',
            'email' => 'new.phone@example.com',
            'so_dien_thoai' => '0987654321',
            'quan_huyen' => 'Hoàn Kiếm',
            'xa_phuong' => 'Hàng Bạc',
            'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
            'mat_khau' => '123456',
            'mat_khau_confirmation' => '123456',
        ];

        // ACT: gửi yêu cầu đăng ký tài khoản B.
        $response = $this->from('/register')
            ->post('/register', $data);

        // Đọc lại tài khoản A và hồ sơ sau thao tác.
        $userAfterModel = $existingUser->fresh();
        $customerAfterModel = $existingCustomer->fresh();

        $this->assertNotNull(
            $userAfterModel,
            'Tài khoản đối chiếu không còn tồn tại.'
        );

        $this->assertNotNull(
            $customerAfterModel,
            'Hồ sơ khách hàng đối chiếu không còn tồn tại.'
        );

        $userAfter = $userAfterModel->getAttributes();
        $customerAfter = $customerAfterModel->getAttributes();

        ksort($userAfter);
        ksort($customerAfter);

        // ASSERT 1: dữ liệu tài khoản A giữ nguyên.
        $this->assertSame(
            $userBefore,
            $userAfter,
            'Thông tin tài khoản đối chiếu đã bị thay đổi.'
        );

        $this->assertSame(
            $customerBefore,
            $customerAfter,
            'Thông tin khách hàng đối chiếu đã bị thay đổi.'
        );

        // ASSERT 2: không tạo thêm tài khoản hoặc hồ sơ.
        $this->assertDatabaseCount(
            'nguoi_dung',
            $userCountBefore
        );

        $this->assertDatabaseCount(
            'khach_hang',
            $customerCountBefore
        );

        // ASSERT 3: tài khoản B và hồ sơ tương ứng không được tạo.
        $this->assertDatabaseMissing('nguoi_dung', [
            'ten_dang_nhap' => $data['ten_dang_nhap'],
        ]);

        $this->assertDatabaseMissing('khach_hang', [
            'email' => $data['email'],
        ]);

        // ASSERT 4: báo lỗi đúng trường số điện thoại.
        $response->assertSessionHasErrors([
            'so_dien_thoai',
        ]);

        // ASSERT 5: quay về biểu mẫu đăng ký.
        $response->assertRedirect('/register');

        // ASSERT 6: yêu cầu bị từ chối không tạo phiên đăng nhập.
        $this->assertGuest();
    }

    /**
 * Test Case: TC_AUTH_004_02
 * Scenario: TS-AUTH-004
 * Quy tắc: BR-2 — Tên đăng nhập không được trùng.
 */
public function test_TC_AUTH_004_02_rejects_duplicate_username(): void
{
    // ARRANGE: tạo tài khoản A làm đối chiếu.
    $existingUser = NguoiDung::create([
        'ten_dang_nhap' => 'existing_username',
        'mat_khau' => '123456',
        'vai_tro' => 'KHACH_HANG',
    ]);

    $existingCustomer = KhachHang::create([
        'ma_khach_hang' => $existingUser->ma_nguoi_dung,
        'ten_khach_hang' => 'Khách hàng đối chiếu',
        'email' => 'existing.username@example.com',
        'so_dien_thoai' => '0987654321',
        'dia_chi' => 'Địa chỉ tài khoản đối chiếu',
        'diem_tich_luy' => 0,
    ]);

    // Đọc lại từ CSDL trước khi lưu trạng thái đối chiếu.
    $existingUser->refresh();
    $existingCustomer->refresh();

    $userBefore = $existingUser->getAttributes();
    $customerBefore = $existingCustomer->getAttributes();

    ksort($userBefore);
    ksort($customerBefore);

    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    // Chỉ tên đăng nhập bị trùng.
    // Email và số điện thoại khác tài khoản A.
    $data = [
        'ten_dang_nhap' => 'existing_username',
        'ten_khach_hang' => 'Khách hàng đăng ký mới',
        'email' => 'new.username@example.com',
        'so_dien_thoai' => '0912345602',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi yêu cầu đăng ký với tên đăng nhập đã tồn tại.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: báo lỗi đúng trường tên đăng nhập.
    $response->assertSessionHasErrors([
        'ten_dang_nhap',
    ]);

    // Các trường còn lại không được gây lỗi trong ca này.
    $response->assertSessionDoesntHaveErrors([
        'ten_khach_hang',
        'email',
        'so_dien_thoai',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'mat_khau',
        'mat_khau_confirmation',
        'general',
    ]);

    // ASSERT 2: quay lại biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // Không lưu hồ sơ của người đăng ký mới.
    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 4: tài khoản và hồ sơ đối chiếu vẫn tồn tại.
    $userAfterModel = $existingUser->fresh();
    $customerAfterModel = $existingCustomer->fresh();

    $this->assertNotNull(
        $userAfterModel,
        'Tài khoản đối chiếu không còn tồn tại.'
    );

    $this->assertNotNull(
        $customerAfterModel,
        'Hồ sơ khách hàng đối chiếu không còn tồn tại.'
    );

    $userAfter = $userAfterModel->getAttributes();
    $customerAfter = $customerAfterModel->getAttributes();

    ksort($userAfter);
    ksort($customerAfter);

    // ASSERT 5: dữ liệu cũ không bị ghi đè.
    $this->assertSame(
        $userBefore,
        $userAfter,
        'Thông tin tài khoản đối chiếu đã bị thay đổi.'
    );

    $this->assertSame(
        $customerBefore,
        $customerAfter,
        'Thông tin khách hàng đối chiếu đã bị thay đổi.'
    );

    // ASSERT 6: yêu cầu bị từ chối không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_008_02
 * Scenario: TS-AUTH-008
 * Quy tắc: BR-4 — Email không được trùng.
 */
public function test_TC_AUTH_008_02_rejects_duplicate_email(): void
{
    // ARRANGE: tạo tài khoản A và hồ sơ đối chiếu.
    $existingUser = NguoiDung::create([
        'ten_dang_nhap' => 'existing_email_user',
        'mat_khau' => '123456',
        'vai_tro' => 'KHACH_HANG',
    ]);

    $existingCustomer = KhachHang::create([
        'ma_khach_hang' => $existingUser->ma_nguoi_dung,
        'ten_khach_hang' => 'Khách hàng đối chiếu',
        'email' => 'existing.email@example.com',
        'so_dien_thoai' => '0987654321',
        'dia_chi' => 'Địa chỉ tài khoản đối chiếu',
        'diem_tich_luy' => 0,
    ]);

    // Đọc lại từ CSDL trước khi lưu trạng thái đối chiếu.
    $existingUser->refresh();
    $existingCustomer->refresh();

    $userBefore = $existingUser->getAttributes();
    $customerBefore = $existingCustomer->getAttributes();

    ksort($userBefore);
    ksort($customerBefore);

    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    // Tài khoản B: tên đăng nhập và số điện thoại mới.
    // Chỉ email trùng với tài khoản A.
    $data = [
        'ten_dang_nhap' => 'new_email_user',
        'ten_khach_hang' => 'Khách hàng đăng ký mới',
        'email' => 'existing.email@example.com',
        'so_dien_thoai' => '0912345603',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi yêu cầu đăng ký bằng email đã tồn tại.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: tài khoản và hồ sơ cũ vẫn tồn tại.
    $userAfterModel = $existingUser->fresh();
    $customerAfterModel = $existingCustomer->fresh();

    $this->assertNotNull(
        $userAfterModel,
        'Tài khoản đối chiếu không còn tồn tại.'
    );

    $this->assertNotNull(
        $customerAfterModel,
        'Hồ sơ khách hàng đối chiếu không còn tồn tại.'
    );

    $userAfter = $userAfterModel->getAttributes();
    $customerAfter = $customerAfterModel->getAttributes();

    ksort($userAfter);
    ksort($customerAfter);

    // ASSERT 2: dữ liệu cũ không bị ghi đè.
    $this->assertSame(
        $userBefore,
        $userAfter,
        'Thông tin tài khoản đối chiếu đã bị thay đổi.'
    );

    $this->assertSame(
        $customerBefore,
        $customerAfter,
        'Thông tin khách hàng đối chiếu đã bị thay đổi.'
    );

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 4: báo lỗi đúng trường email.
    $response->assertSessionHasErrors([
        'email',
    ]);

    // Các trường còn lại hợp lệ, không được gây lỗi trong ca này.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'so_dien_thoai',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'mat_khau',
        'mat_khau_confirmation',
        'general',
    ]);

    // ASSERT 5: quay về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 6: yêu cầu bị từ chối không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_003_07
 * Scenario: TS-AUTH-003
 * Quy tắc: BR-2 — Tên đăng nhập là bắt buộc.
 *
 * Gửi trực tiếp yêu cầu đăng ký với tên đăng nhập rỗng.
 * Các trường còn lại hợp lệ.
 */
public function test_TC_AUTH_003_07_rejects_empty_username(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => '',
        'ten_khach_hang' => 'Khách hàng kiểm thử',
        'email' => 'empty.username@example.com',
        'so_dien_thoai' => '0912345604',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi yêu cầu trực tiếp, không qua trình duyệt.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: báo lỗi bắt buộc tại trường tên đăng nhập.
    $response->assertSessionHasErrors([
        'ten_dang_nhap',
    ]);

    // Các trường hợp lệ còn lại không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_khach_hang',
        'email',
        'so_dien_thoai',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'mat_khau',
        'mat_khau_confirmation',
        'general',
    ]);

    // ASSERT 2: chuyển về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // ASSERT 4: không lưu hồ sơ từ yêu cầu bị từ chối.
    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_006_02
 * Scenario: TS-AUTH-006
 * Quy tắc: BR-3 — Họ và tên là bắt buộc.
 *
 * Gửi trực tiếp yêu cầu đăng ký với họ và tên rỗng.
 * Các trường còn lại hợp lệ.
 */
public function test_TC_AUTH_006_02_rejects_empty_full_name(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    // Chỉ họ và tên bị bỏ trống.
    $data = [
        'ten_dang_nhap' => 'empty_fullname_user',
        'ten_khach_hang' => '',
        'email' => 'empty.fullname@example.com',
        'so_dien_thoai' => '0912345605',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi yêu cầu trực tiếp đến backend.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: báo lỗi tại trường họ và tên.
    $response->assertSessionHasErrors([
        'ten_khach_hang',
    ]);

    // Các trường hợp lệ còn lại không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'email',
        'so_dien_thoai',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'mat_khau',
        'mat_khau_confirmation',
        'general',
    ]);

    // ASSERT 2: chuyển về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // ASSERT 4: không lưu dữ liệu của yêu cầu bị từ chối.
    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_007_04
 * Scenario: TS-AUTH-007
 * Quy tắc: BR-4 — Email phải đúng định dạng.
 *
 * Gửi trực tiếp email thiếu ký tự @ đến backend.
 */
public function test_TC_AUTH_007_04_rejects_invalid_email(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    // Chỉ email sai định dạng.
    $data = [
        'ten_dang_nhap' => 'invalid_email_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử',
        'email' => 'invalid.email.example.com',
        'so_dien_thoai' => '0912345606',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi trực tiếp, không qua validation của trình duyệt.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: báo lỗi tại trường email.
    $response->assertSessionHasErrors([
        'email',
    ]);

    // Các trường hợp lệ còn lại không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'so_dien_thoai',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'mat_khau',
        'mat_khau_confirmation',
        'general',
    ]);

    // ASSERT 2: chuyển về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // ASSERT 4: không lưu dữ liệu của yêu cầu bị từ chối.
    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_009_04
 * Scenario: TS-AUTH-009
 * Quy tắc: BR-5 — Số điện thoại phải gồm đúng 10 chữ số.
 *
 * Kiểm tra biên trên: số điện thoại có 11 chữ số.
 */
public function test_TC_AUTH_009_04_rejects_phone_with_11_digits(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    // Số điện thoại có đúng 11 chữ số.
    // Các trường còn lại hợp lệ.
    $data = [
        'ten_dang_nhap' => 'phone_11_digits_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử',
        'email' => 'phone.11digits@example.com',
        'so_dien_thoai' => '09123456789',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi trực tiếp yêu cầu đến backend.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: báo lỗi tại trường số điện thoại.
    $response->assertSessionHasErrors([
        'so_dien_thoai',
    ]);

    // Các trường hợp lệ còn lại không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'email',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'mat_khau',
        'mat_khau_confirmation',
        'general',
    ]);

    // ASSERT 2: chuyển về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // ASSERT 4: không lưu dữ liệu của yêu cầu bị từ chối.
    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_009_03
 * Scenario: TS-AUTH-009
 * Quy tắc: BR-5 — Số điện thoại phải gồm đúng 10 chữ số.
 *
 * Kiểm tra biên dưới: số điện thoại có 9 chữ số.
 */
public function test_TC_AUTH_009_03_rejects_phone_with_9_digits(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    // Số điện thoại có đúng 9 chữ số, không chứa ký tự khác.
    // Các trường còn lại hợp lệ.
    $data = [
        'ten_dang_nhap' => 'phone_9_digits_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử',
        'email' => 'phone.9digits@example.com',
        'so_dien_thoai' => '091234567',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi trực tiếp yêu cầu đến backend.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: báo lỗi tại trường số điện thoại.
    $response->assertSessionHasErrors([
        'so_dien_thoai',
    ]);

    // Các trường hợp lệ còn lại không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'email',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'mat_khau',
        'mat_khau_confirmation',
        'general',
    ]);

    // ASSERT 2: chuyển về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // ASSERT 4: không lưu dữ liệu của yêu cầu bị từ chối.
    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_010_04
 * Scenario: TS-AUTH-010
 * Quy tắc: BR-5 — Số điện thoại chỉ được chứa chữ số.
 *
 * Dữ liệu có đúng 10 ký tự nhưng chứa một chữ cái.
 */
public function test_TC_AUTH_010_04_rejects_phone_with_letter(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    // Chỉ số điện thoại vi phạm định dạng.
    $data = [
        'ten_dang_nhap' => 'phone_letter_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử',
        'email' => 'phone.letter@example.com',
        'so_dien_thoai' => '091234567a',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi trực tiếp yêu cầu đến backend.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: báo lỗi tại trường số điện thoại.
    $response->assertSessionHasErrors([
        'so_dien_thoai',
    ]);

    // Các trường hợp lệ còn lại không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'email',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'mat_khau',
        'mat_khau_confirmation',
        'general',
    ]);

    // ASSERT 2: chuyển về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // ASSERT 4: không lưu dữ liệu của yêu cầu bị từ chối.
    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_012_03
 * Scenario: TS-AUTH-012
 * Quy tắc: BR-6 — Mật khẩu phải có tối thiểu 6 ký tự.
 *
 * Kiểm tra biên dưới: mật khẩu có 5 ký tự.
 * Xác nhận mật khẩu khớp; các trường còn lại hợp lệ.
 */
public function test_TC_AUTH_012_03_rejects_password_with_5_characters(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'password_5_chars_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử',
        'email' => 'password.5chars@example.com',
        'so_dien_thoai' => '0912345607',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '12345',
        'mat_khau_confirmation' => '12345',
    ];

    // ACT: gửi trực tiếp yêu cầu đến backend.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: báo lỗi tại trường mật khẩu.
    $response->assertSessionHasErrors([
        'mat_khau',
    ]);

    // Các trường hợp lệ còn lại không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'email',
        'so_dien_thoai',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'mat_khau_confirmation',
        'general',
    ]);

    // ASSERT 2: chuyển về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // ASSERT 4: không lưu dữ liệu của yêu cầu bị từ chối.
    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_013_03
 * Scenario: TS-AUTH-013
 * Quy tắc: BR-7 — Xác nhận mật khẩu phải khớp mật khẩu.
 *
 * Mật khẩu đủ 6 ký tự; xác nhận khác mật khẩu.
 * Các trường còn lại hợp lệ.
 */
public function test_TC_AUTH_013_03_rejects_password_confirmation_mismatch(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'password_mismatch_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử',
        'email' => 'password.mismatch@example.com',
        'so_dien_thoai' => '0912345608',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123457',
    ];

    // ACT: gửi trực tiếp yêu cầu đến backend.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: báo đúng lỗi xác nhận mật khẩu không khớp.
    // Controller đặt quy tắc confirmed trên trường mat_khau.
    $response->assertSessionHasErrors([
        'mat_khau' => 'Xác nhận mật khẩu không khớp.',
    ]);

    // Các trường hợp lệ còn lại không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'email',
        'so_dien_thoai',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'general',
    ]);

    // ASSERT 2: chuyển về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // ASSERT 4: không lưu dữ liệu của yêu cầu bị từ chối.
    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_017_02
 * Scenario: TS-AUTH-017
 * Quy tắc: BR-9 — Đăng ký công khai chỉ tạo vai trò Khách hàng.
 *
 * Gửi thêm vai_tro = ADMIN trong yêu cầu đăng ký.
 * Kiểm tra vai trò thực tế được lưu trong CSDL.
 */
public function test_TC_AUTH_017_02_cannot_register_as_admin(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'role_spoof_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử vai trò',
        'email' => 'role.spoof@example.com',
        'so_dien_thoai' => '0912345609',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',

        // Giả lập sửa request để yêu cầu vai trò Admin.
        'vai_tro' => 'ADMIN',
    ];

    // ACT: gửi yêu cầu đăng ký có trường vai trò giả mạo.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: dữ liệu đăng ký hợp lệ được xử lý.
    $response->assertSessionHasNoErrors();

    // ASSERT 2: tạo đúng một tài khoản và một hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore + 1
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore + 1
    );

    $user = NguoiDung::where(
        'ten_dang_nhap',
        $data['ten_dang_nhap']
    )->first();

    $this->assertNotNull(
        $user,
        'Không tìm thấy tài khoản vừa đăng ký.'
    );

    // ASSERT 3: vai trò thực tế phải là Khách hàng.
    $this->assertSame(
        'KHACH_HANG',
        $user->vai_tro,
        'Tài khoản đăng ký công khai không được mang vai trò đặc quyền.'
    );

    $this->assertDatabaseHas('nguoi_dung', [
        'ma_nguoi_dung' => $user->ma_nguoi_dung,
        'ten_dang_nhap' => $data['ten_dang_nhap'],
        'vai_tro' => 'KHACH_HANG',
    ]);

    // ASSERT 4: không tạo tài khoản Admin mang tên đăng nhập này.
    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
        'vai_tro' => 'ADMIN',
    ]);

    // ASSERT 5: hồ sơ khách hàng liên kết đúng tài khoản mới.
    $this->assertDatabaseHas('khach_hang', [
        'ma_khach_hang' => $user->ma_nguoi_dung,
        'ten_khach_hang' => $data['ten_khach_hang'],
        'email' => $data['email'],
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);
}
/**
 * Test Case: TC_AUTH_018_01
 * Scenario: TS-AUTH-018
 * Quy tắc: BR-10 — Bảo vệ mật khẩu khi lưu.
 *
 * Kiểm tra mật khẩu được băm:
 * - Không lưu dưới dạng văn bản rõ.
 * - Xác minh được với mật khẩu đúng.
 * - Không xác minh được với mật khẩu sai.
 */
public function test_TC_AUTH_018_01_stores_hashed_password(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'password_hash_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử mật khẩu',
        'email' => 'password.hash@example.com',
        'so_dien_thoai' => '0912345610',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => 'TestHash123',
        'mat_khau_confirmation' => 'TestHash123',
    ];

    // ACT: đăng ký thông qua endpoint thực tế.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: yêu cầu không có lỗi validation hoặc tạo tài khoản.
    $response->assertSessionHasNoErrors();

    // ASSERT 2: tạo đúng một tài khoản và một hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore + 1
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore + 1
    );

    // Đọc trực tiếp giá trị mật khẩu từ CSDL.
    $storedPassword = \Illuminate\Support\Facades\DB::table('nguoi_dung')
        ->where('ten_dang_nhap', $data['ten_dang_nhap'])
        ->value('mat_khau');

    // ASSERT 3: mật khẩu được lưu là một chuỗi không rỗng.
    $this->assertIsString(
        $storedPassword,
        'Không tìm thấy mật khẩu dạng chuỗi trong CSDL.'
    );

    $this->assertNotSame(
        '',
        $storedPassword,
        'Mật khẩu lưu trong CSDL đang rỗng.'
    );

    // ASSERT 4: không lưu nguyên văn mật khẩu người dùng nhập.
    $this->assertNotSame(
        $data['mat_khau'],
        $storedPassword,
        'Mật khẩu đang được lưu dưới dạng văn bản rõ.'
    );

    // ASSERT 5: giá trị băm xác minh được mật khẩu đúng.
    $this->assertTrue(
        Hash::check($data['mat_khau'], $storedPassword),
        'Giá trị lưu không xác minh được mật khẩu đã đăng ký.'
    );

    // ASSERT 6: giá trị băm không chấp nhận mật khẩu sai.
    $this->assertFalse(
        Hash::check('WrongHash123', $storedPassword),
        'Giá trị lưu chấp nhận một mật khẩu không đúng.'
    );
}
/**
 * Test Case: TC_AUTH_020_01
 * Scenario: TS-AUTH-020
 * Quy tắc: BR-4, BR-5, BR-7 và BR-12.
 *
 * Gửi đồng thời:
 * - Email thiếu @.
 * - Số điện thoại có 9 chữ số.
 * - Xác nhận mật khẩu không khớp.
 */
public function test_TC_AUTH_020_01_rejects_multiple_invalid_fields(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'multiple_errors_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử',
        'email' => 'invalid.email.example.com',
        'so_dien_thoai' => '091234567',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123457',
    ];

    // ACT: gửi yêu cầu có ba trường không hợp lệ.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: ghi nhận đầy đủ cả ba trường bị lỗi.
    // Lỗi confirmed thuộc khóa mat_khau theo controller.
    $response->assertSessionHasErrors([
        'email',
        'so_dien_thoai',
        'mat_khau',
    ]);

    // Các trường hợp lệ còn lại không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'general',
    ]);

    // ASSERT 2: chuyển về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // ASSERT 4: không lưu dữ liệu của yêu cầu bị từ chối.
    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_019_01
 * Scenario: TS-AUTH-019
 * Tham chiếu: A4, BR-11 và yêu cầu toàn vẹn dữ liệu.
 *
 * Mô phỏng lỗi trước khi lưu hồ sơ khách hàng,
 * sau khi tài khoản đã được tạo trong transaction.
 */
public function test_TC_AUTH_019_01_rolls_back_when_customer_creation_fails(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'rollback_register_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử hoàn nguyên',
        'email' => 'rollback.register@example.com',
        'so_dien_thoai' => '0912345611',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    $failureTriggered = false;
    $userExistedBeforeFailure = false;

    // Khởi tạo model trước khi thiết lập listener kiểm thử.
    new KhachHang();

    // Dùng bản sao dispatcher để không giữ lại listener gây lỗi.
    $originalDispatcher = KhachHang::getEventDispatcher();

    $this->assertNotNull(
        $originalDispatcher,
        'Không có event dispatcher để mô phỏng lỗi.'
    );

    $testDispatcher = clone $originalDispatcher;

    $testDispatcher->listen(
        'eloquent.creating: ' . KhachHang::class,
        function (KhachHang $customer) use (
            $data,
            &$failureTriggered,
            &$userExistedBeforeFailure
        ) {
            // Chỉ gây lỗi cho hồ sơ thuộc ca kiểm thử này.
            if ($customer->email !== $data['email']) {
                return;
            }

            $failureTriggered = true;

            // Xác minh tài khoản đã được ghi trước thời điểm gây lỗi.
            $userExistedBeforeFailure = NguoiDung::where(
                'ma_nguoi_dung',
                $customer->ma_khach_hang
            )->where(
                'ten_dang_nhap',
                $data['ten_dang_nhap']
            )->exists();

            throw new \RuntimeException(
                'TEST_CUSTOMER_SAVE_FAILURE'
            );
        }
    );

    // ACT: gửi yêu cầu trong thời gian bật mô phỏng lỗi.
    KhachHang::setEventDispatcher($testDispatcher);

    try {
        $response = $this->from('/register')
            ->post('/register', $data);
    } finally {
        // Luôn khôi phục dispatcher, kể cả khi request phát sinh lỗi.
        KhachHang::setEventDispatcher($originalDispatcher);
    }

    // ASSERT 1: lỗi đã được kích hoạt đúng tại bước lưu hồ sơ.
    $this->assertTrue(
        $failureTriggered,
        'Chưa chạy đến bước mô phỏng lỗi lưu hồ sơ khách hàng.'
    );

    $this->assertTrue(
        $userExistedBeforeFailure,
        'Chưa chứng minh tài khoản đã tồn tại trước khi gây lỗi.'
    );

    // ASSERT 2: không giữ lại tài khoản hoặc hồ sơ dở dang.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    // ASSERT 3: controller trả về lỗi xử lý chung.
    $response->assertSessionHasErrors([
        'general',
    ]);

    // Dữ liệu đầu vào hợp lệ, không phải lỗi validation.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'email',
        'so_dien_thoai',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'mat_khau',
        'mat_khau_confirmation',
    ]);

    // ASSERT 4: quay lại biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_014_01
 * Scenario: TS-AUTH-014
 * Quy tắc: BR-8 — Thông tin địa chỉ phải đầy đủ.
 *
 * Gửi trực tiếp yêu cầu có Quận/Huyện rỗng.
 * Giữ các trường khác hợp lệ để kiểm tra riêng trường này.
 */
public function test_TC_AUTH_014_01_rejects_empty_district(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'empty_district_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử địa chỉ',
        'email' => 'empty.district@example.com',
        'so_dien_thoai' => '0912345612',
        'quan_huyen' => '',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi trực tiếp yêu cầu đến backend.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: báo lỗi tại trường Quận/Huyện.
    $response->assertSessionHasErrors([
        'quan_huyen',
    ]);

    // Các trường còn lại không phát sinh lỗi
    // theo validation trong controller đã cung cấp.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'email',
        'so_dien_thoai',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'mat_khau',
        'mat_khau_confirmation',
        'general',
    ]);

    // ASSERT 2: chuyển về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // ASSERT 4: không lưu dữ liệu của yêu cầu bị từ chối.
    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_014_02
 * Scenario: TS-AUTH-014
 * Quy tắc: BR-8 — Thông tin địa chỉ phải đầy đủ.
 *
 * Gửi trực tiếp yêu cầu có Xã/Phường rỗng.
 * Các trường còn lại hợp lệ.
 */
public function test_TC_AUTH_014_02_rejects_empty_ward(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'empty_ward_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử địa chỉ',
        'email' => 'empty.ward@example.com',
        'so_dien_thoai' => '0912345613',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => '',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi trực tiếp yêu cầu đến backend.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: báo lỗi tại trường Xã/Phường.
    $response->assertSessionHasErrors([
        'xa_phuong',
    ]);

    // Các trường hợp lệ còn lại không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'email',
        'so_dien_thoai',
        'quan_huyen',
        'dia_chi_chi_tiet',
        'mat_khau',
        'mat_khau_confirmation',
        'general',
    ]);

    // ASSERT 2: chuyển về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // ASSERT 4: không lưu dữ liệu của yêu cầu bị từ chối.
    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_014_03
 * Scenario: TS-AUTH-014
 * Quy tắc: BR-8 — Thông tin địa chỉ phải đầy đủ.
 *
 * Gửi trực tiếp yêu cầu có địa chỉ chi tiết rỗng.
 * Các trường còn lại hợp lệ.
 */
public function test_TC_AUTH_014_03_rejects_empty_address_detail(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'empty_address_detail_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử địa chỉ',
        'email' => 'empty.address.detail@example.com',
        'so_dien_thoai' => '0912345614',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => '',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi trực tiếp yêu cầu đến backend.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: báo lỗi tại trường địa chỉ chi tiết.
    $response->assertSessionHasErrors([
        'dia_chi_chi_tiet',
    ]);

    // Các trường hợp lệ còn lại không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'email',
        'so_dien_thoai',
        'quan_huyen',
        'xa_phuong',
        'mat_khau',
        'mat_khau_confirmation',
        'general',
    ]);

    // ASSERT 2: chuyển về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // ASSERT 4: không lưu dữ liệu của yêu cầu bị từ chối.
    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_005_01
 * Scenario: TS-AUTH-005
 * Quy tắc: BR-2 — Tên đăng nhập chỉ gồm chữ cái,
 * chữ số và dấu gạch dưới.
 *
 * Gửi tên đăng nhập chứa ký tự @.
 * Các trường còn lại hợp lệ.
 */
public function test_TC_AUTH_005_01_rejects_username_with_at_sign(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'invalid@username',
        'ten_khach_hang' => 'Khách hàng kiểm thử',
        'email' => 'invalid.username@example.com',
        'so_dien_thoai' => '0912345615',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi trực tiếp yêu cầu đến backend.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // ASSERT 2: không lưu dữ liệu của yêu cầu không hợp lệ.
    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 3: báo lỗi đúng trường tên đăng nhập.
    $response->assertSessionHasErrors([
        'ten_dang_nhap',
    ]);

    // Các trường hợp lệ còn lại không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_khach_hang',
        'email',
        'so_dien_thoai',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'mat_khau',
        'mat_khau_confirmation',
        'general',
    ]);

    // ASSERT 4: chuyển về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_003_08
 * Scenario: TS-AUTH-003
 * Tham chiếu: A2 — Bỏ trống trường bắt buộc.
 *
 * Gửi trực tiếp yêu cầu có email rỗng.
 * Các trường còn lại hợp lệ.
 */
public function test_TC_AUTH_003_08_rejects_empty_email(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'empty_email_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử',
        'email' => '',
        'so_dien_thoai' => '0912345616',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi trực tiếp yêu cầu đến backend.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: báo lỗi tại trường email.
    $response->assertSessionHasErrors([
        'email',
    ]);

    // Các trường hợp lệ còn lại không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'so_dien_thoai',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'mat_khau',
        'mat_khau_confirmation',
        'general',
    ]);

    // ASSERT 2: chuyển về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // ASSERT 4: không lưu dữ liệu của yêu cầu bị từ chối.
    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_003_09
 * Scenario: TS-AUTH-003
 * Tham chiếu: A2 và BR-5.
 *
 * Gửi trực tiếp yêu cầu có số điện thoại rỗng.
 * Các trường còn lại hợp lệ.
 */
public function test_TC_AUTH_003_09_rejects_empty_phone(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'empty_phone_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử',
        'email' => 'empty.phone@example.com',
        'so_dien_thoai' => '',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi trực tiếp yêu cầu đến backend.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: báo lỗi tại trường số điện thoại.
    $response->assertSessionHasErrors([
        'so_dien_thoai',
    ]);

    // Các trường hợp lệ còn lại không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'email',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'mat_khau',
        'mat_khau_confirmation',
        'general',
    ]);

    // ASSERT 2: chuyển về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // ASSERT 4: không lưu dữ liệu của yêu cầu bị từ chối.
    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_003_10
 * Scenario: TS-AUTH-003
 * Tham chiếu: A2, BR-6.
 *
 * Mật khẩu rỗng; các trường khác được cung cấp đầy đủ.
 */
public function test_TC_AUTH_003_10_rejects_empty_password(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'empty_password_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử',
        'email' => 'empty.password@example.com',
        'so_dien_thoai' => '0912345617',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi trực tiếp yêu cầu đến backend.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: xác minh đúng lỗi thiếu mật khẩu.
    // Nội dung thông báo lấy từ controller đã cung cấp.
    $response->assertSessionHasErrors([
        'mat_khau' => 'Vui lòng nhập mật khẩu.',
    ]);

    // Các trường độc lập còn lại không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'email',
        'so_dien_thoai',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'general',
    ]);

    // ASSERT 2: chuyển về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // ASSERT 4: không lưu dữ liệu của yêu cầu bị từ chối.
    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case: TC_AUTH_003_11
 * Scenario: TS-AUTH-003
 * Tham chiếu: A2, BR-7.
 *
 * Mật khẩu hợp lệ nhưng xác nhận mật khẩu rỗng.
 */
public function test_TC_AUTH_003_11_rejects_empty_password_confirmation(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'empty_confirmation_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử',
        'email' => 'empty.confirmation@example.com',
        'so_dien_thoai' => '0912345618',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '',
    ];

    // ACT: gửi trực tiếp yêu cầu đến backend.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: ghi nhận lỗi xác nhận mật khẩu.
    // Thông báo lấy từ controller đã cung cấp.
    $response->assertSessionHasErrors([
        'mat_khau' => 'Xác nhận mật khẩu không khớp.',
    ]);

    // Các trường độc lập còn lại không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'email',
        'so_dien_thoai',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'general',
    ]);

    // ASSERT 2: chuyển về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 3: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    // ASSERT 4: không lưu dữ liệu của yêu cầu bị từ chối.
    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 5: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case tạm: TC_AUTH_BR01_01
 * Requirement: UC1.1 — BR-1.
 *
 * Người đã đăng nhập gửi POST đăng ký tài khoản khác.
 * Kỳ vọng:
 * - Không tạo thêm tài khoản hoặc hồ sơ.
 * - Không thay đổi dữ liệu tài khoản đang đăng nhập.
 * - Không chuyển phiên sang tài khoản khác.
 */
public function test_TC_AUTH_BR01_01_blocks_registration_when_authenticated(): void
{
    // ARRANGE: tạo tài khoản A và hồ sơ khách hàng.
    $existingUser = NguoiDung::create([
        'ten_dang_nhap' => 'logged_in_customer',
        'mat_khau' => '123456',
        'vai_tro' => 'KHACH_HANG',
    ]);

    $existingCustomer = KhachHang::create([
        'ma_khach_hang' => $existingUser->ma_nguoi_dung,
        'ten_khach_hang' => 'Khách hàng đang đăng nhập',
        'email' => 'logged.in.customer@example.com',
        'so_dien_thoai' => '0912345619',
        'dia_chi' => 'Địa chỉ khách hàng đối chiếu',
        'diem_tich_luy' => 0,
    ]);

    // Lưu trạng thái dữ liệu từ CSDL.
    $existingUser->refresh();
    $existingCustomer->refresh();

    $userBefore = $existingUser->getAttributes();
    $customerBefore = $existingCustomer->getAttributes();

    ksort($userBefore);
    ksort($customerBefore);

    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    // Thiết lập người dùng đã đăng nhập bằng tài khoản A.
    $this->actingAs($existingUser);
    $this->assertAuthenticatedAs($existingUser);

    // Dữ liệu tài khoản B hoàn toàn hợp lệ và chưa tồn tại.
    $data = [
        'ten_dang_nhap' => 'blocked_new_customer',
        'ten_khach_hang' => 'Khách hàng đăng ký mới',
        'email' => 'blocked.new.customer@example.com',
        'so_dien_thoai' => '0912345620',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi yêu cầu đăng ký khi đang đăng nhập.
    $response = $this->post('/register', $data);

    // ASSERT 1: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    // ASSERT 2: không chấp nhận lỗi máy chủ như một lần chặn hợp lệ.
    $this->assertLessThan(
        500,
        $response->getStatusCode(),
        'Yêu cầu gây lỗi máy chủ; chưa chứng minh cơ chế chặn hợp lệ.'
    );

    // ASSERT 3: tài khoản và hồ sơ A vẫn tồn tại.
    $userAfterModel = $existingUser->fresh();
    $customerAfterModel = $existingCustomer->fresh();

    $this->assertNotNull(
        $userAfterModel,
        'Tài khoản đang đăng nhập không còn tồn tại.'
    );

    $this->assertNotNull(
        $customerAfterModel,
        'Hồ sơ khách hàng đang đăng nhập không còn tồn tại.'
    );

    $userAfter = $userAfterModel->getAttributes();
    $customerAfter = $customerAfterModel->getAttributes();

    ksort($userAfter);
    ksort($customerAfter);

    // ASSERT 4: dữ liệu tài khoản A không bị thay đổi.
    $this->assertSame(
        $userBefore,
        $userAfter,
        'Thông tin tài khoản đang đăng nhập đã bị thay đổi.'
    );

    $this->assertSame(
        $customerBefore,
        $customerAfter,
        'Hồ sơ khách hàng đang đăng nhập đã bị thay đổi.'
    );

    // ASSERT 5: vẫn đăng nhập bằng tài khoản A.
    $this->assertAuthenticatedAs($existingUser);
}
/**
 * Test Case bổ sung: TC_AUTH_015_API_01
 * Scenario: TS-AUTH-015
 * Quy tắc: BR-13, BR-15.
 *
 * Gửi trực tiếp Quận/Huyện không có trong hanoiData.
 * Không thay đổi code sản phẩm.
 */
public function test_TC_AUTH_015_API_01_rejects_unknown_district(): void
{
    // ARRANGE: ghi nhận dữ liệu trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'unknown_district_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử địa chỉ',
        'email' => 'unknown.district@example.com',
        'so_dien_thoai' => '0912345621',
        'quan_huyen' => 'QUAN_KIEM_THU_KHONG_TON_TAI',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi giá trị ngoài dropdown trực tiếp đến backend.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    // ASSERT 2: báo lỗi tại trường Quận/Huyện.
    // Không yêu cầu xa_phuong không có lỗi vì địa chỉ phụ thuộc nhau.
    $response->assertSessionHasErrors([
        'quan_huyen',
    ]);

    // Các trường độc lập hợp lệ không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'email',
        'so_dien_thoai',
        'dia_chi_chi_tiet',
        'mat_khau',
        'general',
    ]);

    // ASSERT 3: quay lại biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 4: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Test Case bổ sung: TC_AUTH_015_API_02
 * Scenario: TS-AUTH-015
 * Quy tắc: BR-13, BR-15.
 *
 * Theo hanoiData trong register.blade.php:
 * - Hoàn Kiếm là Quận/Huyện hợp lệ.
 * - Phúc Xá thuộc Ba Đình, không thuộc Hoàn Kiếm.
 *
 * Kiểm tra backend từ chối cặp địa chỉ không phù hợp.
 */
public function test_TC_AUTH_015_API_02_rejects_ward_from_another_district(): void
{
    // ARRANGE: ghi nhận số bản ghi trước thao tác.
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'mismatched_address_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử địa chỉ',
        'email' => 'mismatched.address@example.com',
        'so_dien_thoai' => '0912345622',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Phúc Xá',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT: gửi trực tiếp cặp địa chỉ không phù hợp.
    $response = $this->from('/register')
        ->post('/register', $data);

    // ASSERT 1: không tạo thêm tài khoản hoặc hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore
    );

    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    // ASSERT 2: có lỗi gắn với ít nhất một trường địa chỉ.
    // SRS chưa quy định lỗi phải nằm chính xác ở trường nào.
    $response->assertSessionHasErrors();

    $errors = $this->app['session.store']->get('errors');

    $this->assertNotNull(
        $errors,
        'Không tìm thấy thông tin lỗi trong session.'
    );

    $this->assertTrue(
        $errors->has('quan_huyen') || $errors->has('xa_phuong'),
        'Lỗi phải chỉ ra Quận/Huyện hoặc Xã/Phường không phù hợp.'
    );

    // Các trường độc lập hợp lệ không phát sinh lỗi.
    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'email',
        'so_dien_thoai',
        'dia_chi_chi_tiet',
        'mat_khau',
        'general',
    ]);

    // ASSERT 3: quay về biểu mẫu đăng ký.
    $response->assertRedirect('/register');

    // ASSERT 4: không tạo phiên đăng nhập.
    $this->assertGuest();
}
/**
 * Automation ID: TC_AUTH_002_02
 * Đối chiếu: UC1.1_FUNC_002_02, TS-AUTH-002.
 * Quy tắc: BR-6 — Mật khẩu có tối thiểu 6 ký tự.
 *
 * Kiểm tra backend chấp nhận mật khẩu 7 ký tự
 * và lưu đúng dữ liệu đăng ký.
 *
 * Không kiểm tra thông báo hoặc điều hướng trong ca này.
 */
public function test_TC_AUTH_002_02_accepts_password_with_7_characters(): void
{
    // ARRANGE
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'password_7_chars_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử',
        'email' => 'password.7chars@example.com',
        'so_dien_thoai' => '0912345623',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '1234567',
        'mat_khau_confirmation' => '1234567',
    ];

    $expectedAddress = $data['dia_chi_chi_tiet']
        . ', ' . $data['xa_phuong']
        . ', ' . $data['quan_huyen']
        . ', Hà Nội';

    // ACT
    $response = $this->post('/register', $data);

    // ASSERT 1: dữ liệu không bị từ chối.
    $response->assertSessionHasNoErrors();

    // ASSERT 2: tạo đúng một tài khoản và một hồ sơ.
    $this->assertDatabaseCount(
        'nguoi_dung',
        $userCountBefore + 1
    );

    $this->assertDatabaseCount(
        'khach_hang',
        $customerCountBefore + 1
    );

    $user = NguoiDung::where(
        'ten_dang_nhap',
        $data['ten_dang_nhap']
    )->first();

    $this->assertNotNull(
        $user,
        'Không tìm thấy tài khoản đăng ký với mật khẩu 7 ký tự.'
    );

    // ASSERT 3: tài khoản mang vai trò Khách hàng.
    $this->assertSame(
        'KHACH_HANG',
        $user->vai_tro
    );

    // ASSERT 4: hồ sơ được liên kết và lưu đúng.
    $this->assertDatabaseHas('khach_hang', [
        'ma_khach_hang' => $user->ma_nguoi_dung,
        'ten_khach_hang' => $data['ten_khach_hang'],
        'email' => $data['email'],
        'so_dien_thoai' => $data['so_dien_thoai'],
        'dia_chi' => $expectedAddress,
    ]);

    // ASSERT 5: mật khẩu 7 ký tự được băm đúng.
    $storedPassword = $user->getRawOriginal('mat_khau');

    $this->assertNotSame(
        $data['mat_khau'],
        $storedPassword,
        'Mật khẩu đang được lưu dưới dạng văn bản rõ.'
    );

    $this->assertTrue(
        Hash::check($data['mat_khau'], $storedPassword),
        'Giá trị lưu không xác minh được mật khẩu 7 ký tự.'
    );
}
/**
 * TC_AUTH_002_03 — TS-AUTH-002.
 * Đối chiếu: UC1.1_FUNC_002_03.
 *
 * Tài khoản vừa đăng ký có thể đăng nhập bằng mật khẩu đã nhập.
 */
public function test_TC_AUTH_002_03_login_with_newly_registered_account(): void
{
    // ARRANGE
    $data = [
        'ten_dang_nhap' => 'new_account_login_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử đăng nhập',
        'email' => 'new.account.login@example.com',
        'so_dien_thoai' => '0912345624',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    // ACT 1: tạo tài khoản qua chức năng đăng ký.
    $registerResponse = $this->post('/register', $data);

    $registerResponse->assertSessionHasNoErrors();

    $user = NguoiDung::where(
        'ten_dang_nhap',
        $data['ten_dang_nhap']
    )->first();

    $this->assertNotNull(
        $user,
        'Không tìm thấy tài khoản vừa đăng ký.'
    );

    $this->assertSame('KHACH_HANG', $user->vai_tro);

    // Xóa trạng thái xác thực do đăng ký có thể tự đăng nhập.
    // Đây là chuẩn bị cho lần đăng nhập mới, không kiểm thử route logout.
    \Illuminate\Support\Facades\Auth::logout();
    $this->app['session.store']->invalidate();
    $this->app['session.store']->regenerateToken();

    $this->assertGuest();

    // ACT 2: đăng nhập bằng tài khoản vừa tạo.
    $loginResponse = $this->from('/login')->post('/login', [
    'ten_dang_nhap' => $data['ten_dang_nhap'],
    'password' => $data['mat_khau'],
    ]);

    // ASSERT: yêu cầu đăng nhập tạo xác thực đúng tài khoản.
    $loginResponse->assertSessionHasNoErrors();

    $this->assertAuthenticatedAs($user);

    $loginResponse->assertRedirect(
        route('customer.dashboard')
    );
}

/**
 * TC_AUTH_010_05 — TS-AUTH-010.
 * Đối chiếu: UC1.1_FUNC_010_02.
 * BR-5: số điện thoại chỉ gồm chữ số.
 *
 * Chuỗi có đúng 10 ký tự và chứa khoảng trắng ở giữa.
 */
public function test_TC_AUTH_010_05_rejects_phone_with_space(): void
{
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'phone_space_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử',
        'email' => 'phone.space@example.com',
        'so_dien_thoai' => '09123 5678',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    $response = $this->from('/register')
        ->post('/register', $data);

    $response->assertSessionHasErrors([
        'so_dien_thoai',
    ]);

    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'email',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'mat_khau',
        'mat_khau_confirmation',
        'general',
    ]);

    $response->assertRedirect('/register');

    $this->assertDatabaseCount('nguoi_dung', $userCountBefore);
    $this->assertDatabaseCount('khach_hang', $customerCountBefore);

    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    $this->assertGuest();
}

/**
 * TC_AUTH_012_04 — TS-AUTH-012.
 * Đối chiếu: UC1.1_FUNC_012_01.
 * BR-6: mật khẩu phải có tối thiểu 6 ký tự.
 *
 * Mật khẩu có 1 ký tự; xác nhận khớp.
 */
public function test_TC_AUTH_012_04_rejects_password_with_1_character(): void
{
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'password_1_char_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử',
        'email' => 'password.1char@example.com',
        'so_dien_thoai' => '0912345625',
        'quan_huyen' => 'Hoàn Kiếm',
        'xa_phuong' => 'Hàng Bạc',
        'dia_chi_chi_tiet' => 'Số 10, đường kiểm thử',
        'mat_khau' => '1',
        'mat_khau_confirmation' => '1',
    ];

    $response = $this->from('/register')
        ->post('/register', $data);

    $response->assertSessionHasErrors([
        'mat_khau' => 'Mật khẩu phải có ít nhất 6 ký tự.',
    ]);

    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'email',
        'so_dien_thoai',
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
        'mat_khau_confirmation',
        'general',
    ]);

    $response->assertRedirect('/register');

    $this->assertDatabaseCount('nguoi_dung', $userCountBefore);
    $this->assertDatabaseCount('khach_hang', $customerCountBefore);

    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    $this->assertGuest();
}

/**
 * TC_AUTH_014_04 — TS-AUTH-014.
 * Đối chiếu: UC1.1_FUNC_014_04.
 * BR-8: thông tin địa chỉ phải đầy đủ.
 *
 * Bỏ trống đồng thời Quận/Huyện, Xã/Phường và địa chỉ chi tiết.
 */
public function test_TC_AUTH_014_04_rejects_all_address_fields_empty(): void
{
    $userCountBefore = NguoiDung::count();
    $customerCountBefore = KhachHang::count();

    $data = [
        'ten_dang_nhap' => 'empty_all_address_user',
        'ten_khach_hang' => 'Khách hàng kiểm thử',
        'email' => 'empty.all.address@example.com',
        'so_dien_thoai' => '0912345626',
        'quan_huyen' => '',
        'xa_phuong' => '',
        'dia_chi_chi_tiet' => '',
        'mat_khau' => '123456',
        'mat_khau_confirmation' => '123456',
    ];

    $response = $this->from('/register')
        ->post('/register', $data);

    // Phải ghi nhận cả ba lỗi, không chỉ một lỗi đầu tiên.
    $response->assertSessionHasErrors([
        'quan_huyen',
        'xa_phuong',
        'dia_chi_chi_tiet',
    ]);

    $response->assertSessionDoesntHaveErrors([
        'ten_dang_nhap',
        'ten_khach_hang',
        'email',
        'so_dien_thoai',
        'mat_khau',
        'mat_khau_confirmation',
        'general',
    ]);

    $response->assertRedirect('/register');

    $this->assertDatabaseCount('nguoi_dung', $userCountBefore);
    $this->assertDatabaseCount('khach_hang', $customerCountBefore);

    $this->assertDatabaseMissing('nguoi_dung', [
        'ten_dang_nhap' => $data['ten_dang_nhap'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'email' => $data['email'],
    ]);

    $this->assertDatabaseMissing('khach_hang', [
        'so_dien_thoai' => $data['so_dien_thoai'],
    ]);

    $this->assertGuest();
}
/**
 * Test Case: UC1.1_FUNC_015_01
 * Scenario: TS-AUTH-015
 * Quy tắc: BR-14.
 *
 * Kiểm tra HTML phản hồi:
 * - Có trường Tỉnh/Thành phố.
 * - Giá trị mặc định là Hà Nội.
 * - Có thuộc tính readonly.
 */
public function test_UC1_1_FUNC_015_01_defaults_city_to_hanoi(): void
{
    // ARRANGE: chưa đăng nhập, không có dữ liệu biểu mẫu cũ.
    $this->assertGuest();

    // ACT: mở trang đăng ký.
    $response = $this->get('/register');

    // ASSERT 1: trả về đúng trang đăng ký.
    $response->assertOk();
    $response->assertViewIs('auth.register');

    // Phân tích HTML để kiểm tra đúng input của trường thành phố.
    $document = new \DOMDocument();

    $previousSetting = libxml_use_internal_errors(true);

    try {
        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8">' . $response->getContent()
        );

        $this->assertTrue(
            $loaded,
            'Không phân tích được HTML của trang đăng ký.'
        );
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($previousSetting);
    }

    $xpath = new \DOMXPath($document);

    // Theo cấu trúc Blade đã cung cấp:
    // label và input nằm trực tiếp trong cùng một div.
    $inputs = $xpath->query(
        '//label[contains(normalize-space(.), "Tỉnh/Thành phố")]'
        . '/following-sibling::input[1]'
    );

    $this->assertNotFalse(
        $inputs,
        'Không truy vấn được trường Tỉnh/Thành phố.'
    );

    $this->assertSame(
        1,
        $inputs->length,
        'Phải có đúng một input tương ứng nhãn Tỉnh/Thành phố.'
    );

    $cityInput = $inputs->item(0);

    // ASSERT 2: mặc định là Hà Nội.
    $this->assertSame(
        'Hà Nội',
        $cityInput->getAttribute('value'),
        'Giá trị mặc định của Tỉnh/Thành phố phải là Hà Nội.'
    );

    // ASSERT 3: HTML khai báo trường chỉ đọc.
    $this->assertTrue(
        $cityInput->hasAttribute('readonly'),
        'Trường Tỉnh/Thành phố phải có thuộc tính readonly.'
    );
}
}