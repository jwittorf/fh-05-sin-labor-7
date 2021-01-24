<?php

session_start();

require_once "../db-config.php";

$response = "<hr>";

// TODO: einkommentieren, um Zugriffskontrolle zu aktivieren
if (array_key_exists("result", $_SESSION) && $_SESSION["result"] !== $_POST["id"]) {
    die($response . "<p class='alert alert-danger'>Kein Zugriff auf die E-Mail dieser ID!</p>");
}

$sql = "SELECT email FROM form_data_users WHERE ID = " . $_POST["id"];
$result = mysqli_query($db, $sql);

if (!$result) {
    $response .= "<p class='alert alert-danger'>SQL Fehler!</p>";
} else {
    if ($row = mysqli_fetch_row($result)) {
        $response .= "<p class='alert alert-success'>$row[0]</p>";
    } else {
        $response .= "<p class='alert alert-warning'>Keine E-Mail gefunden.</p>";
    }
}

echo $response;