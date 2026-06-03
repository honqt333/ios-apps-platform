<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'version'         => $this->version,
            'build_number'    => $this->build_number,
            'disk'            => $this->disk,
            'size_bytes'      => $this->size_bytes,
            'size_human'      => $this->size_human,
            'checksum_sha256' => $this->checksum_sha256,
            'is_current'      => $this->is_current,
            'url'             => $this->url,
            'manifest_url'    => $this->manifest_url,
            'metadata'        => $this->metadata,
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
