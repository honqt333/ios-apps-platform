"use client";

import { Toaster as Sonner } from "sonner";

export function Toaster() {
  return (
    <Sonner
      position="top-right"
      toastOptions={{
        classNames: {
          toast: "group toast bg-card text-foreground border-border shadow-lg",
          description: "text-muted-foreground",
        },
      }}
    />
  );
}
