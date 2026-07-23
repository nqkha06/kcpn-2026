import { Plus } from "lucide-react";
import Link from "next/link";

export function AdminPageHeader({
  title,
  description,
  createHref,
  createLabel,
}: {
  title: string;
  description: string;
  createHref?: string;
  createLabel?: string;
}) {
  return (
    <div className="flex flex-wrap items-end justify-between gap-2">
      <div>
        <h2 className="text-2xl font-bold tracking-tight">{title}</h2>
        <p className="text-muted-foreground">{description}</p>
      </div>
      {createHref && createLabel ? (
        <Link
          href={createHref}
          className="inline-flex h-9 items-center gap-2 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow-xs hover:bg-primary/90"
        >
          <Plus className="size-4" />
          {createLabel}
        </Link>
      ) : null}
    </div>
  );
}
