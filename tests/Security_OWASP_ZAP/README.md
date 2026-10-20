# 🛡️ BỘ KIỂM THỬ AN TOÀN BẢO MẬT VỚI OWASP ZAP

Tài liệu hướng dẫn rà soát và kiểm thử tự động các lỗ hổng bảo mật web theo tiêu chuẩn **OWASP Top 10** cho dự án **RoseShop / FlowerStore**.

---

## 📌 1. Phạm Vi Kiểm Thử Bảo Mật

Bộ kiểm thử tập trung đánh giá 5 danh mục lỗ hổng cốt lõi trong hệ thống:

| Lỗ hổng theo OWASP | Biện pháp bảo vệ trong mã nguồn Laravel | Phương pháp kiểm thử |
| :--- | :--- | :--- |
| **SQL Injection (A03:2021)** | Sử dụng Eloquent ORM & Prepared Statements (PDO Parameter Binding) | Kiểm thử injection qua ô đăng nhập và API tham số |
| **Cross-Site Scripting - XSS (A03:2021)** | Blade Template tự động Escape mã HTML (`{{ ... }}`) | Kiểm thử chèn payload `<script>alert(1)</script>` |
| **CSRF - Cross-Site Request Forgery (A01:2021)** | Bắt buộc middleware `VerifyCsrfToken` trên mọi form POST | Kiểm thử gửi POST request không kèm token `@csrf` |
| **Broken Access Control (A01:2021)** | Middleware `CheckRole` chặn truy cập trái vai trò (403 Forbidden) | Kiểm thử RBAC truy cập chéo đơn hàng và route Admin |
| **Session Fixation (A07:2021)** | Hàm `session()->regenerate()` tự động đổi ID phiên khi đăng nhập | Kiểm thử đối soát Session ID trước và sau đăng nhập |

---

## 🚀 2. Hướng Dẫn Thực Hiện Quét Tự Động Với OWASP ZAP

1. Khởi động công cụ **OWASP ZAP**.
2. Chọn **Automated Scan**.
3. Điền URL mục tiêu: `http://127.0.0.1:8000`.
4. Nhấn **Attack** để ZAP thực hiện:
   - **Spider Scan:** Thu thập toàn bộ cây thư mục và liên kết trên web.
   - **Active Scan:** Bắn các payload kiểm tra lỗ hổng bảo mật.
5. Xuất báo cáo kết quả quét dạng HTML: `Report -> Generate Report -> Save as ZAP_Security_Report.html` và lưu vào thư mục `tests/Evidence/`.
