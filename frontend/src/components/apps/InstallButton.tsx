"use client";

import { useState } from "react";
import { Download, Loader2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { toast } from "sonner";
import { appsService } from "@/services/appsService";

interface InstallButtonProps {
  appSlug: string;
  installUrl?: string;
}

export function InstallButton({ appSlug, installUrl }: InstallButtonProps) {
  const [loading, setLoading] = useState(false);

  const onClick = async () => {
    setLoading(true);
    try {
      let url = installUrl;
      if (!url) {
        const res = await appsService.trackInstall(appSlug);
        url = res.install_url;
      }
      // Trigger iOS install
      if (url) {
        window.location.href = url;
      } else {
        toast.error("Install URL is not available");
      }
    } catch (e: any) {
      toast.error(e?.response?.data?.message || "Install failed");
    } finally {
      setLoading(false);
    }
  };

  return (
    <Button
      onClick={onClick}
      disabled={loading}
      size="lg"
      variant="gradient"
      className="w-full sm:w-auto"
    >
      {loading ? (
        <Loader2 className="h-4 w-4 me-2 animate-spin" />
      ) : (
        <Download className="h-4 w-4 me-2" />
      )}
      Install
    </Button>
  );
}
