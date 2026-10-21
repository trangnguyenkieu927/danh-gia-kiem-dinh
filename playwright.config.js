// @ts-check
import { defineConfig, devices } from '@playwright/test';

/**
 * Master Playwright Configuration for RoseShop E2E Test Suite (Nhom 07)
 * Ho tro chay ca 3 Use Case:
 * - UC1.1: Dang ky (Nguyen Duc Trong)
 * - UC1.2: Dang nhap (Nguyen Kieu Trang)
 * - UC4.2: Giao hang Shipper (Duong Hung Manh)
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
      name: 'uc1.1-register',
      testMatch: 'UC1_1_Register.spec.js',
      use: { ...devices['Desktop Chrome'], channel: 'chrome' },
    },
    {
      name: 'uc1.2-login',
      testMatch: 'uc1_2_login.spec.js',
      use: { ...devices['Desktop Chrome'], channel: 'chrome' },
    },
    {
      name: 'uc4.2-shipper',
      testMatch: 'uc4_2_shipper.spec.js',
      use: { ...devices['Desktop Chrome'], channel: 'chrome' },
    },
  ],
});
