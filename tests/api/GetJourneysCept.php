<?php 
$I = new ApiTester($scenario);
$I->wantTo('see all journeys');
$I->sendGet('journeys');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();

