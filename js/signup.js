// Show / Hide password buttons (swaps eye.svg <-> eye-off.svg)
var buttons = document.querySelectorAll(".toggle");
for (var i = 0; i < buttons.length; i++) {
  buttons[i].addEventListener("click", function () {
    var input = document.getElementById(this.getAttribute("data-target"));
    var icon = this.querySelector("img");
    var hidden = input.type === "password";
    input.type = hidden ? "text" : "password";
    icon.src = hidden ? "login_assets/eye-off.svg" : "login_assets/eye.svg";
    this.setAttribute("aria-label", hidden ? "Hide password" : "Show password");
  });
}

// Live hint while typing: do both passwords match?
// (PHP still does the real check when the form is submitted.)
var pass = document.getElementById("password");
var confirm = document.getElementById("confirm");
var hint = document.getElementById("confirm-error");
function checkMatch() {
  if (confirm.value.length > 0 && pass.value !== confirm.value) {
    hint.textContent = "Passwords do not match.";
  } else {
    hint.textContent = "";
  }
}
pass.addEventListener("input", checkMatch);
confirm.addEventListener("input", checkMatch);
