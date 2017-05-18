<?php 
function getUUID() {
	// source: http://guid.us/GUID/PHP
	mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $uuid = 
            substr($charid, 0, 8)
            .substr($charid, 8, 4)
            .substr($charid,12, 4)
            .substr($charid,16, 4)
            .substr($charid,20,12);
        return $uuid;
}



$I = new ApiTester($scenario);
$I->wantTo('Create a journey');

// login
$login = ["email" => "admin@domain.de","password"=>"MakeMeRandomAndSecure"];
$I->sendPost('login', json_encode($login));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$response = json_decode($I->grabResponse());
$token = $response->object;
$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VySWQiOiIzMyIsImlzcyI6InNhdnZhZ28iLCJpYXQiOjE0OTUwMjk3OTgsImV4cCI6MTQ5NTExNjE5OH0.rcoteAlkRyhtLhbD36eEYW_H0BKskibvh7QvSd5XBDQ";
$I->haveHttpHeader("access_token", $token); // this does not work, don't know why, header won't show up in backend
$I->setHeader("access_token", $token); // this does not work, don't know why, header won't show up in backend
$I->amBearerAuthenticated($token); // this does not work, don't know why, header won't show up in backend
$I->setCookie("savvago_token", $token); // this is the workaround: headers won't work but cookie

// create journey
$journey = ["title"=> getUUID(), "tags"=>"tag1 tag2"];
$I->sendPost('journeys', json_encode($journey));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$response = json_decode($I->grabResponse());
$journey = $response->object;

// activate journey
$activate = ["isActive" => "1"];
$I->sendPost('journeys/' . $journey->journeyId, json_encode($activate));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
