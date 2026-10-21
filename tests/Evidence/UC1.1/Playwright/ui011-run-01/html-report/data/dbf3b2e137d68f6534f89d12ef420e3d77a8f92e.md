# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: UC1_1_Register.spec.js >> UC1.1_UI_011 - Dang ky thanh cong va chuyen ve Dang nhap
- Location: e2e\UC1_1_Register.spec.js:1270:1

# Error details

```
Error: Phải hiển thị thông báo đăng ký tài khoản thành công.

expect(locator).toBeVisible() failed

Locator: getByText('Đăng ký tài khoản thành công', { exact: true })
Expected: visible
Timeout: 5000ms
Error: element(s) not found

Call log:
  - Phải hiển thị thông báo đăng ký tài khoản thành công. getByText('Đăng ký tài khoản thành công', { exact: true }) with timeout 5000ms
  - waiting for getByText('Đăng ký tài khoản thành công', { exact: true })

```

```yaml
- text: "🌸 📞 Hotline: 0964023510 💬 Zalo:"
- link "0357 634 696":
  - /url: https://zalo.me/0357634696
- link "📘 Facebook":
  - /url: https://www.facebook.com/duong.manh.19423
- text: 🕐 Phục vụ 24/7
- banner:
  - link "Logo RoseShop Chuyên Các Loại Hoa Tươi":
    - /url: http://127.0.0.1:8000/customer/dashboard
    - img "Logo"
    - text: RoseShop Chuyên Các Loại Hoa Tươi
  - textbox "Tìm sản phẩm...":
    - /placeholder: "Tìm sản phẩm... "
  - button "Tìm kiếm"
  - link "":
    - /url: http://127.0.0.1:8000/customer/thong-bao
  - button "🛒 0"
  - link "👤 Xin chào, ui011_1792558156939_7383":
    - /url: http://127.0.0.1:8000/customer/profile
    - text: 👤 Xin chào,
    - strong: ui011_1792558156939_7383
  - button "Đăng xuất"
- navigation:
  - link "TRANG CHỦ":
    - /url: http://127.0.0.1:8000/customer/dashboard
  - link "HOA TƯƠI":
    - /url: http://127.0.0.1:8000/customer/hoa-tuoi
  - link "PHỤ KIỆN":
    - /url: http://127.0.0.1:8000/customer/phu-kien
  - link "Quà tặng":
    - /url: http://127.0.0.1:8000/customer/qua-tang
  - link "Giới thiệu":
    - /url: http://127.0.0.1:8000/customer/gioi-thieu
  - link "TIN TỨC":
    - /url: http://127.0.0.1:8000/customer/tin-tuc
  - link "LIÊN HỆ":
    - /url: http://127.0.0.1:8000/customer/lien-he
- img "Banner 1"
- img "Banner 2"
- img "Banner 3"
- button "‹"
- button "›"
- text: 🚚
- heading "Giao hàng nhanh" [level=4]
- paragraph: Giao 2h-4h trong nội thành
- text: 💐
- heading "Hoa tươi mỗi ngày" [level=4]
- paragraph: Cam kết hoa tươi 100%
- text: 💳
- heading "Thanh toán dễ dàng" [level=4]
- paragraph: Nhiều hình thức thanh toán
- text: ☎️
- heading "Hỗ trợ 24/7" [level=4]
- paragraph: "Hotline: 0357 634 696"
- heading "🌸 Hoa tươi bán chạy" [level=2]
- link "Xem tất cả":
  - /url: http://127.0.0.1:8000/customer/hoa-tuoi
- img "Hoa hong do"
- heading "Hoa hong do" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Hoa tươi tự nhiên, chất lượng cao, được nhập mới mỗi ngày, màu sắc đa dạng, độ tươi lâu, thích hợp cho trang trí, cắm hoa và các dịp đặc biệt. 🌷🌹🌻🌼
- text: "350.000đ / cành Sản phẩm: 1389"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/1
- img "Hoa lan cao cap"
- heading "Hoa lan cao cap" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Hoa tươi tự nhiên, chất lượng cao, được nhập mới mỗi ngày, màu sắc đa dạng, độ tươi lâu, thích hợp cho trang trí, cắm hoa và các dịp đặc biệt. 🌷🌹🌻🌼
- text: "600.000đ / cành Sản phẩm: 38"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/2
- img "Hoa Ban"
- heading "Hoa Ban" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Hoa tươi tự nhiên, chất lượng cao, được nhập mới mỗi ngày, màu sắc đa dạng, độ tươi lâu, thích hợp cho trang trí, cắm hoa và các dịp đặc biệt. 🌷🌹🌻🌼
- text: "150.000đ / cành Sản phẩm: 44"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/3
- img "Hoa Valin"
- heading "Hoa Valin" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Hoa tươi tự nhiên, chất lượng cao, được nhập mới mỗi ngày, màu sắc đa dạng, độ tươi lâu, thích hợp cho trang trí, cắm hoa và các dịp đặc biệt. 🌷🌹🌻🌼
- text: "10.000đ / cành Sản phẩm: 6"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/4
- img "Hoa Nym"
- heading "Hoa Nym" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Hoa tươi tự nhiên, chất lượng cao, được nhập mới mỗi ngày, màu sắc đa dạng, độ tươi lâu, thích hợp cho trang trí, cắm hoa và các dịp đặc biệt. 🌷🌹🌻🌼
- text: "250.000đ / cành Sản phẩm: 13"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/5
- img "Hoa Vàng"
- heading "Hoa Vàng" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Hoa tươi tự nhiên, chất lượng cao, được nhập mới mỗi ngày, màu sắc đa dạng, độ tươi lâu, thích hợp cho trang trí, cắm hoa và các dịp đặc biệt. 🌷🌹🌻🌼
- text: "450.000đ / cành Sản phẩm: 38"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/6
- img "Hoa Đặc Biệt"
- heading "Hoa Đặc Biệt" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Hoa tươi tự nhiên, chất lượng cao, được nhập mới mỗi ngày, màu sắc đa dạng, độ tươi lâu, thích hợp cho trang trí, cắm hoa và các dịp đặc biệt. 🌷🌹🌻🌼
- text: "350.000đ / cành Sản phẩm: 15"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/7
- img "Hoa Cúc"
- heading "Hoa Cúc" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Hoa tươi tự nhiên, chất lượng cao, được nhập mới mỗi ngày, màu sắc đa dạng, độ tươi lâu, thích hợp cho trang trí, cắm hoa và các dịp đặc biệt. 🌷🌹🌻🌼
- text: "750.000đ / cành Sản phẩm: 58"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/8
- heading "🎀 Phụ kiện nổi bật" [level=2]
- link "Xem tất cả":
  - /url: http://127.0.0.1:8000/customer/phu-kien
- img "Hộp Trái Tim"
- heading "Hộp Trái Tim" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa. 🌷🎁
- text: "45.000đ / cái Sản phẩm: 91"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/14
- img "Dây Đèn"
- heading "Dây Đèn" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa." 🌷🎁
- text: "Liên hệ / cái Sản phẩm: 10"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/32
- img "Bình Thủy Tinh"
- heading "Bình Thủy Tinh" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa.🌷🎁
- text: "Liên hệ / cái Sản phẩm: 15"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/33
- img "Giấy Gói Hàn Quốc"
- heading "Giấy Gói Hàn Quốc" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa.🌷🎁
- text: "Liên hệ / cái Sản phẩm: 200"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/34
- img "Kraft"
- heading "Kraft" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa.🌷🎁
- text: "Liên hệ / cái Sản phẩm: 0"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/35
- img "Giỏ Mây Tre"
- heading "Giỏ Mây Tre" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa. 🌷🎁
- text: "Liên hệ / cái Sản phẩm: 30"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/36
- heading "🎁 Quà tặng nổi bật" [level=2]
- link "Xem tất cả":
  - /url: http://127.0.0.1:8000/customer/qua-tang
- img "Khung Ảnh Mini"
- heading "Khung Ảnh Mini" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa. 🌷🎁
- text: "200.000đ / sản phẩm Sản phẩm: 0"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/11
- img "Thỏ Bông Mini"
- heading "Thỏ Bông Mini" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa. 🌷🎁
- text: "111.000đ / sản phẩm Sản phẩm: 32"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/12
- img "Ấm Trà Hoa"
- heading "Ấm Trà Hoa" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa. 🌷🎁
- text: "298.000đ / sản phẩm Sản phẩm: 24"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/44
- img "Cốc Giữ Nhiệt"
- heading "Cốc Giữ Nhiệt" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa. 🌷🎁
- text: "Liên hệ / sản phẩm Sản phẩm: 0"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/45
- img "Cốc Trà Hoa"
- heading "Cốc Trà Hoa" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa. 🌷🎁
- text: "Liên hệ / sản phẩm Sản phẩm: 45"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/46
- img "Gấu Bông Mini"
- heading "Gấu Bông Mini" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa. 🌷🎁
- text: "Liên hệ / sản phẩm Sản phẩm: 97"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/48
- heading "📰 Tin tức nổi bật" [level=2]
- link "Xem tất cả":
  - /url: http://127.0.0.1:8000/customer/tin-tuc
- img "Cách bảo quản hoa"
- heading "Cách bảo quản hoa hồng lâu tàn" [level=3]
- paragraph: Mẹo nhỏ giúp hoa hồng tươi lâu hơn và giữ màu đẹp.
- link "Xem chi tiết →":
  - /url: http://127.0.0.1:8000/customer/tin-tuc/bao-quan-hoa-hong
- img "Ý nghĩa hoa"
- heading "Ý nghĩa các màu hoa tươi" [level=3]
- paragraph: Mỗi loài hoa và màu sắc đều mang một thông điệp riêng.
- link "Xem chi tiết →":
  - /url: http://127.0.0.1:8000/customer/tin-tuc/y-nghia-mau-hoa
- img "Hoa sinh nhật"
- heading "Gợi ý hoa tặng sinh nhật" [level=3]
- paragraph: Những mẫu hoa đẹp, dễ tặng và phù hợp nhiều đối tượng.
- link "Xem chi tiết →":
  - /url: http://127.0.0.1:8000/customer/tin-tuc/goi-y-hoa-tang-sinh-nhat
- contentinfo:
  - img "RoseShop"
  - heading "RoseShop" [level=3]
  - paragraph: Chuyên cung cấp hoa tươi chất lượng cao, giao hàng nhanh chóng và tận tâm.
  - link "f":
    - /url: "#"
  - link "▶":
    - /url: "#"
  - link "◎":
    - /url: "#"
  - heading "THÔNG TIN LIÊN HỆ" [level=4]
  - paragraph: 📍 123 Nguyễn Trãi, Thanh Xuân, Hà Nội
  - paragraph: 📞 0357 634 696
  - paragraph: ✉️ roseshop@gmail.com
  - paragraph: 🕘 7:00 - 22:00 (Tất cả các ngày)
  - heading "DANH MỤC" [level=4]
  - link "Trang chủ":
    - /url: http://127.0.0.1:8000/customer/dashboard
  - link "Hoa tươi":
    - /url: http://127.0.0.1:8000/customer/hoa-tuoi
  - link "Phụ kiện":
    - /url: http://127.0.0.1:8000/customer/phu-kien
  - link "Tin tức":
    - /url: http://127.0.0.1:8000/customer/tin-tuc
  - link "Giới thiệu":
    - /url: "#"
  - link "Liên hệ":
    - /url: "#"
  - heading "CHÍNH SÁCH" [level=4]
  - link "Chính sách giao hàng":
    - /url: "#"
  - link "Chính sách đổi trả":
    - /url: "#"
  - link "Chính sách bảo mật":
    - /url: "#"
  - link "Điều khoản sử dụng":
    - /url: "#"
  - heading "ĐĂNG KÝ NHẬN TIN" [level=4]
  - paragraph: Nhận ưu đãi và thông tin mới nhất từ RoseShop.
  - textbox "Nhập email của bạn"
  - button "Đăng ký"
  - paragraph: © 2026 RoseShop. All Rights Reserved.
  - text: VISA 🔴🟠 mastercard momo ZaloPay
- text: ✅ Đã thêm sản phẩm vào giỏ hàng thành công
```

