import { test, expect } from '@playwright/test';

/**
 * Test Case: UC1.1_UI_001
 * Scenario: TS-AUTH-001
 *
 * Kiểm tra biểu mẫu hiển thị đủ 10 trường và nhãn.
 * Không gửi yêu cầu tạo tài khoản.
 */
test('UC1.1_UI_001 - Hien thi du 10 truong dang ky', async ({
  page,
}, testInfo) => {
  await test.step('Mở trang Đăng ký', async () => {
    const response = await page.goto('/register');

    expect(
      response,
      'Không nhận được phản hồi mở trang'
    ).not.toBeNull();

    expect(response.status()).toBe(200);

    await expect(page).toHaveURL(/\/register\/?$/);

    await expect(
      page.getByRole('heading', {
        name: 'Đăng ký tài khoản',
        exact: true,
      })
    ).toBeVisible();
  });

  const form = page.locator('form').filter({
    has: page.locator('input[name="ten_dang_nhap"]'),
  });

  await expect(form).toHaveCount(1);

  const fields = [
    {
      label: 'Tên đăng nhập',
      locator: form.locator('input[name="ten_dang_nhap"]'),
    },
    {
      label: 'Họ và tên',
      locator: form.locator('input[name="ten_khach_hang"]'),
    },
    {
      label: 'Email',
      locator: form.locator('input[name="email"]'),
    },
    {
      label: 'Số điện thoại',
      locator: form.locator('input[name="so_dien_thoai"]'),
    },
    {
      label: 'Tỉnh/Thành phố',
      locator: form.locator('input[readonly]'),
    },
    {
      label: 'Quận/Huyện',
      locator: form.locator('select[name="quan_huyen"]'),
    },
    {
      label: 'Xã/Phường',
      locator: form.locator('select[name="xa_phuong"]'),
    },
    {
      label: 'Địa chỉ chi tiết',
      locator: form.locator('input[name="dia_chi_chi_tiet"]'),
    },
    {
      label: 'Mật khẩu',
      locator: form.locator('input[name="mat_khau"]'),
    },
    {
      label: 'Xác nhận mật khẩu',
      locator: form.locator(
        'input[name="mat_khau_confirmation"]'
      ),
    },
  ];

  for (const field of fields) {
    await test.step(`Kiểm tra trường ${field.label}`, async () => {
      await expect(field.locator).toHaveCount(1);

      await field.locator.scrollIntoViewIfNeeded();
      await expect(field.locator).toBeVisible();

      // Theo Blade: label và input/select cùng một div cha.
      const label = field.locator
        .locator('..')
        .locator(':scope > label');

      await expect(label).toHaveCount(1);
      await expect(label).toBeVisible();

      const actualLabel = (await label.innerText())
        .replace(/\*/g, '')
        .replace(/\s+/g, ' ')
        .trim();

      expect(actualLabel).toBe(field.label);
    });
  }

  await test.step('Lưu ảnh bằng chứng', async () => {
    const screenshotPath = testInfo.outputPath(
      'UC1.1_UI_001-register-form.png'
    );

    await page.screenshot({
      path: screenshotPath,
      fullPage: true,
    });

    await testInfo.attach('Biểu mẫu đăng ký', {
      path: screenshotPath,
      contentType: 'image/png',
    });
  });
});
/**
 * Test Case: UC1.1_UI_002
 * Scenario: TS-AUTH-001
 *
 * Kiểm tra nút Đăng ký và liên kết Đăng nhập.
 * Không nhấn nút gửi biểu mẫu trong ca này.
 */
