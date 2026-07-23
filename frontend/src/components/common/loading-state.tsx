import { LoaderCircle } from "lucide-react";

export function LoadingState({ label = "Loading..." }: { label?: string }) {
  return (
    <div className="flex min-h-48 items-center justify-center gap-3 text-sm text-muted-foreground">
      <LoaderCircle className="size-5 animate-spin" aria-hidden="true" />
      <span>{label}</span>
    </div>
  );
}
