// ===============================
// MOBILE SIDEBAR
// ===============================

const mobileMenu = document.getElementById("mobileMenu");
const sidebar = document.querySelector(".sidebar");

if (mobileMenu) {
    mobileMenu.addEventListener("click", function () {
        sidebar.classList.toggle("show");
    });
}


// ===============================
// SHOW / HIDE BALANCE
// ===============================

const toggleBalance = document.getElementById("toggleBalance");
const balance = document.getElementById("balance");

let balanceVisible = true;

if (toggleBalance) {

    toggleBalance.addEventListener("click", function () {

        if (balanceVisible) {

            balance.textContent = "₹••••••";
            toggleBalance.textContent = "🙈";

        } else {

            balance.textContent = "₹25,840.00";
            toggleBalance.textContent = "👁";

        }

        balanceVisible = !balanceVisible;

    });

}


// ===============================
// NOTIFICATION
// ===============================

const notificationBtn =
    document.getElementById("notificationBtn");

if (notificationBtn) {

    notificationBtn.addEventListener("click", function () {

        alert(
            "You have 3 new notifications."
        );

    });

}


// ===============================
// ACTIVITY FILTER
// ===============================

const activityFilter =
    document.getElementById("activityFilter");

if (activityFilter) {

    activityFilter.addEventListener("change", function () {

        alert(
            "Activity updated for: " + this.value
        );

    });

}


// ===============================
// QUICK ACTION ANIMATION
// ===============================

const actionCards =
    document.querySelectorAll(".action-card");

actionCards.forEach(function (card) {

    card.addEventListener("click", function () {

        console.log(
            "Opening: " + card.innerText
        );

    });

});