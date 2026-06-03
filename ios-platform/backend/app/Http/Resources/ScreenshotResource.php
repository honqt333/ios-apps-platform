<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScreenshotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'url'         => $this->url,
            'device_type' => $this->device_type,
            'width'       => $this->width,
            'height'      => $this->height,
            'sort_order'  => $this->sort_order,
        ];
    }
}
