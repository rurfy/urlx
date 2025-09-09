<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LinkExportController extends Controller
{
    /**
     * Exportiert Click-Events für einen Link als JSON (default) oder CSV (?format=csv).
     * Optional: Filter per ?from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function export(Request $request, string $slug)
    {
        $format = strtolower((string) $request->query('format', 'json'));
        $from = $request->date('from'); // optional
        $to = $request->date('to');   // optional

        $link = Link::where('slug', $slug)->firstOrFail();

        // Basisquery
        $q = DB::table('click_events')
            ->where('link_id', $link->id)
            ->select(['id', 'link_id', 'ip_hash', 'user_agent', 'referrer', 'clicked_at', 'created_at']);

        if ($from)
            $q->whereDate('clicked_at', '>=', $from->toDateString());
        if ($to)
            $q->whereDate('clicked_at', '<=', $to->toDateString());

        if ($format === 'csv') {
            $filename = sprintf('urlx-%s-events-%s.csv', $link->slug, now()->format('Ymd-His'));

            // StreamedResponse hält RAM klein, schreibt on-the-fly
            return response()->streamDownload(function () use ($q) {
                $out = fopen('php://output', 'w');
                // Header
                fputcsv($out, ['id', 'link_id', 'ip_hash', 'user_agent', 'referrer', 'clicked_at', 'created_at']);
                // Daten in Chunks streamen
                $q->orderBy('id')->chunk(1000, function ($rows) use ($out) {
                    foreach ($rows as $r) {
                        fputcsv($out, [
                            $r->id,
                            $r->link_id,
                            $r->ip_hash,
                            $r->user_agent,
                            $r->referrer,
                            $r->clicked_at,
                            $r->created_at,
                        ]);
                    }
                });
                fclose($out);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        // JSON (default)
        $events = $q->orderByDesc('id')->limit(10_000)->get(); // Sicherheitslimit
        return response()->json([
            'slug' => $link->slug,
            'count' => $events->count(),
            'from' => $from?->toDateString(),
            'to' => $to?->toDateString(),
            'events' => $events,
        ]);
    }
}