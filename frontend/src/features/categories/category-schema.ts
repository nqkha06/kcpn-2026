import { z } from "zod";

export const categorySchema = z.object({
  name: z.string().trim().min(1, "Vui lòng nhập tên danh mục.").max(120),
  color: z
    .string()
    .regex(/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/, "Vui lòng chọn mã màu hợp lệ."),
  description: z.string().trim().max(500, "Mô tả không được vượt quá 500 ký tự."),
});

export type CategoryFormValues = z.infer<typeof categorySchema>;
