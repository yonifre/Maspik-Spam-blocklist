document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("frmContact").addEventListener("submit", function(event) {
        event.preventDefault(); // Prevent the form from submitting normally

        // Your AJAX logic goes here
        var formData = new FormData(this);
        formData.append('action', 'handle_contact_form'); // Add action to formData

        fetch(ajax_object.ajax_url, {
            method: "POST",
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById("statusMessage").innerHTML = "<p class='" + data.status + "Message'>" + data.message + "</p>";
            // Add innerHTML and class based on data value for each input element
            var noteNameElement = document.getElementById("note-name");
            noteNameElement.innerHTML = data.name;
            noteNameElement.parentElement.classList.toggle("red", data.name);
            
            var noteEmailElement = document.getElementById("note-email");
            noteEmailElement.innerHTML = data.email;
            noteEmailElement.parentElement.classList.toggle("red", data.email);
            
            var noteTelElement = document.getElementById("note-tel");
            noteTelElement.innerHTML = data.tel;
            noteTelElement.parentElement.classList.toggle("red", data.tel);
            
            var noteTextareaElement = document.getElementById("note-textarea");
            noteTextareaElement.innerHTML = data.textarea;
            noteTextareaElement.parentElement.classList.toggle("red", data.textarea);
        })
        .catch(error => {
            console.error("Error:", error);
            document.getElementById("statusMessage").innerHTML = "<p class='errorMessage'>An error occurred. Please try again later.</p>";
        });
    });
});
