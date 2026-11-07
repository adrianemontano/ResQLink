export type PublicEnvironment = {
  apiBaseUrl: string;
  mapStyleUrl: string;
};

const apiBaseUrl = process.env.EXPO_PUBLIC_API_BASE_URL?.trim();
const mapStyleUrl = process.env.EXPO_PUBLIC_MAP_STYLE_URL?.trim();

export function hasPublicConfiguration(): boolean {
  return Boolean(apiBaseUrl && mapStyleUrl);
}

export function getPublicEnvironment(): PublicEnvironment {
  if (!apiBaseUrl || !mapStyleUrl) {
    throw new Error(
      'Missing EXPO_PUBLIC_API_BASE_URL or EXPO_PUBLIC_MAP_STYLE_URL. Copy .env.example to .env.local.',
    );
  }

  return {
    apiBaseUrl: withoutTrailingSlash(apiBaseUrl),
    mapStyleUrl,
  };
}

function withoutTrailingSlash(value: string): string {
  return value.replace(/\/+$/, '');
}
