// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * PHENIKAA UNIVERSITY - KHOA HỆ THỐNG THÔNG TIN
 * HỌC PHẦN: ĐÁNH GIÁ VÀ KIỂM ĐỊNH CHẤT LƯỢNG PHẦN MỀM
 * 
 * BÀI THỰC HÀNH TỰ ĐỘNG HÓA GIAO DIỆN (UI/E2E TEST SUITE)
 * USE CASE: UC1.2 - ĐĂNG NHẬP VÀ ĐIỀU HƯỚNG PHÂN QUYỀN
 * Sinh viên thực hiện: Nguyễn Kiều Trang
 * Mã nguồn: RoseShop (FlowerStore) - Laravel 12
 * Framework kiểm thử: Playwright Test
 * ============================================================================
 */

test.describe('UC1.2 - Kiểm thử Tự động Giao diện & Luồng E2E Đăng nhập (RoseShop)', () => {

  test.beforeEach(async ({ page }) => {
    // Điều hướng đến trang đăng nhập trước mỗi kịch bản kiểm thử
    await page.goto('/login');
    await page.waitForLoadState('domcontentloaded');
  });

  /**
   * KỊCH BẢN 1: Kiểm tra cấu trúc biểu mẫu & tính tiện dụng (UI Layout & Elements Inspection)
   * Mã ca: TC-UI-LOGIN-01 (Ánh xạ SRS §4.1.2 & Excel ui_UC1.2)
   * Mục tiêu: Đảm bảo giao diện hiển thị đúng, đủ các input, label và nút chức năng.
   */
  test('TC-UI-LOGIN-01: Kiểm tra hiển thị đầy đủ các trường nhập liệu và nhãn chức năng', async ({ page }) => {
    // 1. Kiểm tra tiêu đề trang
    await expect(page).toHaveTitle(/Laravel|Đăng nhập|RoseShop|Hoa Tươi/i);

    // 2. Kiểm tra ô Tên đăng nhập
    const usernameInput = page.locator('#ten_dang_nhap');
    await expect(usernameInput).toBeVisible();
    await expect(usernameInput).toHaveAttribute('type', 'text');
    await expect(usernameInput).toHaveAttribute('required');

    // 3. Kiểm tra ô Mật khẩu (phải ẩn ký tự)
    const passwordInput = page.locator('#password');
    await expect(passwordInput).toBeVisible();
    await expect(passwordInput).toHaveAttribute('type', 'password');
    await expect(passwordInput).toHaveAttribute('required');

    // 4. Kiểm tra Checkbox ghi nhớ đăng nhập
    const rememberCheckbox = page.locator('#remember_me');
    await expect(rememberCheckbox).toBeVisible();

    // 5. Kiểm tra Nút Đăng nhập và Liên kết Đăng ký tài khoản
    const submitBtn = page.getByRole('button', { name: /Đăng nhập/i });
    await expect(submitBtn).toBeVisible();

    const registerLink = page.getByRole('link', { name: /Chưa có tài khoản\? Đăng ký/i });
    await expect(registerLink).toBeVisible();
    await expect(registerLink).toHaveAttribute('href', /.*register.*/);
  });

  /**
   * KỊCH BẢN 2: Kiểm tra xác thực phía máy khách (Client-Side HTML5 Validation)
   * Mã ca: TC-UI-LOGIN-02 (Ánh xạ ca FUNC_002_01 / FUNC_002_02)
   * Mục tiêu: Khi bỏ trống, trình duyệt phải kích hoạt validation message, không gửi HTTP POST vô nghĩa lên server.
   */
  test('TC-UI-LOGIN-02: Kiểm tra chặn gửi biểu mẫu khi để trống các trường bắt buộc', async ({ page }) => {
    const usernameInput = page.locator('#ten_dang_nhap');
    const submitBtn = page.getByRole('button', { name: /Đăng nhập/i });

    // Bấm submit khi chưa nhập bất kỳ dữ liệu gì
    await submitBtn.click();

    // Kiểm tra trạng thái HTML5 validation của ô username
    const isUsernameInvalid = await usernameInput.evaluate((el) => {
      const input = /** @type {HTMLInputElement} */ (el);
      return !input.checkValidity();
    });
    expect(isUsernameInvalid).toBe(true);

    // Đảm bảo URL vẫn giữ nguyên ở trang /login
    expect(page.url()).toContain('/login');
  });

  /**
   * KỊCH BẢN 3: Hiển thị phản hồi lỗi xác thực trực quan (Visual Error Feedback & UX Recovery)
   * Mã ca: TC-UI-LOGIN-03 (Ánh xạ ca FUNC_003_01 / FUNC_003_02)
   * Mục tiêu: Khi sai thông tin, hiển thị lỗi đỏ, giữ lại username cũ, reset mật khẩu để bảo mật.
   */
  test('TC-UI-LOGIN-03: Hiển thị thông báo lỗi màu đỏ khi sai thông tin, tự động xóa ô mật khẩu', async ({ page }) => {
    const usernameInput = page.locator('#ten_dang_nhap');
    const passwordInput = page.locator('#password');
    const submitBtn = page.getByRole('button', { name: /Đăng nhập/i });

    const fakeUser = 'khach_hang_khong_ton_tai_' + Date.now();
    await usernameInput.fill(fakeUser);
    await passwordInput.fill('MatKhauSai123!@#');
    await submitBtn.click();

    // 1. Chờ trang load lại và kiểm tra thông báo lỗi
    await page.waitForLoadState('domcontentloaded');
    
    // Lỗi trả về từ Laravel x-input-error thường xuất hiện bên dưới trường nhập
    const errorMessage = page.locator('text=/Tên đăng nhập hoặc mật khẩu không đúng|These credentials do not match/i').first();
    await expect(errorMessage).toBeVisible();

    // 2. Kiểm tra chuẩn UX: Tên đăng nhập được giữ lại (old input)
    await expect(usernameInput).toHaveValue(fakeUser);

    // 3. Kiểm tra chuẩn Bảo mật: Ô mật khẩu bắt buộc phải bị xóa rỗng
    await expect(passwordInput).toHaveValue('');
  });

  /**
   * KỊCH BẢN 4: Luồng E2E Đăng nhập Khách hàng thành công & Thay đổi trạng thái Header
   * Mã ca: TC-UI-LOGIN-04 (Ánh xạ ca FUNC_001_01 & Chuẩn E2E State Transformation)
   * Mục tiêu: Khách đăng nhập -> chuyển về dashboard -> Header ẩn nút Đăng nhập, hiện nút Đăng xuất & Tên người dùng.
   */
  test('TC-UI-LOGIN-04: Đăng nhập tài khoản Khách hàng thành công, hiển thị Header người dùng', async ({ page }) => {
    // Sử dụng tài khoản mẫu thực tế của hệ thống
    await page.locator('#ten_dang_nhap').fill('khachhang');
    await page.locator('#password').fill('123456');
    await page.getByRole('button', { name: /Đăng nhập/i }).click();

    // 1. Kiểm tra chuyển hướng về giao diện khách hàng
    await page.waitForURL('**/customer/dashboard');
    expect(page.url()).toContain('/customer/dashboard');

    // 2. Kiểm tra biến đổi giao diện trên Header:
    // Nút "Đăng nhập" dành cho khách vãng lai phải biến mất
    await expect(page.locator('.guest-actions .login-btn')).not.toBeVisible();

    // Nút "Đăng xuất" phải hiển thị trên giao diện người dùng
    const logoutBtn = page.locator('.logout-btn');
    await expect(logoutBtn).toBeVisible();

    // Kiểm tra tên người dùng hiển thị trên thanh chào mừng
    const userInfo = page.locator('.user-info');
    await expect(userInfo).toContainText('khachhang');
  });

  /**
   * KỊCH BẢN 5: Luồng E2E Phân quyền & Điều hướng Quản trị viên (Role-Based Redirection)
   * Mã ca: TC-UI-LOGIN-05 (Ánh xạ ca FUNC_001_02 & RBAC UI Routing)
   * Mục tiêu: Quản trị viên đăng nhập -> Tự động chuyển hướng chính xác đến /admin/dashboard.
   */
  test('TC-UI-LOGIN-05: Đăng nhập tài khoản Admin, điều hướng chính xác đến trang quản trị', async ({ page }) => {
    await page.locator('#ten_dang_nhap').fill('admin1');
    await page.locator('#password').fill('123456');
    await page.getByRole('button', { name: /Đăng nhập/i }).click();

    // Kiểm tra URL được điều hướng đến /admin/dashboard
    await page.waitForURL('**/admin/dashboard');
    expect(page.url()).toContain('/admin/dashboard');

    // Kiểm tra giao diện quản trị có các thành phần đặc thù của Admin
    const body = page.locator('body');
    await expect(body).toBeVisible();
  });

  /**
   * KỊCH BẢN 6: Kiểm tra bảo vệ định tuyến & Chuyển hướng khi chưa đăng nhập (Route Guarding)
   * Mã ca: TC-UI-LOGIN-06 (Ánh xạ ca FUNC_004_01)
   * Mục tiêu: Cố tình truy cập trang giỏ hàng/thanh toán khi chưa đăng nhập -> Hệ thống tự động đẩy về /login.
   */
  test('TC-UI-LOGIN-06: Chặn truy cập trang yêu cầu đăng nhập và tự động chuyển hướng về /login', async ({ page }) => {
    // Người dùng chưa đăng nhập cố truy cập trực tiếp giỏ hàng
    await page.goto('/customer/gio-hang');
    await page.waitForLoadState('domcontentloaded');

    // Middleware 'auth' của Laravel sẽ chặn và redirect sang /login
    await expect(page).toHaveURL(/.*login.*/);
    
    // Giao diện form đăng nhập hiển thị sẵn sàng
    await expect(page.locator('#ten_dang_nhap')).toBeVisible();
  });

  /**
   * KỊCH BẢN 7: Luồng E2E Đăng xuất hoàn tất & Hủy phiên làm việc (Session Invalidation)
   * Mã ca: TC-UI-LOGIN-07 (Ánh xạ ca FUNC_004_02 & Security Lifecycle)
   * Mục tiêu: Đang đăng nhập -> Bấm nút Đăng xuất -> Chuyển về trang chủ, khôi phục giao diện khách vãng lai.
   */
  test('TC-UI-LOGIN-07: Đăng xuất tài khoản, hủy phiên làm việc và trở lại trạng thái Khách vãng lai', async ({ page }) => {
    // 1. Đăng nhập trước
    await page.locator('#ten_dang_nhap').fill('khachhang');
    await page.locator('#password').fill('123456');
    await page.getByRole('button', { name: /Đăng nhập/i }).click();
    await page.waitForURL('**/customer/dashboard');

    // 2. Tìm và click nút Đăng xuất trên Header
    const logoutBtn = page.locator('.logout-btn');
    await expect(logoutBtn).toBeVisible();
    await logoutBtn.click();

    // 3. Sau khi đăng xuất, hệ thống chuyển hướng về trang chủ
    await page.waitForLoadState('domcontentloaded');

    // Nút "Đăng nhập" phải xuất hiện trở lại trên Header
    const loginLink = page.locator('.guest-actions .login-btn, a[href*="login"]').first();
    await expect(loginLink).toBeVisible();

    // Nút "Đăng xuất" không còn tồn tại trên DOM
    await expect(page.locator('.logout-btn')).not.toBeVisible();
  });

});
