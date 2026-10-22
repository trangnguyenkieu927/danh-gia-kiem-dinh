import { test, expect } from '@playwright/test';

/**
 * ============================================================
 * DỮ LIỆU MẪU DÙNG CHUNG CHO UC1.1 (Đăng ký)
 * ============================================================
 * - EXISTING: tài khoản ĐÃ TỒN TẠI sẵn trong CSDL kiểm thử.
 *   Dùng cho các ca kiểm tra trùng tên đăng nhập / số điện thoại.
 * - Các ca đăng ký thành công/trùng 1 trường dùng dữ liệu MỚI
 *   (tạo bằng uniqueUser()) để chỉ trường cần kiểm thử bị trùng.
 *
 * Lưu ý: "Yên Nghĩa" là Xã/Phường thuộc Quận/Huyện "Hà Đông"
 * trong danh mục hanoiData của ứng dụng, nên Quận/Huyện = Hà Đông.
 */
const EXISTING = {
  ten_dang_nhap: 'khachhang',
  so_dien_thoai: '0983241301',
  email: '23010594@st.phenikaa-uni.edu.vn',
};

const SAMPLE = {
  ten_khach_hang: 'Nguyễn Đức Trọng',
  tinh_thanh: 'Hà Nội',
  quan_huyen: 'Hà Đông',
  xa_phuong: 'Yên Nghĩa',
  dia_chi_chi_tiet: 'Xuân Mai Complex',
  mat_khau_6: '123456',
  mat_khau_7: '1234567',
};

/** Dữ liệu mới, chưa tồn tại trong CSDL (tránh trùng với EXISTING). */
function uniqueUser(tag) {
  const stamp = Date.now().toString().slice(-7);
  const rand = Math.floor(Math.random() * 1000);

  // Số điện thoại 10 chữ số, có thể ghi đè bằng biến môi trường.
  const phone =
    process.env.PW_NEW_PHONE ||
    `09${stamp}${String(rand).padStart(1, '0')}`.slice(0, 10);

  return {
    ten_dang_nhap: `khachhang_${tag}_${stamp}`,
    ten_khach_hang: SAMPLE.ten_khach_hang,
    email: `23010594+${tag}${stamp}@st.phenikaa-uni.edu.vn`,
    so_dien_thoai: phone,
    quan_huyen: SAMPLE.quan_huyen,
    xa_phuong: SAMPLE.xa_phuong,
    dia_chi_chi_tiet: SAMPLE.dia_chi_chi_tiet,
    mat_khau: SAMPLE.mat_khau_6,
    mat_khau_confirmation: SAMPLE.mat_khau_6,
  };
}

/** Dữ liệu cơ sở (đúng thông tin mẫu), dùng cho ca không gửi biểu mẫu. */
function baseData() {
  return {
    ten_dang_nhap: EXISTING.ten_dang_nhap,
    ten_khach_hang: SAMPLE.ten_khach_hang,
    email: EXISTING.email,
    so_dien_thoai: EXISTING.so_dien_thoai,
    quan_huyen: SAMPLE.quan_huyen,
    xa_phuong: SAMPLE.xa_phuong,
    dia_chi_chi_tiet: SAMPLE.dia_chi_chi_tiet,
    mat_khau: SAMPLE.mat_khau_6,
    mat_khau_confirmation: SAMPLE.mat_khau_6,
  };
}

function getForm(page) {
  return page.locator('form').filter({
    has: page.locator('input[name="ten_dang_nhap"]'),
  });
}

async function openRegister(page) {
  const response = await page.goto('/register');

  expect(response, 'Không nhận được phản hồi mở trang').not.toBeNull();
  expect(response.status()).toBe(200);

  await expect(page).toHaveURL(/\/register\/?$/);
  await expect(
    page.getByRole('heading', { name: 'Đăng ký tài khoản', exact: true })
  ).toBeVisible();
}

