<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $api_key = config('services.tmdb.api_key');
        $user = Auth()->user();
        $favorites = $user->favorites;
        // return response()->json($favorites);
        //お気に入りを一つずつ取得する
        $favorite_media = [];
        foreach ($favorites as $favorite) {
            $media_type = $favorite->media_type;
            $media_id = $favorite->media_id;
            $url = "https://api.themoviedb.org/3/" . $media_type . "/" . $media_id . "?api_key=" . $api_key;
            $json = file_get_contents($url);
            $data = json_decode($json);
            $favorite_media[] = $data;
            //media_typeをマージする
            $favorite_media[count($favorite_media) - 1]->media_type = $media_type;
        }
        return response()->json($favorite_media);
    }

    public function toggleFavorite(Request $request)
    {
        $validatedData = $request->validate([
            'media_id' => 'required|integer',
            'media_type' => 'required|string',
        ]);
        $existingFavorite = Favorite::where([
            'media_id' => $validatedData['media_id'],
            'media_type' => $validatedData['media_type'],
            'user_id' => auth()->id(),
        ])->first();

        if ($existingFavorite) {
            $existingFavorite->delete();
            return response()->json(['status' => 'removed']);
        } else {
            Favorite::create([
                'media_id' => $validatedData['media_id'],
                'media_type' => $validatedData['media_type'],
                'user_id' => auth()->id(),
            ]);
            return response()->json(['status' => 'added']);
        }
    }

    public function checkFavoriteStatus(Request $request)
    {
        $validatedData = $request->validate([
            'media_id' => 'required|integer',
            'media_type' => 'required|string',
        ]);
        $existingFavorite = Favorite::where([
            'media_id' => $validatedData['media_id'],
            'media_type' => $validatedData['media_type'],
            'user_id' => auth()->id(),
        ])->first();

        if ($existingFavorite) {
            return response()->json(['status' => 'added']);
        } else {
            return response()->json(['status' => 'removed']);
        }
    }
}
