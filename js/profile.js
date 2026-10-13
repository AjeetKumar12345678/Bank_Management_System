
document.addEventListener("DOMContentLoaded", function () {
    const editButton = document.getElementById("editButton");
    const cancelButton = document.getElementById("cancelButton");
    const profileForm = document.getElementById("profileForm");
    const formActions = document.getElementById("formActions");
    const balanceToggle = document.getElementById("balanceToggle");
    const balanceValue = document.getElementById("balanceValue");
    const copyButton = document.getElementById("copyButton");

    if (!profileForm) return;

    const inputs = [
        ...profileForm.querySelectorAll(
            'input[name="name"], input[name="email"], input[name="phone"]'
        )
    ];

    let originalValues = {};

    // Save the original profile values
    function saveOriginalValues() {
        originalValues = {};

        inputs.forEach(function (input) {
            originalValues[input.name] = input.value;
        });
    }

    // Enable or disable profile editing
    function setEditing(enabled) {
        inputs.forEach(function (input) {
            input.disabled = !enabled;
        });

        if (formActions) formActions.hidden = !enabled;
        if (editButton) editButton.hidden = enabled;
    }

    // Edit Profile button
    if (editButton) {
        editButton.addEventListener("click", function () {
            saveOriginalValues();
            setEditing(true);

            const nameInput = profileForm.querySelector('[name="name"]');
            if (nameInput) nameInput.focus();
        });
    }

    // Cancel editing and restore original values
    if (cancelButton) {
        cancelButton.addEventListener("click", function () {
            inputs.forEach(function (input) {
                input.value = originalValues[input.name] ?? input.value;
            });

            setEditing(false);
        });
    }

    // Validate the form before submitting
    profileForm.addEventListener("submit", function (event) {
        const name = profileForm.querySelector('[name="name"]').value.trim();
        const email = profileForm.querySelector('[name="email"]').value.trim();
        const phone = profileForm.querySelector('[name="phone"]').value.trim();

        if (!name || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
            || !/^[0-9]{10}$/.test(phone)) {
            event.preventDefault();
            alert("Please enter a valid name, email and 10-digit phone number.");
            return;
        }

        if (!confirm("Do you want to save your profile changes?")) {
            event.preventDefault();
        }
    });

    // Show or hide account balance
    let balanceVisible = false;

    if (balanceToggle && balanceValue) {
        balanceToggle.addEventListener("click", function () {
            balanceVisible = !balanceVisible;

            if (balanceVisible) {
                const amount = Number(balanceValue.dataset.balance);

                balanceValue.textContent = "₹" + amount.toLocaleString("en-IN", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                balanceToggle.textContent = "Hide";
                balanceToggle.setAttribute("aria-label", "Hide balance");
            } else {
                balanceValue.textContent = "₹••••••";
                balanceToggle.textContent = "Show";
                balanceToggle.setAttribute("aria-label", "Show balance");
            }
        });
    }

    // Copy account number
    if (copyButton) {
        copyButton.addEventListener("click", async function () {
            const accountNumber = document
                .getElementById("accountNumber")?.textContent.trim();

            if (!accountNumber) return;

            try {
                await navigator.clipboard.writeText(accountNumber);
                copyButton.textContent = "Copied!";
            } catch (error) {
                alert("Copy failed. Please select and copy the account number manually.");
            }

            setTimeout(function () {
                copyButton.textContent = "Copy";
            }, 1800);
        });
    }

    // Initialize page state
    saveOriginalValues();
    setEditing(false);
});
