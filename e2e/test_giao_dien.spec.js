// @ts-check
import { test, expect } from '@playwright/test';

test.describe('Kiểm thử tự động giao diện RoseShop 🌸', () => {

  test('TC-UI-01: Giao diện Trang chủ (Dashboard)', async ({ page }) => {
    console.log('\n[TEST 1] Đang mở giao diện Trang chủ...');
    const response = await page.goto('/customer/dashboard');
    expect(response?.status()).toBe(200);

    // Kiểm tra logo và tiêu đề
    await expect(page).toHaveTitle(/Hoa Tươi/i);
    const logo = page.locator('#logoLink');
    await expect(logo).toBeVisible();

    // Kiểm tra thanh tìm kiếm
    const searchInput = page.locator('#searchInput');
    await expect(searchInput).toBeVisible();

    // Kiểm tra danh sách sản phẩm hiển thị trên trang chủ
    const productCards = page.locator('.home-product-card');
    const count = await productCards.count();
    console.log(`✅ [Trang chủ] Tải thành công HTTP 200, hiển thị logo và ${count} sản phẩm.`);
    expect(count).toBeGreaterThan(0);
  });

  test('TC-UI-02: Giao diện Chi tiết sản phẩm & Kiểm tra phân quyền', async ({ page }) => {
    console.log('\n[TEST 2] Đang mở giao diện Chi tiết sản phẩm ID: 2...');
    const response = await page.goto('/customer/san-pham/2');
    expect(response?.status()).toBe(200);

    // Kiểm tra tiêu đề tên hoa
    const title = page.locator('.info-title');
    await expect(title).toBeVisible();
    const productName = await title.textContent();

    // Kiểm tra nút mua hàng theo trạng thái khách đăng nhập hoặc vãng lai
    const btnAddCart = page.locator('#btnAddCart');
    const btnLoginToOrder = page.getByRole('button', { name: /Đăng nhập để đặt hoa/i });

    if (await btnAddCart.isVisible()) {
      await expect(btnAddCart).toBeDisabled();
      console.log(`✅ [Chi tiết SP] ${productName?.trim()} - Người dùng đã đăng nhập: Nút Thêm giỏ bị disabled khi số lượng = 0.`);
    } else {
      await expect(btnLoginToOrder).toBeVisible();
      console.log(`✅ [Chi tiết SP] ${productName?.trim()} - Khách vãng lai: Hiển thị đúng nút "Đăng nhập để đặt hoa".`);
    }
  });

  test('TC-UI-03: Giao diện Giỏ hàng & Kiểm tra bảo mật định tuyến', async ({ page }) => {
    console.log('\n[TEST 3] Đang mở giao diện Giỏ hàng...');
    await page.goto('/customer/gio-hang');

    // Nếu chưa đăng nhập, hệ thống tự động bảo vệ định tuyến và chuyển hướng sang /login
    if (page.url().includes('/login')) {
      console.log('✅ [Giỏ hàng] Bảo vệ định tuyến Auth thành công: Khách chưa đăng nhập tự động chuyển sang trang Đăng nhập.');
      await expect(page).toHaveURL(/.*login.*/);
    } else {
      const heading = page.locator('.cart-heading');
      await expect(heading).toBeVisible();
      console.log('✅ [Giỏ hàng] Tải giao diện giỏ hàng thành công.');
    }
  });

  test('TC-UI-04: Giao diện Đăng nhập (Login)', async ({ page }) => {
    console.log('\n[TEST 4] Đang mở giao diện Đăng nhập...');
    const response = await page.goto('/login');
    expect(response?.status()).toBe(200);

    // Kiểm tra các trường nhập liệu
    const emailOrUserInput = page.locator('input[name="ten_dang_nhap"], input[type="email"], input[name="email"]').first();
    const passwordInput = page.locator('input[type="password"]');
    const submitBtn = page.locator('button[type="submit"]');

    await expect(emailOrUserInput).toBeVisible();
    await expect(passwordInput).toBeVisible();
    await expect(submitBtn).toBeVisible();
    console.log('✅ [Đăng nhập] Giao diện Form đăng nhập hiển thị đầy đủ ô tài khoản, mật khẩu và nút submit.');
  });

  test('TC-UI-05: Giao diện các trang Danh mục & Vệ tinh (Phụ kiện, Quà tặng, Tin tức, Liên hệ)', async ({ page }) => {
    console.log('\n[TEST 5] Đang kiểm tra các trang điều hướng phụ...');
    
    const pagesToCheck = [
      { url: '/customer/phu-kien', name: 'Phụ kiện' },
      { url: '/customer/qua-tang', name: 'Quà tặng' },
      { url: '/customer/tin-tuc', name: 'Tin tức' },
      { url: '/customer/lien-he', name: 'Liên hệ' },
      { url: '/customer/gioi-thieu', name: 'Giới thiệu' }
    ];

    for (const p of pagesToCheck) {
      const res = await page.goto(p.url);
      expect(res?.status()).toBe(200);
      console.log(`  ➔ Trang [${p.name}] (${p.url}): HTTP 200 OK.`);
    }
    console.log('✅ [Danh mục & Vệ tinh] Toàn bộ 5 trang đều phản hồi 200 OK.');
  });

});
