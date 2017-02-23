<?php 
$I = new AcceptanceTester($scenario);
$I = new AcceptanceTester($scenario);
$I->wantTo('ensure that frontpage works');
$I->amOnPage('/');
$I->see('I am a hero');
$I->amOnPage('/login');
$I->see('Login to your savvago account');
$I->fillField('email','admin@admin.de');
$I->fillField('password','admin');
$I->click('login');
$token = $I->grabCookie('savvago_token');
$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VySWQiOiIzMCIsImlzcyI6InNhdnZhZ28iLCJpYXQiOjE0ODc4ODQ5NDksImV4cCI6MTQ4Nzk3MTM0OX0.kKKiM5H-WfozMTpBOrncvQkfGvNeQGvH7GqYgZGkHL0";
$I->setCookie('savvago_token', $token);
$I->amOnPage('/');
$I->see('Account Settings');


