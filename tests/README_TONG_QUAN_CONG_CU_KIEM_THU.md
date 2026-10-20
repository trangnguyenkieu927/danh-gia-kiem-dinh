# 📚 TỔNG QUAN HỆ THỐNG CÔNG CỤ VÀ MÃ NGUỒN KIỂM THỬ (NHÓM 07 - ROSESHOP)

**Học phần:** Đánh giá và kiểm định chất lượng phần mềm  
**Giảng viên phụ trách:** TS. Mai Xuân Tráng  
**Trợ lý nghiên cứu & Chấm vấn đáp:** Anh Nguyễn Chí Công  
**Sinh viên thực hiện:** 
1. **Nguyễn Kiều Trang** (Trưởng nhóm / Phụ trách UC1.2 Đăng nhập & Điều hướng phân quyền)
2. **Dương Hùng Mạnh** (Thành viên / Phụ trách UC4.2 Quản lý và thực hiện giao hàng)
3. **Nguyễn Đức Trọng** (Thành viên / Phụ trách UC1.1 Đăng ký tài khoản)

---

## 🏗️ 1. Mô Hình Kim Tự Tháp Kiểm Thử (Test Pyramid Architecture)

Dự án áp dụng mô hình kim tự tháp kiểm thử phần mềm chuẩn **ISTQB / Mike Cohn** gồm 5 tầng công cụ rõ ràng:

```text
                  / \
                 / E2E \         <-- PLAYWRIGHT (tests/Playwright_UI/): 7 ca test giao diện & luồng người dùng
                /-------\
               / API/SEC \       <-- POSTMAN & OWASP ZAP (tests/Postman_API/, tests/Security_OWASP_ZAP/)
              /-----------\
             / PERFORMANCE \     <-- APACHE JMETER (tests/JMeter_Performance/): 50 luồng tải đồng thời
            /---------------\
           /  PHPUNIT TESTS  \   <-- PHPUNIT 11 (tests/Feature/, tests/Unit/): 51+ ca test nghiệp vụ cô lập
          /-------------------\
```

---

## 📁 2. Sơ Đồ Cây Thư Mục Phân Loại Theo Công Cụ

Toàn bộ các loại kiểm thử được phân nhóm chuyên biệt trong thư mục `tests/`:

```text
tests/
│
├── PHPUnit/                          <-- [PHPUnit] Toàn bộ mã kiểm thử Backend (Feature & Unit)
│   ├── Feature/                      * Kiểm thử tích hợp nghiệp vụ hệ thống
│   │   ├── UC1_2_LoginTest.php       * UC1.2 (Nguyễn Kiều Trang): 20 tests (146 assertions) - 100% Passed
│   │   ├── UC4_2_ShipperTest.php     * UC4.2 (Dương Hùng Mạnh): 43 tests nghiệp vụ giao hàng
│   │   ├── Auth/                     * Các test xác thực cơ sở mặc định của framework
│   │   ├── ProfileTest.php           * Test cập nhật hồ sơ người dùng
│   │   └── TrangChuTest.php          * Test hiển thị trang chủ
│   ├── Unit/                         * Kiểm thử đơn vị
│   │   └── ExampleTest.php
│   └── README.md                     * Cẩm nang kỹ thuật PHPUnit
│
├── Playwright_UI/                    <-- [Playwright] Kiểm thử Tự động Giao diện & Luồng E2E
│   ├── e2e/
│   │   ├── uc1_2_login.spec.js       * 7 kịch bản E2E Đăng nhập, Header, RBAC của Trang - 100% Passed
│   │   └── test_giao_dien.spec.js    * Kịch bản quét E2E Dashboard, Giỏ hàng, Chi tiết sản phẩm
│   └── README.md                     * Hướng dẫn chạy và giải trình kỹ thuật Playwright
│
├── Postman_API/                      <-- [Postman / Newman] Kiểm thử Giao diện Lập trình Ứng dụng (API)
│   ├── FlowerStore_API_Collection.json * Collection kiểm tra Status 200/302, Response Time, Session Cookie
│   └── README.md                     * Hướng dẫn chạy bằng Postman GUI hoặc lệnh Newman CLI
│
├── JMeter_Performance/               <-- [Apache JMeter] Kiểm thử Hiệu năng & Tải (Performance / Load)
│   ├── FlowerStore_LoadTest_Plan.jmx * Kịch bản tải 50 Concurrent Users, ngưỡng thời gian phản hồi < 2000ms
│   └── README.md                     * Hướng dẫn xuất Dashboard HTML & phân tích Throughput / Latency
│
├── Security_OWASP_ZAP/               <-- [OWASP ZAP] Kiểm thử An toàn Bảo mật Web
│   └── README.md                     * Phân tích 5 danh mục lỗ hổng OWASP Top 10 (SQLi, XSS, CSRF, RBAC)
│
├── Evidence/                         <-- Toàn bộ Bằng chứng (Evidence Logs) thực tế phục vụ vấn đáp
│   ├── UC1.1/                        * Thư mục bằng chứng của bạn Trọng
│   ├── UC1.2/                        * junit.xml (146 assertions) & testdox.txt (mô tả chi tiết bằng tiếng Việt)
│   ├── UC4.2/                        * junit.xml & testdox.txt (các kịch bản Shipper của bạn Mạnh)
│   └── Review/                       * uc42-review.xml
│
└── README_TONG_QUAN_CONG_CU_KIEM_THU.md  <-- TÀI LIỆU NÀY
```

