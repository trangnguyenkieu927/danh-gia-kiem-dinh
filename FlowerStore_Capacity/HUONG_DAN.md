# JM-REG-003-CAP — Khảo sát ngưỡng tải đăng ký FlowerStore

## Mục tiêu và giới hạn kết luận

Xác định khoảng tải bắt đầu không đáp ứng **tiêu chí khảo sát đề xuất**, trong môi trường hiện tại `php artisan serve`. Không suy rộng sang máy chủ production hoặc toàn hệ thống. Chưa có ngưỡng hiệu năng được phê duyệt trong SRS.

Tiêu chí đề xuất cho từng lần chạy hợp lệ: tỷ lệ lỗi kết nối/timeout không quá 1% tổng HTTP request và p95 thời gian POST đăng ký không quá 10.000 ms. p95 dùng nearest rank, tính trên toàn bộ POST, kể cả POST timeout. Timeout 15 giây làm giới hạn khả năng quan sát thời gian phản hồi thật của các yêu cầu này.

Lỗi nghiệp vụ chuyển tới dashboard thay vì `/login` vẫn là FAILED theo SRS. Bộ chạy không đổi assertion này. Kết quả khảo sát hiệu năng và kết quả chức năng là hai kết luận riêng. Đối chiếu CSDL bắt buộc trước khi chốt số lượt đăng ký thành công; HTTP 302 tự nó không chứng minh đã tạo đủ hồ sơ.

## 1. Chuẩn bị

1. Giải nén nguyên thư mục này. Có thể đặt tại `D:\trích lỏ\danh-gia-kiem-dinh\jmeter-capacity`.
2. Trong terminal khác, tại thư mục dự án, giữ ứng dụng chạy: `php artisan serve --host=127.0.0.1 --port=8000`.
3. Chỉ chạy một bài tải tại một thời điểm. Giữ nguyên máy, ứng dụng, cấu hình DB, danh mục địa chỉ, mật khẩu và thời gian nghỉ giữa các lần đo. Ghi thời điểm bắt đầu/kết thúc và khoảng nghỉ thực tế. Quan sát CPU/RAM trong Task Manager của PHP, Java và máy; nếu máy phát tải bão hòa thì chưa kết luận giới hạn thuộc ứng dụng.
4. Dữ liệu mặc định: họ tên Nguyễn Đức Trọng; quận Hà Đông; phường Yên Nghĩa; địa chỉ Xuân Mai Complex; mật khẩu `123456`; email và username tự sinh mới. Xác nhận cặp địa chỉ này có trong danh mục của ứng dụng đang chạy; sửa dòng tạo dữ liệu trong `capacity.cjs` nếu danh mục khác.
5. **Chọn dải số điện thoại chưa tồn tại trong DB thử nghiệm.** Mỗi lần chạy dành riêng 1.000 số từ số bắt đầu, dù không sử dụng hết. Các dải ví dụ dưới đây chỉ được dùng nếu chưa tồn tại. Không dùng thông tin liên hệ thật. Không tự động xóa hoặc reset DB. Script chặn dùng lại thư mục kết quả và dải số đã đặt trước trong chính bộ này; không biết các tài khoản đã có từ trước trong DB.
6. Nếu đường dẫn máy khác, đặt trong CMD:

```bat
set "CAP_JAVA=C:\Program Files\Eclipse Adoptium\jdk-17.0.14.7-hotspot\bin\java.exe"
set "CAP_JMETER_JAR=D:\trích lỏ\apache-jmeter-5.6.3\bin\ApacheJMeter.jar"
```

Script gọi thẳng Java 17 và kiểm tra phiên bản; không phụ thuộc Java 24 đang đứng trước trên PATH. Chạy CLI, không cần mở JMeter GUI.

## 2. Chạy từng mức, không chạy cả chuỗi một lúc

Mở CMD trong thư mục vừa giải nén. Chạy mức đầu tiên:

```bat
run-capacity.cmd run 5 cap5-01 0900100000
```

Mỗi mức: ramp-up 30 giây, tổng thời gian scheduler 180 giây (đã gồm ramp-up), vòng lặp liên tục, nghỉ 1.000 ms trước **mỗi** GET và POST; connect timeout 5.000 ms; response timeout 15.000 ms. Kết thúc có thể muộn hơn do request đang xử lý. Số users là số phiên ảo, không phải số request thực sự đang được máy chủ xử lý đồng thời.

