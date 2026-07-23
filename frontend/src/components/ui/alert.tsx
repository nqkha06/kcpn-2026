import { cn } from "@/lib/utils";
import type { HTMLAttributes } from "react";

export function Alert({ className, ...props }: HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      role="alert"
      className={cn("rounded-lg border border-destructive/30 bg-destructive/10 p-3 text-sm text-destructive", className)}
      {...props}
    />
  );
}
