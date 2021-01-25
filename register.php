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

$register = false;
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
        $otp = TOTP::create();
        $secret = $otp->getSecret();
        mysqli_stmt_bind_param($statement, "isssss", $admin, $firstname, $email, $password_welldone, $password_salt, $secret);
        $register = mysqli_stmt_execute($statement);

        //    $qrCode = new QrCode($secret);
        $qrCode = new QrCode("otpauth://totp/SIN Labor 7:$email?secret=$secret&issuer=SIN Labor 7");
        $qrCode->writeFile(__DIR__ . "/qrcode.png");
        $qrCodePath = $qrCode->writeDataUri();
    }

    $error = mysqli_error($db);
    mysqli_stmt_close($statement);

}

echo $twig->render('register.html.twig', [
    "qrCodePath" => $qrCodePath,
    "register" => $register,
    "error" => $error
]);

mysqli_close($db);