async function fillRegisterForm(form, data) {
  for (const name of [
    'ten_dang_nhap',
    'ten_khach_hang',
    'email',
    'so_dien_thoai',
  ]) {
    await form.locator(`[name="${name}"]`).fill(data[name]);
  }

  await form
    .locator('[name="quan_huyen"]')
    .selectOption({ label: data.quan_huyen });
  await form
    .locator('[name="xa_phuong"]')
    .selectOption({ label: data.xa_phuong });

  await form
    .locator('[name="dia_chi_chi_tiet"]')
    .fill(data.dia_chi_chi_tiet);
  await form.locator('[name="mat_khau"]').fill(data.mat_khau);
  await form
    .locator('[name="mat_khau_confirmation"]')
    .fill(data.mat_khau_confirmation);
}

async function attachScreenshot(page, testInfo, fileName, title) {
  const screenshotPath = testInfo.outputPath(fileName);

  await page.screenshot({ path: screenshotPath, fullPage: true });
  await testInfo.attach(title, {
    path: screenshotPath,
    contentType: 'image/png',
  });
}

const isRegisterPost = (request) =>
  request.method() === 'POST' &&
  /^\/register\/?$/.test(new URL(request.url()).pathname);

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
    await openRegister(page);
  });

  const form = getForm(page);

  await expect(form).toHaveCount(1);

  const fields = [
    { label: 'Tên đăng nhập', locator: form.locator('input[name="ten_dang_nhap"]') },
    { label: 'Họ và tên', locator: form.locator('input[name="ten_khach_hang"]') },
    { label: 'Email', locator: form.locator('input[name="email"]') },
    { label: 'Số điện thoại', locator: form.locator('input[name="so_dien_thoai"]') },
    { label: 'Tỉnh/Thành phố', locator: form.locator('input[readonly]') },
    { label: 'Quận/Huyện', locator: form.locator('select[name="quan_huyen"]') },
    { label: 'Xã/Phường', locator: form.locator('select[name="xa_phuong"]') },
    { label: 'Địa chỉ chi tiết', locator: form.locator('input[name="dia_chi_chi_tiet"]') },
    { label: 'Mật khẩu', locator: form.locator('input[name="mat_khau"]') },
    { label: 'Xác nhận mật khẩu', locator: form.locator('input[name="mat_khau_confirmation"]') },
  ];

  for (const field of fields) {
    await test.step(`Kiểm tra trường ${field.label}`, async () => {
      await expect(field.locator).toHaveCount(1);

      await field.locator.scrollIntoViewIfNeeded();
      await expect(field.locator).toBeVisible();

      // Theo Blade: label và input/select cùng một div cha.
      const label = field.locator.locator('..').locator(':scope > label');

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
    await attachScreenshot(
      page,
      testInfo,
      'UC1.1_UI_001-register-form.png',
      'Biểu mẫu đăng ký'
    );
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

  const form = getForm(page);

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

    expect(new URL(href, page.url()).pathname).toBe('/login');

    // Chưa điều hướng; ca UI_012 sẽ kiểm tra thao tác nhấn thật.
    await loginLink.click({ trial: true });
  });

  await test.step('Lưu ảnh bằng chứng', async () => {
    await attachScreenshot(
      page,
      testInfo,
      'UC1.1_UI_002-buttons-links.png',
      'Nút và liên kết đăng ký'
    );
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

  const form = getForm(page);

  // Trường thành phố không có name trong Blade đã cung cấp.
  const cityInput = form.locator('input[readonly]');

  await test.step('Kiểm tra giá trị mặc định', async () => {
    await expect(cityInput).toHaveCount(1);
    await expect(cityInput).toBeVisible();
    await expect(cityInput).toHaveValue(SAMPLE.tinh_thanh);

    await expect(cityInput).toHaveJSProperty('readOnly', true);
    await expect(cityInput).not.toBeEditable();
  });

  await test.step('Thử thay thế nội dung bằng bàn phím', async () => {
    await cityInput.click();
    await expect(cityInput).toBeFocused();

    await page.keyboard.press('ControlOrMeta+A');
    await page.keyboard.type('Da Nang');

    await expect(cityInput).toHaveValue(SAMPLE.tinh_thanh);
  });

  await test.step('Thử xóa nội dung bằng bàn phím', async () => {
    await cityInput.click();
    await page.keyboard.press('ControlOrMeta+A');
    await page.keyboard.press('Backspace');

    await expect(cityInput).toHaveValue(SAMPLE.tinh_thanh);
    await expect(cityInput).not.toBeEditable();
  });

  await test.step('Kiểm tra giá trị sau khi mất focus', async () => {
    await form.locator('input[name="ten_dang_nhap"]').click();

    await expect(cityInput).not.toBeFocused();
    await expect(cityInput).toHaveValue(SAMPLE.tinh_thanh);
  });

  await test.step('Lưu ảnh bằng chứng', async () => {
    await attachScreenshot(
      page,
      testInfo,
      'UC1.1_UI_003-hanoi-readonly.png',
      'Thành phố mặc định Hà Nội'
    );
  });
});

/**
 * Test Case: UC1.1_UI_004
 * Danh sách và lựa chọn Quận/Huyện.
 */
test('UC1.1_UI_004 - Danh sach va lua chon Quan Huyen', async ({ page }, testInfo) => {
  // Danh mục tham chiếu từ dữ liệu ứng dụng đã cung cấp.
  const expectedDistricts = [
    'Ba Đình', 'Hoàn Kiếm', 'Đống Đa', 'Hai Bà Trưng', 'Cầu Giấy',
    'Thanh Xuân', 'Hoàng Mai', 'Long Biên', 'Nam Từ Liêm', 'Bắc Từ Liêm',
    'Tây Hồ', 'Hà Đông', 'Gia Lâm', 'Đông Anh', 'Sóc Sơn',
    'Thạch Thất', 'Quốc Oai', 'Mê Linh', 'Chương Mỹ',
  ];

  await test.step('Mở biểu mẫu đăng ký', async () => {
    await openRegister(page);
  });

  const form = getForm(page);
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

    expect([...actualValues].sort()).toEqual([...expectedDistricts].sort());
    expect([...actualLabels].sort()).toEqual([...expectedDistricts].sort());

    expect(new Set(actualValues).size).toBe(actualValues.length);

    for (const option of actualOptions) {
      expect(option.label).toBe(option.value);
      expect(option.disabled).toBe(false);
    }
  });

  await test.step(`Chọn ${SAMPLE.quan_huyen} và kiểm tra giá trị được giữ lại`, async () => {
    await district.selectOption({ label: SAMPLE.quan_huyen });

    await expect(district).toHaveValue(SAMPLE.quan_huyen);
    await expect(district.locator('option:checked')).toHaveText(
      SAMPLE.quan_huyen
    );

    await form.locator('input[name="dia_chi_chi_tiet"]').click();

    await expect(district).not.toBeFocused();
    await expect(district).toHaveValue(SAMPLE.quan_huyen);
  });

  await test.step('Lưu bằng chứng', async () => {
    await attachScreenshot(
      page,
      testInfo,
      'UC1.1_UI_004-district-selection.png',
      `Quận/Huyện đã chọn ${SAMPLE.quan_huyen}`
    );
  });
});

