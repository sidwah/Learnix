 
<!-- ========== FOOTER ========== -->
<footer class="bg-dark">
    <div class="container pb-1 pb-lg-5">
        <div class="row content-space-t-2">
            <div class="col-lg-3 mb-7 mb-lg-0">
                <!-- Logo -->
                <div class="mb-5">
                    <a class="navbar-brand" href="../index.php" aria-label="Learnix">
                        <img class="navbar-brand-logo" src="../assets/svg/logos/logo-white.svg" alt="Learnix Logo" />
                    </a>
                </div>
                <!-- End Logo -->

                <!-- Contact Info -->
                <ul class="list-unstyled list-py-1">
                    <li>
                        <a class="link-sm link-light" href="#">
                            <i class="bi-geo-alt-fill me-1"></i> Learnix, East Legon, Accra, Ghana
                        </a>
                    </li>
                    <li>
                        <a class="link-sm link-light" href="tel:+233123456789">
                            <i class="bi-telephone-inbound-fill me-1"></i> +233 25 779 9736
                        </a>
                    </li>
                </ul>
                <!-- End Contact Info -->
            </div>
            <!-- End Col -->

            <div class="col-sm mb-7 mb-sm-0">
                <h5 class="text-white mb-3">Company</h5>
                <!-- List -->
                <ul class="list-unstyled list-py-1 mb-0">
                    <li><a class="link-sm link-light" href="about-us.php">About Us</a></li>
                    <li>
                        <a class="link-sm link-light" href="instructor-signup.php">
                            Careers
                            <span class="badge bg-warning text-dark rounded-pill ms-1">We're hiring</span>
                        </a>
                    </li>
                </ul>
                <!-- End List -->
            </div>
            <!-- End Col -->

            <div class="col-sm mb-7 mb-sm-0">
                <h5 class="text-white mb-3">Resources</h5>
                <!-- List -->
                <ul class="list-unstyled list-py-1 mb-0">
                    <li><a class="link-sm link-light" href="#">Help Center</a></li>
                    <li><a class="link-sm link-light" href="#">Your Account</a></li>
                </ul>
                <!-- End List -->
            </div>
            <!-- End Col -->

            <div class="col-sm mb-7 mb-sm-0">
                <h5 class="text-white mb-3">Legal</h5>
                <!-- List -->
                <ul class="list-unstyled list-py-1 mb-0">
                    <li>
                        <a class="link-sm link-light" href="privacy-policy.php">Privacy Policy</a>
                    </li>
                    <li>
                        <a class="link-sm link-light" href="terms-and-conditions.php">Terms of Service</a>
                    </li>
                </ul>
                <!-- End List -->
            </div>
            <!-- End Col -->
        </div>
        <!-- End Row -->

        <div class="border-top border-white-10 my-7"></div>

        <div class="row mb-7 justify-content-between">
            <div class="col-sm mb-3 mb-sm-0">
                <!-- Socials -->
                <ul class="list-inline mb-0">
                </ul>
                <!-- End Socials -->
            </div>

            <div class="col-sm-auto">
                <!-- Socials -->
                <ul class="list-inline mb-0">
                    <li class="list-inline-item">
                        <a class="btn btn-soft-light btn-xs btn-icon" href="#">
                            <i class="bi-facebook"></i>
                        </a>
                    </li>

                    <li class="list-inline-item">
                        <a class="btn btn-soft-light btn-xs btn-icon" href="#">
                            <i class="bi-google"></i>
                        </a>
                    </li>

                    <li class="list-inline-item">
                        <a class="btn btn-soft-light btn-xs btn-icon" href="#">
                            <i class="bi-twitter"></i>
                        </a>
                    </li>

                    <li class="list-inline-item">
                        <a class="btn btn-soft-light btn-xs btn-icon" href="#">
                            <i class="bi-github"></i>
                        </a>
                    </li>
                </ul>
                <!-- End Socials -->
            </div>
        </div>

        <!-- Copyright -->
        <div class="w-md-85 text-lg-center mx-lg-auto">
            <p class="text-white-50 small">
                &copy; Learnix. <span id="currentYear"></span> All rights reserved.
            </p>
        </div>

        <script>
            // Get the current year
            const currentYear = new Date().getFullYear();
            // Set the text content of the element with ID 'currentYear'
            document.getElementById("currentYear").textContent = currentYear;
        </script>
        <!-- End Copyright -->
    </div>
</footer>



<!-- ========== END FOOTER ========== -->

<!-- ========== SECONDARY CONTENTS ========== -->
<!-- Go To -->
<a
    class="js-go-to go-to position-fixed"
    href="javascript:;"
    style="visibility: hidden"
    data-hs-go-to-options='{
    "offsetTop": 700,
    "position": {
        "init": {
        "right": "2rem"
        },
        "show": {
        "bottom": "2rem"
        },
        "hide": {
        "bottom": "-2rem"
        }
    }
    }'
>
    <i class="bi-chevron-up"></i>
</a>

    <!-- ========== END SECONDARY CONTENTS ========== -->

    
<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- JS Implementing Plugins -->
<script src="../assets/js/vendor.min.js"></script>

<!-- JS Learnix -->
<script src="../assets/js/theme.min.js"></script>

    
<!-- Bootstrap 5 JS (Ensure you include the Bootstrap 5 JavaScript for modal functionality) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- JS Plugins Init. -->
<script>
    (function () {
    // INITIALIZATION OF HEADER
    // =======================================================
    new HSHeader("#header").init();

    // INITIALIZATION OF MEGA MENU
    // =======================================================
    new HSMegaMenu(".js-mega-menu", {
        desktop: {
        position: "left",
        },
    });

    // INITIALIZATION OF SHOW ANIMATIONS
    // =======================================================
    new HSShowAnimation(".js-animation-link");

    // INITIALIZATION OF BOOTSTRAP VALIDATION
    // =======================================================
    HSBsValidation.init(".js-validate", {
        onSubmit: (data) => {
        data.event.preventDefault();
        // alert("Submited");
        },
    });

    // INITIALIZATION OF BOOTSTRAP DROPDOWN
    // =======================================================
    HSBsDropdown.init();

    // INITIALIZATION OF GO TO
    // =======================================================
    new HSGoTo(".js-go-to");

    // INITIALIZATION OF TEXT ANIMATION (TYPING)
    // =======================================================
    HSCore.components.HSTyped.init(".js-typedjs");

    // INITIALIZATION OF SWIPER
    // =======================================================
    var swiper = new Swiper(".js-swiper-course-hero", {
        preloaderClass: "custom-swiper-lazy-preloader",
        navigation: {
        nextEl: ".js-swiper-course-hero-button-next",
        prevEl: ".js-swiper-course-hero-button-prev",
        },
        slidesPerView: 1,
        loop: 1,
        breakpoints: {
        380: {
            slidesPerView: 2,
            spaceBetween: 15,
        },
        580: {
            slidesPerView: 3,
            spaceBetween: 15,
        },
        768: {
            slidesPerView: 4,
            spaceBetween: 15,
        },
        1024: {
            slidesPerView: 6,
            spaceBetween: 15,
        },
        },
        on: {
        imagesReady: function (swiper) {
            const preloader = swiper.el.querySelector(
            ".js-swiper-course-hero-preloader"
            );
            preloader.parentNode.removeChild(preloader);
        },
        },
    });
    })();
</script>