```
Error: Theo SRS, đăng ký thành công phải chuyển về trang Đăng nhập.

expect(page).toHaveURL(expected) failed

Expected pattern: /\/login\/?$/
Received string:  "http://127.0.0.1:8000/customer/dashboard"
Timeout: 5000ms

Call log:
  - Theo SRS, đăng ký thành công phải chuyển về trang Đăng nhập. with timeout 5000ms
    13 × locator resolved to <html lang="vi">…</html>
       - unexpected value "http://127.0.0.1:8000/customer/dashboard"

```

```yaml
- text: "🌸 📞 Hotline: 0964023510 💬 Zalo:"
- link "0357 634 696":
  - /url: https://zalo.me/0357634696
- link "📘 Facebook":
  - /url: https://www.facebook.com/duong.manh.19423
- text: 🕐 Phục vụ 24/7
- banner:
  - link "Logo RoseShop Chuyên Các Loại Hoa Tươi":
    - /url: http://127.0.0.1:8000/customer/dashboard
    - img "Logo"
    - text: RoseShop Chuyên Các Loại Hoa Tươi
  - textbox "Tìm sản phẩm...":
    - /placeholder: "Tìm sản phẩm... "
  - button "Tìm kiếm"
  - link "":
    - /url: http://127.0.0.1:8000/customer/thong-bao
  - button "🛒 0"
  - link "👤 Xin chào, ui011_1792558156939_7383":
    - /url: http://127.0.0.1:8000/customer/profile
    - text: 👤 Xin chào,
    - strong: ui011_1792558156939_7383
  - button "Đăng xuất"
- navigation:
  - link "TRANG CHỦ":
    - /url: http://127.0.0.1:8000/customer/dashboard
  - link "HOA TƯƠI":
    - /url: http://127.0.0.1:8000/customer/hoa-tuoi
  - link "PHỤ KIỆN":
    - /url: http://127.0.0.1:8000/customer/phu-kien
  - link "Quà tặng":
    - /url: http://127.0.0.1:8000/customer/qua-tang
  - link "Giới thiệu":
    - /url: http://127.0.0.1:8000/customer/gioi-thieu
  - link "TIN TỨC":
    - /url: http://127.0.0.1:8000/customer/tin-tuc
  - link "LIÊN HỆ":
    - /url: http://127.0.0.1:8000/customer/lien-he
- img "Banner 1"
- img "Banner 2"
- img "Banner 3"
- button "‹"
- button "›"
- text: 🚚
- heading "Giao hàng nhanh" [level=4]
- paragraph: Giao 2h-4h trong nội thành
- text: 💐
- heading "Hoa tươi mỗi ngày" [level=4]
- paragraph: Cam kết hoa tươi 100%
- text: 💳
- heading "Thanh toán dễ dàng" [level=4]
- paragraph: Nhiều hình thức thanh toán
- text: ☎️
- heading "Hỗ trợ 24/7" [level=4]
- paragraph: "Hotline: 0357 634 696"
- heading "🌸 Hoa tươi bán chạy" [level=2]
- link "Xem tất cả":
  - /url: http://127.0.0.1:8000/customer/hoa-tuoi
- img "Hoa hong do"
- heading "Hoa hong do" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Hoa tươi tự nhiên, chất lượng cao, được nhập mới mỗi ngày, màu sắc đa dạng, độ tươi lâu, thích hợp cho trang trí, cắm hoa và các dịp đặc biệt. 🌷🌹🌻🌼
- text: "350.000đ / cành Sản phẩm: 1389"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/1
- img "Hoa lan cao cap"
- heading "Hoa lan cao cap" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Hoa tươi tự nhiên, chất lượng cao, được nhập mới mỗi ngày, màu sắc đa dạng, độ tươi lâu, thích hợp cho trang trí, cắm hoa và các dịp đặc biệt. 🌷🌹🌻🌼
- text: "600.000đ / cành Sản phẩm: 38"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/2
- img "Hoa Ban"
- heading "Hoa Ban" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Hoa tươi tự nhiên, chất lượng cao, được nhập mới mỗi ngày, màu sắc đa dạng, độ tươi lâu, thích hợp cho trang trí, cắm hoa và các dịp đặc biệt. 🌷🌹🌻🌼
- text: "150.000đ / cành Sản phẩm: 44"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/3
- img "Hoa Valin"
- heading "Hoa Valin" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Hoa tươi tự nhiên, chất lượng cao, được nhập mới mỗi ngày, màu sắc đa dạng, độ tươi lâu, thích hợp cho trang trí, cắm hoa và các dịp đặc biệt. 🌷🌹🌻🌼
- text: "10.000đ / cành Sản phẩm: 6"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/4
- img "Hoa Nym"
- heading "Hoa Nym" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Hoa tươi tự nhiên, chất lượng cao, được nhập mới mỗi ngày, màu sắc đa dạng, độ tươi lâu, thích hợp cho trang trí, cắm hoa và các dịp đặc biệt. 🌷🌹🌻🌼
- text: "250.000đ / cành Sản phẩm: 13"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/5
- img "Hoa Vàng"
- heading "Hoa Vàng" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Hoa tươi tự nhiên, chất lượng cao, được nhập mới mỗi ngày, màu sắc đa dạng, độ tươi lâu, thích hợp cho trang trí, cắm hoa và các dịp đặc biệt. 🌷🌹🌻🌼
- text: "450.000đ / cành Sản phẩm: 38"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/6
- img "Hoa Đặc Biệt"
- heading "Hoa Đặc Biệt" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Hoa tươi tự nhiên, chất lượng cao, được nhập mới mỗi ngày, màu sắc đa dạng, độ tươi lâu, thích hợp cho trang trí, cắm hoa và các dịp đặc biệt. 🌷🌹🌻🌼
- text: "350.000đ / cành Sản phẩm: 15"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/7
- img "Hoa Cúc"
- heading "Hoa Cúc" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Hoa tươi tự nhiên, chất lượng cao, được nhập mới mỗi ngày, màu sắc đa dạng, độ tươi lâu, thích hợp cho trang trí, cắm hoa và các dịp đặc biệt. 🌷🌹🌻🌼
- text: "750.000đ / cành Sản phẩm: 58"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/8
- heading "🎀 Phụ kiện nổi bật" [level=2]
- link "Xem tất cả":
  - /url: http://127.0.0.1:8000/customer/phu-kien
- img "Hộp Trái Tim"
- heading "Hộp Trái Tim" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa. 🌷🎁
- text: "45.000đ / cái Sản phẩm: 91"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/14
- img "Dây Đèn"
- heading "Dây Đèn" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa." 🌷🎁
- text: "Liên hệ / cái Sản phẩm: 10"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/32
- img "Bình Thủy Tinh"
- heading "Bình Thủy Tinh" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa.🌷🎁
- text: "Liên hệ / cái Sản phẩm: 15"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/33
- img "Giấy Gói Hàn Quốc"
- heading "Giấy Gói Hàn Quốc" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa.🌷🎁
- text: "Liên hệ / cái Sản phẩm: 200"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/34
- img "Kraft"
- heading "Kraft" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa.🌷🎁
- text: "Liên hệ / cái Sản phẩm: 0"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/35
- img "Giỏ Mây Tre"
- heading "Giỏ Mây Tre" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa. 🌷🎁
- text: "Liên hệ / cái Sản phẩm: 30"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/36
- heading "🎁 Quà tặng nổi bật" [level=2]
- link "Xem tất cả":
  - /url: http://127.0.0.1:8000/customer/qua-tang
- img "Khung Ảnh Mini"
- heading "Khung Ảnh Mini" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa. 🌷🎁
- text: "200.000đ / sản phẩm Sản phẩm: 0"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/11
- img "Thỏ Bông Mini"
- heading "Thỏ Bông Mini" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa. 🌷🎁
- text: "111.000đ / sản phẩm Sản phẩm: 32"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/12
- img "Ấm Trà Hoa"
- heading "Ấm Trà Hoa" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa. 🌷🎁
- text: "298.000đ / sản phẩm Sản phẩm: 24"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/44
- img "Cốc Giữ Nhiệt"
- heading "Cốc Giữ Nhiệt" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa. 🌷🎁
- text: "Liên hệ / sản phẩm Sản phẩm: 0"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/45
- img "Cốc Trà Hoa"
- heading "Cốc Trà Hoa" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa. 🌷🎁
- text: "Liên hệ / sản phẩm Sản phẩm: 45"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/46
- img "Gấu Bông Mini"
- heading "Gấu Bông Mini" [level=3]
- paragraph:
  - strong: "Mô tả:"
  - text: Các sản phẩm phụ kiện và quà tặng được thiết kế để kết hợp hài hòa cùng hoa tươi, góp phần tạo nên những món quà đẹp mắt, sang trọng và giàu ý nghĩa. 🌷🎁
- text: "Liên hệ / sản phẩm Sản phẩm: 97"
- link "Chi tiết":
  - /url: http://127.0.0.1:8000/customer/san-pham/48
- heading "📰 Tin tức nổi bật" [level=2]
- link "Xem tất cả":
  - /url: http://127.0.0.1:8000/customer/tin-tuc
- img "Cách bảo quản hoa"
- heading "Cách bảo quản hoa hồng lâu tàn" [level=3]
- paragraph: Mẹo nhỏ giúp hoa hồng tươi lâu hơn và giữ màu đẹp.
- link "Xem chi tiết →":
  - /url: http://127.0.0.1:8000/customer/tin-tuc/bao-quan-hoa-hong
- img "Ý nghĩa hoa"
- heading "Ý nghĩa các màu hoa tươi" [level=3]
- paragraph: Mỗi loài hoa và màu sắc đều mang một thông điệp riêng.
- link "Xem chi tiết →":
  - /url: http://127.0.0.1:8000/customer/tin-tuc/y-nghia-mau-hoa
- img "Hoa sinh nhật"
- heading "Gợi ý hoa tặng sinh nhật" [level=3]
- paragraph: Những mẫu hoa đẹp, dễ tặng và phù hợp nhiều đối tượng.
- link "Xem chi tiết →":
  - /url: http://127.0.0.1:8000/customer/tin-tuc/goi-y-hoa-tang-sinh-nhat
- contentinfo:
  - img "RoseShop"
  - heading "RoseShop" [level=3]
  - paragraph: Chuyên cung cấp hoa tươi chất lượng cao, giao hàng nhanh chóng và tận tâm.
  - link "f":
    - /url: "#"
  - link "▶":
    - /url: "#"
  - link "◎":
    - /url: "#"
  - heading "THÔNG TIN LIÊN HỆ" [level=4]
  - paragraph: 📍 123 Nguyễn Trãi, Thanh Xuân, Hà Nội
  - paragraph: 📞 0357 634 696
  - paragraph: ✉️ roseshop@gmail.com
  - paragraph: 🕘 7:00 - 22:00 (Tất cả các ngày)
  - heading "DANH MỤC" [level=4]
  - link "Trang chủ":
    - /url: http://127.0.0.1:8000/customer/dashboard
  - link "Hoa tươi":
    - /url: http://127.0.0.1:8000/customer/hoa-tuoi
  - link "Phụ kiện":
    - /url: http://127.0.0.1:8000/customer/phu-kien
  - link "Tin tức":
    - /url: http://127.0.0.1:8000/customer/tin-tuc
  - link "Giới thiệu":
    - /url: "#"
  - link "Liên hệ":
    - /url: "#"
  - heading "CHÍNH SÁCH" [level=4]
  - link "Chính sách giao hàng":
    - /url: "#"
  - link "Chính sách đổi trả":
    - /url: "#"
  - link "Chính sách bảo mật":
    - /url: "#"
  - link "Điều khoản sử dụng":
    - /url: "#"
  - heading "ĐĂNG KÝ NHẬN TIN" [level=4]
  - paragraph: Nhận ưu đãi và thông tin mới nhất từ RoseShop.
  - textbox "Nhập email của bạn"
  - button "Đăng ký"
  - paragraph: © 2026 RoseShop. All Rights Reserved.
  - text: VISA 🔴🟠 mastercard momo ZaloPay
- text: ✅ Đã thêm sản phẩm vào giỏ hàng thành công
```