test('UC1.1_UI_002 - Nut Dang ky va lien ket Dang nhap', async ({
  page,
}, testInfo) => {
  await page.goto('/register');

  const form = page.locator('form').filter({
    has: page.locator('input[name="ten_dang_nhap"]'),
  });

  await expect(form).toHaveCount(1);

  await test.step('Kiểm tra nút Đăng ký', async () => {
    const registerButton = form.getByRole('button', {
      name: 'Đăng ký',
      exact: true,
    });

    await expect(registerButton).toHaveCount(1);
    await registerButton.scrollIntoViewIfNeeded();
    await expect(registerButton).toBeVisible();
    await expect(registerButton).toBeEnabled();
    await expect(registerButton).toHaveAttribute('type', 'submit');

    // Kiểm tra có thể tương tác mà không thực sự gửi form.
    await registerButton.click({ trial: true });
  });

  await test.step('Kiểm tra liên kết Đăng nhập', async () => {
    const loginLink = form.getByRole('link', {
      name: 'Đăng nhập',
      exact: true,
    });

    await expect(loginLink).toHaveCount(1);
    await loginLink.scrollIntoViewIfNeeded();
    await expect(loginLink).toBeVisible();

    const href = await loginLink.getAttribute('href');
    expect(href).not.toBeNull();

    const target = new URL(href, page.url());

    expect(target.pathname).toBe('/login');

    // Chưa điều hướng; ca UI_012 sẽ kiểm tra thao tác nhấn thật.
    await loginLink.click({ trial: true });
  });

  await test.step('Lưu ảnh bằng chứng', async () => {
    const screenshotPath = testInfo.outputPath(
      'UC1.1_UI_002-buttons-links.png'
    );

    await page.screenshot({
      path: screenshotPath,
      fullPage: true,
    });

    await testInfo.attach('Nút và liên kết đăng ký', {
      path: screenshotPath,
      contentType: 'image/png',
    });
  });
});
/**
 * Test Case: UC1.1_UI_003
 * Scenario: TS-AUTH-015
 * Quy tắc: BR-14.
 *
 * Kiểm tra giá trị mặc định và thao tác bàn phím.
 */
test('UC1.1_UI_003 - Ha Noi mac dinh va khong sua duoc', async ({
  page,
}, testInfo) => {
  await page.goto('/register');

  const form = page.locator('form').filter({
    has: page.locator('input[name="ten_dang_nhap"]'),
  });

  // Trường thành phố không có name trong Blade đã cung cấp.
  const cityInput = form.locator('input[readonly]');

  await test.step('Kiểm tra giá trị mặc định', async () => {
    await expect(cityInput).toHaveCount(1);
    await expect(cityInput).toBeVisible();
    await expect(cityInput).toHaveValue('Hà Nội');

    await expect(cityInput).toHaveJSProperty('readOnly', true);
    await expect(cityInput).not.toBeEditable();
  });

  await test.step('Thử thay thế nội dung bằng bàn phím', async () => {
    await cityInput.click();
    await expect(cityInput).toBeFocused();

    await page.keyboard.press('ControlOrMeta+A');
    await page.keyboard.type('Da Nang');

    await expect(cityInput).toHaveValue('Hà Nội');
  });

  await test.step('Thử xóa nội dung bằng bàn phím', async () => {
    await cityInput.click();
    await page.keyboard.press('ControlOrMeta+A');
    await page.keyboard.press('Backspace');

    await expect(cityInput).toHaveValue('Hà Nội');
    await expect(cityInput).not.toBeEditable();
  });

  await test.step('Kiểm tra giá trị sau khi mất focus', async () => {
    await form.locator('input[name="ten_dang_nhap"]').click();

    await expect(cityInput).not.toBeFocused();
    await expect(cityInput).toHaveValue('Hà Nội');
  });

  await test.step('Lưu ảnh bằng chứng', async () => {
    const screenshotPath = testInfo.outputPath(
      'UC1.1_UI_003-hanoi-readonly.png'
    );

    await page.screenshot({
      path: screenshotPath,
      fullPage: true,
    });

    await testInfo.attach('Thành phố mặc định Hà Nội', {
      path: screenshotPath,
      contentType: 'image/png',
    });
  });
});