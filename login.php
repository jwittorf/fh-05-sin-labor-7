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
$posted = false;
$otpCurrent = "";

if ($_POST) {
    $posted = true;

    $email = $_POST["email"];
    $password = $_POST["password"];

    $sqlGetSalt = "SELECT password_salt FROM form_data_users WHERE email = ?";
    // Vorbereitung: Salt für den User/E-Mail holen
    if ($statementSalt = $db->prepare($sqlGetSalt)) {
        $statementSalt->bind_param("s", $email);
        if ($statementSalt->execute()) {
            $statementSalt->bind_result($salt);
            if ($statementSalt->fetch()) {
                $statementSalt->close();

                $password_salt = $salt;
                $password_welldone = sha1($password . $password_salt . $pepper);

                $sqlGetSecret = "SELECT secret FROM form_data_users WHERE email = ? AND password_hash = ?";
                $statement = mysqli_prepare($db, $sqlGetSecret);
                // Vorbereitung: Secret für User/E-Mail holen
                if ($statement) {
                    mysqli_stmt_bind_param($statement, "ss", $email, $password_welldone);
                    if (mysqli_stmt_execute($statement)) {
                        mysqli_stmt_bind_result($statement, $secret);
                        if ($login = mysqli_stmt_fetch($statement)) {
                            $otp = TOTP::create($secret);
                            $otp->setLabel("SIN Labor 7");
                            $otpCurrent = $otp->now();

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
    "posted" => $posted,
    "otp" => $otpCurrent,
    "error" => $error
]);

mysqli_close($db);