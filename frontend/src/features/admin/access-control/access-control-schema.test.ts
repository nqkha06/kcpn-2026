import {
  createUserSchema,
  permissionSchema,
  roleSchema,
  updateUserSchema,
} from "@/features/admin/access-control/access-control-schema";
import { describe, expect, it } from "vitest";

describe("admin access control schemas", () => {
  it("requires a matching password when creating users", () => {
    const result = createUserSchema.safeParse({
      name: "Admin",
      email: "admin@example.com",
      password: "secret",
      password_confirmation: "different",
      roles: [1],
    });

    expect(result.success).toBe(false);
  });

  it("allows an empty password when updating users", () => {
    const result = updateUserSchema.safeParse({
      name: "Admin",
      email: "admin@example.com",
      password: "",
      password_confirmation: "",
      roles: [1],
    });

    expect(result.success).toBe(true);
  });

  it("validates role and permission names", () => {
    expect(roleSchema.safeParse({ name: "", permissions: [] }).success).toBe(false);
    expect(permissionSchema.safeParse({ name: "manage users" }).success).toBe(true);
  });
});
