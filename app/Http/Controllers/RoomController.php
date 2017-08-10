<?php

namespace App\Http\Controllers;

use App\Category;
use App\Factories\RoomFactory;
use App\Profile;
use App\Room;
use App\States;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoomController extends Controller
{
    public function bulkInsert(){
        $str = <<<EOD
[{
	"category": "Flirt",
	"description": "Bring out the naughty side of you.",
	"rooms": [{
		"name": "Flirt Zone",
		"description": "Core flirting zone. What your see is what you get",
		"image": "https://unsplash.it/400/600?image=1008"
	}, {
		"name": "Love",
		"description": "Falling in love with the right person is a very good thing.",
		"image": "https://unsplash.it/400/600?image=185"
	}, {
		"name": "Sex Talk",
		"description": "Hormones kicking in? Say no more",
		"image": "https://unsplash.it/400/600?image=1069"
	}, {
		"name": "Romance",
		"description": "All the feels. ",
		"image": "https://unsplash.it/400/600?image=1083"
	}, {
		"name": "Singles",
		"description": "No partner? Not Problem.... Sobs",
		"image": "https://unsplash.it/400/600?image=1025"
	}]
}, {
	"category": "Entertainment",
	"description": "Find Love but have fun while doing so",
	"rooms": [{
		"name": "Technology",
		"description": "Everyone here is a geek. You wont survive if you are not",
		"image": "https://unsplash.it/400/600?image=816"
	}, {
		"name": "Poetry",
		"description": "Enter the innermost depth of the soul and connect on the deepest level ever",
		"image": "https://unsplash.it/400/600?image=802"
	}, {
		"name": "Movies",
		"description": "Sometimes the world is your big screen.",
		"image": "https://unsplash.it/400/600?image=768"
	}]
}, {
	"category": "Faith",
	"description": "Be Holy",
	"rooms": [{
		"name": "Christianity",
		"description": "Date yourselves in a christian way. Watch what you say :)",
		"image": "https://unsplash.it/400/600?image=782"
	}, {
		"name": "Islam",
		"description": "Meetup with fellow brethren",
		"image": "https://unsplash.it/400/600?image=760"
	}, {
		"name": "Traditionalist",
		"description": "Yea. Sango, orumila and the likes. Meet yourself here",
		"image": "https://unsplash.it/400/600?image=783"
	}]
}, {
	"category": "Hangout",
	"description": "Be in the right mood and find the right person for you",
	"rooms": [{
		"name": "All night cafe",
		"description": "Chill with like minded people. Late night, just talking.",
		"image": "https://unsplash.it/400/600?image=845"
	}, {
		"name": "Fashion",
		"description": "Rule 1: You must look good",
		"image": "https://unsplash.it/400/600?image=838"
	}, {
		"name": "Agony Aunt",
		"description": "Pour your heart out. It's ok, we got you",
		"image": "https://unsplash.it/400/600?image=1001"
	}]
}, {
	"category": "Football",
	"description": "Meet Fellow football freaks",
	"rooms": [{
		"name": "Chelsea",
		"description": "Up Chelsea, connect with your fellow Agberos",
		"image": "https://s-media-cache-ak0.pinimg.com/736x/c4/4d/71/c44d71f19c55fb94f97ed404a9679ed6.jpg"
	}, {
		"name": "Arsenal",
		"description": "Welcome to Arsenal, we teach patience here. Be patient with your partner",
		"image": "https://s-media-cache-ak0.pinimg.com/originals/bb/13/29/bb1329b4f95322a59338fcf2aae97bdf.jpg"
	}, {
		"name": "Barcelona",
		"description": "Welcome to the club of champions. You know what to do",
		"image": "http://cdn.traveler.es/uploads/images/thumbs/201244/100_cosas_sobre_barcelona_que_deberias_saber_114549714_800x1200.jpg"
	}, {
		"name": "Manchester United",
		"description": "Glory Glory Man United!",
		"image": "https://s-media-cache-ak0.pinimg.com/736x/aa/00/fc/aa00fc5afc750a13693d690eb3e9f3f5.jpg"
	}, {
		"name": "Hala Madrid",
		"description": "The best club in the world",
		"image": "https://s-media-cache-ak0.pinimg.com/736x/cf/63/ed/cf63ed08b75497b51046d41cf3826838.jpg"
	}]
}]
EOD;
        $categories = json_decode($str);
        foreach ($categories as $category){
            $genCat = Category::create([
                'name' => $category->category,
                'description' => $category->description
            ]);
            foreach ($category->rooms as $room) {
                Room::create([
                   'title' => $room->name,
                    'description' => $room->description,
                    'image' => $room->image,
                    'category_id' => $genCat->id,
                    'start_time' => '2011-10-10',
                    'end_time' => '2011-10-10'
                ]);
            }
        }
        $sampleStates = [
            [
                'title' => 'Lagos, Nigeria',
                'code' => 'NG-LA'
            ]
        ];
        States::insert($sampleStates);

        return response("All done", Response::HTTP_OK);
    }
    public function unMatch(){

    }
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
    public function createCategory(Request $request){
        $a = [
            'name' => 'required'
        ];
         $validator = \Validator::make($request->all(), $a);
        \Log::info('Creating category '.json_encode($request->all()));
        if ($validator->fails()) {
            throw new \Exception($validator->errors());
        }
        return Category::create($request->all());
    }
    public function getCategory(){
        return Category::with(['rooms'])->get();
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
