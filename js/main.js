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

// aktuellen OTP zum Debugging live ausgeben
setInterval(function(){
    sendAjax("app/ajax/get-otp-ajax.php", "otp-placeholder", false);
},1000);