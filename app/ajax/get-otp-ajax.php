<?php

use OTPHP\TOTP;

require_once '../../vendor/autoload.php';

$secret = (array_key_exists("s", $_GET) && !empty($_GET["s"])) ? $_GET["s"] : "JNM4R6KF62ZWRZD7VEP7T37IG73XVBRUNCCPV6SW2A6QUN76TZ33YGUDNQPP6JHDM2UWYDXUOA4WNO5EKOXSLPSZQDQ23CBJEP4UX5Y";
$otp = TOTP::create($secret);
echo $otp->now();