<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extra\Intl\IntlExtension;
use OTPHP\TOTP;
use Endroid\QrCode\QrCode;

require_once 'vendor/autoload.php';
require_once "app/db-config.php";

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader, ['cache' => false]);
$twig->addExtension(new IntlExtension());

$otp = TOTP::create(null, 30, 'sha256');
$secret = $otp->getSecret();

$resultInsert = false;
$error = "";
$qrCodePath = "";

if ($_POST) {

    $admin = 0;
    $email = $_POST["email"];
    $firstname = $_POST["firstname"];
    $password = $_POST["password"];
    $password_salt = base64_encode("s&meK1ndOfSalt,L0l");
    $password_welldone = sha1($password . $password_salt . $pepper);

    $sqlInsert = "INSERT INTO form_data_users(admin, firstname, email, password_hash, password_salt, secret) VALUES(?, ?, ?, ?, ?, ?)";
    $statement = mysqli_prepare($db, $sqlInsert);
    if ($statement) {
        mysqli_stmt_bind_param($statement, "isssss", $admin, $firstname, $email, $password_welldone, $password_salt, $secret);
        $resultInsert = mysqli_stmt_execute($statement);
    }

    $error = mysqli_error($db);
    mysqli_stmt_close($statement);

//    $qrCode = new QrCode($secret);
    $qrCode = new QrCode("otpauth://totp/SIN Labor 7:$email?secret=$secret&issuer=SIN Labor 7");
    $qrCode->writeFile(__DIR__ . "/qrcode.png");
    $qrCodePath = $qrCode->writeDataUri();
}

echo $twig->render('register.html.twig', [
    "qrCodePath" => $qrCodePath,
    "resultInsert" => $resultInsert,
    "error" => $error
]);

mysqli_close($db);