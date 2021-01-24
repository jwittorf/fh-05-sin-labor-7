<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extra\Intl\IntlExtension;
use OTPHP\TOTP;
use Endroid\QrCode\QrCode;

require_once 'vendor/autoload.php';

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);
$twig->addExtension(new IntlExtension());

$otp = TOTP::create(null, 30, 'sha256');

$qrCode = new QrCode($otp->getSecret());
$qrCode->writeFile(__DIR__ . "/qrcode.png");
$qrCodePath = $qrCode->writeDataUri();

echo $twig->render('register.html.twig', ['otp' => $otp->now(), 'secret' => $otp->getSecret(), 'qrCodePath' => $qrCodePath]);