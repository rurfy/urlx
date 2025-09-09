<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLinkRequest;
use App\Models\Link;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class LinkController extends Controller
{
    public function store(StoreLinkRequest $request): JsonResponse
    {
        $slug = $request->input('slug') ?: Str::random(7);

        $link = Link::create([
            'slug' => $slug,
            'target_url' => $request->string('target_url'),
            'expires_at' => $request->date('expires_at'),
        ]);

        return response()->json([
            'slug' => $link->slug,
            'short_url' => url($link->slug),
            'target_url' => $link->target_url,
            'expires_at' => $link->expires_at,
        ], 201);
    }

    public function index()
    {
        return Link::query()
            ->select(['id', 'slug', 'target_url', 'clicks', 'created_at'])
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function stats(string $slug)
    {
        $link = Link::where('slug', $slug)->firstOrFail();

        $rows = \DB::table('click_events')
            ->selectRaw("DATE(clicked_at) as day, COUNT(*) as clicks")
            ->where('link_id', $link->id)
            ->groupByRaw('DATE(clicked_at)')
            ->orderBy('day', 'desc')
            ->limit(30)
            ->get();

        return response()->json([
            'slug' => $link->slug,
            'total_clicks' => $link->clicks,
            'by_day' => $rows,
        ]);
    }

}
