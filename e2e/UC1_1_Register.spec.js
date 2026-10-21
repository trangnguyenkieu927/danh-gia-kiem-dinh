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
test('UC1.1_UI_004 - Danh sach va lua chon Quan Huyen', async ({ page }, testInfo) => {
  // Danh mục tham chiếu từ dữ liệu ứng dụng đã cung cấp.
  // Không phải khẳng định về danh mục hành chính hiện hành.
  const expectedDistricts = [
    'Ba Đình',
    'Hoàn Kiếm',
    'Đống Đa',
    'Hai Bà Trưng',
    'Cầu Giấy',
    'Thanh Xuân',
    'Hoàng Mai',
    'Long Biên',
    'Nam Từ Liêm',
    'Bắc Từ Liêm',
    'Tây Hồ',
    'Hà Đông',
    'Gia Lâm',
    'Đông Anh',
    'Sóc Sơn',
    'Thạch Thất',
    'Quốc Oai',
    'Mê Linh',
    'Chương Mỹ',
  ];

  await test.step('Mở biểu mẫu đăng ký', async () => {
    const response = await page.goto('/register');

    expect(response).not.toBeNull();
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

  const district = form.locator('select[name="quan_huyen"]');

  await test.step('Kiểm tra trạng thái ban đầu của Quận/Huyện', async () => {
    await expect(form).toHaveCount(1);
    await expect(district).toHaveCount(1);

    await district.scrollIntoViewIfNeeded();

    await expect(district).toBeVisible();
    await expect(district).toBeEnabled();
    await expect(district).toHaveJSProperty('required', true);

    // Ban đầu chưa tự chọn quận/huyện.
    await expect(district).toHaveValue('');

    const placeholder = district.locator('option[value=""]');

    await expect(placeholder).toHaveCount(1);
    await expect(placeholder).toHaveText('Chọn quận huyện');
  });

  await test.step('Đối chiếu danh mục Quận/Huyện', async () => {
    const options = district.locator('option:not([value=""])');

    // Chờ danh mục được JavaScript điền đầy đủ.
    await expect(options).toHaveCount(expectedDistricts.length);

    const actualOptions = await options.evaluateAll((elements) =>
      elements.map((element) => ({
        value: element.value,
        label: (element.textContent || '').trim(),
        disabled: element.disabled,
      }))
    );

    const actualValues = actualOptions.map((option) => option.value);
    const actualLabels = actualOptions.map((option) => option.label);

    // Kiểm tra đúng danh mục, không phụ thuộc thứ tự hiển thị.
    expect([...actualValues].sort()).toEqual(
      [...expectedDistricts].sort()
    );

    expect([...actualLabels].sort()).toEqual(
      [...expectedDistricts].sort()
    );

    // Không được có giá trị trùng.
    expect(new Set(actualValues).size).toBe(actualValues.length);

    // Mỗi lựa chọn phải có nhãn khớp giá trị và được phép chọn.
    for (const option of actualOptions) {
      expect(option.label).toBe(option.value);
      expect(option.disabled).toBe(false);
    }
  });

  await test.step('Chọn Hà Đông và kiểm tra giá trị được giữ lại', async () => {
    await district.selectOption({ label: 'Hà Đông' });

    await expect(district).toHaveValue('Hà Đông');
    await expect(
      district.locator('option:checked')
    ).toHaveText('Hà Đông');

    // Chuyển sang trường khác để kiểm tra lựa chọn sau khi mất focus.
    await form.locator('input[name="dia_chi_chi_tiet"]').click();

    await expect(district).not.toBeFocused();
    await expect(district).toHaveValue('Hà Đông');
  });

  await test.step('Lưu bằng chứng', async () => {
    const screenshotPath = testInfo.outputPath(
      'UC1.1_UI_004-district-selection.png'
    );

    await page.screenshot({
      path: screenshotPath,
      fullPage: true,
    });

    await testInfo.attach('Quận/Huyện đã chọn Hà Đông', {
      path: screenshotPath,
      contentType: 'image/png',
    });
  });
});
test('UC1.1_UI_005 - Danh sach Xa Phuong theo Quan Huyen', async ({ page }, testInfo) => {
  // Danh mục tham chiếu của ứng dụng cho Hà Đông.
  const expectedWards = [
    'Nguyễn Trãi',
    'Mỗ Lao',
    'Văn Quán',
    'Vạn Phúc',
    'Yết Kiêu',
    'Quang Trung',
    'La Khê',
    'Phú La',
    'Phúc La',
    'Hà Cầu',
    'Yên Nghĩa',
    'Kiến Hưng',
    'Phú Lương',
    'Dương Nội',
    'Đồng Mai',
    'Biên Giang',
  ];

  await test.step('Mở biểu mẫu đăng ký', async () => {
    const response = await page.goto('/register');

    expect(response).not.toBeNull();
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

  const district = form.locator('select[name="quan_huyen"]');
  const ward = form.locator('select[name="xa_phuong"]');

  await test.step('Kiểm tra trạng thái ban đầu của Xã/Phường', async () => {
    await expect(form).toHaveCount(1);
    await expect(ward).toHaveCount(1);

    await ward.scrollIntoViewIfNeeded();
    await expect(ward).toBeVisible();
    await expect(ward).toHaveJSProperty('required', true);

    await expect(district).toHaveValue('');
    await expect(ward).toHaveValue('');

    await expect(
      ward.locator('option[value=""]')
    ).toHaveText('Chọn xã/phường');

    // Chưa chọn quận/huyện thì chưa có phường cụ thể.
    await expect(
      ward.locator('option:not([value=""])')
    ).toHaveCount(0);
  });

  await test.step('Chọn Hà Đông và kiểm tra danh sách phường', async () => {
    await district.selectOption({ label: 'Hà Đông' });

    await expect(district).toHaveValue('Hà Đông');
    await expect(ward).toBeEnabled();

    // Người dùng phải tự chọn phường.
    await expect(ward).toHaveValue('');

    const options = ward.locator('option:not([value=""])');

    await expect(options).toHaveCount(expectedWards.length);

    const actualOptions = await options.evaluateAll((elements) =>
      elements.map((element) => ({
        value: element.value,
        label: (element.textContent || '').trim(),
        disabled: element.disabled,
      }))
    );

    const actualValues = actualOptions.map((option) => option.value);
    const actualLabels = actualOptions.map((option) => option.label);

    // Đối chiếu danh mục, không phụ thuộc thứ tự hiển thị.
    expect([...actualValues].sort()).toEqual(
      [...expectedWards].sort()
    );

    expect([...actualLabels].sort()).toEqual(
      [...expectedWards].sort()
    );

    // Không có lựa chọn trùng giá trị.
    expect(new Set(actualValues).size).toBe(actualValues.length);

    for (const option of actualOptions) {
      expect(option.label).toBe(option.value);
      expect(option.disabled).toBe(false);
    }
  });

  await test.step('Chọn Yên Nghĩa và kiểm tra lựa chọn được giữ lại', async () => {
    await ward.selectOption({ label: 'Yên Nghĩa' });

    await expect(ward).toHaveValue('Yên Nghĩa');
    await expect(
      ward.locator('option:checked')
    ).toHaveText('Yên Nghĩa');

    await form.locator('input[name="dia_chi_chi_tiet"]').click();

    await expect(ward).not.toBeFocused();
    await expect(ward).toHaveValue('Yên Nghĩa');
    await expect(district).toHaveValue('Hà Đông');
  });

  await test.step('Lưu bằng chứng', async () => {
    const screenshotPath = testInfo.outputPath(
      'UC1.1_UI_005-ward-selection.png'
    );

    await page.screenshot({
      path: screenshotPath,
      fullPage: true,
    });

    await testInfo.attach('Hà Đông - Yên Nghĩa', {
      path: screenshotPath,
      contentType: 'image/png',
    });
  });
});
test('UC1.1_UI_006 - Che mat khau va xac nhan mat khau', async ({ page }, testInfo) => {
  const testPassword = 'Test123!';

  await test.step('Mở biểu mẫu đăng ký', async () => {
    const response = await page.goto('/register');

    expect(response).not.toBeNull();
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

  const password = form.locator('input[name="mat_khau"]');
  const confirmation = form.locator(
    'input[name="mat_khau_confirmation"]'
  );

  await test.step('Kiểm tra trạng thái mặc định của hai ô mật khẩu', async () => {
    await expect(form).toHaveCount(1);

    for (const field of [password, confirmation]) {
      await expect(field).toHaveCount(1);

      await field.scrollIntoViewIfNeeded();
      await expect(field).toBeVisible();
      await expect(field).toBeEditable();
      await expect(field).toHaveValue('');

      // Trình duyệt che ký tự đối với input có type="password".
      await expect(field).toHaveAttribute('type', 'password');
      await expect(field).toHaveJSProperty('type', 'password');
    }
  });

  await test.step('Nhập mật khẩu và kiểm tra chế độ che khi đang nhập', async () => {
    await password.click();
    await password.pressSequentially(testPassword);

    await expect(password).toBeFocused();
    await expect(password).toHaveValue(testPassword);
    await expect(password).toHaveJSProperty('type', 'password');

    await confirmation.click();
    await confirmation.pressSequentially(testPassword);

    await expect(confirmation).toBeFocused();
    await expect(confirmation).toHaveValue(testPassword);
    await expect(confirmation).toHaveJSProperty('type', 'password');

    // Ô mật khẩu đầu tiên vẫn giữ chế độ che sau khi mất focus.
    await expect(password).not.toBeFocused();
    await expect(password).toHaveJSProperty('type', 'password');
  });

  await test.step('Chuyển sang trường khác và kiểm tra hai ô vẫn che nội dung', async () => {
    await form.locator('input[name="dia_chi_chi_tiet"]').click();

    await expect(password).not.toBeFocused();
    await expect(confirmation).not.toBeFocused();

    for (const field of [password, confirmation]) {
      await expect(field).toHaveValue(testPassword);
      await expect(field).toHaveAttribute('type', 'password');
      await expect(field).toHaveJSProperty('type', 'password');
    }
  });

  await test.step('Lưu ảnh bằng chứng', async () => {
    await password.scrollIntoViewIfNeeded();

    const screenshotPath = testInfo.outputPath(
      'UC1.1_UI_006-masked-passwords.png'
    );

    await page.screenshot({
      path: screenshotPath,
      fullPage: true,
    });

    await testInfo.attach('Hai trường mật khẩu ở chế độ che', {
      path: screenshotPath,
      contentType: 'image/png',
    });
  });
});
test('UC1.1_UI_007 - Bo trong Ho va ten', async ({ page }, testInfo) => {
  await test.step('Mở biểu mẫu đăng ký', async () => {
    const response = await page.goto('/register');

    expect(response).not.toBeNull();
    expect(response.status()).toBe(200);
    await expect(page).toHaveURL(/\/register\/?$/);
  });

  const form = page.locator('form').filter({
    has: page.locator('input[name="ten_dang_nhap"]'),
  });

  const fullName = form.locator('input[name="ten_khach_hang"]');
  const submitButton = form.getByRole('button', {
    name: 'Đăng ký',
    exact: true,
  });

  const registrationPosts = [];

  const trackRegistrationPost = (request) => {
    const pathname = new URL(request.url()).pathname;

    if (
      request.method() === 'POST' &&
      /^\/register\/?$/.test(pathname)
    ) {
      registrationPosts.push(request.url());
    }
  };

  page.on('request', trackRegistrationPost);

  // Chặn yêu cầu tạo tài khoản nếu giao diện bất ngờ cho gửi.
  // Yêu cầu vẫn được ghi nhận để assertion báo Failed.
  await page.route('**/*', async (route) => {
    const request = route.request();
    const pathname = new URL(request.url()).pathname;

    if (
      request.method() === 'POST' &&
      /^\/register\/?$/.test(pathname)
    ) {
      await route.abort();
      return;
    }

    await route.continue();
  });

  try {
    await test.step('Nhập các trường còn lại, bỏ trống Họ và tên', async () => {
      await expect(form).toHaveCount(1);

      await form.locator('[name="ten_dang_nhap"]').fill('ui007_test');
      await fullName.fill('');
      await form.locator('[name="email"]').fill('ui007@example.com');
      await form.locator('[name="so_dien_thoai"]').fill('0912345607');

      await form.locator('[name="quan_huyen"]')
        .selectOption({ label: 'Hà Đông' });

      await form.locator('[name="xa_phuong"]')
        .selectOption({ label: 'Yên Nghĩa' });

      await form.locator('[name="dia_chi_chi_tiet"]')
        .fill('Số 10, đường kiểm thử');

      await form.locator('[name="mat_khau"]').fill('Test123!');
      await form.locator('[name="mat_khau_confirmation"]').fill('Test123!');

      await expect(fullName).toHaveValue('');
      await expect(fullName).toHaveJSProperty('required', true);

      // Xác nhận chỉ Họ và tên vi phạm validation của trình duyệt.
      const invalidFields = await form.locator(':invalid')
        .evaluateAll((elements) =>
          elements.map((element) => element.getAttribute('name'))
        );

      expect(invalidFields).toEqual(['ten_khach_hang']);

      // Ghi nhận sự kiện validation để chứng minh trình duyệt
      // đã kiểm tra trường này khi người dùng nhấn Đăng ký.
      await fullName.evaluate((element) => {
        element.dataset.ui007InvalidCount = '0';

        element.addEventListener('invalid', () => {
          element.dataset.ui007InvalidCount = String(
            Number(element.dataset.ui007InvalidCount) + 1
          );
        });
      });
    });

    await test.step('Nhấn Đăng ký và kiểm tra lỗi bắt buộc', async () => {
      await submitButton.click();

      await expect(fullName).toHaveAttribute(
        'data-ui007-invalid-count',
        '1'
      );

      await expect(fullName).toBeFocused();

      const validation = await fullName.evaluate((element) => ({
        valueMissing: element.validity.valueMissing,
        valid: element.validity.valid,
        message: element.validationMessage,
      }));

      expect(validation.valueMissing).toBe(true);
      expect(validation.valid).toBe(false);

      // Nội dung thông báo phụ thuộc ngôn ngữ trình duyệt.
      expect(validation.message.trim()).not.toBe('');

      await expect(page).toHaveURL(/\/register\/?$/);
      await expect(form).toBeVisible();

      expect(
        registrationPosts,
        'Biểu mẫu thiếu Họ và tên không được gửi POST /register.'
      ).toHaveLength(0);

      const screenshotPath = testInfo.outputPath(
        'UC1.1_UI_007-empty-fullname.png'
      );

      await page.screenshot({
        path: screenshotPath,
        fullPage: true,
      });

      await testInfo.attach('Biểu mẫu khi thiếu Họ và tên', {
        path: screenshotPath,
        contentType: 'image/png',
      });

      await testInfo.attach('Kết quả validation của trình duyệt', {
        body: JSON.stringify(validation, null, 2),
        contentType: 'application/json',
      });
    });

    await test.step('Bổ sung Họ và tên và kiểm tra lỗi được xóa', async () => {
      await fullName.fill('Nguyễn Văn Kiểm Thử');

      await expect(fullName).toHaveValue('Nguyễn Văn Kiểm Thử');

      const validation = await fullName.evaluate((element) => ({
        valueMissing: element.validity.valueMissing,
        valid: element.validity.valid,
        message: element.validationMessage,
      }));

      expect(validation.valueMissing).toBe(false);
      expect(validation.valid).toBe(true);
      expect(validation.message).toBe('');

      // Các trường đã nhập không bị mất dữ liệu.
      await expect(form.locator('[name="ten_dang_nhap"]'))
        .toHaveValue('ui007_test');
      await expect(form.locator('[name="email"]'))
        .toHaveValue('ui007@example.com');
      await expect(form.locator('[name="so_dien_thoai"]'))
        .toHaveValue('0912345607');
      await expect(form.locator('[name="quan_huyen"]'))
        .toHaveValue('Hà Đông');
      await expect(form.locator('[name="xa_phuong"]'))
        .toHaveValue('Yên Nghĩa');
      await expect(form.locator('[name="dia_chi_chi_tiet"]'))
        .toHaveValue('Số 10, đường kiểm thử');

      await expect(form.locator(':invalid')).toHaveCount(0);

      // Không nhấn Đăng ký lần hai trong ca này.
      expect(registrationPosts).toHaveLength(0);
    });
  } finally {
    page.off('request', trackRegistrationPost);
  }
});
test('UC1.1_UI_008 - Email sai dinh dang', async ({ page }, testInfo) => {
  await test.step('Mở biểu mẫu đăng ký', async () => {
    const response = await page.goto('/register');

    expect(response).not.toBeNull();
    expect(response.status()).toBe(200);
    await expect(page).toHaveURL(/\/register\/?$/);
  });

  const form = page.locator('form').filter({
    has: page.locator('input[name="ten_dang_nhap"]'),
  });

  const email = form.locator('input[name="email"]');
  const registrationPosts = [];

  // Ghi nhận và chặn mọi POST đăng ký nếu giao diện gửi ngoài dự kiến.
  // Assertion sẽ đánh dấu Failed nếu có yêu cầu này.
  await page.route('**/*', async (route) => {
    const request = route.request();
    const pathname = new URL(request.url()).pathname;

    if (
      request.method() === 'POST' &&
      /^\/register\/?$/.test(pathname)
    ) {
      registrationPosts.push(request.url());
      await route.abort();
      return;
    }

    await route.continue();
  });

  await test.step('Nhập email thiếu @, các trường khác hợp lệ', async () => {
    await expect(form).toHaveCount(1);

    await form.locator('[name="ten_dang_nhap"]').fill('ui008_test');
    await form.locator('[name="ten_khach_hang"]')
      .fill('Nguyễn Văn Kiểm Thử');

    await email.fill('ui008.example.com');

    await form.locator('[name="so_dien_thoai"]').fill('0912345608');
    await form.locator('[name="quan_huyen"]')
      .selectOption({ label: 'Hà Đông' });
    await form.locator('[name="xa_phuong"]')
      .selectOption({ label: 'Yên Nghĩa' });
    await form.locator('[name="dia_chi_chi_tiet"]')
      .fill('Số 10, đường kiểm thử');
    await form.locator('[name="mat_khau"]').fill('Test123!');
    await form.locator('[name="mat_khau_confirmation"]').fill('Test123!');

    await expect(email).toHaveAttribute('type', 'email');
    await expect(email).toHaveValue('ui008.example.com');

    // Chỉ email được phép vi phạm validation trong dữ liệu thử nghiệm.
    const invalidFields = await form.locator(':invalid')
      .evaluateAll((elements) =>
        elements.map((element) => element.getAttribute('name'))
      );

    expect(invalidFields).toEqual(['email']);

    // Theo dõi validation phát sinh từ thao tác nhấn Đăng ký.
    await email.evaluate((element) => {
      element.dataset.ui008InvalidCount = '0';

      element.addEventListener('invalid', () => {
        element.dataset.ui008InvalidCount = String(
          Number(element.dataset.ui008InvalidCount) + 1
        );
      });
    });
  });

  await test.step('Nhấn Đăng ký và kiểm tra lỗi định dạng email', async () => {
    await form.getByRole('button', {
      name: 'Đăng ký',
      exact: true,
    }).click();

    await expect(email).toHaveAttribute('data-ui008-invalid-count', '1');
    await expect(email).toBeFocused();

    const validation = await email.evaluate((element) => ({
      valueMissing: element.validity.valueMissing,
      typeMismatch: element.validity.typeMismatch,
      valid: element.validity.valid,
      message: element.validationMessage,
    }));

    // Email đã được nhập, nhưng sai định dạng.
    expect(validation.valueMissing).toBe(false);
    expect(validation.typeMismatch).toBe(true);
    expect(validation.valid).toBe(false);
    expect(validation.message.trim()).not.toBe('');

    await expect(page).toHaveURL(/\/register\/?$/);
    await expect(form).toBeVisible();

    expect(
      registrationPosts,
      'Email sai định dạng không được gửi POST /register.'
    ).toHaveLength(0);

    const screenshotPath = testInfo.outputPath(
      'UC1.1_UI_008-invalid-email.png'
    );

    await page.screenshot({
      path: screenshotPath,
      fullPage: true,
    });

    await testInfo.attach('Biểu mẫu khi email sai định dạng', {
      path: screenshotPath,
      contentType: 'image/png',
    });

    await testInfo.attach('Validation email của trình duyệt', {
      body: JSON.stringify(validation, null, 2),
      contentType: 'application/json',
    });
  });

  await test.step('Sửa email hợp lệ và kiểm tra lỗi được xóa', async () => {
    await email.fill('ui008@example.com');

    await expect(email).toHaveValue('ui008@example.com');

    const validation = await email.evaluate((element) => ({
      typeMismatch: element.validity.typeMismatch,
      valid: element.validity.valid,
      message: element.validationMessage,
    }));

    expect(validation.typeMismatch).toBe(false);
    expect(validation.valid).toBe(true);
    expect(validation.message).toBe('');

    // Các thông tin còn lại được giữ nguyên.
    const expectedValues = {
      ten_dang_nhap: 'ui008_test',
      ten_khach_hang: 'Nguyễn Văn Kiểm Thử',
      so_dien_thoai: '0912345608',
      quan_huyen: 'Hà Đông',
      xa_phuong: 'Yên Nghĩa',
      dia_chi_chi_tiet: 'Số 10, đường kiểm thử',
      mat_khau: 'Test123!',
      mat_khau_confirmation: 'Test123!',
    };

    for (const [name, value] of Object.entries(expectedValues)) {
      await expect(form.locator(`[name="${name}"]`)).toHaveValue(value);
    }

    await expect(form.locator(':invalid')).toHaveCount(0);

    // Không gửi lại biểu mẫu sau khi sửa email trong ca này.
    expect(registrationPosts).toHaveLength(0);
  });
});
test('UC1.1_UI_009 - Dang ky bang so dien thoai da ton tai', async ({
  page,
}, testInfo) => {
  // Phải là số điện thoại của tài khoản đã tồn tại
  // trong CSDL của ứng dụng đang chạy trên trình duyệt.
  const existingPhone = process.env.PW_EXISTING_PHONE;

  expect(
    existingPhone,
    'Cần đặt PW_EXISTING_PHONE bằng số điện thoại đã tồn tại trong CSDL test.'
  ).toMatch(/^[0-9]{10}$/);

  // Tên đăng nhập và email mới để chỉ kiểm tra trùng số điện thoại.
  const suffix = `${Date.now()}_${Math.floor(Math.random() * 100000)}`;
  const username = `ui009_${suffix}`;
  const emailAddress = `${username}@example.com`;

  await test.step('Mở biểu mẫu đăng ký', async () => {
    const response = await page.goto('/register');

    expect(response).not.toBeNull();
    expect(response.status()).toBe(200);
    await expect(page).toHaveURL(/\/register\/?$/);
  });

  const form = page.locator('form').filter({
    has: page.locator('input[name="ten_dang_nhap"]'),
  });

  await test.step('Nhập thông tin mới và số điện thoại đã tồn tại', async () => {
    await expect(form).toHaveCount(1);

    await form.locator('[name="ten_dang_nhap"]').fill(username);
    await form.locator('[name="ten_khach_hang"]')
      .fill('Khách kiểm thử trùng số điện thoại');

    await form.locator('[name="email"]').fill(emailAddress);
    await form.locator('[name="so_dien_thoai"]').fill(existingPhone);

    await form.locator('[name="quan_huyen"]')
      .selectOption({ label: 'Hà Đông' });

    await form.locator('[name="xa_phuong"]')
      .selectOption({ label: 'Yên Nghĩa' });

    await form.locator('[name="dia_chi_chi_tiet"]')
      .fill('Số 10, đường kiểm thử');

    await form.locator('[name="mat_khau"]').fill('Test123!');
    await form.locator('[name="mat_khau_confirmation"]').fill('Test123!');

    // Dữ liệu phải vượt qua validation trình duyệt
    // để yêu cầu thực sự được gửi tới backend.
    await expect(form.locator(':invalid')).toHaveCount(0);

    await testInfo.attach('Dữ liệu truy vết tài khoản thử nghiệm', {
      body: JSON.stringify({
        username,
        email: emailAddress,
        phone: existingPhone,
        precondition: 'Số điện thoại đã tồn tại trong CSDL kiểm thử',
      }, null, 2),
      contentType: 'application/json',
    });
  });

  await test.step('Gửi đăng ký và kiểm tra phản hồi giao diện', async () => {
    const responsePromise = page.waitForResponse((response) => {
      const request = response.request();

      return request.method() === 'POST' &&
        /^\/register\/?$/.test(new URL(response.url()).pathname);
    });

    await form.getByRole('button', {
      name: 'Đăng ký',
      exact: true,
    }).click();

    const response = await responsePromise;

    // Chờ tài liệu sau thao tác gửi form được tải.
    await page.waitForLoadState('load');

    await testInfo.attach('Phản hồi yêu cầu đăng ký', {
      body: JSON.stringify({
        requestUrl: response.url(),
        status: response.status(),
      }, null, 2),
      contentType: 'application/json',
    });

    // Dùng soft assertion để thu thập cả lỗi điều hướng
    // và lỗi thiếu thông báo trong cùng lần chạy.
    await expect.soft(
      page,
      'Đăng ký trùng số điện thoại phải quay về trang đăng ký.'
    ).toHaveURL(/\/register\/?$/);

    await expect.soft(
      form,
      'Biểu mẫu đăng ký phải hiển thị để người dùng sửa thông tin.'
    ).toBeVisible();

    // Không cố định câu chữ khi SRS chưa quy định thông báo chính xác.
    // Thông báo phải nêu số điện thoại đã tồn tại/được sử dụng.
    const phoneError = page.locator('p').filter({
      hasText: /số điện thoại.*(đã|tồn tại|trùng|sử dụng)/i,
    });

    await expect.soft(
      phoneError.first(),
      'Phải hiển thị thông báo về số điện thoại đã tồn tại.'
    ).toBeVisible();

    await expect.soft(
      page.getByText('Đăng ký tài khoản thành công', { exact: true })
    ).not.toBeVisible();
  });

  await test.step('Lưu kết quả thực tế', async () => {
    await testInfo.attach('Địa chỉ trang sau đăng ký', {
      body: page.url(),
      contentType: 'text/plain',
    });

    const screenshotPath = testInfo.outputPath(
      'UC1.1_UI_009-duplicate-phone.png'
    );

    await page.screenshot({
      path: screenshotPath,
      fullPage: true,
    });

    await testInfo.attach('Giao diện sau đăng ký trùng số điện thoại', {
      path: screenshotPath,
      contentType: 'image/png',
    });
  });
});
test('UC1.1_UI_010 - Giu thong tin hop le sau loi dang ky', async ({
  page,
}, testInfo) => {
  test.setTimeout(90_000);

  const existingUsername = process.env.PW_EXISTING_USERNAME;
  const newPhone = process.env.PW_NEW_PHONE;

  expect(
    existingUsername,
    'Đặt PW_EXISTING_USERNAME bằng tên đăng nhập đã tồn tại.'
  ).toBeTruthy();

  expect(
    newPhone,
    'Đặt PW_NEW_PHONE bằng số điện thoại thử nghiệm mới gồm 10 chữ số.'
  ).toMatch(/^[0-9]{10}$/);

  const suffix = `${Date.now()}_${Math.floor(Math.random() * 100000)}`;

  const data = {
    ten_dang_nhap: existingUsername,
    ten_khach_hang: 'Nguyễn Văn Kiểm Thử',
    email: `ui010_${suffix}@example.com`,
    so_dien_thoai: newPhone,
    quan_huyen: 'Hà Đông',
    xa_phuong: 'Yên Nghĩa',
    dia_chi_chi_tiet: 'Số 10, đường kiểm thử',
  };

  await test.step('Mở biểu mẫu đăng ký', async () => {
    const response = await page.goto('/register');

    expect(response).not.toBeNull();
    expect(response.status()).toBe(200);

    await expect(page).toHaveURL(/\/register\/?$/);
  });

  const form = page.locator('form').filter({
    has: page.locator('input[name="ten_dang_nhap"]'),
  });

  await test.step('Nhập tên đăng nhập trùng và các thông tin hợp lệ', async () => {
    await expect(form).toHaveCount(1);

    for (const name of [
      'ten_dang_nhap',
      'ten_khach_hang',
      'email',
      'so_dien_thoai',
      'dia_chi_chi_tiet',
    ]) {
      await form.locator(`[name="${name}"]`).fill(data[name]);
    }

    await form.locator('[name="quan_huyen"]')
      .selectOption({ label: data.quan_huyen });

    await form.locator('[name="xa_phuong"]')
      .selectOption({ label: data.xa_phuong });

    await form.locator('[name="mat_khau"]').fill('Test123!');
    await form.locator('[name="mat_khau_confirmation"]').fill('Test123!');

    // Chứng minh biểu mẫu không bị validation phía trình duyệt chặn.
    await expect(form.locator(':invalid')).toHaveCount(0);

    await testInfo.attach('Dữ liệu trước khi gửi', {
      body: JSON.stringify(data, null, 2),
      contentType: 'application/json',
    });
  });

  await test.step('Gửi biểu mẫu và nhận lỗi từ backend', async () => {
    const responsePromise = page.waitForResponse((response) => {
      return response.request().method() === 'POST' &&
        /^\/register\/?$/.test(new URL(response.url()).pathname);
    });

    await form.getByRole('button', {
      name: 'Đăng ký',
      exact: true,
    }).click();

    const response = await responsePromise;

    await testInfo.attach('Phản hồi POST đăng ký', {
      body: JSON.stringify({
        url: response.url(),
        status: response.status(),
      }, null, 2),
      contentType: 'application/json',
    });

    // Kiểm tra thông báo để xác nhận đã nhận trang phản hồi backend.
    const usernameError = page.locator('p').filter({
      hasText: 'Tên đăng nhập đã tồn tại.',
    });

    await expect(usernameError.first()).toBeVisible();
    await expect(page).toHaveURL(/\/register\/?$/);
    await expect(form).toBeVisible();

    // Lưu ảnh trước các assertion đối chiếu dữ liệu.
    const screenshotPath = testInfo.outputPath(
      'UC1.1_UI_010-retained-inputs.png'
    );

    await page.screenshot({
      path: screenshotPath,
      fullPage: true,
    });

    await testInfo.attach('Biểu mẫu sau lỗi trùng tên đăng nhập', {
      path: screenshotPath,
      contentType: 'image/png',
    });
  });

  await test.step('Kiểm tra thông tin đã nhập được giữ lại', async () => {
    for (const [name, value] of Object.entries(data)) {
      await expect.soft(
        form.locator(`[name="${name}"]`),
        `Trường ${name} phải giữ nguyên dữ liệu đã nhập.`
      ).toHaveValue(value, { timeout: 3_000 });
    }

    await expect.soft(
      form.locator('input[readonly]'),
      'Tỉnh/Thành phố vẫn là Hà Nội.'
    ).toHaveValue('Hà Nội', { timeout: 3_000 });

    await expect.soft(
      page.getByText('Đăng ký tài khoản thành công', {
        exact: true,
      })
    ).not.toBeVisible({ timeout: 3_000 });

    const actualValues = {};

    for (const name of Object.keys(data)) {
      actualValues[name] = await form
        .locator(`[name="${name}"]`)
        .inputValue();
    }

    await testInfo.attach('Dữ liệu thực tế sau lỗi đăng ký', {
      body: JSON.stringify(actualValues, null, 2),
      contentType: 'application/json',
    });
  });
});
test('UC1.1_UI_011 - Dang ky thanh cong va chuyen ve Dang nhap', async ({
  page,
}, testInfo) => {
  test.setTimeout(60_000);

  // Dùng số điện thoại thử nghiệm chưa tồn tại trong CSDL ứng dụng.
  const newPhone = process.env.PW_NEW_PHONE;

  expect(
    newPhone,
    'Đặt PW_NEW_PHONE bằng số điện thoại mới gồm đúng 10 chữ số.'
  ).toMatch(/^[0-9]{10}$/);

  const suffix = `${Date.now()}_${Math.floor(Math.random() * 100000)}`;

  const data = {
    ten_dang_nhap: `ui011_${suffix}`,
    ten_khach_hang: 'Nguyễn Văn Kiểm Thử',
    email: `ui011_${suffix}@example.com`,
    so_dien_thoai: newPhone,
    quan_huyen: 'Hà Đông',
    xa_phuong: 'Yên Nghĩa',
    dia_chi_chi_tiet: 'Số 10, đường kiểm thử',
  };

  await test.step('Mở biểu mẫu đăng ký', async () => {
    const response = await page.goto('/register');

    expect(response).not.toBeNull();
    expect(response.status()).toBe(200);
    await expect(page).toHaveURL(/\/register\/?$/);
  });

  const form = page.locator('form').filter({
    has: page.locator('input[name="ten_dang_nhap"]'),
  });

  await test.step('Nhập đầy đủ thông tin hợp lệ', async () => {
    await expect(form).toHaveCount(1);

    for (const name of [
      'ten_dang_nhap',
      'ten_khach_hang',
      'email',
      'so_dien_thoai',
      'dia_chi_chi_tiet',
    ]) {
      await form.locator(`[name="${name}"]`).fill(data[name]);
    }

    await form.locator('[name="quan_huyen"]')
      .selectOption({ label: data.quan_huyen });

    await form.locator('[name="xa_phuong"]')
      .selectOption({ label: data.xa_phuong });

    await form.locator('[name="mat_khau"]').fill('Test123!');
    await form.locator('[name="mat_khau_confirmation"]').fill('Test123!');

    await expect(form.locator(':invalid')).toHaveCount(0);

    await testInfo.attach('Dữ liệu đăng ký để đối chiếu', {
      body: JSON.stringify(data, null, 2),
      contentType: 'application/json',
    });
  });

  await test.step('Gửi đăng ký và lưu trang phản hồi', async () => {
    const responsePromise = page.waitForResponse((response) => {
      return response.request().method() === 'POST' &&
        /^\/register\/?$/.test(new URL(response.url()).pathname);
    });

    // Chờ điều hướng của biểu mẫu trước khi kiểm tra trang kết quả.
    const navigationPromise = page.waitForNavigation({
      waitUntil: 'load',
    });

    await form.getByRole('button', {
      name: 'Đăng ký',
      exact: true,
    }).click();

    const [response] = await Promise.all([
      responsePromise,
      navigationPromise,
    ]);

    await testInfo.attach('Phản hồi đăng ký và URL thực tế', {
      body: JSON.stringify({
        requestUrl: response.url(),
        responseStatus: response.status(),
        finalUrl: page.url(),
      }, null, 2),
      contentType: 'application/json',
    });

    // Lưu ảnh trước assertion để vẫn có bằng chứng khi điều hướng sai.
    const screenshotPath = testInfo.outputPath(
      'UC1.1_UI_011-registration-result.png'
    );

    await page.screenshot({
      path: screenshotPath,
      fullPage: true,
    });

    await testInfo.attach('Trang thực tế sau khi đăng ký', {
      path: screenshotPath,
      contentType: 'image/png',
    });
  });

  await test.step('Đối chiếu thông báo và điều hướng theo SRS', async () => {
    await expect.soft(
      page.getByText('Đăng ký tài khoản thành công', {
        exact: true,
      }),
      'Phải hiển thị thông báo đăng ký tài khoản thành công.'
    ).toBeVisible({ timeout: 5_000 });

    await expect.soft(
      page,
      'Theo SRS, đăng ký thành công phải chuyển về trang Đăng nhập.'
    ).toHaveURL(/\/login\/?$/, { timeout: 5_000 });
  });
});
test('UC1.1_UI_012 - Chuyen sang trang Dang nhap', async ({
  page,
}, testInfo) => {
  const registrationPosts = [];

  const trackRequest = (request) => {
    if (
      request.method() === 'POST' &&
      /^\/register\/?$/.test(new URL(request.url()).pathname)
    ) {
      registrationPosts.push(request.url());
    }
  };

  page.on('request', trackRequest);

  try {
    await test.step('Mở biểu mẫu đăng ký', async () => {
      const response = await page.goto('/register');

      expect(response).not.toBeNull();
      expect(response.status()).toBe(200);

      await expect(page).toHaveURL(/\/register\/?$/);

      await expect(
        page.locator('input[name="mat_khau_confirmation"]')
      ).toBeVisible();
    });

    await test.step('Nhấn liên kết Đăng nhập', async () => {
      const loginLink = page.getByRole('link', {
        name: 'Đăng nhập',
        exact: true,
      });

      await expect(loginLink).toHaveCount(1);
      await expect(loginLink).toBeVisible();

      const href = await loginLink.getAttribute('href');

      expect(href).not.toBeNull();
      expect(new URL(href, page.url()).pathname).toBe('/login');

      // Biểu mẫu đăng ký đang trống:
      // nhấn liên kết không được kích hoạt validation đăng ký.
      await loginLink.click();

      await expect(page).toHaveURL(/\/login\/?$/);
    });

    await test.step('Kiểm tra biểu mẫu đăng nhập và yêu cầu mạng', async () => {
      const loginForm = page.locator('form').filter({
        has: page.locator('input[name="password"]'),
      });

      await expect(loginForm).toHaveCount(1);
      await expect(loginForm).toBeVisible();

      await expect(
        loginForm.locator('input[name="ten_dang_nhap"]')
      ).toBeVisible();

      await expect(
        loginForm.locator('input[name="password"]')
      ).toBeVisible();

      await expect(
        loginForm.getByRole('button', {
          name: 'Đăng nhập',
          exact: true,
        })
      ).toBeVisible();

      expect(
        registrationPosts,
        'Chuyển sang Đăng nhập không được gửi POST /register.'
      ).toHaveLength(0);

      await testInfo.attach('Yêu cầu đăng ký đã ghi nhận', {
        body: JSON.stringify(registrationPosts, null, 2),
        contentType: 'application/json',
      });
    });

    await test.step('Lưu ảnh trang Đăng nhập', async () => {
      const screenshotPath = testInfo.outputPath(
        'UC1.1_UI_012-login-navigation.png'
      );

      await page.screenshot({
        path: screenshotPath,
        fullPage: true,
      });

      await testInfo.attach('Trang Đăng nhập sau khi nhấn liên kết', {
        path: screenshotPath,
        contentType: 'image/png',
      });
    });
  } finally {
    page.off('request', trackRequest);
  }
});