'use strict';
var app                     = require('express')();
var http                    = require('http').Server(app);
var io                      = require('socket.io')(http);
var mysql                   = require("mysql"); // Sorry I don't just fancy the whole mongo stuff
var moment                  = require('moment');
var port                    = 8013;
var runningInstances = [];
var pattern = 'dddd[,] MMMM Do YYYY h:mm A';
var connectionDetails = {
    host: "localhost",
    port:"3307",
    user: "root",
    password: "s0ftware!",
    database: "sd"
};
var con = mysql.createConnection(connectionDetails);
var ROOM_STATUS = {
    PENDING : 0,
    ACTIVE : 1,
    END : 2
};
var CONSTANTS = {
    MESSAGE     : 'new_message',
    MESSAGE_FOR_ME     : 'recieved_message',
    JOIN        : 'join',
    REGISTER    : 'register',
    CONNECTION  : 'connect',
    DISCONNECTION  : 'disconnect',
    CHAT        : 'chat',
    REGISTER_CONFIRM : 'registered_confirmed',
    TIMER : 'timer',
    ROOM_STATUS : 'room_status',
    CANDIDATE : 'new_maga',
    LIKED : 'liked',
    MATCHED : 'matched',
    USER_DISCONNECTED : 'user_disconnected',
    QUIT : 'quit',
    UPDATE : 'update_credentials',
    INTERNAL : 'internal_message',
    USER_DISCONNECT : 'disconnect reallly this time',
    MATCHING : 'matching_users',
    SEARCHING : 'No_Candidate_at_the_moment'
};

var query = function(sql, successCB, errorCB){
    con.query(sql,function(err,rows){
        if(err) throw err;
        // TODO Call errorCB
        if (successCB){
            successCB(rows);
        }
    });
};
var isValidDate = function(start_date, end_date){
    return true;
    var startDate = moment(start_date, pattern);
    var endDate = moment(end_date, pattern);
    var currentDate = moment();
    return currentDate.isAfter(startDate) && currentDate.isBefore(endDate);
};
var format = function(id){
    // return "user_"+id;
    return id;
};
var strip = function(userString){
    return userString;
    // return userString.split('_')[1];
};
var updateRoomStatus = function(roomID, status){
    var sql = "UPDATE rooms set STATUS = "+status+" WHERE ID = "+roomID;
    query(sql);
};

