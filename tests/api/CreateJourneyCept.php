<?php 
$I = new ApiTester($scenario);
$I->wantTo('Create a journey');

login($I, "admin@domain.de","MakeMeRandomAndSecure");

// create journey
$journey = ["title"=> getUUID(), "tags"=>"tag1 tag2", "description"=>"hallo was geht"];
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

// create lessons
$lesson = ["title"=> getUUID(), "tags"=>"tag1 tag2", "type"=>"jpg"];
$I->sendPost('lessons', json_encode($lesson));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$response = json_decode($I->grabResponse());
$lesson = $response->object;

// activate lesson
$activate = ["isActive" => "1"];
$I->sendPost('lessons/' . $lesson->lessonId, json_encode($activate));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
