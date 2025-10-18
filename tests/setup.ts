import { vi } from 'vitest';
import { config } from '@vue/test-utils';

// Mock Inertia
vi.mock('@inertiajs/vue3', () => ({
  Link: {
    name: 'Link',
    template: '<a><slot /></a>',
    props: ['href']
  },
  router: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
  },
  usePage: vi.fn(() => ({
    props: {}
  }))
}));

// Mock Chart.js
vi.mock('chart.js', () => ({
  Chart: class MockChart {
    constructor() {}
    destroy() {}
    update() {}
    static register() {}
  },
  registerables: [],
}));

// Configure Vue Test Utils global properties
config.global.mocks = {
  route: vi.fn((name: string, params?: any) => `/${name}${params ? `/${params}` : ''}`)
};

// Also set it globally for direct access
global.route = vi.fn((name: string, params?: any) => `/${name}${params ? `/${params}` : ''}`);