Luồng mỗi vòng: xóa cookie → đọc bộ dữ liệu mới → GET `/register` → lấy CSRF → POST `/register` → kiểm tra 302 và Location `/login`. Không theo redirect. Bước trước lỗi thì chuyển vòng kế tiếp; không gửi POST khi GET/CSRF thất bại. Không kiểm tra nội dung thông báo thành công hay giao diện trong bài này.

Sau mỗi mức, xem `runs\<run-id>\summary.json`, đối chiếu log và CSDL rồi mới chuyển mức. Nếu 5 users đã vượt tiêu chí, dừng chuỗi tăng và khảo sát thấp hơn bằng kế hoạch riêng; không gọi 5 là tải tối đa. Bộ hiện tại chủ ý chỉ nhận mức 5–10.

| Thứ tự | Lệnh mẫu | Thực hiện khi |
|---|---|---|
| 1 | `run-capacity.cmd run 5 cap5-01 0900100000` | Lần đầu, dải số mới |
| 2 | `run-capacity.cmd run 6 cap6-01 0900101000` | 5 đáp ứng tiêu chí khảo sát |
| 3 | `run-capacity.cmd run 8 cap8-01 0900102000` | 6 đáp ứng tiêu chí |
| 4 | `run-capacity.cmd run 10 cap10-01 0900103000` | 8 đáp ứng tiêu chí |
| Thu hẹp | `run-capacity.cmd run 7 cap7-01 0900104000` | 6 đáp ứng, 8 vượt tiêu chí |
| Thu hẹp | `run-capacity.cmd run 9 cap9-01 0900105000` | 8 đáp ứng, 10 vượt tiêu chí |

Nếu 6 vượt tiêu chí trong khi 5 đáp ứng, khoảng khảo sát là 5–6. Nếu 10 vẫn đáp ứng, chỉ biết mức cao nhất đã đo là 10, chưa tìm được cận trên; không tự nhận đây là điểm gãy. Sau khi tìm được khoảng, chạy lại mức cao nhất đáp ứng và mức thấp nhất vượt tiêu chí, bằng run ID và dải số mới; nên có ít nhất hai lần đo mỗi mức. Nếu kết quả không lặp lại, ghi chưa ổn định thay vì chốt một con số.

## 3. Đọc kết quả đúng

| Trường | Ý nghĩa |
|---|---|
| `transportErrorPercent` | Lỗi có responseCode không phải HTTP, gồm timeout/socket; không phải toàn bộ lỗi chức năng |
| `knownSrsRedirectFailures` | POST nhận 302 dashboard; sai SRS, vẫn Failed chức năng |
| `unexpectedHttpOrScriptFailures` | HTTP sai dự kiến, về register, mất CSRF, hoặc assertion/script bất thường; phải điều tra, chưa chốt ngưỡng |
| `postP95Ms` | 95% POST có elapsed không lớn hơn giá trị này, theo nearest rank |
| `httpThroughputPerSecond` | Tổng HTTP samples chia thời gian quan sát; không phải lượt đăng ký thành công/giây |
| `maxObservedThreads` | Đối chiếu mức threads quan sát; không chứng minh xử lý song song trên server |
| `finalMinutePostCount`, `finalMinutePostP95Ms` | Kiểm tra có POST ở phút cuối và độ trễ giai đoạn cuối |
| `MEETS_PROPOSED_CRITERIA` | Đáp ứng hai tiêu chí khảo sát; cần kiểm tra CSDL/log/tài nguyên trước khi chốt |
| `EXCEEDS_PROPOSED_LIMIT` | Vượt ít nhất một tiêu chí khảo sát; chưa chứng minh server sập |
| `INCONCLUSIVE` | Có dữ liệu bất thường, thời gian ngắn, thiếu mẫu hoặc không đạt mức users; điều tra rồi chạy mới |

