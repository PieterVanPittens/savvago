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

function login($I, $email, $password) {
	// login
	$login = ["email" => $email,"password" => $password];
	$I->sendPost('login', json_encode($login));
	$I->seeResponseCodeIs(200);
	$I->seeResponseIsJson();
	$response = json_decode($I->grabResponse());
	$token = $response->object;
	echo "Token: $token";
	$I->haveHttpHeader("access_token", $token); // this does not work, don't know why, header won't show up in backend
	$I->setHeader("access_token", $token); // this does not work, don't know why, header won't show up in backend
	$I->amBearerAuthenticated($token); // this does not work, don't know why, header won't show up in backend
	$I->setCookie("savvago_token", $token); // this is the workaround: headers won't work but cookie
	
}

?>