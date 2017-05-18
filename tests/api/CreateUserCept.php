<?php 
$I = new ApiTester($scenario);
$I->wantTo('Create a user');


// create user (anonymous)
$user = ["displayName" => getUUID(), "email" => getUUID()."@dev.de", "password" => "hello"];
$I->sendPost('users', json_encode($user));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$response = json_decode($I->grabResponse());
$user = $response->object->user;

// login as admin
login($I, "admin@domain.de","MakeMeRandomAndSecure");


// activate user
$activate = ["isActive" => "1"];
$I->sendPost('users/' . $user->userId . '/activate', json_encode($activate));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