/**
 * Test Case: UC1.1_UI_005
 * Danh sách Xã/Phường theo Quận/Huyện.
 */
test('UC1.1_UI_005 - Danh sach Xa Phuong theo Quan Huyen', async ({ page }, testInfo) => {
  // Danh mục tham chiếu của ứng dụng cho Hà Đông.
  const expectedWards = [
    'Nguyễn Trãi', 'Mỗ Lao', 'Văn Quán', 'Vạn Phúc', 'Yết Kiêu',
    'Quang Trung', 'La Khê', 'Phú La', 'Phúc La', 'Hà Cầu',
    'Yên Nghĩa', 'Kiến Hưng', 'Phú Lương', 'Dương Nội', 'Đồng Mai',
    'Biên Giang',
  ];

  await test.step('Mở biểu mẫu đăng ký', async () => {
    await openRegister(page);
  });

  const form = getForm(page);
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

    await expect(ward.locator('option[value=""]')).toHaveText('Chọn xã/phường');

    // Chưa chọn quận/huyện thì chưa có phường cụ thể.
    await expect(ward.locator('option:not([value=""])')).toHaveCount(0);
  });

  await test.step(`Chọn ${SAMPLE.quan_huyen} và kiểm tra danh sách phường`, async () => {
    await district.selectOption({ label: SAMPLE.quan_huyen });

    await expect(district).toHaveValue(SAMPLE.quan_huyen);
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

    expect([...actualValues].sort()).toEqual([...expectedWards].sort());
    expect([...actualLabels].sort()).toEqual([...expectedWards].sort());

    expect(new Set(actualValues).size).toBe(actualValues.length);

    for (const option of actualOptions) {
      expect(option.label).toBe(option.value);
      expect(option.disabled).toBe(false);
    }
  });

  await test.step(`Chọn ${SAMPLE.xa_phuong} và kiểm tra lựa chọn được giữ lại`, async () => {
    await ward.selectOption({ label: SAMPLE.xa_phuong });

    await expect(ward).toHaveValue(SAMPLE.xa_phuong);
    await expect(ward.locator('option:checked')).toHaveText(SAMPLE.xa_phuong);

    await form.locator('input[name="dia_chi_chi_tiet"]').click();

    await expect(ward).not.toBeFocused();
    await expect(ward).toHaveValue(SAMPLE.xa_phuong);
    await expect(district).toHaveValue(SAMPLE.quan_huyen);
  });

  await test.step('Lưu bằng chứng', async () => {
    await attachScreenshot(
      page,
      testInfo,
      'UC1.1_UI_005-ward-selection.png',
      `${SAMPLE.quan_huyen} - ${SAMPLE.xa_phuong}`
    );
  });
});

