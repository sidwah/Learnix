</body>
<!-- ========== FOOTER ========== -->
  <footer class="bg-dark">
    <div class="container pb-1 pb-lg-5">
      <div class="row content-space-t-2">
        <div class="col-lg-3 mb-7 mb-lg-0">
          <!-- Logo -->
          <div class="mb-5">
            <a class="navbar-brand" href="index.php" aria-label="Space">
              <img class="navbar-brand-logo" src="../assets/svg/logos/logo-white.svg" alt="Image Description">
            </a>
          </div>
          <!-- End Logo -->

          <!-- List -->
          <ul class="list-unstyled list-py-1">
            <li><a class="link-sm link-secondary" href="#"><i class="bi-geo-alt-fill me-1"></i> Dimension College, Ghana</a></li>
            <li><a class="link-sm link-secondary" href="tel:1-062-109-9222"><i class="bi-telephone-inbound-fill me-1"></i> +1 (23) 109-9222</a></li>
          </ul>
          <!-- End List -->

        </div>
        <!-- End Col -->

        <div class="col-sm mb-7 mb-sm-0">
          <h5 class="text-white mb-3">Company</h5>

          <!-- List -->
          <ul class="list-unstyled list-py-1 mb-0">
            <li><a class="link-sm link-secondary" href="#">About</a></li>
            <li><a class="link-sm link-secondary" href="#">Careers <span class="badge bg-warning text-dark rounded-pill ms-1">We're hiring</span></a></li>
            <li><a class="link-sm link-secondary" href="#">Blog</a></li>
            <li><a class="link-sm link-secondary" href="#">Customers <i class="bi-box-arrow-up-right small ms-1"></i></a></li>
            <li><a class="link-sm link-secondary" href="#">Hire us</a></li>
          </ul>
          <!-- End List -->
        </div>
        <!-- End Col -->


        <div class="col-sm mb-7 mb-sm-0">
          <h5 class="text-white mb-3">Resources</h5>

          <!-- List -->
          <ul class="list-unstyled list-py-1 mb-5">
            <li><a class="link-sm link-secondary" href="#"><i class="bi-question-circle-fill me-1"></i> Help</a></li>
            <li><a class="link-sm link-secondary" href="#"><i class="bi-person-circle me-1"></i> Your Account</a></li>
          </ul>
          <!-- End List -->
        </div>
        <!-- End Col -->

        <div class="col-sm mb-7 mb-sm-0">
            <h5 class="text-white mb-3">Legal</h5>
            <!-- List -->
            <ul class="list-unstyled list-py-1 mb-0">
              <li>
                <a class="link-sm link-secondary" href="privacy-policy.php">Privacy Policy</a>
              </li>
              <li>
                <a class="link-sm link-secondary" href="terms.php">Terms of Service</a>
              </li>
            </ul>
            <!-- End List -->
          </div>
          <!-- End Col -->
      </div>
      <!-- End Row -->

      <div class="border-top my-7"></div>

      <div class="row mb-7">
        <div class="col-sm mb-3 mb-sm-0">
          <!-- Socials -->
          <ul class="list-inline list-separator mb-0">
            <li class="list-inline-item">
              <a class="text-body" href="privacy-policy.php">Privacy &amp; Policy</a>
            </li>
            <li class="list-inline-item">
              <a class="text-body" href="Terms.php">Terms</a>
            </li>
          </ul>
          <!-- End Socials -->
        </div>

        <div class="col-sm-auto">
          <!-- Socials -->
          <ul class="list-inline mb-0">
            <li class="list-inline-item">
              <a class="btn btn-soft-secondary btn-xs btn-icon" href="#">
                <i class="bi-facebook"></i>
              </a>
            </li>

            <li class="list-inline-item">
              <a class="btn btn-soft-secondary btn-xs btn-icon" href="#">
                <i class="bi-google"></i>
              </a>
            </li>

            <li class="list-inline-item">
              <a class="btn btn-soft-secondary btn-xs btn-icon" href="#">
                <i class="bi-twitter"></i>
              </a>
            </li>

            <li class="list-inline-item">
              <a class="btn btn-soft-secondary btn-xs btn-icon" href="#">
                <i class="bi-github"></i>
              </a>
            </li>

          </ul>
          <!-- End Socials -->
        </div>
      </div>

      <!-- Copyright -->
      <div class="w-md-85 text-lg-center mx-lg-auto">
        <p class="text-muted small">  &copy; Learnix. <span id="currentYear"></span> All rights
        reserved.</p>
      </div>
      <!-- End Copyright -->

      
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

