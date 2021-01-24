<?php

require_once "../db-config.php";

$sql = "SELECT ID, password FROM form_data_users";
$result = mysqli_query($db, $sql);

$response = "";
if (!$result) {
    $response = "<p class='alert alert-danger'>SQL Fehler! " . mysqli_error($db) . "</p>";
} else {
    $counter = 0;
    while ($row = mysqli_fetch_row($result)) {
        $id = $row[0];
        $password = $row[1];

        // TODO: aktivieren für unique Salt und etwas mehr Sicherheit
        $salt = md5("s&meK1ndOfSalt,L0l$counter");
        $salt = "";
        $password_hash = md5($password . $salt);

        $sqlUpdate = "UPDATE form_data_users SET password_hash = '" . $password_hash . "', password_salt = '" . $salt . "' WHERE ID = " . $id;
        $resultUpdate = mysqli_query($db, $sqlUpdate);
        if (!$resultUpdate) {
            $response .= "<p class='alert alert-danger'>SQL Fehler! " . mysqli_error($db) . "</p>";
        } else {
            $counter++;
        }
    }
    mysqli_free_result($result);
    $response .= "<p class='alert alert-success'>$counter Passwörter wurden gehashed!</p>";
}

mysqli_close($db);

echo $response;