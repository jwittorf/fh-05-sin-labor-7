<?php

// TODO: use session cookie for security, short lifetime just for demo, could be 1 day in reality 86400
//session_start();
//session_start(['cookie_lifetime' => 30]);
session_start(['cookie_lifetime' => 86400]);

require_once "../db-config.php";

// Das Salting wird "nur" für A3: Verlust der Vertraulichkeit sensibler Daten verwendet
$sqlSalt = "SELECT password_salt FROM form_data_users WHERE email = '" . $_POST["email"] . "'";
$resultSalt = mysqli_query($db, $sqlSalt);
$salt = mysqli_fetch_row($resultSalt);
$pepper = \Safe\openssl_random_pseudo_bytes(16);

if (!$salt) {
    $_SESSION["code"] = -1; // fail
    $_SESSION["result"] = mysqli_error($db);
    $_SESSION["loggedIn"] = false;
    die("<p class='alert alert-danger'>SQL Fehler beim Hash!</p>");
}
// Verbesserter Login mit Hash und Salt
$sql = "SELECT ID, firstname, admin from form_data_users WHERE email = '" . $_POST["email"] . "' AND password_hash = '" . sodium_crypto_pwhash_scryptsalsa208sha256_str($_POST["password"] . $salt[0] . $pepper) . "'";

// Einfacher Login mit Plaintext
//$sql = "SELECT ID, firstname from form_data_users WHERE email = '" . $_POST["email"] . "' AND password = '" . $_POST["password"] . "'";
$result = mysqli_query($db, $sql);
// TODO: set true for security
$regenerateSession = true;

$response = "";
if (!$result) {
    $_SESSION["code"] = -1; // fail
    $_SESSION["result"] = mysqli_error($db);
    $_SESSION["loggedIn"] = false;
    $response = "<p class='alert alert-danger'>SQL Fehler!</p>";
} else {
    $_SESSION["code"] = 0; // success
    if ($row = mysqli_fetch_row($result)) {
        $_SESSION["result"] = $row[0];
        $_SESSION["loggedIn"] = true;
        $_SESSION["firstname"] = $row[1];
        $_SESSION["admin"] = $row[2];
        if ($regenerateSession) {
            session_regenerate_id();
            $response = "<p class='alert alert-success'>Anmeldung erfolgreich! <strong>Seite neu laden, um die neue Session ID anzuzeigen (steht schon in den Dev-Tools).</strong></p>";
        } else {
            $response = "<p class='alert alert-warning'>Anmeldung erfolgreich! <strong>Die Session ID wurde <span class='text-danger'>NICHT</span> neu erzeugt (unsicher!)</strong></p>";
        }
    } else {
        $_SESSION["result"] = null;
        $_SESSION["loggedIn"] = false;
        $response = "<p class='alert alert-danger'>Anmeldung fehlgeschlagen!</p>";
    }
}
mysqli_free_result($result);
mysqli_close($db);

echo $response;