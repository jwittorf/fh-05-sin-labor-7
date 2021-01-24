<?php

// Session initialisieren, nutzt die aktuelle Session
session_start();

// Session Variablen löschen
$_SESSION = [];

// Session Cookie löschen
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Session löschen
session_destroy();

// Sessiondatei auf dem Server löschen
 unlink(session_save_path() . "/sess_" . session_id());
// TODO: Löschen alter Sessions auf dem Server per Crontab implementieren oder Garbage Collection verwenden

header("Location: " . $_SERVER["HTTP_REFERER"]);