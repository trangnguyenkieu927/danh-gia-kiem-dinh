import { defineConfig, devices } from '@playwright/test';

const runId = process.env.PW_RUN_ID || 'register-run-01';
const evidenceDir = `tests/Evidence/UC1.1/Playwright/${runId}`;

export default defineConfig({
  testDir: './e2e',
  testMatch: 'UC1_1_Register.spec.js',

  fullyParallel: false,
  workers: 1,
  retries: 0,
  forbidOnly: !!process.env.CI,

  timeout: 30_000,
  expect: {
    timeout: 10_000,
  },

  outputDir: `${evidenceDir}/artifacts`,

  reporter: [
    ['list'],
    ['html', {
      outputFolder: `${evidenceDir}/html-report`,
      open: 'never',
    }],
    ['junit', {
      outputFile: `${evidenceDir}/junit.xml`,
    }],
  ],

  use: {
    baseURL: process.env.PW_BASE_URL || 'http://127.0.0.1:8000',

    storageState: {
      cookies: [],
      origins: [],
    },

    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'off',
  },

  projects: [
    {
      name: 'chrome',
      use: {
        ...devices['Desktop Chrome'],
        channel: 'chrome',
      },
    },
  ],
});