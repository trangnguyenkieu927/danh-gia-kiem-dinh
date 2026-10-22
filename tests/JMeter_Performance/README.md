# ⚡ BỘ KIỂM THỬ HIỆU NĂNG & ĐỘ TẢI VỚI APACHE JMETER

Tài liệu hướng dẫn chạy kiểm thử hiệu năng, độ trễ và khả năng chịu tải (Load & Stress Testing) cho hệ thống **RoseShop / FlowerStore**.

---

## 📌 1. Danh Mục Tập Tin

- [`FlowerStore_LoadTest_Plan.jmx`](file:///e:/đgkđ/danh-gia-kiem-dinh/tests/JMeter_Performance/FlowerStore_LoadTest_Plan.jmx): Kịch bản kiểm thử tải gồm 50 luồng người dùng đồng thời (50 Concurrent Users, Ramp-up 10s, 5 Loops), kiểm tra:
  * Tuyến đường `/` (Trang chủ)
  * Tuyến đường `/login` (Trang đăng nhập)
  * Assertion: Mã HTTP trả về phải là **200 OK**
  * Assertion: Thời gian phản hồi phải nhỏ hơn **2000ms** (ngưỡng chấp nhận theo SRS)

---

## 🚀 2. Hướng Dẫn Thực Thi

### Cách 1: Chạy bằng giao diện đồ họa JMeter GUI:
1. Mở file thực thi JMeter trên máy:
   ```text
   E:\apache-jmeter-5.6.3\apache-jmeter-5.6.3\bin\jmeter.bat
   ```
2. Nhấn `File -> Open` và chọn `tests/JMeter_Performance/FlowerStore_LoadTest_Plan.jmx`.
3. Nhấn nút **Start (màu xanh lá ▶)** để chạy.
4. Xem kết quả trực quan tại **View Results Tree** và **Summary Report**.

### Cách 2: Chạy dòng lệnh Non-GUI Mode và tự động sinh Dashboard HTML:
Mở PowerShell tại thư mục dự án và chạy:
```powershell
& "E:\apache-jmeter-5.6.3\apache-jmeter-5.6.3\bin\jmeter.bat" -n -t tests/JMeter_Performance/FlowerStore_LoadTest_Plan.jmx -l tests/Evidence/jmeter-results.jtl -e -o tests/Evidence/jmeter-dashboard-report
```
*Lưu ý: Nếu thư mục `tests/Evidence/jmeter-dashboard-report` hoặc file `.jtl` đã tồn tại từ lần chạy trước, hãy xóa chúng đi trước khi chạy lại.*

---

## 📊 3. Các Chỉ Số Cốt Lõi Khi Trình Bày Vấn Đáp

| Chỉ số | Định nghĩa | Ý nghĩa đánh giá |
| :--- | :--- | :--- |
| **Throughput (TPS)** | Số giao dịch xử lý được trong 1 giây | Đánh giá năng lực phục vụ của web server Laravel |
| **Average Response Time** | Thời gian phản hồi trung bình (ms) | Đánh giá độ trễ truy vấn cơ sở dữ liệu MySQL |
| **Error Rate (%)** | Tỷ lệ phần trăm request bị lỗi (4xx, 5xx) | Đánh giá độ ổn định của hệ thống khi có tải cao |
