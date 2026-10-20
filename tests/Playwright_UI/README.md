# 🎭 BỘ KIỂM THỬ TỰ ĐỘNG GIAO DIỆN (E2E / UI TEST) VỚI PLAYWRIGHT

Tài liệu hướng dẫn thực thi kịch bản kiểm thử giao diện người dùng và luồng nghiệp vụ E2E của dự án **RoseShop / FlowerStore**.

---

## 📌 1. Danh Mục Các Tập Tin Kiểm Thử Playwright

Các tập tin kiểm thử được tổ chức trong thư mục `tests/Playwright_UI/e2e/`:

| Tập tin kiểm thử | Use Case / Phạm vi | Người thực hiện | Số lượng test | Trạng thái |
| :--- | :--- | :---: | :---: | :---: |
| [`uc1_2_login.spec.js`](file:///e:/đgkđ/danh-gia-kiem-dinh/tests/Playwright_UI/e2e/uc1_2_login.spec.js) | **UC1.2 – Kiểm thử E2E Đăng nhập, Header và Phân quyền** | **Nguyễn Kiều Trang** | **7 Tests E2E** | **100% Passed (Chromium)** |
| [`test_giao_dien.spec.js`](file:///e:/đgkđ/danh-gia-kiem-dinh/tests/Playwright_UI/e2e/test_giao_dien.spec.js) | **Giao diện tổng hợp: Dashboard, Giỏ hàng, Chi tiết sản phẩm** | Nhóm 07 | 4 Tests | Hoạt động |

---

## 🎯 2. Chi Tiết 7 Kịch Bản Tinh Hoa Của UC1.2 (`uc1_2_login.spec.js`)

1. **`TC-UI-LOGIN-01`**: Kiểm tra hiển thị đầy đủ các trường nhập liệu (`#ten_dang_nhap`, `#password`), nhãn chức năng, checkbox ghi nhớ và liên kết sang Đăng ký.
2. **`TC-UI-LOGIN-02`**: Kiểm tra chặn gửi biểu mẫu phía Client (HTML5 Validation) khi để trống các trường bắt buộc.
3. **`TC-UI-LOGIN-03`**: Kiểm tra hiển thị thông báo lỗi màu đỏ khi nhập sai tài khoản/mật khẩu, tự động xóa ô mật khẩu để bảo mật.
4. **`TC-UI-LOGIN-04`**: Đăng nhập tài khoản Khách hàng (`kh27` / `123456`) thành công, điều hướng về trang chủ và hiển thị Header người dùng kèm điểm tích lũy.
5. **`TC-UI-LOGIN-05`**: Đăng nhập tài khoản Admin (`admin` / `123456`), điều hướng chính xác đến trang quản trị `/admin/dashboard`.
6. **`TC-UI-LOGIN-06`**: Chặn người dùng chưa xác thực (Unauthenticated) truy cập trực tiếp các route bảo vệ và tự động redirect về trang `/login`.
7. **`TC-UI-LOGIN-07`**: Đăng xuất tài khoản, hủy phiên làm việc thành công và trở lại trạng thái Khách vãng lai (`Guest`).

---

## 🚀 3. Hướng Dẫn Chạy Lệnh Kiểm Thử

Đảm bảo web server Laravel đang chạy (`php artisan serve` tại cổng `8000`).

### Chạy kiểm thử tự động ở chế độ dòng lệnh (Headless - khuyến nghị):
```powershell
npx playwright test tests/Playwright_UI/e2e/uc1_2_login.spec.js --project=chromium
```

### Chạy kiểm thử ở chế độ giao diện trực quan (UI Mode - dùng khi trình chiếu vấn đáp):
```powershell
npx playwright test tests/Playwright_UI/e2e/uc1_2_login.spec.js --ui
```

### Chạy có mở trình duyệt thực tế (Headed mode):
```powershell
npx playwright test tests/Playwright_UI/e2e/uc1_2_login.spec.js --headed --project=chromium
```

### Mở báo cáo HTML trực quan kèm ảnh chụp màn hình (HTML Report):
```powershell
npx playwright show-report
```
