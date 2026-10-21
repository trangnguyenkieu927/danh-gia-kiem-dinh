# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: UC1_1_Register.spec.js >> UC1.1_UI_010 - Giu thong tin hop le sau loi dang ky
- Location: e2e\UC1_1_Register.spec.js:1117:1

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: locator('p').filter({ hasText: 'Tên đăng nhập đã tồn tại.' }).first()
Expected: visible
Timeout: 10000ms
Error: element(s) not found

Call log:
  - Expect "toBeVisible" locator('p').filter({ hasText: 'Tên đăng nhập đã tồn tại.' }).first() with timeout 10000ms
  - waiting for locator('p').filter({ hasText: 'Tên đăng nhập đã tồn tại.' }).first()

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
  - link "👤 Xin chào, TEN_DANG_NHAP_DA_TON_TAI":
    - /url: http://127.0.0.1:8000/customer/profile
    - text: 👤 Xin chào,
    - strong: TEN_DANG_NHAP_DA_TON_TAI
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
  1117 | test('UC1.1_UI_010 - Giu thong tin hop le sau loi dang ky', async ({
  1118 |   page,
  1119 | }, testInfo) => {
  1120 |   test.setTimeout(90_000);
  1121 | 
  1122 |   const existingUsername = process.env.PW_EXISTING_USERNAME;
  1123 |   const newPhone = process.env.PW_NEW_PHONE;
  1124 | 
  1125 |   expect(
  1126 |     existingUsername,
  1127 |     'Đặt PW_EXISTING_USERNAME bằng tên đăng nhập đã tồn tại.'
  1128 |   ).toBeTruthy();
  1129 | 
  1130 |   expect(
  1131 |     newPhone,
  1132 |     'Đặt PW_NEW_PHONE bằng số điện thoại thử nghiệm mới gồm 10 chữ số.'
  1133 |   ).toMatch(/^[0-9]{10}$/);
  1134 | 
  1135 |   const suffix = `${Date.now()}_${Math.floor(Math.random() * 100000)}`;
  1136 | 
  1137 |   const data = {
  1138 |     ten_dang_nhap: existingUsername,
  1139 |     ten_khach_hang: 'Nguyễn Văn Kiểm Thử',
  1140 |     email: `ui010_${suffix}@example.com`,
  1141 |     so_dien_thoai: newPhone,
  1142 |     quan_huyen: 'Hà Đông',
  1143 |     xa_phuong: 'Yên Nghĩa',
  1144 |     dia_chi_chi_tiet: 'Số 10, đường kiểm thử',
  1145 |   };
  1146 | 
  1147 |   await test.step('Mở biểu mẫu đăng ký', async () => {
  1148 |     const response = await page.goto('/register');
  1149 | 
  1150 |     expect(response).not.toBeNull();
  1151 |     expect(response.status()).toBe(200);
  1152 | 
  1153 |     await expect(page).toHaveURL(/\/register\/?$/);
  1154 |   });
  1155 | 
  1156 |   const form = page.locator('form').filter({
  1157 |     has: page.locator('input[name="ten_dang_nhap"]'),
  1158 |   });
  1159 | 
  1160 |   await test.step('Nhập tên đăng nhập trùng và các thông tin hợp lệ', async () => {
  1161 |     await expect(form).toHaveCount(1);
  1162 | 
  1163 |     for (const name of [
  1164 |       'ten_dang_nhap',
  1165 |       'ten_khach_hang',
  1166 |       'email',
  1167 |       'so_dien_thoai',
  1168 |       'dia_chi_chi_tiet',
  1169 |     ]) {
  1170 |       await form.locator(`[name="${name}"]`).fill(data[name]);
  1171 |     }
  1172 | 
  1173 |     await form.locator('[name="quan_huyen"]')
  1174 |       .selectOption({ label: data.quan_huyen });
  1175 | 
  1176 |     await form.locator('[name="xa_phuong"]')
  1177 |       .selectOption({ label: data.xa_phuong });
  1178 | 
  1179 |     await form.locator('[name="mat_khau"]').fill('Test123!');
  1180 |     await form.locator('[name="mat_khau_confirmation"]').fill('Test123!');
  1181 | 
  1182 |     // Chứng minh biểu mẫu không bị validation phía trình duyệt chặn.
  1183 |     await expect(form.locator(':invalid')).toHaveCount(0);
  1184 | 
  1185 |     await testInfo.attach('Dữ liệu trước khi gửi', {
  1186 |       body: JSON.stringify(data, null, 2),
  1187 |       contentType: 'application/json',
  1188 |     });
  1189 |   });
  1190 | 
  1191 |   await test.step('Gửi biểu mẫu và nhận lỗi từ backend', async () => {
  1192 |     const responsePromise = page.waitForResponse((response) => {
  1193 |       return response.request().method() === 'POST' &&
  1194 |         /^\/register\/?$/.test(new URL(response.url()).pathname);
  1195 |     });
  1196 | 
  1197 |     await form.getByRole('button', {
  1198 |       name: 'Đăng ký',
  1199 |       exact: true,
  1200 |     }).click();
  1201 | 
  1202 |     const response = await responsePromise;
  1203 | 
  1204 |     await testInfo.attach('Phản hồi POST đăng ký', {
  1205 |       body: JSON.stringify({
  1206 |         url: response.url(),
  1207 |         status: response.status(),
  1208 |       }, null, 2),
  1209 |       contentType: 'application/json',
  1210 |     });
  1211 | 
  1212 |     // Kiểm tra thông báo để xác nhận đã nhận trang phản hồi backend.
  1213 |     const usernameError = page.locator('p').filter({
  1214 |       hasText: 'Tên đăng nhập đã tồn tại.',
  1215 |     });
  1216 | 
> 1217 |     await expect(usernameError.first()).toBeVisible();
       |                                         ^ Error: expect(locator).toBeVisible() failed
  1218 |     await expect(page).toHaveURL(/\/register\/?$/);
  1219 |     await expect(form).toBeVisible();
  1220 | 
  1221 |     // Lưu ảnh trước các assertion đối chiếu dữ liệu.
  1222 |     const screenshotPath = testInfo.outputPath(
  1223 |       'UC1.1_UI_010-retained-inputs.png'
  1224 |     );
  1225 | 
  1226 |     await page.screenshot({
  1227 |       path: screenshotPath,
  1228 |       fullPage: true,
  1229 |     });
  1230 | 
  1231 |     await testInfo.attach('Biểu mẫu sau lỗi trùng tên đăng nhập', {
  1232 |       path: screenshotPath,
  1233 |       contentType: 'image/png',
  1234 |     });
  1235 |   });
  1236 | 
  1237 |   await test.step('Kiểm tra thông tin đã nhập được giữ lại', async () => {
  1238 |     for (const [name, value] of Object.entries(data)) {
  1239 |       await expect.soft(
  1240 |         form.locator(`[name="${name}"]`),
  1241 |         `Trường ${name} phải giữ nguyên dữ liệu đã nhập.`
  1242 |       ).toHaveValue(value, { timeout: 3_000 });
  1243 |     }
  1244 | 
  1245 |     await expect.soft(
  1246 |       form.locator('input[readonly]'),
  1247 |       'Tỉnh/Thành phố vẫn là Hà Nội.'
  1248 |     ).toHaveValue('Hà Nội', { timeout: 3_000 });
  1249 | 
  1250 |     await expect.soft(
  1251 |       page.getByText('Đăng ký tài khoản thành công', {
  1252 |         exact: true,
  1253 |       })
  1254 |     ).not.toBeVisible({ timeout: 3_000 });
  1255 | 
  1256 |     const actualValues = {};
  1257 | 
  1258 |     for (const name of Object.keys(data)) {
  1259 |       actualValues[name] = await form
  1260 |         .locator(`[name="${name}"]`)
  1261 |         .inputValue();
  1262 |     }
  1263 | 
  1264 |     await testInfo.attach('Dữ liệu thực tế sau lỗi đăng ký', {
  1265 |       body: JSON.stringify(actualValues, null, 2),
  1266 |       contentType: 'application/json',
  1267 |     });
  1268 |   });
  1269 | });
```