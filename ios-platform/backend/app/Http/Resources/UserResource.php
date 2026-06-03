<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'email'           => $this->email,
            'username'        => $this->username,
            'phone'           => $this->phone,
            'avatar'          => $this->avatarUrl(),
            'locale'          => $this->locale,
            'is_active'       => $this->is_active,
            'roles'           => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'permissions'     => $this->when($request->user()?->isAdmin(), fn () => $this->getAllPermissions()->pluck('name')),
            'last_login_at'   => $this->last_login_at?->toIso8601String(),
            'last_login_ip'   => $this->last_login_ip,
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
