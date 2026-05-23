<?php

use App\Jobs\SyncGscPropertiesJob;
use App\Models\GscProperty;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('returns user properties', function () {
    GscProperty::factory()->count(3)->create(['user_id' => $this->user->id]);

    $this->getJson(route('api.properties.index'))
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('does not return other users properties', function () {
    $otherUser = User::factory()->create();
    GscProperty::factory()->create(['user_id' => $otherUser->id]);

    $this->getJson(route('api.properties.index'))
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('dispatches sync job', function () {
    Queue::fake();

    $this->postJson(route('api.properties.sync'))
        ->assertOk();

    Queue::assertPushed(SyncGscPropertiesJob::class);
});

it('rejects unauthenticated access to properties', function () {
    $this->withoutMiddleware(\App\Http\Middleware\Authenticate::class);

    auth()->logout();

    $this->getJson(route('api.properties.index'))
        ->assertUnauthorized();
})->skip('requires middleware setup');

it('enforces authorization on property metrics', function () {
    $otherUser = User::factory()->create();
    $property = GscProperty::factory()->create(['user_id' => $otherUser->id]);

    $this->getJson(route('api.properties.metrics', $property->id))
        ->assertForbidden();
});
