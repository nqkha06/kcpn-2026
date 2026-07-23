import { cn } from "@/lib/utils";
import Link from "next/link";
import type { ComponentProps } from "react";

export function TextLink({ className = "", children, ...props }: ComponentProps<typeof Link>) {
  return (
    <Link
      className={cn(
        "text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current dark:decoration-neutral-500",
        className,
      )}
      {...props}
    >
      {children}
    </Link>
  );
}
