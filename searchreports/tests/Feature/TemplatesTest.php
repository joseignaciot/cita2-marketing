<?php

use App\Models\ReportTemplate;
use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

function validTemplatePayload(): array
{
    return [
        'name' => 'Test Template',
        'description' => 'A test template',
        'is_public' => false,
        'config' => [
            'layout' => 'grid',
            'widgets' => [
                [
                    'id' => Str::uuid(),
                    'type' => 'metrics_summary',
                    'title' => 'Summary',
                    'position' => ['col' => 1, 'row' => 1, 'w' => 12, 'h' => 2],
                    'config' => ['metrics' => ['clicks']],
                ],
            ],
        ],
    ];
}

it('creates a template', function () {
    $this->postJson(route('api.templates.store'), validTemplatePayload())
        ->assertCreated();

    expect(ReportTemplate::where('user_id', $this->user->id)->count())->toBe(1);
});

it('shows own template', function () {
    $template = ReportTemplate::factory()->create(['user_id' => $this->user->id]);

    $this->getJson(route('api.templates.show', $template->id))
        ->assertOk()
        ->assertJsonPath('data.id', $template->id);
});

it('shows public template from other user', function () {
    $other = User::factory()->create();
    $template = ReportTemplate::factory()->public()->create(['user_id' => $other->id]);

    $this->getJson(route('api.templates.show', $template->id))
        ->assertOk();
});

it('cannot see private template from other user', function () {
    $other = User::factory()->create();
    $template = ReportTemplate::factory()->create(['user_id' => $other->id, 'is_public' => false]);

    $this->getJson(route('api.templates.show', $template->id))
        ->assertForbidden();
});

it('updates own template', function () {
    $template = ReportTemplate::factory()->create(['user_id' => $this->user->id]);

    $this->putJson(route('api.templates.update', $template->id), ['name' => 'Updated Name'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Name');
});

it('cannot update other users template', function () {
    $other = User::factory()->create();
    $template = ReportTemplate::factory()->public()->create(['user_id' => $other->id]);

    $this->putJson(route('api.templates.update', $template->id), ['name' => 'Hacked'])
        ->assertForbidden();
});

it('deletes own template', function () {
    $template = ReportTemplate::factory()->create(['user_id' => $this->user->id]);

    $this->deleteJson(route('api.templates.destroy', $template->id))
        ->assertNoContent();

    expect(ReportTemplate::find($template->id))->toBeNull();
});

it('duplicates a template', function () {
    $template = ReportTemplate::factory()->create(['user_id' => $this->user->id]);

    $this->postJson(route('api.templates.duplicate', $template->id))
        ->assertCreated();

    expect(ReportTemplate::where('user_id', $this->user->id)->count())->toBe(2);
});
