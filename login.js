const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');

registerBtn.addEventListener('click', () => {
    container.classList.add("active");
});

loginBtn.addEventListener('click', (e) => {
    e.preventDefault();  // prevent default button behavior

    const signInForm = container.querySelector('.sign-in form');
    const email = signInForm.querySelector('input[type="email"]').value.trim();
    const password = signInForm.querySelector('input[type="password"]').value.trim();

    if (!email || !password) {
        alert('Please enter both email and password.');
        return;
    }

    const successMessage = signInForm.querySelector('#successMessage');
    successMessage.style.display = 'flex';  // show the success popup

    // Disable all inputs and buttons while message shows
    signInForm.querySelectorAll('input, button, a').forEach(el => el.disabled = true);

    setTimeout(() => {
        successMessage.style.display = 'none';  // hide success popup
        signInForm.reset();                      // reset form fields
        signInForm.querySelectorAll('input, button, a').forEach(el => el.disabled = false);
    }, 3000);
});
