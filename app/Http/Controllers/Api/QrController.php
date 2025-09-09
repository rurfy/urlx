<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class QrController extends Controller
{
    public function show(string $slug)
    {
        $url = url($slug);
        $png = QrCode::format('png')->size(300)->margin(1)->generate($url);
        return response($png, Response::HTTP_OK)
            ->header('Content-Type', 'image/png');
    }
}
