import { z } from "zod";

const userFields = {
  name: z.string().trim().min(1, "The name field is required.").max(255),
  email: z.string().trim().min(1, "The email field is required.").email("Enter a valid email address."),
  password: z.string(),
  password_confirmation: z.string(),
  roles: z.array(z.number().int()),
};

export const createUserSchema = z
  .object(userFields)
  .superRefine((value, context) => {
    if (!value.password) {
      context.addIssue({ code: "custom", path: ["password"], message: "The password field is required." });
    }
    if (value.password !== value.password_confirmation) {
      context.addIssue({
        code: "custom",
        path: ["password_confirmation"],
        message: "The password confirmation does not match.",
      });
    }
  });

export const updateUserSchema = z
  .object(userFields)
  .superRefine((value, context) => {
    if (value.password !== value.password_confirmation) {
      context.addIssue({
        code: "custom",
        path: ["password_confirmation"],
        message: "The password confirmation does not match.",
      });
    }
  });

export const roleSchema = z.object({
  name: z.string().trim().min(1, "The role name field is required.").max(255),
  permissions: z.array(z.number().int()),
});

export const permissionSchema = z.object({
  name: z.string().trim().min(1, "The permission name field is required.").max(255),
});

export type UserFormValues = z.infer<typeof createUserSchema>;
export type RoleFormValues = z.infer<typeof roleSchema>;
export type PermissionFormValues = z.infer<typeof permissionSchema>;
