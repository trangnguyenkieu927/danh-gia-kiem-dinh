# FlowerStore — 9 kịch bản JMeter

Trạng thái: CHƯA CHẠY trên FlowerStore. Bộ này được dựng theo route/controller đã cung cấp. Đã kiểm tra cấu trúc XML; chưa xác minh bằng JMeter và ứng dụng thực tế. Chạy smoke trước mỗi nhóm. Không tự kết luận Passed từ HTTP 200 hoặc mã thoát của JMeter.

## 1. Chuẩn bị

1. Giải nén thư mục này. Mở CMD tại thư mục chứa run.cmd và các file .jmx.
2. Mở CMD khác tại dự án Laravel, chạy `php artisan serve --host=127.0.0.1 --port=8000`.
3. Dùng môi trường thử nghiệm và dữ liệu có thể tạo/cập nhật. JMeter truy cập DB do ứng dụng đang chạy sử dụng, KHÔNG sử dụng SQLite :memory: của PHPUnit. Các ca đăng ký tạo tài khoản thật trong DB này; các ca giao hàng chuyển trạng thái đơn thật trong DB này.
4. JMeter 5.6.3 phải được cài. Cấu hình đường dẫn, sửa theo máy của bạn:

```bat
set "JMETER_BAT=C:\apache-jmeter-5.6.3\bin\jmeter.bat"
set HOST=127.0.0.1
set PORT=8000
```

5. CSV UTF-8, giữ nguyên tiêu đề. Không mở/lưu số điện thoại bằng Excel theo kiểu Number làm mất số 0. Không gửi mật khẩu thật hoặc cookie trong báo cáo chia sẻ.
6. Địa chỉ dữ liệu mẫu: Hà Nội (server tự thêm), Quận/Huyện Hà Đông, Xã/Phường Yên Nghĩa, Xuân Mai Complex. Đối chiếu đúng danh mục ứng dụng trước chạy; không dùng Yên Nghĩa đồng thời làm quận và phường.

## 2. Dữ liệu cho ba UC

### Đăng ký

Tạo dữ liệu mới bằng Node đã có trên máy:

```bat
node generate-register-data.cjs reg01 10000 0900000000
set "DATA_FILE=data/register-reg01.csv"
```

Tên đăng nhập/email sinh theo batch; điện thoại là dải 10 chữ số. Kiểm tra dải này chưa tồn tại trong DB thử nghiệm. Mỗi lần chạy phải tạo batch mới VÀ dải điện thoại mới chưa dùng. Batch mới không tự làm điện thoại mới. CSV không recycle; hết dòng thì thread dừng, phải ghi nhận lần chạy thiếu dữ liệu. Các hàng không dùng hết không được coi là tài khoản đã tạo.

### Đăng nhập

`data/auth.csv` có mẫu khachhang/123456. Xác nhận tài khoản tồn tại và đúng mật khẩu. Với tải, chuẩn bị tối thiểu số tài khoản khác nhau bằng số threads (spike cần 20). Cột dashboard/protected_path phải khớp vai trò:

| Vai trò | dashboard | protected_path |
|---|---|---|
| Khách hàng | /customer/dashboard | /customer/gio-hang |
| Admin/Nhân viên | /admin/dashboard | /admin/dashboard |
| Shipper | /shipper/dashboard | /shipper/dashboard |

CSV login được recycle; session cookie riêng cho từng thread và được xóa mỗi vòng. Nếu chỉ dùng một dòng thì đang đo nhiều phiên cùng một tài khoản, không được ghi là nhiều tài khoản độc lập. Việc tái sử dụng tài khoản giữa các vòng không chứng minh phân quyền đầy đủ.

### Giao hàng

Sửa `data/shipping.csv`: username,password,order_id. Các giá trị REPLACE... chỉ là chỗ trống, KHÔNG phải tài khoản/mã đơn thật.

JM-SHIP-001/002: mỗi dòng là một đơn CONFIRMED riêng, được phân công cho Shipper cùng dòng. Không lặp order_id. Hồ sơ nhân viên, khách hàng, chi tiết đơn và sản phẩm liên quan phải đầy đủ. Dòng đã chạy có thể chuyển SHIPPING/DELIVERED; không chạy lại với dữ liệu đó. Nếu lần chạy bị ngắt giữa chừng, kiểm tra trạng thái trước khi dùng lại.

