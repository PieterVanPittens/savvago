<?php 
$I = new ApiTester($scenario);
$I->wantTo('see all lessons');
$I->sendGet('lessons');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();

