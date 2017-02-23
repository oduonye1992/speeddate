<?php

namespace App\Factories;


use App\Matches;
use App\Profile;
use App\Room;
use App\RoomUser;

class RoomFactory {
    // Room Management
    public function createRoom(array $data){
        $validator = \Validator::make($data, $this->validationRules("create"));
        \Log::info('Creating room '.json_encode($data));
        if ($validator->fails()) {
            throw new \Exception($validator->errors());
        }
        $room = Room::create($data);
        /*$newData = [
            //'user_id' => $data['creator_id'],
            'room_id' => $room->id
        ];
        $this->subscribeToRoom($newData);
        */
        return $room;
    }
    public function getRooms(array $data){
        return Room::with(['creator'])->get();
    }
    public function editRoom($roomID, array $data){}
    public function getRoomByID($roomID){
        return Room::with(['creator'])->findOrFail($roomID);
    }
    public function updateProfile($profileID, array $data){
        Profile::findOrFail($profileID)->update($data);
        return Profile::findOrFail($profileID);
    }
    public function getRoomSubscribers($roomID){
        return RoomUser::where('room_id', $roomID)->get();
    }
    public function subscribeToRoom(array $data){
            $validator = \Validator::make($data, $this->validationRules("subscribe"));
            if ($validator->fails()) {
                throw new \Exception($validator->errors());
            }
            return RoomUser::create($data);
    }
    public function deleteRoomByID($roomID){}
    public function isUserInRoom($userID, $roomID){
        $roomUser = RoomUser::where('user_id', $userID)
            ->where('room_id', $roomID)
            ->get();
        $exists = count($roomUser) > 0;
        return [
            'status' => $exists
        ];
    }

    public function getUserMatches($userID){
        $matches = Matches::where('user_id', $userID)->orWhere('matcher_id', $userID)->with(['user', 'matcher', 'room'])->get();
        $friends = [];
        $alreadyAddedFriends = [];
        foreach ($matches as $match){
            if ($match['user_id'] == $userID){
                if (in_array($match['matcher_id'], $alreadyAddedFriends)){
                    continue;
                }
                $id = $match['matcher_id'];
                $ma = $match['matcher'];
                $profile = Profile::findOrFail($id);
                $ma['profile'] = $profile;
                array_push($friends, $ma);
                array_push($alreadyAddedFriends, $id);
            } else {
                if (in_array($match['user_id'], $alreadyAddedFriends)){
                    continue;
                }
                $id = $match['user_id'];
                $ma = $match['user'];
                $profile = Profile::findOrFail($id);
                $ma['profile'] = $profile;
                array_push($friends, $ma);
                array_push($alreadyAddedFriends, $id);
            }
        }
        return $friends;
    }


    public function getUserRooms($userID) {
        return RoomUser::where('user_id', $userID)->with(['user', 'room'])->get();
    }
    // Match Management
    public function getSubscribedRoomForUser($userID){
        $sub = RoomUser::where('user_id', $userID)->pluck('room_id');
        return Room::whereIn('id', $sub);
    }

    // User Management
    public function editProfile(array $data){}
    public function getMatchesForUser($userID){

    }

    private function validationRules($type)
    {
        switch ($type) {
            case "create":
                return [
                    'title' => 'required',
                    'description' => 'required',
                    'start_time' => 'required',
                    'end_time' => 'required',
                    'category_id' => 'required|integer|exists:categories,id',
                ];
                break;
            case "subscribe":
                return [
                    'user_id' => 'required|integer',
                    'room_id' => 'required|integer',
                ];
                break;
        }
    }
}