# 🐘 BỘ KIỂM THỬ BACKEND VỚI PHPUNIT 11 (LARAVEL 12)

Tài liệu hướng dẫn thực thi, cấu trúc và giải trình kỹ thuật kiểm thử Backend (Feature & Unit Test) của dự án **RoseShop / FlowerStore**.

---

## 📌 1. Cấu Trúc Các Bộ Kiểm Thử PHPUnit

Toàn bộ các ca kiểm thử PHPUnit được lưu trữ trong thư mục `tests/Feature/` và `tests/Unit/`, kế thừa từ lớp kiểm thử chuẩn `Tests\TestCase`:

| Tập tin kiểm thử | Use Case / Phạm vi | Người thực hiện | Số lượng test | Trạng thái |
| :--- | :--- | :---: | :---: | :---: |
| [`UC1_2_LoginTest.php`](file:///e:/đgkđ/danh-gia-kiem-dinh/tests/Feature/UC1_2_LoginTest.php) | **UC1.2 – Đăng nhập & Điều hướng Phân quyền** | **Nguyễn Kiều Trang** | **20 Tests (146 Assertions)** | **100% Passed** |
| [`UC4_2_ShipperTest.php`](file:///e:/đgkđ/danh-gia-kiem-dinh/tests/Feature/UC4_2_ShipperTest.php) | **UC4.2 – Quản lý và Thực hiện Giao hàng (Shipper)** | **Dương Hùng Mạnh** | **31 Tests** | **100% Passed** |
| `UC1_1_RegisterTest.php` *(Đang chờ gộp)* | **UC1.1 – Đăng ký tài khoản Khách hàng** | **Nguyễn Đức Trọng** | Đang cập nhật | Chờ tích hợp |

---

## ⚙️ 2. Môi Trường Thực Thi Cô Lập (Test Isolation)

- **Cơ sở dữ liệu kiểm thử:** Cấu hình tự động sử dụng **SQLite in-memory** (`:memory:`) được định nghĩa trong `phpunit.xml`.
- **Đặc tính an toàn:** Toàn bộ dữ liệu kiểm thử được tạo qua `RefreshDatabase` và các hàm tạo giả lập (`makeUserWithRole`), tự động hủy sạch ngay khi chạy xong bộ test. **Không bao giờ làm thay đổi hay ghi đè dữ liệu trên MySQL dùng chung**.

---

## 🚀 3. Hướng Dẫn Chạy Lệnh Kiểm Thử

Mở PowerShell tại thư mục gốc `danh-gia-kiem-dinh`:

### Chạy riêng suite kiểm thử UC1.2 (Đăng nhập - Trang):
```powershell
php artisan test --filter=UC1_2_LoginTest
```

### Chạy riêng suite kiểm thử UC4.2 (Giao hàng - Mạnh):
```powershell
php artisan test --filter=UC4_2_ShipperTest
```

### Chạy toàn bộ tất cả bài kiểm thử của dự án:
```powershell
php artisan test
```

### Xuất báo cáo chi tiết kèm định dạng TestDox:
```powershell
php artisan test --filter=UC1_2_LoginTest --testdox
```

---

## 🎓 4. Trọng Tâm Vấn Đáp Với Giảng Viên (TS. Mai Xuân Tráng & Anh Công)

1. **Tại sao lại dùng `#[DataProvider]` thay vì viết từng hàm rời rạc?**
   - *Trả lời:* Sử dụng DataProvider giúp áp dụng nguyên lý kiểm thử tham số hóa (**Parameterized Testing**), tách bạch rõ ràng giữa mã kiểm thử logic và tập dữ liệu thử nghiệm (Input Values), tránh trùng lặp code (DRY) và dễ mở rộng các ca biên.
2. **Kiểm tra phân quyền chéo (Cross-Role Access / RBAC) như thế nào?**
   - *Trả lời:* Scenario `TS-AUTH-005` kiểm thử 5 ca phân quyền: Khách hàng vào Doanh thu Admin, Khách hàng vào Admin Dashboard, Khách hàng vào Shipper Dashboard, Nhân viên vào Quản lý người dùng, Shipper vào Admin Dashboard. Tất cả đều phải trả về đúng mã trạng thái HTTP **403 Forbidden**.
3. **Tại sao lại kiểm tra giữ nguyên giỏ hàng khi đăng nhập (Scenario 6)?**
   - *Trả lời:* Khi người dùng đăng nhập, Laravel tự động gọi `session()->regenerate()` để chống tấn công **Session Fixation**. Bài test `test_TS_AUTH_006` xác minh giỏ hàng của khách vãng lai trước đăng nhập vẫn được bảo toàn nguyên vẹn sau khi đổi Session ID.
