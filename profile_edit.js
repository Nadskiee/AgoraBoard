document.addEventListener('DOMContentLoaded', () => {
    // Get the necessary elements
    const displayView = document.getElementById('profile-display');
    const editForm = document.getElementById('profile-edit-form');
    const editButton = document.getElementById('edit-button');
    const cancelButton = document.getElementById('cancel-button');

    // Get the form input fields (for dynamic reset/pre-fill)
    const formFullName = document.getElementById('full_name');
    const formEmail = document.getElementById('email');
    const formBio = document.getElementById('bio');

    // Get the display elements (to get initial values before form is shown)
    const displayFullName = document.getElementById('display-full-name').textContent;
    const displayEmail = document.getElementById('display-email').textContent;
    const displayBio = document.getElementById('display-bio').textContent;

    /**
     * Toggles the display mode: show form and hide display, or vice-versa.
     * @param {boolean} isEditing - true to show the form, false to show the display.
     */
    function toggleEditMode(isEditing) {
        if (isEditing) {
            // Set form values to current displayed values before showing
            formFullName.value = displayFullName.trim();
            formEmail.value = displayEmail.trim();
            formBio.value = displayBio.trim();

            displayView.style.display = 'none';
            editForm.style.display = 'block';
        } else {
            editForm.style.display = 'none';
            displayView.style.display = 'block';
        }
    }

    // Event listener for the "Edit Profile" button
    editButton.addEventListener('click', () => {
        toggleEditMode(true);
    });

    // Event listener for the "Cancel" button
    cancelButton.addEventListener('click', (e) => {
        e.preventDefault(); // Stop the form from submitting
        toggleEditMode(false);
    });

    // **Optional:** If you want to use pure AJAX for saving instead of a page reload,
    // you would add an event listener here for the form's 'submit' event,
    // prevent the default action, and use the Fetch API or XMLHttpRequest.
    // editForm.addEventListener('submit', (e) => {
    //    e.preventDefault();
    //    // AJAX code to send data to a separate PHP API endpoint (e.g., update.php)
    // });
});