/**
 * Test Case: UC1.1_UI_006
 * Che mật khẩu và xác nhận mật khẩu (mật khẩu 6 ký tự: 123456).
 */
test('UC1.1_UI_006 - Che mat khau va xac nhan mat khau', async ({ page }, testInfo) => {
  const testPassword = SAMPLE.mat_khau_6;

  await test.step('Mở biểu mẫu đăng ký', async () => {
    await openRegister(page);
  });

  const form = getForm(page);
  const password = form.locator('input[name="mat_khau"]');
  const confirmation = form.locator('input[name="mat_khau_confirmation"]');

  await test.step('Kiểm tra trạng thái mặc định của hai ô mật khẩu', async () => {
    await expect(form).toHaveCount(1);

    for (const field of [password, confirmation]) {
      await expect(field).toHaveCount(1);

      await field.scrollIntoViewIfNeeded();
      await expect(field).toBeVisible();
      await expect(field).toBeEditable();
      await expect(field).toHaveValue('');

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

    await attachScreenshot(
      page,
      testInfo,
      'UC1.1_UI_006-masked-passwords.png',
      'Hai trường mật khẩu ở chế độ che'
    );
  });
});

/**
 * Test Case: UC1.1_UI_007
 * Bỏ trống Họ và tên.
 */
test('UC1.1_UI_007 - Bo trong Ho va ten', async ({ page }, testInfo) => {
  await test.step('Mở biểu mẫu đăng ký', async () => {
    await openRegister(page);
  });

  const form = getForm(page);
  const fullName = form.locator('input[name="ten_khach_hang"]');
  const submitButton = form.getByRole('button', {
    name: 'Đăng ký',
    exact: true,
  });

  const registrationPosts = [];

  const trackRegistrationPost = (request) => {
    if (isRegisterPost(request)) {
      registrationPosts.push(request.url());
    }
  };

  page.on('request', trackRegistrationPost);

  // Chặn yêu cầu tạo tài khoản nếu giao diện bất ngờ cho gửi.
  // Yêu cầu vẫn được ghi nhận để assertion báo Failed.
  await page.route('**/*', async (route) => {
    if (isRegisterPost(route.request())) {
      await route.abort();
      return;
    }

    await route.continue();
  });

  const data = { ...baseData(), ten_khach_hang: '' };

  try {
    await test.step('Nhập các trường còn lại, bỏ trống Họ và tên', async () => {
      await expect(form).toHaveCount(1);

      await fillRegisterForm(form, data);

      await expect(fullName).toHaveValue('');
      await expect(fullName).toHaveJSProperty('required', true);

      // Chỉ Họ và tên vi phạm validation của trình duyệt.
      const invalidFields = await form
        .locator(':invalid')
        .evaluateAll((elements) =>
          elements.map((element) => element.getAttribute('name'))
        );

      expect(invalidFields).toEqual(['ten_khach_hang']);

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

      await expect(fullName).toHaveAttribute('data-ui007-invalid-count', '1');
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

      await attachScreenshot(
        page,
        testInfo,
        'UC1.1_UI_007-empty-fullname.png',
        'Biểu mẫu khi thiếu Họ và tên'
      );

      await testInfo.attach('Kết quả validation của trình duyệt', {
        body: JSON.stringify(validation, null, 2),
        contentType: 'application/json',
      });
    });

    await test.step('Bổ sung Họ và tên và kiểm tra lỗi được xóa', async () => {
      await fullName.fill(SAMPLE.ten_khach_hang);

      await expect(fullName).toHaveValue(SAMPLE.ten_khach_hang);

      const validation = await fullName.evaluate((element) => ({
        valueMissing: element.validity.valueMissing,
        valid: element.validity.valid,
        message: element.validationMessage,
      }));

      expect(validation.valueMissing).toBe(false);
      expect(validation.valid).toBe(true);
      expect(validation.message).toBe('');

      // Các trường đã nhập không bị mất dữ liệu.
      await expect(form.locator('[name="ten_dang_nhap"]')).toHaveValue(data.ten_dang_nhap);
      await expect(form.locator('[name="email"]')).toHaveValue(data.email);
      await expect(form.locator('[name="so_dien_thoai"]')).toHaveValue(data.so_dien_thoai);
      await expect(form.locator('[name="quan_huyen"]')).toHaveValue(data.quan_huyen);
      await expect(form.locator('[name="xa_phuong"]')).toHaveValue(data.xa_phuong);
      await expect(form.locator('[name="dia_chi_chi_tiet"]')).toHaveValue(data.dia_chi_chi_tiet);

      await expect(form.locator(':invalid')).toHaveCount(0);

      // Không nhấn Đăng ký lần hai trong ca này.
      expect(registrationPosts).toHaveLength(0);
    });
  } finally {
    page.off('request', trackRegistrationPost);
  }
});

/**
 * Test Case: UC1.1_UI_008
 * Email sai định dạng (thiếu @).
 */
test('UC1.1_UI_008 - Email sai dinh dang', async ({ page }, testInfo) => {
  await test.step('Mở biểu mẫu đăng ký', async () => {
    await openRegister(page);
  });

  const form = getForm(page);
  const email = form.locator('input[name="email"]');
  const registrationPosts = [];

  // Ghi nhận và chặn mọi POST đăng ký nếu giao diện gửi ngoài dự kiến.
  await page.route('**/*', async (route) => {
    if (isRegisterPost(route.request())) {
      registrationPosts.push(route.request().url());
      await route.abort();
      return;
    }

    await route.continue();
  });

  const invalidEmail = '23010594.st.phenikaa-uni.edu.vn'; // thiếu @
  const data = { ...baseData(), email: invalidEmail };

  await test.step('Nhập email thiếu @, các trường khác hợp lệ', async () => {
    await expect(form).toHaveCount(1);

    await fillRegisterForm(form, data);

    await expect(email).toHaveAttribute('type', 'email');
    await expect(email).toHaveValue(invalidEmail);

    // Chỉ email được phép vi phạm validation trong dữ liệu thử nghiệm.
    const invalidFields = await form
      .locator(':invalid')
      .evaluateAll((elements) =>
        elements.map((element) => element.getAttribute('name'))
      );

    expect(invalidFields).toEqual(['email']);

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
    await form.getByRole('button', { name: 'Đăng ký', exact: true }).click();

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

    await attachScreenshot(
      page,
      testInfo,
      'UC1.1_UI_008-invalid-email.png',
      'Biểu mẫu khi email sai định dạng'
    );

    await testInfo.attach('Validation email của trình duyệt', {
      body: JSON.stringify(validation, null, 2),
      contentType: 'application/json',
    });
  });

  await test.step('Sửa email hợp lệ và kiểm tra lỗi được xóa', async () => {
    await email.fill(EXISTING.email);

    await expect(email).toHaveValue(EXISTING.email);

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
      ten_dang_nhap: data.ten_dang_nhap,
      ten_khach_hang: data.ten_khach_hang,
      so_dien_thoai: data.so_dien_thoai,
      quan_huyen: data.quan_huyen,
      xa_phuong: data.xa_phuong,
      dia_chi_chi_tiet: data.dia_chi_chi_tiet,
      mat_khau: data.mat_khau,
      mat_khau_confirmation: data.mat_khau_confirmation,
    };

    for (const [name, value] of Object.entries(expectedValues)) {
      await expect(form.locator(`[name="${name}"]`)).toHaveValue(value);
    }

    await expect(form.locator(':invalid')).toHaveCount(0);

    // Không gửi lại biểu mẫu sau khi sửa email trong ca này.
    expect(registrationPosts).toHaveLength(0);
  });
});

