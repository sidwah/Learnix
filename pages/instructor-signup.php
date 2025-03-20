<?php include '../includes/header.php'; ?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
<!-- Form -->
<div class="container content-space-3 content-space-t-lg-4 content-space-b-lg-3">
    <div class="flex-grow-1 mx-auto" style="max-width: 28rem;">
    <!-- Heading -->
    <div class="text-center mb-5 mb-md-7">
        <h1 class="h2">Welcome to Learnix</h1>
        <p>Fill out the form to get started.</p>
    </div>
    <!-- End Heading -->

    <!-- Form -->
    <!-- Updated Form -->
    <form class="js-validate needs-validation" id="signupForm" novalidate>
        <!-- Form Fields -->
        <div class="mb-3">
        <label class="form-label" for="firstName">First Name</label>
        <input type="text" class="form-control form-control-lg" name="firstName" id="firstName" placeholder="Barrock" aria-label="Barrock" required>
        <span class="invalid-feedback">Please enter a valid first name.</span>
        </div>

        <div class="mb-3">
        <label class="form-label" for="lastName">Last Name</label>
        <input type="text" class="form-control form-control-lg" name="lastName" id="lastName" placeholder="Sidwah" aria-label="Sidwah" required>
        <span class="invalid-feedback">Please enter a valid last name.</span>
        </div>

        <div class="mb-3">
        <label class="form-label" for="signupSimpleSignupEmail">Your email</label>
        <input type="email" class="form-control form-control-lg" name="email" id="signupSimpleSignupEmail" placeholder="dimentionalcollege@email.com" aria-label="dimentionalcollege@email.com" required>
        <span class="invalid-feedback">Please enter a valid email address.</span>
        </div>

        <div class="mb-3">
        <label class="form-label" for="signupSimpleSignupPassword">Password</label>
        <div class="input-group input-group-merge">
            <input type="password" class="js-toggle-password form-control form-control-lg" name="password" id="signupSimpleSignupPassword" placeholder="8+ characters required" aria-label="8+ characters required" required>
            <a class="input-group-text" href="javascript:;" onclick="togglePassword('signupSimpleSignupPassword')">
            <i class="bi-eye"></i>
            </a>
        </div>
        <span class="invalid-feedback">Your password is invalid. Please try again.</span>
        </div>

        <div class="mb-3">
        <label class="form-label" for="signupSimpleSignupConfirmPassword">Confirm Password</label>
        <div class="input-group input-group-merge">
            <input type="password" class="form-control form-control-lg" name="confirmPassword" id="signupSimpleSignupConfirmPassword" placeholder="8+ characters required" aria-label="8+ characters required" required>
            <a class="input-group-text" href="javascript:;" onclick="togglePassword('signupSimpleSignupConfirmPassword')">
            <i class="bi-eye"></i>
            </a>
        </div>
        <span class="invalid-feedback">Password does not match.</span>
        </div>

        <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="signupHeroFormPrivacyCheck" name="signupFormPrivacyCheck" required>
        <label class="form-check-label small" for="signupHeroFormPrivacyCheck">I agree to the <a href="privacy-policy.php">Privacy Policy</a></label>
        <span class="invalid-feedback">You must agree to the Privacy Policy.</span>
        </div>

        <div class="d-grid mb-3">
        <button type="submit" id="submitButton" class="btn btn-primary btn-lg">Sign Up</button>
        </div>

        <div class="text-center">
        <p>Already have an account? <a class="link" href="instructor-signin.php">Sign in here</a></p>
        </div>
    </form>

    <!-- Welcome Modal -->
    <div class="modal" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="welcomeModalLabel">Welcome to Learnix!</h5>
            </div>
            <div class="modal-body">
            <p>Thank you for signing up, we're excited to have you on board!</p>
            <p>Your account has been successfully created, and you're all set to start exploring.</p>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="closeModalButton">OK</button>
            </div>
        </div>
        </div>
    </div>

    <!-- Updated Script -->
    <script>
        // Toggle Password Visibility
        function togglePassword(id) {
        const input = document.getElementById(id);
        const icon = input.nextElementSibling.querySelector('i');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = "password";
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
        }

        document.getElementById("signupForm").onsubmit = async (e) => {
        e.preventDefault();

        const form = e.target;
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        const submitButton = document.getElementById("submitButton");
        submitButton.disabled = true;
        submitButton.innerHTML = 'Signing up...';

        const formData = new FormData(form);

        try {
            const response = await fetch('../backend/auth/instructor-signup.php', {
            method: 'POST',
            body: formData,
            });

            const result = await response.text();

            if (response.ok && result === "success") {
            const modal = new bootstrap.Modal(document.getElementById('welcomeModal'));
            modal.show();
            } else {
            alert(result || "There was an issue with your registration. Please try again.");
            }
        } catch (error) {
            alert("There was an error processing your request.");
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Sign Up';
        }
        };

        document.getElementById("closeModalButton").addEventListener("click", () => {
        window.location.href = "../users";
        });
    </script>

    <!-- End Form -->
    </div>
</div>
<!-- End Form -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<?php include '../includes/footer.php'; ?>
<?php include '../includes/student-auth.php'; ?>

