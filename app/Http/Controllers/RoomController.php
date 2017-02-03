<?php

namespace App\Http\Controllers;

use App\Factories\RoomFactory;
use App\Profile;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function createRoom(Request $request, RoomFactory $factory){
        try {
            $request['creator_id'] = $request['user_id'];
            $all = $request->all();
            return $factory->createRoom($all);
        } catch (\Exception $e){
            return response($e->getMessage().$e->getTraceAsString(), 400);
        }
    }
    public function getRooms(Request $request, RoomFactory $factory){
        try {
            $all = $request->all();
            return $factory->getRooms($all);
        } catch (\Exception $e){
            return response($e->getMessage().$e->getTraceAsString(), 400);
        }
    }
    public function editRoom($roomID, Request $request, RoomFactory $factory){
        try {
            $request['user'] = $request['user_id'];
            return $factory->editRoom($roomID, $request->all());
        } catch (\Exception $e) {
            return response($e->getMessage().$e->getTraceAsString(), 400);
        }
    }
    public function getRoomByID($roomID, Request $request, RoomFactory $factory){
        try {
            $request['user'] = $request['user_id'];
            return $factory->getRoomByID($roomID);
        } catch (\Exception $e) {
            return response($e->getMessage().$e->getTraceAsString(), 400);
        }
    }
    public function getRoomSubscribers($roomID, Request $request, RoomFactory $factory){
        try {
            $request['user'] = $request['user_id'];
            return $factory->getRoomSubscribers($roomID);
        } catch (\Exception $e) {
            return response($e->getMessage().$e->getTraceAsString(), 400);
        }
    }
    public function subscribeToRoom($roomID, Request $request, RoomFactory $factory){
        try {
            $request['user'] = $request['user_id'];
            $request['room_id'] = $roomID;
            return $factory->subscribeToRoom($request->all());
        } catch (\Exception $e) {
            return response($e->getMessage().$e->getTraceAsString(), 400);
        }
    }
    public function deleteRoomByID(){

    }
    public function isUserInRoom($userID, $roomID, Request $request, RoomFactory $factory){
        try {
            return $factory->isUserInRoom($userID, $roomID);
        } catch (\Exception $e) {
            return response($e->getMessage().$e->getTraceAsString(), 400);
        }
    }
    public function getUserMatches(RoomFactory $factory, Request $request){
        try {
            $request['user'] = $request['user_id'];
            return $factory->getUserMatches($request['user']);
        } catch (\Exception $e) {
            return response($e->getMessage().$e->getTraceAsString(), 400);
        }
    }
    public function updateProfile(RoomFactory $factory, Request $request){
        try {
            $profile = Profile::where('user_id', $request['user_id'])->get()[0];
            return $factory->updateProfile($profile->id, $request->all());
        } catch (\Exception $e) {
            return response($e->getMessage().$e->getTraceAsString(), 400);
        }
    }
}
