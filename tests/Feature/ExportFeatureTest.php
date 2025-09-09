<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Link;
use Illuminate\Support\Facades\DB;

class ExportFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_json_export_works(): void
    {
        $link = Link::create(['slug' => 'expjson', 'target_url' => 'https://example.com']);

        // ein paar Events erzeugen
        DB::table('click_events')->insert([
            [
                'link_id' => $link->id,
                'ip_hash' => 'hash1',
                'user_agent' => 'ua',
                'referrer' => 'https://ref',
                'clicked_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'link_id' => $link->id,
                'ip_hash' => 'hash2',
                'user_agent' => 'ua',
                'referrer' => null,
                'clicked_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $resp = $this->getJson('/api/links/expjson/export');
        $resp->assertOk()
            ->assertJsonStructure(['slug', 'count', 'events']);
        $this->assertSame('expjson', $resp->json('slug'));
        $this->assertSame(2, $resp->json('count'));
    }

    public function test_csv_export_works(): void
    {
        $link = Link::create(['slug' => 'expcsv', 'target_url' => 'https://example.com']);

        DB::table('click_events')->insert([
            'link_id' => $link->id,
            'ip_hash' => 'hashCSV',
            'user_agent' => 'ua',
            'referrer' => 'https://ref',
            'clicked_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resp = $this->get('/api/links/expcsv/export?format=csv');

        $resp->assertOk();
        $resp->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $resp->assertHeader('content-disposition');

        // Bei StreamedResponse: Inhalt so abgreifen
        $csv = $resp->streamedContent();
        $this->assertStringContainsString('id,link_id,ip_hash,user_agent,referrer,clicked_at,created_at', $csv);
        $this->assertStringContainsString('hashCSV', $csv);
    }
}
