import { ApiError } from "@/lib/api/errors";
import type { QueryParams, QueryValue } from "@/types/api";

export const UNAUTHENTICATED_EVENT = "api:unauthenticated";

interface ApiRequestOptions extends Omit<RequestInit, "body"> {
  body?: unknown;
  query?: QueryParams;
}

function getApiUrl(): string {
  const apiUrl = process.env.NEXT_PUBLIC_API_URL;

  if (!apiUrl) {
    throw new Error("NEXT_PUBLIC_API_URL is not configured");
  }

  return apiUrl.replace(/\/$/, "");
}

function buildUrl(path: string, query?: QueryParams): string {
  const normalizedPath = path.startsWith("/") ? path : `/${path}`;
  const url = new URL(`${getApiUrl()}${normalizedPath}`);

  if (query) {
    Object.entries(query).forEach(([key, value]) => appendQuery(url, key, value));
  }

  return url.toString();
}

function appendQuery(url: URL, key: string, value: QueryValue | QueryValue[]): void {
  const values = Array.isArray(value) ? value : [value];

  values.forEach((item) => {
    if (item !== null && item !== undefined && item !== "") {
      url.searchParams.append(key, String(item));
    }
  });
}

function xsrfToken(): string | null {
  if (typeof document === "undefined") {
    return null;
  }

  const cookie = document.cookie
    .split(";")
    .map((entry) => entry.trim())
    .find((entry) => entry.startsWith("XSRF-TOKEN="));

  return cookie ? decodeURIComponent(cookie.slice("XSRF-TOKEN=".length)) : null;
}

async function parsePayload(response: Response): Promise<unknown> {
  if (response.status === 204) {
    return undefined;
  }

  const contentType = response.headers.get("content-type") ?? "";

  if (contentType.includes("application/json")) {
    return response.json();
  }

  const text = await response.text();
  return text === "" ? undefined : { message: text };
}

async function request<T>(
  path: string,
  options: ApiRequestOptions = {},
  hasRetriedCsrf = false,
): Promise<T> {
  const { body, query, headers: incomingHeaders, ...requestInit } = options;
  const headers = new Headers(incomingHeaders);
  const isFormData = typeof FormData !== "undefined" && body instanceof FormData;
  const method = (requestInit.method ?? "GET").toUpperCase();

  headers.set("Accept", "application/json");

  if (body !== undefined && !isFormData) {
    headers.set("Content-Type", "application/json");
  }

  if (!["GET", "HEAD", "OPTIONS"].includes(method)) {
    const token = xsrfToken();

    if (token) {
      headers.set("X-XSRF-TOKEN", token);
    }
  }

  const response = await fetch(buildUrl(path, query), {
    ...requestInit,
    method,
    credentials: "include",
    headers,
    body: body === undefined ? undefined : isFormData ? body : JSON.stringify(body),
  });
  const payload = await parsePayload(response);

  if (!response.ok) {
    if (response.status === 419 && !hasRetriedCsrf && !["GET", "HEAD", "OPTIONS"].includes(method)) {
      await request<void>("/sanctum/csrf-cookie", { method: "GET" }, true);

      return request<T>(path, options, true);
    }

    const error = ApiError.fromResponse(response.status, payload);

    if (response.status === 401 && typeof window !== "undefined") {
      window.dispatchEvent(new CustomEvent(UNAUTHENTICATED_EVENT));
    }

    throw error;
  }

  return payload as T;
}

export const apiClient = {
  get: <T>(path: string, options: ApiRequestOptions = {}) =>
    request<T>(path, { ...options, method: "GET" }),
  post: <T>(path: string, body?: unknown, options: ApiRequestOptions = {}) =>
    request<T>(path, { ...options, method: "POST", body }),
  put: <T>(path: string, body?: unknown, options: ApiRequestOptions = {}) =>
    request<T>(path, { ...options, method: "PUT", body }),
  patch: <T>(path: string, body?: unknown, options: ApiRequestOptions = {}) =>
    request<T>(path, { ...options, method: "PATCH", body }),
  delete: <T>(path: string, options: ApiRequestOptions = {}) =>
    request<T>(path, { ...options, method: "DELETE" }),
};
