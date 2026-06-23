/**
 * validation.js - Client-side form validation for the University Voting System
 * Validates the registration form before submission
 */

// Wait for DOM to load before attaching event listeners
document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("registerForm");
    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault(); // Stop default form submission
            if (validateAll()) {
                this.submit(); // Submit only if all fields pass
            }
        });
    }

    // Real-time validation on input events
    const fields = ["fullname", "regNumber", "email", "password", "confirmPassword"];
    fields.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener("input", () => validateField(id));
    });
});

// ── Validate all fields and return true if all pass ──────────────────
function validateAll() {
    const results = [
        validateField("fullname"),
        validateField("regNumber"),
        validateField("email"),
        validateField("password"),
        validateField("confirmPassword"),
    ];
    return results.every(Boolean); // true only if ALL return true
}

// ── Route to the correct validator based on field ID ─────────────────
function validateField(id) {
    switch (id) {
        case "fullname": return validateName();
        case "regNumber": return validateRegNumber();
        case "email": return validateEmail();
        case "password": return validatePassword();
        case "confirmPassword": return validateConfirm();
    }
}

function validateName() {
    const val = getValue("fullname");
    if (val.length < 3) return showError("fullname", "Name must be at least 3 characters.");
    return clearError("fullname");
}

function validateRegNumber() {
    const val = getValue("regNumber");
    // Format: BSCCS/2024/12345 or similar
    const regex = /^[A-Z]+\/\d{4}\/\d{4,6}$/;
    if (!regex.test(val)) return showError("regNumber", "Enter a valid registration number (e.g. BSCCS/2024/53895).");
    return clearError("regNumber");
}

function validateEmail() {
    const val = getValue("email");
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!regex.test(val)) return showError("email", "Enter a valid email address.");
    return clearError("email");
}

function validatePassword() {
    const val = getValue("password");
    if (val.length < 8) return showError("password", "Password must be at least 8 characters.");
    if (!/[A-Z]/.test(val)) return showError("password", "Password must contain at least one uppercase letter.");
    if (!/[0-9]/.test(val)) return showError("password", "Password must contain at least one number.");
    return clearError("password");
}

function validateConfirm() {
    const pass = getValue("password");
    const confirm = getValue("confirmPassword");
    if (pass !== confirm) return showError("confirmPassword", "Passwords do not match.");
    return clearError("confirmPassword");
}

// ── Helpers ──────────────────────────────────────────────────────────
function getValue(id) { return document.getElementById(id).value.trim(); }
function showError(id, msg) {
    const el = document.getElementById(id + "-error");
    if (el) { el.textContent = msg; el.style.color = "#c0392b"; }
    document.getElementById(id).style.borderColor = "#c0392b";
    return false;
}
function clearError(id) {
    const el = document.getElementById(id + "-error");
    if (el) el.textContent = "";
    document.getElementById(id).style.borderColor = "#2E75B6";
    return true;
}