<!-- Go To -->
<a class="js-go-to go-to position-fixed" href="javascript:;" style="visibility: hidden;" data-hs-go-to-options='{
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
     }'>
    <i class="bi-chevron-up"></i>
  </a>
  <!-- ========== END SECONDARY CONTENTS ========== -->

  <!-- JS Implementing Plugins -->
  <script src="../assets/js/vendor.min.js"></script>
  <script src="../assets/vendor/aos/dist/aos.js"></script>

  <!-- JS Learnix -->
  <script src="../assets/js/theme.min.js"></script>

  <script src="./assets/vendor/hs-video-player/dist/hs-video-player.min.js"></script>

  <!-- JS Implementing Plugins -->
<script src="../assets/vendor/hs-sticky-block/dist/hs-sticky-block.min.js"></script>



  <!-- JS Plugins Init. -->
  <script>
    (function() {
      // INITIALIZATION OF HEADER
      // =======================================================
      new HSHeader('#header').init()


      // INITIALIZATION OF MEGA MENU
      // =======================================================
      new HSMegaMenu('.js-mega-menu', {
          desktop: {
            position: 'left'
          }
        })


      // INITIALIZATION OF SHOW ANIMATIONS
      // =======================================================
      new HSShowAnimation('.js-animation-link')


      // INITIALIZATION OF BOOTSTRAP VALIDATION
      // =======================================================
      HSBsValidation.init('.js-validate', {
        onSubmit: data => {
          data.event.preventDefault()
          alert('Submited')
        }
      })


      // INITIALIZATION OF BOOTSTRAP DROPDOWN
      // =======================================================
      HSBsDropdown.init()

      // INITIALIZATION OF VIDEO PLAYER
    // =======================================================
    new HSVideoPlayer('.js-inline-video-player')


      // INITIALIZATION OF GO TO
      // =======================================================
      new HSGoTo('.js-go-to')

      // INITIALIZATION OF STICKY BLOCKS
      // =======================================================
      new HSStickyBlock('.js-sticky-block', {
        targetSelector: document.getElementById('header').classList.contains('navbar-fixed') ? '#header' : null
      })

      // INITIALIZATION OF AOS
      // =======================================================
      AOS.init({
        duration: 650,
        once: true
      });


      // INITIALIZATION OF TEXT ANIMATION (TYPING)
      // =======================================================
      HSCore.components.HSTyped.init('.js-typedjs')

      
       // INITIALIZATION OF NAV SCROLLER
      // =======================================================
      new HsNavScroller('.js-nav-scroller')

      

      // INITIALIZATION OF SWIPER
      // =======================================================
      var sliderThumbs = new Swiper('.js-swiper-thumbs', {
        watchSlidesVisibility: true,
        watchSlidesProgress: true,
        history: false,
        breakpoints: {
          480: {
            slidesPerView: 2,
            spaceBetween: 15,
          },
          768: {
            slidesPerView: 3,
            spaceBetween: 15,
          },
          1024: {
            slidesPerView: 3,
            spaceBetween: 15,
          },
        },
        on: {
          'afterInit': function (swiper) {
            swiper.el.querySelectorAll('.js-swiper-pagination-progress-body-helper')
            .forEach($progress => $progress.style.transitionDuration = `${swiper.params.autoplay.delay}ms`)
          }
        }
      });

      var sliderMain = new Swiper('.js-swiper-main', {
        effect: 'fade',
        autoplay: true,
        loop: true,
        thumbs: {
          swiper: sliderThumbs
        }
      })
    })()
  </script>
</body>

</html>