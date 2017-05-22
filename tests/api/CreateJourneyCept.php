<?php 
$I = new ApiTester($scenario);
$I->wantTo('Create a journey');

login($I, "admin@domain.de","MakeMeRandomAndSecure");

// create journey
$journey = ["title"=> getUUID(), "tags"=>"tag5 tag6", "description"=>"hallo was geht"];
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

// create jpg lessons
for ($i = 1; $i <= 5; $i++) {
	$lesson = ["title"=> $i . " - " . getUUID(), "tags"=>"tag6 tag5", "type"=>"jpg"];
	$I->sendPost('lessons', json_encode($lesson));
	$I->seeResponseCodeIs(200);
	$I->seeResponseIsJson();
	$response = json_decode($I->grabResponse());
	$lesson = $response->object;
	\PHPUnit_Framework_Assert::assertSame(1, $response->message->type);
	// activate lesson
	$activate = ["isActive" => "1"];
	$I->sendPost('lessons/' . $lesson->lessonId, json_encode($activate));
	$I->seeResponseCodeIs(200);
	$I->seeResponseIsJson();
}

// create yt lessons
for ($i = 1; $i <= 10; $i++) {
	$lesson = ["title"=> $i . " - " . getUUID(), "tags"=>"tag5 tag6", "type"=>"youtube", "link" => "https://www.youtube.com/watch?v=5v5YqE__Nco"];
	$I->sendPost('lessons', json_encode($lesson));
	$I->seeResponseCodeIs(200);
	$I->seeResponseIsJson();
	$response = json_decode($I->grabResponse());
	$lesson = $response->object;
	\PHPUnit_Framework_Assert::assertSame(1, $response->message->type);

	// activate lesson
	$activate = ["isActive" => "1"];
	$I->sendPost('lessons/' . $lesson->lessonId, json_encode($activate));
	$I->seeResponseCodeIs(200);
	$I->seeResponseIsJson();
}