# Test source

```ts
  1294 | 
  1295 |   await test.step('Mở biểu mẫu đăng ký', async () => {
  1296 |     const response = await page.goto('/register');
  1297 | 
  1298 |     expect(response).not.toBeNull();
  1299 |     expect(response.status()).toBe(200);
  1300 |     await expect(page).toHaveURL(/\/register\/?$/);
  1301 |   });
  1302 | 
  1303 |   const form = page.locator('form').filter({
  1304 |     has: page.locator('input[name="ten_dang_nhap"]'),
  1305 |   });
  1306 | 
  1307 |   await test.step('Nhập đầy đủ thông tin hợp lệ', async () => {
  1308 |     await expect(form).toHaveCount(1);
  1309 | 
  1310 |     for (const name of [
  1311 |       'ten_dang_nhap',
  1312 |       'ten_khach_hang',
  1313 |       'email',
  1314 |       'so_dien_thoai',
  1315 |       'dia_chi_chi_tiet',
  1316 |     ]) {
  1317 |       await form.locator(`[name="${name}"]`).fill(data[name]);
  1318 |     }
  1319 | 
  1320 |     await form.locator('[name="quan_huyen"]')
  1321 |       .selectOption({ label: data.quan_huyen });
  1322 | 
  1323 |     await form.locator('[name="xa_phuong"]')
  1324 |       .selectOption({ label: data.xa_phuong });
  1325 | 
  1326 |     await form.locator('[name="mat_khau"]').fill('Test123!');
  1327 |     await form.locator('[name="mat_khau_confirmation"]').fill('Test123!');
  1328 | 
  1329 |     await expect(form.locator(':invalid')).toHaveCount(0);
  1330 | 
  1331 |     await testInfo.attach('Dữ liệu đăng ký để đối chiếu', {
  1332 |       body: JSON.stringify(data, null, 2),
  1333 |       contentType: 'application/json',
  1334 |     });
  1335 |   });
  1336 | 
  1337 |   await test.step('Gửi đăng ký và lưu trang phản hồi', async () => {
  1338 |     const responsePromise = page.waitForResponse((response) => {
  1339 |       return response.request().method() === 'POST' &&
  1340 |         /^\/register\/?$/.test(new URL(response.url()).pathname);
  1341 |     });
  1342 | 
  1343 |     // Chờ điều hướng của biểu mẫu trước khi kiểm tra trang kết quả.
  1344 |     const navigationPromise = page.waitForNavigation({
  1345 |       waitUntil: 'load',
  1346 |     });
  1347 | 
  1348 |     await form.getByRole('button', {
  1349 |       name: 'Đăng ký',
  1350 |       exact: true,
  1351 |     }).click();
  1352 | 
  1353 |     const [response] = await Promise.all([
  1354 |       responsePromise,
  1355 |       navigationPromise,
  1356 |     ]);
  1357 | 
  1358 |     await testInfo.attach('Phản hồi đăng ký và URL thực tế', {
  1359 |       body: JSON.stringify({
  1360 |         requestUrl: response.url(),
  1361 |         responseStatus: response.status(),
  1362 |         finalUrl: page.url(),
  1363 |       }, null, 2),
  1364 |       contentType: 'application/json',
  1365 |     });
  1366 | 
  1367 |     // Lưu ảnh trước assertion để vẫn có bằng chứng khi điều hướng sai.
  1368 |     const screenshotPath = testInfo.outputPath(
  1369 |       'UC1.1_UI_011-registration-result.png'
  1370 |     );
  1371 | 
  1372 |     await page.screenshot({
  1373 |       path: screenshotPath,
  1374 |       fullPage: true,
  1375 |     });
  1376 | 
  1377 |     await testInfo.attach('Trang thực tế sau khi đăng ký', {
  1378 |       path: screenshotPath,
  1379 |       contentType: 'image/png',
  1380 |     });
  1381 |   });
  1382 | 
  1383 |   await test.step('Đối chiếu thông báo và điều hướng theo SRS', async () => {
  1384 |     await expect.soft(
  1385 |       page.getByText('Đăng ký tài khoản thành công', {
  1386 |         exact: true,
  1387 |       }),
  1388 |       'Phải hiển thị thông báo đăng ký tài khoản thành công.'
  1389 |     ).toBeVisible({ timeout: 5_000 });
  1390 | 
  1391 |     await expect.soft(
  1392 |       page,
  1393 |       'Theo SRS, đăng ký thành công phải chuyển về trang Đăng nhập.'
> 1394 |     ).toHaveURL(/\/login\/?$/, { timeout: 5_000 });
       |       ^ Error: Theo SRS, đăng ký thành công phải chuyển về trang Đăng nhập.
  1395 |   });
  1396 | });
```