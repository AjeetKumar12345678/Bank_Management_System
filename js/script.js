document.addEventListener("DOMContentLoaded", function () {

    const phoneInput = document.getElementById("phone");

    if (phoneInput) {

        phoneInput.addEventListener("input", function () {

            this.value = this.value.replace(/\D/g, "");

        });

    }

});