JM-SHIP-003: ít nhất 5 dòng với 5 Shipper/đơn được phân công có thể đọc. Đăng nhập một lần mỗi thread rồi lặp thao tác đọc; không PATCH. Bộ này không tự seed hoặc khôi phục CSDL.

## 3. Chạy smoke đầu tiên: JM-AUTH-001

```bat
set "DATA_FILE=data/auth.csv"
set THREADS=1
set LOOPS=1
set RAMP=1
set DURATION=60
set PAUSE_MS=1000
set ON_ERROR=stopthread
run.cmd JM-AUTH-001 auth-smoke-01
```

Gửi kết quả `results/auth-smoke-01/results.jtl`, `jmeter.log` và báo cáo `html/index.html` hoặc ảnh Statistics/Errors. Không coi shell exit code 0 là tất cả assertions Passed.

## 4. Các cấu hình chạy còn lại

Mỗi lần gọi run.cmd, tham số thứ hai là tên lần chạy MỚI. Không ghi đè bằng chứng cũ. Các biến set được giữ trong CMD hiện tại, vì vậy phải đặt đầy đủ theo bảng trước mỗi lần chạy.

| Ca | THREADS | RAMP giây | LOOPS | DURATION giây | ON_ERROR | CSV |
|---|---:|---:|---:|---:|---|---|
| JM-REG-001 smoke | 1 | 1 | 1 | 60 | stopthread | register batch mới |
| JM-REG-001 baseline | 1 | 1 | 20 | 600 | startnextloop | register batch mới |
| JM-REG-002 tải lần 1 / 2 | 5 / 10 | 30 | -1 | 300 | startnextloop | batch riêng mỗi lần |
| JM-REG-003 bước 1 / 2 / 3 | 10 / 20 / 30 | 30 | -1 | 120 | startnextloop | batch riêng mỗi bước |
| JM-AUTH-001 baseline | 1 | 1 | 20 | 600 | stopthread | auth.csv |
| JM-AUTH-002 tải lần 1 / 2 | 5 / 10 | 30 | -1 | 300 | stopthread | auth.csv |
| JM-AUTH-003 spike | cố định 5 + 15 | cấu hình trong JMX | -1 | cố định 180/60 | stopthread | auth.csv ít nhất 20 tài khoản |
| JM-SHIP-001 smoke / baseline | 1 | 1 | 1 / 20 | 60 / 600 | stopthread | đơn CONFIRMED mới |
| JM-SHIP-002 tải lần 1 / 2 | 5 / 10 | 30 | -1 | 300 | stopthread | nhiều đơn CONFIRMED mới |
| JM-SHIP-003 ổn định ngắn | 5 | 5 | 1 | 1800 | stopthread | shipping.csv |

DURATION là giới hạn thời gian chạy thread group, bao gồm ramp; không bảo đảm đủ từng ấy thời gian ở mức tải đỉnh. Vòng lặp, EOF CSV, lỗi hoặc thời gian có thể kết thúc ca sớm. Tổng thời gian thực tế phải lấy từ log. PAUSE_MS=1000 là nghỉ trước MỖI HTTP request; không phải 1 giây mỗi nghiệp vụ. Thời gian nghỉ không nằm trong elapsed của HTTP sampler.

Ví dụ tải đăng nhập 5 users:

```bat
set "DATA_FILE=data/auth.csv"
set THREADS=5
set RAMP=30
set LOOPS=-1
set DURATION=300
set PAUSE_MS=1000
set ON_ERROR=stopthread
run.cmd JM-AUTH-002 auth-load5-01
```

Sau đó đặt THREADS=10 và tên lần chạy auth-load10-01 để chạy mức tiếp theo. Các ca khác làm tương tự theo bảng.

Spike: 5 thread nền chạy 180 giây; thêm 15 thread từ giây 60 trong 60 giây. Mục tiêu 5→20→5 thread hoạt động, không có nghĩa 20 HTTP request đến cùng một thời điểm. Cần LOOPS=-1, đủ dữ liệu và không có thread dừng vì lỗi mới đạt tải đề xuất.

Stress: chạy từng mức, kiểm tra log/tài nguyên giữa các mức. Nếu smoke đã sai chức năng, báo cáo riêng trước khi tăng tải. Nếu xuất hiện lỗi kết nối liên tiếp, tài nguyên vượt mức nhóm cho phép hoặc server không phục hồi, dừng bằng Shutdown trong JMeter, không tiếp tục tăng. Chạy lại mức 1 user sau khi giảm tải để đo phục hồi. Chưa có tiêu chí dừng định lượng do SRS chưa cung cấp; thống nhất trước khi chạy stress. Không tự chạy 1.000 users hoặc 12–24 giờ.

