<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTemplateRequest;
use App\Http\Requests\UpdateTemplateRequest;
use App\Http\Resources\ReportTemplateResource;
use App\Models\ReportTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $templates = ReportTemplate::accessibleBy($request->user()->id)
            ->with('user:id,name,avatar')
            ->latest()
            ->get();

        return ReportTemplateResource::collection($templates);
    }

    public function store(StoreTemplateRequest $request): ReportTemplateResource
    {
        $template = $request->user()->reportTemplates()->create($request->validated());

        return new ReportTemplateResource($template);
    }

    public function show(ReportTemplate $template): ReportTemplateResource
    {
        $this->authorize('view', $template);

        return new ReportTemplateResource($template->load('user:id,name,avatar'));
    }

    public function update(UpdateTemplateRequest $request, ReportTemplate $template): ReportTemplateResource
    {
        $this->authorize('update', $template);

        $template->update($request->validated());

        return new ReportTemplateResource($template);
    }

    public function destroy(ReportTemplate $template): JsonResponse
    {
        $this->authorize('delete', $template);

        $template->delete();

        return response()->json(null, 204);
    }

    public function duplicate(Request $request, ReportTemplate $template): ReportTemplateResource
    {
        $this->authorize('view', $template);

        $newTemplate = $request->user()->reportTemplates()->create([
            'name' => $template->name . ' (Copy)',
            'description' => $template->description,
            'is_public' => false,
            'config' => $template->config,
        ]);

        return new ReportTemplateResource($newTemplate);
    }
}