HTML report của JMeter sẽ vẫn có Error do assertion SRS; tuyệt đối không gọi tỷ lệ này là tỷ lệ timeout. Bộ này xuất JTL, log, JSON, CSV dữ liệu; có thể tạo HTML sau bằng JMeter CLI `-g results.jtl -o thu-muc-html-moi`. Không chỉnh hoặc ghi đè JTL gốc.

Phân tích lại khi cần:

```bat
run-capacity.cmd analyze runs\cap5-01
```

Nếu về `/register`, có thể dữ liệu trùng hoặc lỗi lưu/validation. Nếu Java/Groovy báo lỗi, kết quả đo không hợp lệ. Giữ log, sửa môi trường/dữ liệu rồi chạy thư mục mới; không đổi assertion để làm xanh.

## 4. Đối chiếu DB và server

Trong terminal dự án, chạy `php artisan tinker`. Thay `BATCH_TU_RUN_JSON` bằng giá trị `batch` của lần chạy:

```php
$prefix = 'BATCH_TU_RUN_JSON'.'_';
$ids = \App\Models\NguoiDung::where('ten_dang_nhap', 'like', $prefix.'%')->get()->filter(fn ($u) => str_starts_with($u->ten_dang_nhap, $prefix))->pluck('ma_nguoi_dung');
['accounts' => $ids->count(), 'profiles' => \App\Models\KhachHang::whereIn('ma_khach_hang', $ids)->count()];
```

Lưu kết quả và đối chiếu username trong JTL. Timeout không chắc giao dịch thất bại: server có thể đã commit sau khi client bỏ chờ. Chờ server xử lý hết trước khi kiểm tra DB. Khi cần xác minh từng tài khoản, đối chiếu danh sách username trong JTL, không chỉ số đếm.

Nếu nghi server sập, lưu terminal chạy PHP, `storage\logs\laravel.log`, thời điểm và mã thoát tiến trình nếu có; kiểm tra cổng trong PowerShell:

```powershell
Get-NetTCPConnection -LocalPort 8000 -State Listen
```

Trong CMD kiểm tra sau tải (đây là request kiểm tra bổ sung, không đưa vào JTL tải):

```bat
curl.exe --max-time 30 -sS -o NUL -w "HTTP=%{http_code} TIME=%{time_total}s\n" http://127.0.0.1:8000/register
```

Nếu đang có nhiều request chờ, probe timeout vẫn chưa đủ kết luận sập. Không tự động khởi động lại server giữa các mức; nếu phải khởi động lại, ghi rõ và tách đợt đo. Dừng khảo sát khi đã có cận dưới/cận trên lặp lại được; không cần cố làm ứng dụng sập.

## 5. Mẫu ghi kết luận (chỉ điền sau chạy)

| Users | Run ID | HTTP | POST | Lỗi transport % | POST p95 ms | DB account/profile | Kết quả khảo sát |
|---|---|---|---|---|---|---|---|
| 5 | cap5-01 | Chưa chạy | | | | | Chưa có kết quả |
| 6 | cap6-01 | Chưa chạy | | | | | Chưa có kết quả |
| 8 | cap8-01 | Chưa chạy | | | | | Chưa có kết quả |
| 10 | cap10-01 | Chưa chạy | | | | | Chưa có kết quả |

“Trong môi trường php artisan serve và luồng GET/POST đăng ký có nghỉ 1 giây/request, mức X users đáp ứng tiêu chí khảo sát qua các lần đo …; mức Y users vượt tiêu chí qua các lần đo …. Khoảng bắt đầu suy giảm theo tiêu chí khảo sát là X–Y users. Lỗi điều hướng theo SRS vẫn tồn tại. Chưa có bằng chứng tiến trình máy chủ sập / [bằng chứng cụ thể nếu có].”

Giữ kèm JMX, run.json, CSV, JTL, jmeter.log, summary.json và kết quả đối chiếu DB. Đây là bài kiểm tra bổ sung cho JM-REG-003; không thay kết quả cũ và không chứng minh mọi NFR của hệ thống.

## Kiểm tra gói

Chạy `node self-test.cjs` để kiểm tra bộ phân tích bằng dữ liệu giả lập, không gửi request và không ghi DB. Gói đã kiểm tra cấu trúc XML, cấu hình tĩnh và bộ phân tích. Chưa thực thi JMeter/FlowerStore trên máy của bạn.
