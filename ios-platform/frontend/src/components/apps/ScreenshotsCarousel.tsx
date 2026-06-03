"use client";

import { useState } from "react";
import Image from "next/image";
import { ChevronLeft, ChevronRight, Smartphone } from "lucide-react";
import { cn } from "@/lib/utils";
import type { Screenshot } from "@/types";

interface ScreenshotsCarouselProps {
  screenshots: Screenshot[];
}

export function ScreenshotsCarousel({ screenshots }: ScreenshotsCarouselProps) {
  const [active, setActive] = useState(0);

  if (!screenshots || screenshots.length === 0) {
    return (
      <div className="aspect-[9/16] max-w-xs mx-auto rounded-3xl bg-gradient-to-br from-primary/30 to-primary/10 flex items-center justify-center">
        <Smartphone className="h-20 w-20 text-primary/40" />
      </div>
    );
  }

  const next = () => setActive((a) => (a + 1) % screenshots.length);
  const prev = () => setActive((a) => (a - 1 + screenshots.length) % screenshots.length);

  return (
    <div className="space-y-3">
      <div className="relative aspect-[9/16] max-w-xs mx-auto rounded-3xl overflow-hidden bg-muted shadow-2xl">
        {screenshots.map((s, i) => (
          <Image
            key={s.id}
            src={s.url}
            alt={`Screenshot ${i + 1}`}
            fill
            className={cn(
              "object-cover transition-opacity duration-500",
              i === active ? "opacity-100" : "opacity-0"
            )}
          />
        ))}

        {screenshots.length > 1 && (
          <>
            <button
              onClick={prev}
              className="absolute top-1/2 -translate-y-1/2 start-2 h-9 w-9 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/70"
            >
              <ChevronLeft className="h-5 w-5" />
            </button>
            <button
              onClick={next}
              className="absolute top-1/2 -translate-y-1/2 end-2 h-9 w-9 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/70"
            >
              <ChevronRight className="h-5 w-5" />
            </button>
          </>
        )}
      </div>

      {screenshots.length > 1 && (
        <div className="flex justify-center gap-2">
          {screenshots.map((_, i) => (
            <button
              key={i}
              onClick={() => setActive(i)}
              className={cn(
                "h-1.5 rounded-full transition-all",
                i === active ? "w-8 bg-primary" : "w-1.5 bg-muted-foreground/30"
              )}
            />
          ))}
        </div>
      )}
    </div>
  );
}
