import type { ApiErrors, ApiFailure } from "@/types/api";

export class ApiError extends Error {
  constructor(
    message: string,
    public readonly status: number,
    public readonly errors: ApiErrors = {},
  ) {
    super(message);
    this.name = "ApiError";
  }

  static fromResponse(status: number, payload: unknown): ApiError {
    const failure = isApiFailure(payload) ? payload : null;
    const message = failure?.message ?? defaultMessage(status);
    const errors = failure?.errors ?? {};

    if (status === 401) {
      return new UnauthenticatedError(message, errors);
    }

    if (status === 403) {
      return new ForbiddenError(message, errors);
    }

    if (status === 422) {
      return new ValidationError(message, errors);
    }

    if (status >= 500) {
      return new ServerError(message, status, errors);
    }

    return new ApiError(message, status, errors);
  }

  firstError(field: string): string | undefined {
    return this.errors[field]?.[0];
  }
}

export class UnauthenticatedError extends ApiError {
  constructor(message = "Unauthenticated", errors: ApiErrors = {}) {
    super(message, 401, errors);
    this.name = "UnauthenticatedError";
  }
}

export class ForbiddenError extends ApiError {
  constructor(message = "Forbidden", errors: ApiErrors = {}) {
    super(message, 403, errors);
    this.name = "ForbiddenError";
  }
}

export class ValidationError extends ApiError {
  constructor(message = "Validation failed", errors: ApiErrors = {}) {
    super(message, 422, errors);
    this.name = "ValidationError";
  }
}

export class ServerError extends ApiError {
  constructor(message = "Server error", status = 500, errors: ApiErrors = {}) {
    super(message, status, errors);
    this.name = "ServerError";
  }
}

function isApiFailure(payload: unknown): payload is ApiFailure {
  if (typeof payload !== "object" || payload === null) {
    return false;
  }

  return "success" in payload && payload.success === false && "message" in payload;
}

function defaultMessage(status: number): string {
  if (status === 401) return "Unauthenticated";
  if (status === 403) return "Forbidden";
  if (status === 404) return "Resource not found";
  if (status === 419) return "CSRF token mismatch";
  if (status === 422) return "Validation failed";
  if (status >= 500) return "Server error";

  return "Request failed";
}
