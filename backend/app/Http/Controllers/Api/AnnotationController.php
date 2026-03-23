<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Annotation;
use App\Models\MediaAsset;
use Illuminate\Http\Request;

class AnnotationController extends Controller
{
    // GET /media-assets/{mediaAsset}/annotations
    public function index(MediaAsset $mediaAsset)
    {
        $annotations = $mediaAsset->annotations()
            ->with('user:id,name,role')
            ->orderBy('pin_number')
            ->get();

        return response()->json($annotations);
    }

    // POST /media-assets/{mediaAsset}/annotations
    public function store(Request $request, MediaAsset $mediaAsset)
    {
        $data = $request->validate([
            'x_position' => 'required|numeric|min:0|max:100',
            'y_position' => 'required|numeric|min:0|max:100',
            'comment'    => 'required|string|max:2000',
        ]);

        // pin_number = siguiente número disponible para este asset
        $nextPin = $mediaAsset->annotations()->max('pin_number') + 1;

        $annotation = $mediaAsset->annotations()->create([
            'user_id'    => $request->user()->id,
            'pin_number' => $nextPin,
            'x_position' => $data['x_position'],
            'y_position' => $data['y_position'],
            'comment'    => $data['comment'],
        ]);

        $annotation->load('user:id,name,role');

        return response()->json($annotation, 201);
    }

    // DELETE /media-assets/{mediaAsset}/annotations/{annotation}
    public function destroy(MediaAsset $mediaAsset, Annotation $annotation)
    {
        if ($annotation->media_asset_id !== $mediaAsset->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if ($annotation->user_id !== request()->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $annotation->delete();

        return response()->json(null, 204);
    }
}
