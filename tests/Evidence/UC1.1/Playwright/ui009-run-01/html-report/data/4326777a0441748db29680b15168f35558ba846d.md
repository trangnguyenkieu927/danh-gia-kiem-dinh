# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: UC1_1_Register.spec.js >> UC1.1_UI_009 - Dang ky bang so dien thoai da ton tai
- Location: e2e\UC1_1_Register.spec.js:976:1

# Error details

```
Error: Đăng ký trùng số điện thoại phải quay về trang đăng ký.

expect(page).toHaveURL(expected) failed

Expected pattern: /\/register\/?$/
Received string:  "http://127.0.0.1:8000/customer/dashboard"
Timeout: 10000ms

Call log:
  - Đăng ký trùng số điện thoại phải quay về trang đăng ký. with timeout 10000ms
    23 × locator resolved to <html lang="vi">…</html>
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
  - link "👤 Xin chào, ui009_1792557134219_7170":
    - /url: http://127.0.0.1:8000/customer/profile
    - text: 👤 Xin chào,
    - strong: ui009_1792557134219_7170
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
Test timeout of 30000ms exceeded.
```

```
Error: Biểu mẫu đăng ký phải hiển thị để người dùng sửa thông tin.

expect(locator).toBeVisible() failed

Locator: locator('form').filter({ has: locator('input[name="ten_dang_nhap"]') })
Expected: visible
Error: element(s) not found

Call log:
  - Biểu mẫu đăng ký phải hiển thị để người dùng sửa thông tin. locator('form').filter({ has: locator('input[name="ten_dang_nhap"]') }) with timeout 10000ms
  - waiting for locator('form').filter({ has: locator('input[name="ten_dang_nhap"]') })
  - Test ended.

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
  - link "👤 Xin chào, ui009_1792557134219_7170":
    - /url: http://127.0.0.1:8000/customer/profile
    - text: 👤 Xin chào,
    - strong: ui009_1792557134219_7170
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
  978  | }, testInfo) => {
  979  |   // Phải là số điện thoại của tài khoản đã tồn tại
  980  |   // trong CSDL của ứng dụng đang chạy trên trình duyệt.
  981  |   const existingPhone = process.env.PW_EXISTING_PHONE;
  982  | 
  983  |   expect(
  984  |     existingPhone,
  985  |     'Cần đặt PW_EXISTING_PHONE bằng số điện thoại đã tồn tại trong CSDL test.'
  986  |   ).toMatch(/^[0-9]{10}$/);
  987  | 
  988  |   // Tên đăng nhập và email mới để chỉ kiểm tra trùng số điện thoại.
  989  |   const suffix = `${Date.now()}_${Math.floor(Math.random() * 100000)}`;
  990  |   const username = `ui009_${suffix}`;
  991  |   const emailAddress = `${username}@example.com`;
  992  | 
  993  |   await test.step('Mở biểu mẫu đăng ký', async () => {
  994  |     const response = await page.goto('/register');
  995  | 
  996  |     expect(response).not.toBeNull();
  997  |     expect(response.status()).toBe(200);
  998  |     await expect(page).toHaveURL(/\/register\/?$/);
  999  |   });
  1000 | 
  1001 |   const form = page.locator('form').filter({
  1002 |     has: page.locator('input[name="ten_dang_nhap"]'),
  1003 |   });
  1004 | 
  1005 |   await test.step('Nhập thông tin mới và số điện thoại đã tồn tại', async () => {
  1006 |     await expect(form).toHaveCount(1);
  1007 | 
  1008 |     await form.locator('[name="ten_dang_nhap"]').fill(username);
  1009 |     await form.locator('[name="ten_khach_hang"]')
  1010 |       .fill('Khách kiểm thử trùng số điện thoại');
  1011 | 
  1012 |     await form.locator('[name="email"]').fill(emailAddress);
  1013 |     await form.locator('[name="so_dien_thoai"]').fill(existingPhone);
  1014 | 
  1015 |     await form.locator('[name="quan_huyen"]')
  1016 |       .selectOption({ label: 'Hà Đông' });
  1017 | 
  1018 |     await form.locator('[name="xa_phuong"]')
  1019 |       .selectOption({ label: 'Yên Nghĩa' });
  1020 | 
  1021 |     await form.locator('[name="dia_chi_chi_tiet"]')
  1022 |       .fill('Số 10, đường kiểm thử');
  1023 | 
  1024 |     await form.locator('[name="mat_khau"]').fill('Test123!');
  1025 |     await form.locator('[name="mat_khau_confirmation"]').fill('Test123!');
  1026 | 
  1027 |     // Dữ liệu phải vượt qua validation trình duyệt
  1028 |     // để yêu cầu thực sự được gửi tới backend.
  1029 |     await expect(form.locator(':invalid')).toHaveCount(0);
  1030 | 
  1031 |     await testInfo.attach('Dữ liệu truy vết tài khoản thử nghiệm', {
  1032 |       body: JSON.stringify({
  1033 |         username,
  1034 |         email: emailAddress,
  1035 |         phone: existingPhone,
  1036 |         precondition: 'Số điện thoại đã tồn tại trong CSDL kiểm thử',
  1037 |       }, null, 2),
  1038 |       contentType: 'application/json',
  1039 |     });
  1040 |   });
  1041 | 
  1042 |   await test.step('Gửi đăng ký và kiểm tra phản hồi giao diện', async () => {
  1043 |     const responsePromise = page.waitForResponse((response) => {
  1044 |       const request = response.request();
  1045 | 
  1046 |       return request.method() === 'POST' &&
  1047 |         /^\/register\/?$/.test(new URL(response.url()).pathname);
  1048 |     });
  1049 | 
  1050 |     await form.getByRole('button', {
  1051 |       name: 'Đăng ký',
  1052 |       exact: true,
  1053 |     }).click();
  1054 | 
  1055 |     const response = await responsePromise;
  1056 | 
  1057 |     // Chờ tài liệu sau thao tác gửi form được tải.
  1058 |     await page.waitForLoadState('load');
  1059 | 
  1060 |     await testInfo.attach('Phản hồi yêu cầu đăng ký', {
  1061 |       body: JSON.stringify({
  1062 |         requestUrl: response.url(),
  1063 |         status: response.status(),
  1064 |       }, null, 2),
  1065 |       contentType: 'application/json',
  1066 |     });
  1067 | 
  1068 |     // Dùng soft assertion để thu thập cả lỗi điều hướng
  1069 |     // và lỗi thiếu thông báo trong cùng lần chạy.
  1070 |     await expect.soft(
  1071 |       page,
  1072 |       'Đăng ký trùng số điện thoại phải quay về trang đăng ký.'
  1073 |     ).toHaveURL(/\/register\/?$/);
  1074 | 
  1075 |     await expect.soft(
  1076 |       form,
  1077 |       'Biểu mẫu đăng ký phải hiển thị để người dùng sửa thông tin.'
> 1078 |     ).toBeVisible();
       |       ^ Error: Biểu mẫu đăng ký phải hiển thị để người dùng sửa thông tin.
  1079 | 
  1080 |     // Không cố định câu chữ khi SRS chưa quy định thông báo chính xác.
  1081 |     // Thông báo phải nêu số điện thoại đã tồn tại/được sử dụng.
  1082 |     const phoneError = page.locator('p').filter({
  1083 |       hasText: /số điện thoại.*(đã|tồn tại|trùng|sử dụng)/i,
  1084 |     });
  1085 | 
  1086 |     await expect.soft(
  1087 |       phoneError.first(),
  1088 |       'Phải hiển thị thông báo về số điện thoại đã tồn tại.'
  1089 |     ).toBeVisible();
  1090 | 
  1091 |     await expect.soft(
  1092 |       page.getByText('Đăng ký tài khoản thành công', { exact: true })
  1093 |     ).not.toBeVisible();
  1094 |   });
  1095 | 
  1096 |   await test.step('Lưu kết quả thực tế', async () => {
  1097 |     await testInfo.attach('Địa chỉ trang sau đăng ký', {
  1098 |       body: page.url(),
  1099 |       contentType: 'text/plain',
  1100 |     });
  1101 | 
  1102 |     const screenshotPath = testInfo.outputPath(
  1103 |       'UC1.1_UI_009-duplicate-phone.png'
  1104 |     );
  1105 | 
  1106 |     await page.screenshot({
  1107 |       path: screenshotPath,
  1108 |       fullPage: true,
  1109 |     });
  1110 | 
  1111 |     await testInfo.attach('Giao diện sau đăng ký trùng số điện thoại', {
  1112 |       path: screenshotPath,
  1113 |       contentType: 'image/png',
  1114 |     });
  1115 |   });
  1116 | });
```