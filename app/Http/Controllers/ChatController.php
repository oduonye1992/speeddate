<?php

namespace App\Http\Controllers;

use App\Chat;
use App\Matches;
use App\Utility;
use Illuminate\Http\Request;
use Validator;

class ChatController extends Controller
{
    public function read(Request $request){
        return Chat::where('match_id', $request->match_id)->with(['user'])->latest()->get();
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
        $match = Matches::findOrFail($request->match_id);

        try {
            if ($match->user_id == $request->user_id){
                Utility::notifyUser($match->matcher_id, $match->body);
            } else {
                Utility::notifyUser($match->user_id, $match->body);
            }
        } catch (\Exception $e){

        }
        return Chat::with(['user'])->findOrFail($chat->id);
    }
}
