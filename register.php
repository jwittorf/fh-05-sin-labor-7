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
    if ($statement = $db->prepare($sqlInsert)) {
        $otp = TOTP::create();
        $secret = $otp->getSecret();
        $statement->bind_param("isssss", $admin, $firstname, $email, $password_welldone, $password_salt, $secret);
        $register = $statement->execute();

        //    $qrCode = new QrCode($secret);
        $qrCode = new QrCode("otpauth://totp/SIN Labor 7:$email?secret=$secret&issuer=$firstname");
        $qrCode->writeFile(__DIR__ . "/qrcode.png");
        $qrCodePath = $qrCode->writeDataUri();

        $error = mysqli_error($db);
        $statement->close();
    }

}

echo $twig->render('register.html.twig', [
    "qrCodePath" => $qrCodePath,
    "register" => $register,
    "error" => $error
]);

mysqli_close($db);