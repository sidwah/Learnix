<div
  class="offcanvas offcanvas-end"
  tabindex="-1"
  id="offcanvasNavbarSignup">
  <div class="offcanvas-header justify-content-end border-0 pb-0">
    <button
      type="button"
      class="btn-close text-reset"
      data-bs-dismiss="offcanvas"
      aria-label="Close"></button>
  </div>

  <div class="offcanvas-body">
    <!-- Sign in -->
    <?php include("../includes/student-signin.php") ?>

    <!-- End Sign in -->



    <!-- Sign up -->
    <?php include("../includes/student-signup.php") ?>

    <!-- End Sign up -->



    <!-- Forgot Password -->
        <?php include("../includes/student-forgot-password.php") ?>
    <!-- End Forgot Password -->

  </div>
</div>


<script>
  function showForm(formType) {
    // Hide all forms
    document.getElementById("loginOffcanvasFormLogin").style.display = "none";
    document.getElementById("loginOffcanvasFormSignup").style.display = "none";
    document.getElementById("loginOffcanvasFormResetPassword").style.display = "none";

    // Show the requested form
    if (formType === "login") {
      document.getElementById("loginOffcanvasFormLogin").style.display = "block";
    } else if (formType === "signup") {
      document.getElementById("loginOffcanvasFormSignup").style.display = "block";
    } else if (formType === "reset") {
      document.getElementById("loginOffcanvasFormResetPassword").style.display = "block";
    }
  }

  function showForm(formType) {
    // All forms
    const forms = [
      "loginOffcanvasFormLogin",
      "loginOffcanvasFormSignup",
      "loginOffcanvasFormResetPassword",
    ];

    // Hide all forms
    forms.forEach((formId) => {
      const form = document.getElementById(formId);
      if (form) {
        form.style.display = "none";
        form.style.opacity = "0";
      }
    });

    // Show the requested form
    const selectedForm = document.getElementById(`loginOffcanvasForm${formType}`);
    if (selectedForm) {
      selectedForm.style.display = "block";
      setTimeout(() => {
        selectedForm.style.opacity = "1"; // Smooth opacity transition
      }, 50); // Delay to ensure `display: block` takes effect before applying opacity
    }
  }

  // Add event listeners to links for smooth form transitions
  document.querySelectorAll(".js-animation-link").forEach((link) => {
    link.addEventListener("click", (event) => {
      const targetSelector = event.target.getAttribute("data-hs-show-animation-options");
      const targetFormType = JSON.parse(targetSelector).targetSelector.split("#loginOffcanvasForm")[1];
      showForm(targetFormType);
    });
  });
</script>