/**
 * Test Case: UC1.1_UI_009
 * Đăng ký bằng số điện thoại đã tồn tại (0983241301).
 * Tên đăng nhập và email là dữ liệu mới để chỉ kiểm tra trùng số điện thoại.
 * Điều kiện tiên quyết: CSDL kiểm thử đã có số điện thoại 0983241301.
 */
test('UC1.1_UI_009 - Dang ky bang so dien thoai da ton tai', async ({
  page,
}, testInfo) => {
  const data = {
    ...uniqueUser('ui009'),
    so_dien_thoai: EXISTING.so_dien_thoai,
  };

  await test.step('Mở biểu mẫu đăng ký', async () => {
    await openRegister(page);
  });

  const form = getForm(page);

  await test.step('Nhập thông tin mới và số điện thoại đã tồn tại', async () => {
    await expect(form).toHaveCount(1);

    await fillRegisterForm(form, data);

    // Dữ liệu phải vượt qua validation trình duyệt
    // để yêu cầu thực sự được gửi tới backend.
    await expect(form.locator(':invalid')).toHaveCount(0);

    await testInfo.attach('Dữ liệu thử nghiệm', {
      body: JSON.stringify(
        {
          username: data.ten_dang_nhap,
          email: data.email,
          phone: data.so_dien_thoai,
          precondition: 'Số điện thoại đã tồn tại trong CSDL kiểm thử',
        },
        null,
        2
      ),
      contentType: 'application/json',
    });
  });

  await test.step('Gửi đăng ký và kiểm tra phản hồi giao diện', async () => {
    const responsePromise = page.waitForResponse((response) =>
      isRegisterPost(response.request())
    );

    await form.getByRole('button', { name: 'Đăng ký', exact: true }).click();

    const response = await responsePromise;

    await page.waitForLoadState('load');

    await testInfo.attach('Phản hồi yêu cầu đăng ký', {
      body: JSON.stringify(
        { requestUrl: response.url(), status: response.status() },
        null,
        2
      ),
      contentType: 'application/json',
    });

    // Dùng soft assertion để thu thập đồng thời mọi sai lệch.
    await expect
      .soft(page, 'Đăng ký trùng số điện thoại phải quay về trang đăng ký.')
      .toHaveURL(/\/register\/?$/);

    await expect
      .soft(form, 'Biểu mẫu đăng ký phải hiển thị để người dùng sửa thông tin.')
      .toBeVisible();

    const phoneError = page.locator('p').filter({
      hasText: /số điện thoại.*(đã|tồn tại|trùng|sử dụng)/i,
    });

    await expect
      .soft(
        phoneError.first(),
        'Phải hiển thị thông báo về số điện thoại đã tồn tại.'
      )
      .toBeVisible();

    await expect
      .soft(page.getByText('Đăng ký tài khoản thành công', { exact: true }))
      .not.toBeVisible();
  });

  await test.step('Lưu kết quả thực tế', async () => {
    await testInfo.attach('Địa chỉ trang sau đăng ký', {
      body: page.url(),
      contentType: 'text/plain',
    });

    await attachScreenshot(
      page,
      testInfo,
      'UC1.1_UI_009-duplicate-phone.png',
      'Giao diện sau đăng ký trùng số điện thoại'
    );
  });
});

