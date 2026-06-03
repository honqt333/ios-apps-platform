<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'slug'                  => $this->slug,
            'developer'             => $this->developer,
            'description'           => $this->description,
            'long_description'      => $this->long_description,
            'bundle_id'             => $this->bundle_id,
            'version'               => $this->version,
            'build_number'          => $this->build_number,
            'minimum_ios_version'   => $this->minimum_ios_version,
            'file_size_bytes'       => $this->file_size_bytes,
            'file_size_human'       => $this->file_size_human,
            'icon_url'              => $this->icon_url,
            'icon_path'             => $this->icon_path,
            'ipa_size_bytes'        => $this->ipa_size_bytes,
            'downloads_count'       => $this->downloads_count,
            'is_active'             => $this->is_active,
            'is_archived'           => $this->is_archived,
            'is_featured'           => $this->is_featured,
            'is_installable'        => $this->isInstallable(),
            'install_url'           => $this->install_url,
            'changelog'             => $this->changelog,
            'changelog_history'     => $this->changelog_history,
            'localized'             => $this->localized,
            'category'              => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
            'screenshots'           => $this->whenLoaded('screenshots', fn () => ScreenshotResource::collection($this->screenshots)),
            'files'                 => $this->whenLoaded('files', fn () => AppFileResource::collection($this->files)),
            'created_at'            => $this->created_at?->toIso8601String(),
            'updated_at'            => $this->updated_at?->toIso8601String(),
        ];
    }
}
