<?php 
$I = new ApiTester($scenario);
$I->wantTo('Create a journey');

login($I, "admin@domain.de","MakeMeRandomAndSecure");

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
