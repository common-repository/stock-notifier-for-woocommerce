var stock_notifier_user_plugin_url = stock_notifier_isValidnumber.plugin_Urls;
var stock_notifier_user_input = document.querySelector("#stock_notifier_phone"),
  stock_notifier_user_country_code = document.querySelector("#country_code"),
  stock_notifier_user_ererrorMsg = document.querySelector("#error-msg"),
  stock_notifier_user_validMsg = document.querySelector("#valid-msg"),
  stock_notifier_user_validation = document.querySelector(
    ".profile-php ,#profile-page ,#submit"
  );
// var data=input.value.trim();
// console.log(data);
// here, the index maps to the error code returned from getValidationError - see readme
var stock_notifier_user_errorMap = [
  "Invalid number",
  "Invalid country code",
  "Too short",
  "Too long",
  "Invalid number",
];

// initialise plugin
var stock_notifier_user_iti = window.intlTelInput(stock_notifier_user_input, {
  utilsScript: stock_notifier_user_plugin_url + "assets/utils.js",
});

var reset = function () {
  stock_notifier_user_input.classList.remove("error");
  stock_notifier_user_ererrorMsg.innerHTML = "";
  stock_notifier_user_ererrorMsg.classList.add("hide");
  stock_notifier_user_validMsg.classList.add("hide");
};
stock_notifier_user_input.addEventListener("blur", function () {
  reset();
  if (stock_notifier_user_input.value.trim()) {
    if (stock_notifier_user_iti.isValidNumber()) {
      stock_notifier_user_validMsg.classList.remove("hide");
    } else {
      stock_notifier_user_input.classList.add("error");
      var errorCode = stock_notifier_user_iti.getValidationError();
      stock_notifier_user_ererrorMsg.innerHTML =
        stock_notifier_user_errorMap[errorCode];
      stock_notifier_user_ererrorMsg.classList.remove("hide");
    }
  }
});

stock_notifier_user_validation.addEventListener("click", function () {
  if (stock_notifier_user_input.value.trim()) {
    if (stock_notifier_user_iti.isValidNumber()) {
      return true;
    } else {
      stock_notifier_user_input.value = "";
      stock_notifier_user_country_code.value = "";
    }
  }
});
// on blur: validate

// on keyup / change flag: reset
stock_notifier_user_input.addEventListener("change", reset);
stock_notifier_user_input.addEventListener("keyup", reset);