## 5. Assertion và giới hạn

- Không tự động theo redirect: kiểm tra HTTP 302 và Location đúng, tránh redirect về login rồi HTTP 200 bị tính thành thành công.
- CSRF lấy từ HTML input _token hoặc meta csrf-token; session cookie được giữ trong thread. PATCH gửi X-CSRF-TOKEN và Accept application/json.
- Login dùng ten_dang_nhap + password; đăng ký dùng mat_khau + mat_khau_confirmation.
- Shipper PATCH không gửi trường trạng thái vì controller tự chuyển trạng thái. Không tự retry PATCH.
- Nếu trang dashboard không xuất token, script dừng với Missing CSRF token: gửi HTML/token thực tế để điều chỉnh bộ lấy token; không tắt CSRF.
- Đăng ký vẫn kỳ vọng /login theo SRS. Source hiện tại tự đăng nhập rồi chuyển /customer/dashboard: dự kiến assertion sẽ Fail. Với startnextloop, lần lỗi chuyển sang vòng tiếp theo, cookie được xóa và dùng dữ liệu mới. Tài khoản có thể đã được tạo dù assertion thất bại. Không đổi expected thành dashboard để làm test xanh.
- Thông báo thành công được tìm trong HTML trả về của trang login; đây là kiểm tra phản hồi HTTP, không chứng minh hiển thị bằng JavaScript như Playwright. Không có message trong HTML cần đối chiếu cơ chế render thực tế.
- GET shipper detail kiểm tra HTTP 200, chưa xác minh nội dung chi tiết vì chưa có source view/contract đầy đủ. PATCH kiểm tra JSON success=true và new_status đúng. Sau chạy cần đối chiếu CSDL: trạng thái, ngày giao, thanh toán; cache thông báo và COD kiểm tra riêng. Không dùng HTTP assertion để kết luận đã đúng dữ liệu kho/COD.
- JMeter đo HTTP, không đo render UI, accessibility hoặc toàn bộ bảo mật. Không có kiểm thử tranh chấp nhận đơn vì route đó chưa được xác nhận trong hệ thống.
- Đây là php artisan serve trên máy thử nghiệm; không suy diễn kết quả thành năng lực PHP-FPM hoặc production. JMeter và server cùng máy sẽ tranh CPU/RAM. Ghi cấu hình máy, DB, mạng, thời điểm và phiên bản code.
- Soak 30 phút là kiểm tra ổn định ngắn. Muốn kết luận rò rỉ bộ nhớ cần dữ liệu CPU/RAM theo thời gian, không chỉ response time.

## 6. Gửi kết quả và điền biểu mẫu

`Test_Cases.csv` có 9 dòng đủ các cột Category, Test Case ID, Description, Steps, Step Expected Result, Test Input Value, Test Case Expected Result, Test Case Program Result, Status. Hai cột Steps và Step Expected Result tương ứng nhóm Test Procedures của mẫu bạn yêu cầu.

Gửi cho mỗi lần chạy:
- ID ca, run ID, threads/ramp/loops/duration/CSV số dòng, có đủ điều kiện đầu vào hay không.
- results.jtl, jmeter.log, html/statistics.json (nếu có), ảnh bảng Statistics/Errors.
- Số sample, Average, p90/p95/p99, Max, Error%, Throughput và tài nguyên server quan sát được.
- Với ca ghi: dữ liệu tạo/cập nhật thực tế sau chạy; không gửi mật khẩu hoặc thông tin khách hàng thật.

Báo cáo thống kê theo từng nhãn HTTP. Tổng throughput là request/giây, KHÔNG phải đăng ký hoặc đơn hàng/giây. Các script chưa có Transaction Controller đo elapsed toàn nghiệp vụ. Không lấy tổng sample làm số tài khoản đã tạo. Không có ngưỡng response time/throughput PER được duyệt nên chưa kết luận Passed về hiệu năng; có thể kết luận assertion chức năng đạt/không đạt trong môi trường đã đo. Phân biệt lỗi nghiệp vụ đã biết, thiếu dữ liệu/CSRF và lỗi xuất hiện do tải.

Nguồn cấu hình: Apache JMeter User Manual — https://jmeter.apache.org/usermanual/get-started.html và https://jmeter.apache.org/usermanual/component_reference.html
