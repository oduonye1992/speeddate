<?php

namespace App\Http\Controllers;

use App\Chat;
use Illuminate\Http\Request;
use Validator;

class ChatController extends Controller
{
    public function read(Request $request){
        return Chat::where('match_id', $request->match_id)->with(['user'])->get();
    }
    public function add(Request $request) {
        $rules = [
            'match_id' => 'required|integer|exists:matches,id',
            'user_id' => 'required|integer|exists:users,id',
            'body' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors()->all();
        }
        $chat = Chat::create($request->all());
        return Chat::with(['user'])->findOrFail($chat->id);
    }
}
