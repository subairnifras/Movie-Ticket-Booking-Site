const container = document.getElementById('container');
const toggleRegisterBtn = document.getElementById('register'); 
const toggleLoginBtn = document.querySelector('.toggle-panel.toggle-left button'); 

// Toggle panel transitions
if (toggleRegisterBtn) {
    toggleRegisterBtn.addEventListener('click', () => {
        container.classList.add("active");
    });
}

if (toggleLoginBtn) {
    toggleLoginBtn.addEventListener('click', () => {
        container.classList.remove("active");
    });
}

// Form AJAX submission
const signInForm = document.querySelector('.sign-in form');

if (signInForm) {
    signInForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const email = signInForm.querySelector('input[name="email"]').value.trim();
        const password = signInForm.querySelector('input[name="password"]').value;

        if (!email || !password) {
            alert('Please enter both email and password.');
            return;
        }

        // Disable all inputs and buttons while processing
        const inputsAndButtons = signInForm.querySelectorAll('input, button, a');
        inputsAndButtons.forEach(el => {
            el.disabled = true;
            if (el.classList.contains('button-link')) {
                el.style.pointerEvents = 'none';
            }
        });

        try {
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);

            const response = await fetch('login.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Show the success popup animation
                const successMessage = signInForm.querySelector('#successMessage');
                if (successMessage) {
                    successMessage.style.display = 'flex';
                }

                setTimeout(() => {
                    // Check if there is a redirect parameter in the URL
                    const urlParams = new URLSearchParams(window.location.search);
                    const redirectUrl = urlParams.get('redirect') || 'index.php';
                    window.location.href = redirectUrl;
                }, 2000);
            } else {
                alert(data.message);
                inputsAndButtons.forEach(el => {
                    el.disabled = false;
                    if (el.classList.contains('button-link')) {
                        el.style.pointerEvents = 'auto';
                    }
                });
            }
        } catch (error) {
            console.error('Error logging in:', error);
            alert('Server error occurred. Please try again.');
            inputsAndButtons.forEach(el => {
                el.disabled = false;
                if (el.classList.contains('button-link')) {
                    el.style.pointerEvents = 'auto';
                }
            });
        }
    });
}
