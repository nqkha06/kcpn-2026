import { categorySchema } from "@/features/categories/category-schema";
import { describe, expect, it } from "vitest";

describe("private category schema", () => {
  it("accepts a valid private category", () => {
    expect(categorySchema.safeParse({ name: "Thú cưng", color: "#0EA5E9", description: "Chi phí cho mèo" }).success).toBe(true);
  });

  it("rejects an empty name and invalid color", () => {
    expect(categorySchema.safeParse({ name: "", color: "blue", description: "" }).success).toBe(false);
  });
});
