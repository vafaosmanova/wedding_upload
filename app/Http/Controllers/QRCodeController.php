<?php

namespace App\Http\Controllers;

use App\Models\Album;

class QRCodeController extends Controller
{
    public function show($album_id)
    {
        $album = Album::with('pin')->findOrFail($album_id);

        return response()->json([
            'qr_code' => $album->qr_code,
            'pin'    => $album->pin->pin ?? null,
        ]);
    }
}
