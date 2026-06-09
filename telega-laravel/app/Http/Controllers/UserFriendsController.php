<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddFriendRequest;
use App\Http\Requests\RemoveFriendRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserFriendsController extends Controller
{

    /**
     * Получить список возможных друзей
     * @param Request $request
     * @return JsonResponse
     */
    public function possibleFriends(Request $request) {
        $user = auth()->user();
        $users = User::where('id', '!=', $user->id)->paginate(10);
        return response()->json($users);
    }

    /**
     * Получить список друзей
     * @param Request $request
     * @return JsonResponse
     */
    public function friends(Request $request) {
        $user = auth()->user();
        $user->load('friends');
        return response()->json($user->friends);
    }

    /**
     * Добавление друзей
     * @param AddFriendRequest $request
     * @return JsonResponse
     */
    public function addFriends(AddFriendRequest $request) {
        $validated = $request->validated();
        $user = auth()->user();
        $user->friends()->attach($validated['friend_ids']);
        return response()->json([
            'success' => true,
            'message' => 'Friends added',
        ], 200);
    }

    /**
     * @param RemoveFriendRequest $request
     * @return JsonResponse
     */
    public function removeFriends(RemoveFriendRequest $request) {
        $validated = $request->validated();
        $user = auth()->user();
        $user->friends()->detach($validated['friend_ids']);
        return response()->json([
            'success' => true,
            'message' => 'Friends removed',
        ], 200);
    }
}
