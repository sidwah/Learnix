<?php include '../includes/header.php'; ?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Hero -->
    <div class="container content-space-2">
        <div class="row justify-content-md-between align-items-sm-center">
            <div class="col-8 col-sm-6 col-md-5 mb-5 mb-sm-0">
                <img
                    class="img-fluid"
                    src="../assets/svg/illustrations/oc-collaboration.svg"
                    alt="Image Description" />
            </div>
            <!-- End Col -->

            <div class="col-sm-6">
                <div class="mb-5">
                    <h1 class="display-4 mb-3">
                        Empower your
                        <br>
                        <span class="text-primary text-highlight-warning">
                            <span
                                class="js-typedjs"
                                data-hs-typed-options='{
                    "strings": ["students.", "teaching potential.", "impact."],
                    "typeSpeed": 90,
                    "loop": true,
                    "backSpeed": 30,
                    "backDelay": 2500
                    }'></span>
                        </span>
                    </h1>
                    <p class="lead">
                        With our platform, you can inspire learners, expand your reach, and
                        share your expertise with a global audience.
                    </p>
                </div>

                <div class="d-grid d-md-flex gap-3 align-items-md-center">
                    <a class="btn btn-primary btn-transition" href="../instructor/">Join as an Instructor</a>

                    <a
                        class="video-player video-player-btn"
                        href="https://www.youtube.com/watch?v=KRWRX5xbeMU"
                        role="button"
                        data-fslightbox="youtube-video">
                        <span class="video-player-icon shadow-sm me-2">
                            <i class="bi-play-fill"></i>
                        </span>
                        Learn More
                    </a>

                </div>
            </div>

            <!-- End Col -->
        </div>
        <!-- End Row -->
    </div>
    <!-- End Hero -->

    <!-- Feature 1 -->
    <!-- End Feature 1  -->


    <!-- Testimonials -->
    <div class="overflow-hidden content-space-2">
        <div
            class="position-relative bg-light text-center rounded-2 zi-2 mx-3 mx-md-10">
            <div class="container content-space-2">
                <div class="text-center mb-5">
                    <img
                        class="avatar avatar-lg avatar-4x3"
                        src="../assets/svg/illustrations/oc-person-2.svg"
                        alt="Illustration" />
                </div>

                <!-- Blockquote -->
                <figure class="w-md-75 text-center mx-md-auto">
                    <blockquote class="blockquote mb-7">
                        “ Learnix has transformed my learning experience. The variety of courses and the interactive tools have helped me master new skills at my own pace. It’s an amazing platform for anyone serious about advancing their education. I’m excited to continue my journey! ”
                    </blockquote>

                    <figcaption class="blockquote-footer mt-2">
                        John Doe
                        <span class="blockquote-footer-source">Satisfied Student</span>
                    </figcaption>
                </figure>
                <!-- End Blockquote -->

            </div>

            <!-- SVG Shape -->
            <figure class="position-absolute top-0 start-0 mt-10 ms-10">
                <svg
                    width="70"
                    height="70"
                    viewBox="0 0 200 200"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M60.6655 74.9992C80.4557 74.9992 96.4988 58.9561 96.4988 39.1659C96.4988 19.3757 80.4557 3.33252 60.6655 3.33252C40.8753 3.33252 24.8322 19.3757 24.8322 39.1659C24.8322 58.9561 40.8753 74.9992 60.6655 74.9992Z"
                        stroke="#97a4af"
                        stroke-width="5"
                        stroke-miterlimit="10" />
                    <path
                        d="M158.5 197.5C168.165 197.5 176 189.665 176 180C176 170.335 168.165 162.5 158.5 162.5C148.835 162.5 141 170.335 141 180C141 189.665 148.835 197.5 158.5 197.5Z"
                        stroke="#97a4af"
                        stroke-width="5"
                        stroke-miterlimit="10" />
                </svg>
            </figure>
            <!-- End SVG Shape -->

            <!-- SVG Shape -->
            <figure
                class="position-absolute bottom-0 end-0 mb-n7 me-n7"
                style="width: 10rem">
                <img
                    class="img-fluid"
                    src="../assets/svg/components/dots.svg"
                    alt="Image Description" />
            </figure>
            <!-- End SVG Shape -->
        </div>
    </div>
    <!-- End Testimonials -->

    <!-- CTA -->
    <div class="container content-space-b-2">
        <div
            class="text-center bg-img-start py-6"
            style="
        background: url(../assets/svg/components/shape-6.svg) center
            no-repeat;
        ">
            <div class="mb-5">
                <h2>Find the right learning path for you</h2>
                <p>Answer a few questions and match your goals to our programs.</p>
            </div>

            <a class="btn btn-primary btn-transition" href="#">Explore by category</a>
        </div>
    </div>
    <!-- End CTA -->
</main>
<!-- ========== END MAIN CONTENT ========== -->


<?php include '../includes/footer.php'; ?>
<?php include '../includes/student-auth.php'; ?>