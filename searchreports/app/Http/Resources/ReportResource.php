<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'output_format' => $this->output_format,
            'date_from' => $this->date_from?->toDateString(),
            'date_to' => $this->date_to?->toDateString(),
            'generated_at' => $this->generated_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'property' => $this->whenLoaded('property', fn () => [
                'id' => $this->property->id,
                'display_name' => $this->property->display_name_or_url,
                'site_url' => $this->property->site_url,
            ]),
            'template' => $this->whenLoaded('template', fn () => $this->template ? [
                'id' => $this->template->id,
                'name' => $this->template->name,
            ] : null),
            'share' => $this->whenLoaded('share', fn () => $this->share ? [
                'share_url' => route('reports.shared', $this->share->share_token),
                'expires_at' => $this->share->expires_at?->toISOString(),
                'view_count' => $this->share->view_count,
            ] : null),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
