import { test, expect } from '@playwright/test';

test('kiểm tra trang chủ Laravel', async ({ page }) => {
    // Trỏ vào server Laravel đang chạy
    await page.goto('http://127.0.0.1:8000');

    // Kiểm tra xem tiêu đề trang có chứa tên dự án của bạn không
    // (Bạn có thể mở trình duyệt lên xem tiêu đề thực tế là gì để sửa cho đúng)
    await expect(page).toHaveTitle(/Cửa Hàng Hoa Tươi/i);
});