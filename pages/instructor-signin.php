<?php include '../includes/header.php'; ?>

  <!-- ========== MAIN CONTENT ========== -->
  <main id="content" role="main">
    <!-- Form -->
    <div class="container content-space-3 content-space-t-lg-4 content-space-b-lg-3">
      <div class="flex-grow-1 mx-auto" style="max-width: 28rem;">
        <!-- Heading -->
        <div class="text-center mb-5 mb-md-7">
          <h1 class="h2">Welcome back</h1>
          <p>Sign in to manage your account.</p>
        </div>
        <!-- End Heading -->

        <!-- Form -->
        <form id="signinForm" class="js-validate needs-validation" novalidate>
          <!-- Form -->
          <div class="mb-4">
            <label class="form-label" for="email">Your email</label>
            <input type="email" class="form-control form-control-lg" name="email" id="email" placeholder="dimentionalcollege@email.com" aria-label="dimentionalcollege@email.com" required>
            <span class="invalid-feedback">Please enter a valid email address.</span>
          </div>
          <!-- End Form -->

          <!-- Form -->
          <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
              <label class="form-label" for="password">Password</label>
              <a class="form-label-link" href="reset-password.php">Forgot Password?</a>
            </div>

            <div class="input-group input-group-merge" data-hs-validation-validate-class>
              <input type="password" class="js-toggle-password form-control form-control-lg" name="password" id="password" placeholder="8+ characters required" aria-label="8+ characters required" required minlength="8">
              <a id="togglePassword" class="input-group-append input-group-text" href="javascript:void(0);">
                <i id="passwordIcon" class="bi-eye"></i>
              </a>
            </div>
            <span class="invalid-feedback">Please enter a valid password.</span>
          </div>
          <!-- End Form -->

          <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary btn-lg" id="signinButton">Sign in</button>
          </div>

          <div class="text-center">
            <p>Don't have an account yet? <a class="link" href="instructor-signup.php">Sign up here</a></p>
          </div>
        </form>

        <script>
          document.getElementById("signinForm").onsubmit = async (e) => {
            e.preventDefault();

            const submitButton = document.getElementById("signinButton");
            const form = e.target;
            const formData = new FormData(form);

            // Disable the button and update the text
            submitButton.disabled = true;
            submitButton.innerHTML = "Signing in...";

            try {
              const response = await fetch('../backend/auth/instuctor-signin.php', {
                method: 'POST',
                body: formData,
              });

              const result = await response.text();

              if (result.trim() === "success") {
                // Redirect upon successful login
                window.location.href = "../users"; // Change to your desired location
              } else {
                alert(result || "There was an issue with your login. Please try again.");
              }
            } catch (error) {
              alert("There was an error processing your request. Please check your network and try again.");
            } finally {
              submitButton.disabled = false;
              submitButton.innerHTML = "Sign in";
            }
          };

          // Toggle password visibility
          document.getElementById("togglePassword").addEventListener("click", () => {
            const passwordInput = document.getElementById("password");
            const passwordIcon = document.getElementById("passwordIcon");

            if (passwordInput.type === "password") {
              passwordInput.type = "text";
              passwordIcon.classList.remove("bi-eye");
              passwordIcon.classList.add("bi-eye-slash");
            } else {
              passwordInput.type = "password";
              passwordIcon.classList.remove("bi-eye-slash");
              passwordIcon.classList.add("bi-eye");
            }
          });
        </script>

        <!-- End Form -->
      </div>
    </div>
    <!-- End Form -->
  </main>
  <!-- ========== END MAIN CONTENT ========== -->

<?php include '../includes/footer.php'; ?>
<?php include '../includes/student-auth'; ?>

