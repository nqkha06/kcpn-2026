import type { ApiSuccess } from "@/types/api";
import type { PublicPage } from "@/types/site";

function apiUrl(path: string): string {
  const baseUrl = process.env.NEXT_PUBLIC_API_URL;

  if (!baseUrl) {
    throw new Error("NEXT_PUBLIC_API_URL is not configured");
  }

  return `${baseUrl.replace(/\/$/, "")}/${path.replace(/^\//, "")}`;
}

export async function getPublicPage(slug: string): Promise<PublicPage | null> {
  const response = await fetch(apiUrl(`/public/pages/${encodeURIComponent(slug)}`), {
    headers: { Accept: "application/json" },
    next: { revalidate: 60 },
  });

  if (response.status === 404) {
    return null;
  }

  if (!response.ok) {
    throw new Error(`Unable to load public page (${response.status})`);
  }

  const payload = (await response.json()) as ApiSuccess<PublicPage>;

  return payload.data;
}
