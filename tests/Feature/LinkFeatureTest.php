<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Link;

class LinkFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_short_link(): void
    {
        $resp = $this->postJson('/api/links', [
            'target_url' => 'https://example.com',
        ]);

        $resp->assertCreated()
            ->assertJsonStructure(['slug', 'short_url', 'target_url']);
    }

    public function test_it_redirects_and_tracks(): void
    {
        $link = Link::create([
            'slug' => 'abc12345',
            'target_url' => 'https://example.com',
        ]);

        $resp = $this->get('/abc12345');
        $resp->assertRedirect('https://example.com');

        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'clicks' => 1,
        ]);

        $this->assertDatabaseHas('click_events', [
            'link_id' => $link->id,
        ]);
    }

    public function test_index_and_stats(): void
    {
        $l = Link::create(['slug' => 'stat01', 'target_url' => 'https://example.org']);
        $this->get('/stat01'); // erzeugt 1 Click-Event

        $this->getJson('/api/links')
            ->assertOk()
            ->assertJsonStructure(['data']);

        $this->getJson('/api/links/stat01/stats')
            ->assertOk()
            ->assertJsonStructure(['slug', 'total_clicks', 'by_day']);
    }

    public function test_it_deletes_a_link(): void
    {
        $link = Link::create([
            'slug' => 'todelete1',
            'target_url' => 'https://example.com',
        ]);

        // Einen Klick erzeugen, damit es auch Events gäbe
        $this->get('/todelete1')->assertRedirect('https://example.com');

        // Delete aufrufen
        $this->deleteJson('/api/links/todelete1')
            ->assertNoContent(); // 204

        // Link weg
        $this->assertDatabaseMissing('links', ['id' => $link->id]);

        // Event(s) weg (Cascade)
        $this->assertDatabaseMissing('click_events', ['link_id' => $link->id]);
    }

}
