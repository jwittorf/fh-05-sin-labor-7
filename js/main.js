function sendAjax(targetUrl, responseHtmlId, form) {
    // Daten aus dem Formular holen und für AJAX vorbereiten
    let data = [];
    if (form) {
        for (let i = 0; i < form.elements.length; i++) {
            var element = form.elements[i];
            if (element.name && element.value && element.type !== "submit") {
                data.push(element.name + "=" + element.value);
            }
        }
    }

    // AJAX Anfrage zusammenbauen
    ajax = new XMLHttpRequest();
    // Defnition des Umgangs mit der Anfrage
    ajax.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            document.getElementById(responseHtmlId).innerHTML = this.responseText;
        } else {
            // Anfrage wird verschickt
            if (form) {
                document.getElementById(responseHtmlId).innerHTML = '<p class="alert alert-warning">Sende Daten ...</p>';
            }
        }
    }
    // Anfrage definieren
    ajax.open("POST", targetUrl, true)
    ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    // Anfrage abgeschicken und Daten-String für urlencoded wandeln
    ajax.send(data.toString().replaceAll(",", "&"));
}

// Daten anzeigen
if (document.getElementById("form-show")) {
    document.getElementById("form-show").onsubmit = function (event) {
        // AJAX Anfrage zusammenbauen
        ajax = new XMLHttpRequest();
        // Defnition des Umgangs mit der Anfrage
        ajax.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                // Anfrage abgeschlossen
                document.getElementById("response-show-wrapper").innerHTML = this.responseText;
                document.getElementById("form-show").style.display = "none";
            } else {
                // Anfrage wird verschickt
                document.getElementById("response-show-wrapper").innerHTML = '<p class="alert alert-warning">Sende Daten ...</p>';
            }
        }
        // Anfrage definieren
        ajax.open("POST", "app/list-db-values.php", true)
        ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        // Anfrage abgeschicken und Daten-String für urlencoded wandeln
        ajax.send();
        // Verhindern, dass der Browser das Formular abschickt
        return false;
    }
}

// A1 SQL Injection
if (document.getElementById("form-search-firstname")) {
    document.getElementById("form-search-firstname").onsubmit = function (event) {
        // Verhindern, dass der Browser das Formular abschickt
        event.preventDefault();
        sendAjax("app/form-search-by-firstname-ajax.php", "response-search-firstname-wrapper", this);
        // Verhindern, dass der Browser das Formular abschickt
        return false;
    };
}

// A2 Fehler in der Authentifizierung
if (document.getElementById("form-login")) {
    document.getElementById("form-login").onsubmit = function (event) {
        // Verhindern, dass der Browser das Formular abschickt
        event.preventDefault();
        sendAjax("app/form-login-ajax.php", "response-login-wrapper", this);
        // Verhindern, dass der Browser das Formular abschickt
        return false;
    }
}

// A3 Vorbereitung Verlust der Vertraulichkeit sensibler Daten
if (document.getElementById("btn-hash-salt-passwords")) {
    document.getElementById("btn-hash-salt-passwords").onclick = function (event) {
        // Verhindern, dass der Browser das Formular abschickt
        event.preventDefault();
        sendAjax("app/hash-salt-passwords-ajax.php", "response-hash-salt-passwords-wrapper", false);
        // Verhindern, dass der Browser das Formular abschickt
        return false;
    }
}


// A5 Fehler in der Zugriffskontrolle
if (document.getElementById("form-show-email")) {
    document.getElementById("form-show-email").onsubmit = function (event) {
        // Verhindern, dass der Browser das Formular abschickt
        event.preventDefault();
        sendAjax("app/get-email-ajax.php", "response-form-show-email-wrapper", this);
        // Verhindern, dass der Browser das Formular abschickt
        return false;
    }
}

// aktuellen OTP zum Debugging live ausgeben
setInterval(function(){
    sendAjax("app/ajax/get-otp-ajax.php", "otp-placeholder", false);
},1000);