// @ts-check
import { defineConfig, devices } from '@playwright/test';

/**
 * Master Playwright Configuration for RoseShop E2E Test Suite (Nhom 07)
 * Tich hop Test Explorer cho ca 3 Use Case:
 * - UC1.1: Dang ky tai khoan
 * - UC1.2: Dang nhap & Phan quyen
 * - UC4.2: Quan ly giao hang Shipper
 */
export default defineConfig({
  testDir: './e2e',
  fullyParallel: false,
  workers: 1,
  retries: 0,
  timeout: 30_000,
  expect: { timeout: 10_000 },

  reporter: [
    ['list'],
    ['html', { outputFolder: 'tests/Evidence/Playwright-Report-Master', open: 'never' }],
    ['junit', { outputFile: 'tests/Evidence/Playwright-Master-junit.xml' }],
  ],

  use: {
    baseURL: process.env.PW_BASE_URL || 'http://127.0.0.1:8000',
    trace: 'retain-on-failure',
    screenshot: 'on',
    video: 'off',
  },

  projects: [
    {
      name: 'UC1.1 - Đăng ký tài khoản',
      testMatch: 'UC1_1_Register.spec.js',
      use: { ...devices['Desktop Chrome'], channel: 'chrome' },
    },
    {
      name: 'UC1.2 - Đăng nhập & Phân quyền',
      testMatch: 'uc1_2_login.spec.js',
      use: { ...devices['Desktop Chrome'], channel: 'chrome' },
    },
    {
      name: 'UC4.2 - Quản lý giao hàng Shipper',
      testMatch: 'uc4_2_shipper.spec.js',
      use: { ...devices['Desktop Chrome'], channel: 'chrome' },
    },
  ],
});
