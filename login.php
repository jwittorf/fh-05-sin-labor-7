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
$login = array_key_exists("email", $_SESSION);
$verified = false;
$otpCurrent = "";

if ($_POST || $_SESSION) {

    if ($_POST) {
        $email = $_POST["email"];
        $password = $_POST["password"];
        $_SESSION['email'] = $email;
        // TODO: Das sollte man eher nicht machen ...
        $_SESSION['password'] = $password;
    } else {
        $email = $_SESSION['email'];
        $password = $_SESSION['password'];
    }

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
                // Vorbereitung: Secret für User/E-Mail holen
                if ($statement = $db->prepare($sqlGetSecret)) {
                    $statement->bind_param("ss", $email, $password_welldone);
                    if ($statement->execute()) {
                        $statement->bind_result($secret);
                        if ($login = $statement->fetch()) {
                            // Login erfolgreich
                            $otp = TOTP::create($secret);
                            $otp->setLabel("SIN Labor 7");
                            $otpCurrent = $otp->now();

                            // Verifizierung prüfen
                            if (array_key_exists("otp", $_POST)) {
                                $verified = $otp->verify($_POST["otp"]);
                            }
                        }
                    }
                    $statement->close();
                }
            }
        }
    }
}
$error = mysqli_error($db);

if (session_status() === PHP_SESSION_ACTIVE) {
    $twig->addGlobal("session", $_SESSION);
} else {
    $twig->addGlobal("session", []);
}

echo $twig->render('login.html.twig', [
    "login" => $login,
    "verified" => $verified,
    "otp" => $otpCurrent,
    "error" => $error
]);

mysqli_close($db);