var Cupids = function(_roomOptions){
    var room = _roomOptions.id;
    var roomObj = _roomOptions;
    var timerCountdown          = 40;//seconds
    var timerCountdownMs        = timerCountdown*1000;//ms
    var timerWaitingperiod      = 10;//seconds
    var timerWaitingperiodMs    = timerWaitingperiod*1000;//ms
    var reconnectionTimer       = 30;//s
    var reconnectionTimerMs     = reconnectionTimer*1000;
    var chatInProgress          = false;
    var rooms                   = []; // not in use for now
    var nsp                     = io.of('/'+roomObj.id);

    var Timer = function(){
        var index = 0;
        return {
            start : function(){
                var aa = setInterval(function(){
                    if (chatInProgress){
                        Users.sendBroadcast(CONSTANTS.TIMER, timerCountdown-index);
                    }
                    if (index == timerCountdown-1){
                        index = 0;
                        clearInterval(aa);
                    } else {
                        index++;
                    }
                }, 1000);
            }
        }
    }();

    var cupid = function(){
        console.log('Cupid is starting');
        Users.sendBroadcast(CONSTANTS.INTERNAL, 'Preparing all users');
        Users.sendBroadcast(CONSTANTS.MATCHING);
        var aa = function(){
            console.log('disconnecting all users');
            Users.resetUsers();
            console.log('Removing inactive socket connections');
            Users.weedOutInactiveSockets();
            Users.sendBroadcast(CONSTANTS.INTERNAL, 'Matching you with a partner...');
            console.log('Shuffling users');
            Users.newShuffle();
            console.log('Matching users');
            Users.match();
            chatInProgress = true;
            setTimeout(cupid, timerCountdownMs);
            Timer.start();
        };
        setTimeout(function(){
            aa();
        }, 5000);
    };

    var saveMatch = function(user1, user2, roomID){
        var sql = "INSERT INTO matches (user_id, matcher_id, room_id) VALUES ("+strip(user1)+","+strip(user2)+", "+roomID+" )";
        console.log(sql);
        query(sql, function(){
            console.log('saved matches in room '+roomID+' between '+user1+' and '+user2);
        });
    };

    var Users = function(){
        var users = {};
        var resetUsers = function(){
            // I hope you got the girl man
            for(var key in users){
                if (!users.hasOwnProperty(key)) continue;
                users[key].meta.connected = false;
                users[key].meta.liked = false;
            }
            rooms = [];
        };
        var matchUser = function(userID){
            if (!users.hasOwnProperty(userID)) {
                return console.log('Key not found idiot');
            }
            if (users[userID].meta.connected) {
                return console.log('User already connected idiot to '+users[userID].connected);
            }
            for(var key in users){
                //TODO Add matching validation check
                if (key == userID) continue;
                if (!users.hasOwnProperty(key)) continue;
                if (users[key].meta.connected) continue;
                if (!users[key].sockets.length) continue;
                // TODO Get their preference and check against current user
                if(!users[key].meta.connected){ // Added the latter option
                    console.log('Connection was made between '+userID+' and '+key);
                    users[key].meta.connected = userID;
                    users[userID].meta.connected = key;//
                    return true;
                }
            }
            console.error('No connections found for this user');
            return false;
        };
        var sendToMultipleSockets = function(socketArray, key, message){
            if (socketArray && socketArray.length){
                socketArray.forEach(function(item){
                     if (item && item.connected){
                         item.emit(key, message);
                     }
                });
            }
        };
        var sendUserNewMatches = function(){
            for (var key in users){
                if (!users.hasOwnProperty(key)) throw new Error('Key not found idiot');
                if (users[key].sockets.length){
                    console.log('connected user for '+key+' is '+users[key].meta.connected);
                    var userObj = false;
                    if (users[key].meta.connected){
                        var connectedUser = users[users[key].meta.connected];
                        userObj = {
                            id : connectedUser.id,
                            username : connectedUser.username,
                            details : connectedUser.details
                        };
                        console.log('Sending key '+CONSTANTS.CANDIDATE);
                        sendToMultipleSockets(users[key].sockets, CONSTANTS.CANDIDATE, userObj);
                    } else {
                        sendToMultipleSockets(users[key].sockets, CONSTANTS.SEARCHING, userObj);
                    }
                }
            }
        };
        /**
         * @description It checks if the all the disconnected sockets attached to a user
         * @param key
         * @returns {boolean}
         */
        var weedOutInactiveSocketForUser = function(key){
            if (!users[key]) return false;
            if (!users[key].sockets.length) return false;
            var isActive = false;
            var sockets = [];
            console.log(key + ' has '+users[key].sockets.length+ ' sockets');
            for (var i =0; i < users[key].sockets.length; i++){
                if (users[key].sockets[i] && users[key].sockets[i].connected){
                    sockets.push(users[key].sockets[i]);
                    isActive = true;
                }
            }
            users[key].sockets = sockets;
            console.log('Now '+ key + ' has '+users[key].sockets.length+ ' sockets');
        };
        return {
            resetUsers  : function(){
                resetUsers();
            },
            weedOutInactiveSockets : function (){
                var keys = Object.keys(users);
                for (var i = 0; i < keys.length; i++){
                    weedOutInactiveSocketForUser(keys[i]);
                }
            },
            sendMessageToUserPartner : function(key, message){
                if(!users[key]) return;
                var partner = users[key].meta.connected;
                sendToMultipleSockets(users[partner].sockets, CONSTANTS.MESSAGE_FOR_ME, message);
            },
            getUserPartnerID : function(key){
                if(!users[key]) return null;
                return users[key].meta.connected;
            },
            newShuffle : function(){
                // Shuffle Algo - http://en.wikipedia.org/wiki/Fisher%E2%80%93Yates_shuffle#The_modern_algorithm
                var fisherShuffle = function (sourceArray) {
                    for (var i = 0; i < sourceArray.length - 1; i++) {
                        var j = i + Math.floor(Math.random() * (sourceArray.length - i));

                        var temp = sourceArray[j];
                        sourceArray[j] = sourceArray[i];
                        sourceArray[i] = temp;
                    }
                    return sourceArray;
                };
                // Convert obj to arr
                var tmpArr = [];
                for(var key in users){
                    tmpArr.push(key);
                }
                var shuffledArr = fisherShuffle(tmpArr);
                // Convert Arr to Ibj
                var returnObj = {};
                for(var i = 0; i < shuffledArr.length; i++){
                    returnObj[shuffledArr[i]] = users[shuffledArr[i]];
                }
                users = returnObj;
            },
            shuffle : function(){
                var tmpArr = [];
                for(var key in users){
                    tmpArr.push({'key':key, 'value':users[key]})
                }
                console.log('new arr///////////////////// '+tmpArr.length);
                tmpArr.sort(function(a, b){
                    var rand = Math.round(Math.random()*2);
                    if (rand == 0){
                        return -1;
                    } else if (rand == 1){
                        return 0;
                    } else if (rand == 2){
                        return 1;
                    }
                });
                var newusers = {};
                tmpArr.forEach(function(item){
                    newusers[item.key] = item.value;
                });
                users = newusers;
                console.log('Length of new users '+users.length);
            },
            register :  function(cred, socket, _isUpdateCredentials){
                var isUpdateCredentials = _isUpdateCredentials || false;
                if (users[cred.id]){
                    users[cred.id].socket = socket;
                    users[cred.id].details = cred.details;
                    /**
                     * Add the socket to the existing sockets
                     */
                    if (socket !== undefined) {
                        users[cred.id].sockets.push(socket);
                    }
                    console.log('Updated user instead. '+cred.id);
                    return true;
                }
                var newUser = {
                    id : cred.id,
                    name : cred.name,
                    meta : {
                        connected : false,
                        liked : false
                    },
                    sockets : [],
                    room : cred.room,
                    partner : null,
                    details : {}
                };
                if (socket !== undefined) {
                    newUser.sockets.push(socket);
                    console.log('Added socket')
                }
                users[cred.id] = newUser;
                console.log('Create new user for User '+cred.id);
                return true;
            },
            sendBroadcast : function(event, message){
                nsp.emit(event, message);
            },
            raw : function(){return users},
            match : function(){
                for (var key in users){
                    console.log('There are '+Object.keys(users).length+' users');
                    if (!users.hasOwnProperty(key)) {
                        console.log('Key not found. '+key);
                        continue;
                    }
                    // If user is not matched to a partner and the user has a valid socket connection
                    if (!users[key].meta.connected && users[key].sockets.length){
                        console.log('Evaluating User ID '+key);
                        matchUser(key);
                    } else {
                        console.log('Evaluating User '+key+' doesnt qualify for matching.');
                    }
                }
                sendUserNewMatches();
            },
            sendSystemMessageToUser: function(userID, event, message){
                sendToMultipleSockets(users[userID].sockets, event, message);
            },
            isMatchExist: function(user1, user2){
                return ((users[user1].meta.liked == user2) && (users[user2].meta.liked == user1));
            },
            likeUser: function(liker, likee, sendNotification){
                users[liker].meta.liked = likee;
                if (sendNotification){
                    Users.sendSystemMessageToUser(likee, CONSTANTS.LIKED, liker);
                }
            },
            getUserPinByUsername: function(username){
                return null;
                //return users[username].pin;
            },
            removeUser: function(username){
                if (users[username]){
                    // If there is an active chat already
                    if(users[username].meta.connected){
                        //Users.sendSystemMessageToUser(users[username].meta.connected, CONSTANTS.USER_DISCONNECTED, username);
                    }
                    Users.sendSystemMessageToUser(username, CONSTANTS.USER_DISCONNECT);
                    users[username].sockets = [];
                    //delete users[username];
                    console.log(username+' has been deleted, socket length now '+users[username].sockets.length);
                }
            }
        }
    }();

    var populateUsers = function (id, successCB, errorCB){
        /**
         * Since we are not prepopulating the users, this function will resolve early
         */
        return successCB();
        // Store all the users already subscribed to that room
        console.log('Fetching Subscribers for room');
        //var sql = "SELECT u.* FROM users u ,room_user us where us.room_id = "+id+" and u.id = us.user_id";
        var sql = "SELECT * FROM users";// u ,room_user us where us.room_id = "+id+" and u.id = us.user_id";
        query(sql, function(data){
            console.log('Number of subscription = '+data.length);
            data.forEach(function(item){
                 var userData = {
                     'id' : format(item.id),
                     'name' : item.name,
                     'room' : room
                 };
                Users.register(userData);
            });
            successCB();
        });
    };

    var bindEvents = function(namespace){
        console.log('Setting up connection for the room '+roomObj.id);
        namespace.on('connection', function(socket){
            console.log('a user connected in '+room);
            socket.on(CONSTANTS.REGISTER, function(credentials){
                console.log('User sent registration details '+JSON.stringify(credentials));
                var roomWelcome = function(){
                    console.log('Sending welcome message');
                    Users.sendSystemMessageToUser(credentials.id, CONSTANTS.INTERNAL, "Welcome to Chat Roulette");
                };
                var roomStats = function(){
                    var message = "";
                    if (chatInProgress){
                        message = "Chat room is currently in progress. Wait for a while";
                    } else {
                        message = "You arrived at a very good time. ";
                    }
                    console.log('Sending Stat message');
                    Users.sendSystemMessageToUser(credentials.id, CONSTANTS.INTERNAL, message);
                };
                var map = [
                    roomWelcome,
                    roomStats
                ];
                var interval = null;
                var i = 0;
                var showWelcomeMessages = function(){
                    interval = setInterval(function(){
                        if (!map[i]){
                            return clearInterval(interval);
                        }
                        map[i]();
                        i++;
                    }, 2000); // 3 Seconds
                };
                if (Users.register(credentials, socket)){
                    console.log('Sending register confirmation message');
                    Users.sendSystemMessageToUser(credentials.id, CONSTANTS.REGISTER_CONFIRM);
                    showWelcomeMessages();
                } else {
                    console.log('User not registered');
                }
            });
            socket.on(CONSTANTS.UPDATE, function(credentials){
                Users.register(credentials, socket, true);
                socket.emit(CONSTANTS.REGISTER_CONFIRM);
                socket.emit(CONSTANTS.ROOM_STATUS, chatInProgress);
                console.log('Updated user config');
            });
            socket.on(CONSTANTS.JOIN, function(room){
                socket.join(room);
            });
            socket.on(CONSTANTS.DISCONNECTION, function(){
                console.log('user Disconnected');
            });
            socket.on(CONSTANTS.MESSAGE, function(data){
                // Send msg only if we are in chatting mode
                // Get the user and send message to the match
                console.log('New message '+JSON.stringify(data));
                //if (chatInProgress){
                    //Users.sendMessageToUser(data.id, data.message);
                    Users.sendMessageToUserPartner(data.id, data.message);
                //}
            });
            socket.on(CONSTANTS.LIKED, function(data){
                if (!chatInProgress) return;
                var partner  = Users.getUserPartnerID(data.id);
                Users.likeUser(data.id, partner, false);
                if(Users.isMatchExist(data.id, partner)){
                    Users.sendSystemMessageToUser(data.id, CONSTANTS.MATCHED);
                    Users.sendSystemMessageToUser(partner, CONSTANTS.MATCHED);
                    saveMatch(data.id, partner, room);
                } else {
                    Users.likeUser(data.id, partner, true);
                }
            });
            socket.on(CONSTANTS.QUIT, function(username){
                console.log('request to disconnect '+username);
                Users.removeUser(username);
            });
            socket.on(CONSTANTS.INTERNAL, function(username, message){
                Users.sendSystemMessageToUser(username, CONSTANTS.INTERNAL, message);
            });
        });
    };

    bindEvents(nsp);

    populateUsers(room, function(){
        cupid();
    });
};

/**
 * Fetch all the valid rooms
 */
var start = function(){
    console.log('Fetching rooms');
    var sql = "SELECT * from rooms";
    query(sql, function(data){
        console.log('Number of Rooms = '+data.length);
        if (data.length){
            data.forEach(function(item){
                Cupids(item);
            });
            /*
            Validate that rooms arte meant to run at specific time
            data.forEach(function(item){

                if (isValidDate(item.start_date, item.end_date)){
                    var cup = new Cupids(item);
                    updateRoomStatus(item.id, ROOM_STATUS.ACTIVE);
                    runningInstances.push({id:item.id, ref : cup});
                }
                // return new Cupids(item);
            });
            //console.log('Number of running rooms = '+runningInstances.length);
            */
        }
    });
};

http.listen(port, function(){
    console.log('listening on http://localhost:'+port);
});

//////////////////////////////////////// START HERE ////////////////////////////////////////
start();



