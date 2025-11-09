<?php

namespace App\Http\Controllers;

use App\Models\Album;

class QRCodeController extends Controller
{
    public function show($albumId)
    {
        $album = Album::with('pin')->findOrFail($albumId);

        return response()->json([
            'qr_code' => $album->qr_code,
            'pin'    => $album->pin->pin ?? null,
        ]);
    }
}
