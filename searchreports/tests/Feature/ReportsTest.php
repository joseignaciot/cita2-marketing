<?php

use App\Jobs\GenerateReportJob;
use App\Models\GscProperty;
use App\Models\Report;
use App\Models\ReportShare;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->property = GscProperty::factory()->create(['user_id' => $this->user->id]);
    $this->actingAs($this->user);
    Storage::fake('local');
    Queue::fake();
});

it('creates a report and dispatches generation job', function () {
    $this->postJson(route('api.reports.store'), [
        'name' => 'Test Report',
        'property_id' => $this->property->id,
        'date_from' => '2026-04-01',
        'date_to' => '2026-04-30',
        'output_format' => 'pdf',
    ])->assertCreated();

    Queue::assertPushed(GenerateReportJob::class);
    expect(Report::where('user_id', $this->user->id)->count())->toBe(1);
});

it('shows report status', function () {
    $report = Report::factory()->create(['user_id' => $this->user->id, 'property_id' => $this->property->id]);

    $this->getJson(route('api.reports.show', $report->id))
        ->assertOk()
        ->assertJsonPath('data.status', 'ready');
});

it('cannot access another users report', function () {
    $other = User::factory()->create();
    $otherProp = GscProperty::factory()->create(['user_id' => $other->id]);
    $report = Report::factory()->create(['user_id' => $other->id, 'property_id' => $otherProp->id]);

    $this->getJson(route('api.reports.show', $report->id))
        ->assertForbidden();
});

it('deletes own report', function () {
    $report = Report::factory()->create(['user_id' => $this->user->id, 'property_id' => $this->property->id]);

    $this->deleteJson(route('api.reports.destroy', $report->id))
        ->assertNoContent();

    expect(Report::find($report->id))->toBeNull();
});

it('generates a share token', function () {
    $report = Report::factory()->create(['user_id' => $this->user->id, 'property_id' => $this->property->id]);

    $this->postJson(route('api.reports.share', $report->id))
        ->assertOk()
        ->assertJsonStructure(['share_url', 'token', 'expires_at']);

    expect(ReportShare::where('report_id', $report->id)->count())->toBe(1);
});

it('allows access to shared report via token', function () {
    $report = Report::factory()->create(['user_id' => $this->user->id, 'property_id' => $this->property->id]);
    $share = ReportShare::factory()->create(['report_id' => $report->id]);

    $this->get(route('reports.shared', $share->share_token))
        ->assertOk();
});
