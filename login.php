<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extra\Intl\IntlExtension;
use OTPHP\TOTP;

require_once 'vendor/autoload.php';
require_once "app/db-config.php";

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader, ['cache' => false]);
$twig->addExtension(new IntlExtension());

$secret = "";
$error = "";
$login = false;
$verified = false;
$otpCurrent = "";

if ($_POST) {

    $email = $_POST["email"];
    $password = $_POST["password"];

    $sqlGetSalt = "SELECT password_salt FROM form_data_users WHERE email = ?";
    $statementSalt = mysqli_prepare($db, $sqlGetSalt);

    // Vorbereitung: Salt für den User/E-Mail holen
    if ($statementSalt) {
        mysqli_stmt_bind_param($statementSalt, "s", $email);
        if (mysqli_stmt_execute($statementSalt)) {
            mysqli_stmt_bind_result($statementSalt, $salt);
            if (mysqli_stmt_fetch($statementSalt)) {
//                echo "<pre>Salt:";
//                var_dump($salt);
//                echo "</pre>";
                mysqli_stmt_close($statementSalt);

                $password_salt = $salt;
                $password_welldone = sha1($password . $password_salt . $pepper);

                $sqlGetSecret = "SELECT secret FROM form_data_users WHERE email = ? AND password_hash = ?";
                $statement = mysqli_prepare($db, $sqlGetSecret);
                // Vorbereitung: Secret für User/E-Mail holen
                if ($statement) {
                    mysqli_stmt_bind_param($statement, "ss", $email, $password_welldone);
                    if (mysqli_stmt_execute($statement)) {
                        mysqli_stmt_bind_result($statement, $secret);
                        if (mysqli_stmt_fetch($statement)) {
//                            echo "<pre>Secret:";
//                            var_dump($secret);
//                            echo "</pre>";

                            $login = true;
                            $otp = TOTP::create($secret, 30, 'sha256');
                            $otp->setLabel("SIN Labor 7");
                            $otpCurrent = $otp->now();
//                            echo "<pre>Now/OTP:";
//                            var_dump($otpCurrent);
//                            echo "</pre>";

                            if (array_key_exists("otp", $_POST)) {
                                $verified = $otp->verify($_POST["otp"]);
                            }
                        }
                    }
                }
                mysqli_stmt_close($statement);
            }
        }
    }
}
$error = mysqli_error($db);

echo $twig->render('login.html.twig', [
    "login" => $login,
    "verified" => $verified,
    "otp" => $otpCurrent,
    "error" => $error
]);

mysqli_close($db);