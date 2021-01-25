<?php

use OTPHP\TOTP;

require_once '../../vendor/autoload.php';

$otp = TOTP::create("YEDJB2RX3OYWE47QSU5HCUE3MOYINZDKZZZ34PR3K27TF2LGV7R4XHCC2VCAOP542GWUKXGVW43LND3E4ZJBSHYTN54WNBBZNSWW5MY", 30, 'sha256');
$now = $otp->now();

echo $now;
echo " | ";
echo $otp->verify($now);