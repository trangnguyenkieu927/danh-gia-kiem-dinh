// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * PHENIKAA UNIVERSITY - KHOA HỆ THỐNG THÔNG TIN
 * HỌC PHẦN: ĐÁNH GIÁ VÀ KIỂM ĐỊNH CHẤT LƯỢNG PHẦN MỀM
 * 
 * BÀI THỰC HÀNH TỰ ĐỘNG HÓA GIAO DIỆN (UI/E2E TEST SUITE)
 * USE CASE: UC4.2 - QUẢN LÝ GIAO HÀNG (SHIPPER DASHBOARD)
 * Sinh viên thực hiện: Dương Hùng Mạnh
 * Mã nguồn: RoseShop (FlowerStore) - Laravel 12
 * Framework kiểm thử: Playwright Test
 * ============================================================================
 */

test.describe('UC4.2 - Kiểm thử Tự động Giao diện & Luồng E2E Giao hàng Shipper (RoseShop)', () => {

  /**
   * KỊCH BẢN 1: Bảo mật định tuyến & Chặn truy cập trái phép phía giao diện
   * Mã ca: TC-UI-SHIP-01
   * Mục tiêu: Khách vãng lai chưa đăng nhập truy cập /shipper/dashboard phải bị chặn và chuyển hướng về /login.
   */
  test('TC-UI-SHIP-01: Chặn khách vãng lai truy cập giao diện Shipper, chuyển hướng về đăng nhập', async ({ page }) => {
    // 1. Cố gắng truy cập trực tiếp URL trang Shipper
    await page.goto('/shipper/dashboard');
    await page.waitForLoadState('domcontentloaded');

    // 2. Kiểm tra điều hướng về /login
    expect(page.url()).toContain('/login');
    const loginHeader = page.locator('text=/Đăng nhập|Login/i').first();
    await expect(loginHeader).toBeVisible();
  });

  /**
   * KỊCH BẢN 2: Hiển thị đầy đủ các khối chức năng trên Bảng điều khiển Shipper Dashboard
   * Mã ca: TC-UI-SHIP-02
   * Mục tiêu: Shipper đăng nhập thành công -> hiển thị Topbar, Thông tin cá nhân, Thống kê tiền COD và Danh sách đơn.
   */
  test('TC-UI-SHIP-02: Đăng nhập Shipper thành công, hiển thị đầy đủ bảng điều khiển và thông tin giao hàng', async ({ page }) => {
    // 1. Đăng nhập với tài khoản Shipper
    await page.goto('/login');
    await page.locator('#ten_dang_nhap').fill('shipper1');
    await page.locator('#password').fill('123456');
    await page.getByRole('button', { name: /Đăng nhập/i }).click();

    // 2. Chờ điều hướng vào trang Shipper Dashboard
    await page.waitForURL('**/shipper/dashboard');
    expect(page.url()).toContain('/shipper/dashboard');
    await expect(page).toHaveTitle(/Shipper Dashboard/i);

    // 3. Kiểm tra Topbar
    const brand = page.locator('.topbar-brand');
    await expect(brand).toBeVisible();
    await expect(brand).toContainText('FlowerStore · Shipper');

    // 4. Kiểm tra Left panel: Thông tin cá nhân & Thống kê COD
    const profileHeader = page.locator('.profile-header');
    await expect(profileHeader).toBeVisible();

    const moneySection = page.locator('.money-section');
    await expect(moneySection).toBeVisible();
    await expect(moneySection).toContainText('Tổng cần nộp (COD)');

    // 5. Kiểm tra Right panel: Danh sách đơn hàng cần giao
    const rightHeader = page.locator('.right-header');
    await expect(rightHeader).toBeVisible();
    await expect(rightHeader).toContainText('Đơn hàng cần giao');
  });

  /**
   * KỊCH BẢN 3: Điều hướng Hồ sơ cá nhân Shipper và Trung tâm thông báo
   * Mã ca: TC-UI-SHIP-03
   * Mục tiêu: Shipper có thể chuyển đổi mượt mà giữa Dashboard, Trang Hồ sơ cá nhân và Trang Thông báo.
   */
  test('TC-UI-SHIP-03: Điều hướng liên kết Hồ sơ cá nhân và Trung tâm thông báo của Shipper', async ({ page }) => {
    // 1. Đăng nhập tài khoản Shipper
    await page.goto('/login');
    await page.locator('#ten_dang_nhap').fill('shipper1');
    await page.locator('#password').fill('123456');
    await page.getByRole('button', { name: /Đăng nhập/i }).click();
    await page.waitForURL('**/shipper/dashboard');

    // 2. Bấm vào liên kết Hồ sơ
    const profileLink = page.locator('a[href*="/shipper/profile"]');
    await expect(profileLink).toBeVisible();
    await profileLink.click();

    // 3. Kiểm tra chuyển hướng đến trang Hồ sơ Shipper
    await page.waitForURL('**/shipper/profile');
    expect(page.url()).toContain('/shipper/profile');

    // 4. Điều hướng đến Trung tâm thông báo Shipper
    await page.goto('/shipper/thong-bao');
    await page.waitForURL('**/shipper/thong-bao');
    expect(page.url()).toContain('/shipper/thong-bao');
  });

});