---

## ⚡ 3. Bảng Lệnh Tra Cứu Nhanh Khi Vấn Đáp (Cheat Sheet)

| Công cụ | Mục đích kiểm thử | Lệnh thực thi nhanh trên Terminal |
| :--- | :--- | :--- |
| **PHPUnit (UC1.2)** | Test Backend Đăng nhập & RBAC | `php artisan test --filter=UC1_2_LoginTest` |
| **PHPUnit (UC4.2)** | Test Backend Shipper giao hàng | `php artisan test --filter=UC4_2_ShipperTest` |
| **PHPUnit (All)** | Chạy toàn bộ test Backend | `php artisan test` |
| **Playwright E2E** | Test giao diện E2E UC1.2 | `npx playwright test tests/Playwright_UI/e2e/uc1_2_login.spec.js --project=chromium` |
| **Playwright UI Mode** | Xem giao diện trực quan | `npx playwright test tests/Playwright_UI/e2e/uc1_2_login.spec.js --ui` |
| **Playwright Report** | Mở trang báo cáo HTML | `npx playwright show-report` |
| **Postman API** | Chạy tự động API Test | `npx newman run tests/Postman_API/FlowerStore_API_Collection.json` |
| **JMeter Load Test** | Chạy test tải Non-GUI | `jmeter -n -t tests/JMeter_Performance/FlowerStore_LoadTest_Plan.jmx -l results.jtl` |

---

## 🎯 4. Đáp Ứng Chuẩn Xác Góp Ý Của Anh Công

1. **Về phân chia Use Case:** Trong mã nguồn, các ca kiểm thử thể hiện rõ trách nhiệm từng thành viên (Trang: UC1.2, Mạnh: UC4.2, Trọng: UC1.1) độc lập, không giẫm chân lên nhau.
2. **Về tính rõ ràng & không dùng từ ngữ cảm tính:** Tất cả các tên test case, assertions và evidence đều có mã định danh truy vết (`TS-AUTH-001` đến `TS-AUTH-006`, `TC-UI-LOGIN-01` đến `07`) với kết quả Pass/Fail định lượng rõ ràng.
3. **Về các trường hợp đặc biệt:** Có đầy đủ kiểm thử kiểm soát phân quyền (RBAC 403 Forbidden), bảo toàn giỏ hàng qua phiên làm việc (`session()->regenerate()`), chặn injection và kiểm thử tải với thời gian phản hồi giới hạn.
