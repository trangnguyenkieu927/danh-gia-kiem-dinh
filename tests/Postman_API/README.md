# 📮 BỘ KIỂM THỬ API VỚI POSTMAN & NEWMAN

Tài liệu hướng dẫn kiểm thử tự động API cho hệ thống **RoseShop / FlowerStore**.

---

## 📌 1. Danh Mục Tập Tin

- [`FlowerStore_API_Collection.json`](file:///e:/đgkđ/danh-gia-kiem-dinh/tests/Postman_API/FlowerStore_API_Collection.json): Bộ sưu tập Postman Collection v2.1 chứa các request kiểm thử tự động cho:
  1. API Sản phẩm công khai (`/api/sanpham`)
  2. Xác thực đăng nhập Khách hàng thành công / thất bại
  3. Kiểm soát phân quyền truy cập chéo (RBAC Route Guarding: `/admin/dashboard`, `/shipper/don-hang`)

---

## 🚀 2. Hướng Dẫn Thực Thi

### Cách 1: Chạy trực tiếp bằng Postman GUI
1. Mở phần mềm Postman.
2. Chọn **Import** và kéo thả file `FlowerStore_API_Collection.json`.
3. Chọn Collection -> Nhấn **Run Collection** -> Quan sát toàn bộ Assertions (Pass/Fail).

### Cách 2: Chạy tự động bằng công cụ dòng lệnh Newman (CLI):
```powershell
npx newman run tests/Postman_API/FlowerStore_API_Collection.json
```

Xuất báo cáo HTML đẹp mắt:
```powershell
npx newman run tests/Postman_API/FlowerStore_API_Collection.json -r cli,htmlextra --reporter-htmlextra-export tests/Evidence/postman-report.html
```
