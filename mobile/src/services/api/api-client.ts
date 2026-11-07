import { create } from 'axios';

import { getPublicEnvironment } from '@/config/environment';

const { apiBaseUrl } = getPublicEnvironment();

export const apiClient = create({
  baseURL: apiBaseUrl,
  headers: {
    Accept: 'application/json',
  },
  timeout: 15_000,
});
