<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('cannot login with wrong password');
$I->amOnPage('/');
$I->see('I am a hero');
$I->amOnPage('/login');
$I->see('Login to your savvago account');
$I->fillField('email','admin@admin.de');
$I->fillField('password','wrongpassword');
$I->click('login');
$I->see('Login to your savvago account'); // still not loggedin

