
// @ts-check
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  // Thư mục chứa các bài kiểm thử E2E / UI của nhóm
  testDir: './tests/Playwright_UI/e2e',

  // Hạn chế test tác động dữ liệu chạy đồng thời
  fullyParallel: false,

  // Không cho phép test.only xuất hiện trên CI
  forbidOnly: !!process.env.CI,

  // Thử lại khi chạy trên GitHub Actions
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,

  // Báo cáo kết quả kiểm thử
  reporter: [
    ['list'],
    ['html', {
      open: 'never',
      outputFolder: 'playwright-report'
    }],
    ['junit', {
      outputFile: 'test-results/junit.xml'
    }]
  ],

  // Cấu hình chung cho tất cả bài kiểm thử
  use: {
    baseURL: process.env.BASE_URL || 'http://127.0.0.1:8000',

    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
    video: 'retain-on-failure',

    actionTimeout: 10000
  },

  // Trình duyệt kiểm thử
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] }
    },
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] }
    },
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] }
    }
  ]

  // Laravel cần được chạy trước khi thực thi test.
  // Có thể cấu hình webServer riêng sau khi xác minh
  // cách khởi động ứng dụng của nhóm.
});
