<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RedirectController extends Controller
{
    public function __invoke(Request $r, string $slug)
    {
        $link = Link::where('slug', $slug)->firstOrFail();

        if ($link->expires_at && $link->expires_at->isPast()) {
            abort(410); // Gone
        }

        DB::transaction(function () use ($r, $link) {
            $link->increment('clicks');

            DB::table('click_events')->insert([
                'link_id'    => $link->id,
                'ip_hash'    => hash('sha256', $r->ip() . '|salt'),
                'user_agent' => substr((string) $r->userAgent(), 0, 255),
                'referrer'   => substr((string) $r->headers->get('referer'), 0, 255),
                'clicked_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->away($link->target_url, 302);
    }
}
