<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GscPropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'site_url' => $this->site_url,
            'site_type' => $this->site_type,
            'display_name' => $this->display_name_or_url,
            'permission_level' => $this->permission_level,
            'is_active' => $this->is_active,
            'last_synced_at' => $this->last_synced_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