/**
 * Test Case: UC1.1_UI_010
 * Giữ lại thông tin hợp lệ sau lỗi đăng ký.
 * Tên đăng nhập trùng "khachhang"; email và số điện thoại là dữ liệu mới.
 * Điều kiện tiên quyết: CSDL kiểm thử đã có tên đăng nhập "khachhang".
 */
test('UC1.1_UI_010 - Giu thong tin hop le sau loi dang ky', async ({
  page,
}, testInfo) => {
  test.setTimeout(90_000);

  const data = {
    ...uniqueUser('ui010'),
    ten_dang_nhap: EXISTING.ten_dang_nhap,
  };

  await test.step('Mở biểu mẫu đăng ký', async () => {
    await openRegister(page);
  });

  const form = getForm(page);

  await test.step('Nhập tên đăng nhập trùng và các thông tin hợp lệ', async () => {
    await expect(form).toHaveCount(1);

    await fillRegisterForm(form, data);

    await expect(form.locator(':invalid')).toHaveCount(0);

    await testInfo.attach('Dữ liệu trước khi gửi', {
      body: JSON.stringify(data, null, 2),
      contentType: 'application/json',
    });
  });

  await test.step('Gửi biểu mẫu và nhận lỗi từ backend', async () => {
    const responsePromise = page.waitForResponse((response) =>
      isRegisterPost(response.request())
    );

    await form.getByRole('button', { name: 'Đăng ký', exact: true }).click();

    const response = await responsePromise;

    await testInfo.attach('Phản hồi POST đăng ký', {
      body: JSON.stringify(
        { url: response.url(), status: response.status() },
        null,
        2
      ),
      contentType: 'application/json',
    });

    const usernameError = page.locator('p').filter({
      hasText: 'Tên đăng nhập đã tồn tại.',
    });

    await expect(usernameError.first()).toBeVisible();
    await expect(page).toHaveURL(/\/register\/?$/);
    await expect(form).toBeVisible();

    await attachScreenshot(
      page,
      testInfo,
      'UC1.1_UI_010-retained-inputs.png',
      'Biểu mẫu sau lỗi trùng tên đăng nhập'
    );
  });

  await test.step('Kiểm tra thông tin đã nhập được giữ lại', async () => {
    const retained = [
      'ten_dang_nhap',
      'ten_khach_hang',
      'email',
      'so_dien_thoai',
      'quan_huyen',
      'xa_phuong',
      'dia_chi_chi_tiet',
    ];

    for (const name of retained) {
      await expect
        .soft(
          form.locator(`[name="${name}"]`),
          `Trường ${name} phải giữ nguyên dữ liệu đã nhập.`
        )
        .toHaveValue(data[name], { timeout: 3_000 });
    }

    await expect
      .soft(form.locator('input[readonly]'), 'Tỉnh/Thành phố vẫn là Hà Nội.')
      .toHaveValue(SAMPLE.tinh_thanh, { timeout: 3_000 });

    await expect
      .soft(page.getByText('Đăng ký tài khoản thành công', { exact: true }))
      .not.toBeVisible({ timeout: 3_000 });

    const actualValues = {};

    for (const name of retained) {
      actualValues[name] = await form.locator(`[name="${name}"]`).inputValue();
    }

    await testInfo.attach('Dữ liệu thực tế sau lỗi đăng ký', {
      body: JSON.stringify(actualValues, null, 2),
      contentType: 'application/json',
    });
  });
});

