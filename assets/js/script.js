// Temporary alert message
function showAlert(message, type="info"){
    let alertBox = document.createElement('div');
    alertBox.className = `alert alert-${type} mt-2`;
    alertBox.innerText = message;
    document.body.prepend(alertBox);
    setTimeout(() => alertBox.remove(), 3000);
}

// Example: showAlert("Attendance recorded successfully!", "success");