/**
 * Test Case: UC1.1_UI_011
 * Đăng ký thành công và chuyển về Đăng nhập.
 * Dùng dữ liệu MỚI (khachhang_ui011_xxxxxxx) để không trùng tài khoản "khachhang".
 * Lưu ý: ca này tạo thật một tài khoản trong CSDL kiểm thử.
 */
test('UC1.1_UI_011 - Dang ky thanh cong va chuyen ve Dang nhap', async ({
  page,
}, testInfo) => {
  test.setTimeout(60_000);

  const data = uniqueUser('ui011');

  await test.step('Mở biểu mẫu đăng ký', async () => {
    await openRegister(page);
  });

  const form = getForm(page);

  await test.step('Nhập đầy đủ thông tin hợp lệ', async () => {
    await expect(form).toHaveCount(1);

    await fillRegisterForm(form, data);

    await expect(form.locator(':invalid')).toHaveCount(0);

    await testInfo.attach('Dữ liệu đăng ký để đối chiếu', {
      body: JSON.stringify(data, null, 2),
      contentType: 'application/json',
    });
  });

  await test.step('Gửi đăng ký và lưu trang phản hồi', async () => {
    const responsePromise = page.waitForResponse((response) =>
      isRegisterPost(response.request())
    );

    const navigationPromise = page.waitForNavigation({ waitUntil: 'load' });

    await form.getByRole('button', { name: 'Đăng ký', exact: true }).click();

    const [response] = await Promise.all([responsePromise, navigationPromise]);

    await testInfo.attach('Phản hồi đăng ký và URL thực tế', {
      body: JSON.stringify(
        {
          requestUrl: response.url(),
          responseStatus: response.status(),
          finalUrl: page.url(),
        },
        null,
        2
      ),
      contentType: 'application/json',
    });

    // Lưu ảnh trước assertion để vẫn có bằng chứng khi điều hướng sai.
    await attachScreenshot(
      page,
      testInfo,
      'UC1.1_UI_011-registration-result.png',
      'Trang thực tế sau khi đăng ký'
    );
  });

  await test.step('Đối chiếu thông báo và điều hướng theo SRS', async () => {
    await expect
      .soft(
        page.getByText('Đăng ký tài khoản thành công', { exact: true }),
        'Phải hiển thị thông báo đăng ký tài khoản thành công.'
      )
      .toBeVisible({ timeout: 5_000 });

    await expect
      .soft(
        page,
        'Theo SRS, đăng ký thành công phải chuyển về trang Đăng nhập.'
      )
      .toHaveURL(/\/login\/?$/, { timeout: 5_000 });
  });
});

/**
 * Test Case: UC1.1_UI_012
 * Chuyển sang trang Đăng nhập.
 */
test('UC1.1_UI_012 - Chuyen sang trang Dang nhap', async ({
  page,
}, testInfo) => {
  const registrationPosts = [];

  const trackRequest = (request) => {
    if (isRegisterPost(request)) {
      registrationPosts.push(request.url());
    }
  };

  page.on('request', trackRequest);

  try {
    await test.step('Mở biểu mẫu đăng ký', async () => {
      await openRegister(page);

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

      await expect(loginForm.locator('input[name="ten_dang_nhap"]')).toBeVisible();
      await expect(loginForm.locator('input[name="password"]')).toBeVisible();

      await expect(
        loginForm.getByRole('button', { name: 'Đăng nhập', exact: true })
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
      await attachScreenshot(
        page,
        testInfo,
        'UC1.1_UI_012-login-navigation.png',
        'Trang Đăng nhập sau khi nhấn liên kết'
      );
    });
  } finally {
    page.off('request', trackRequest);
  }
});