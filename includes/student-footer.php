
<!-- ========== FOOTER ========== -->
<footer class="container-fluid bg-light mt-5 py-4">
  <div class="container">
    <div class="row justify-content-between align-items-center">
      
      <!-- Copyright -->
      <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
        <p class="text-muted small mb-0">
          &copy; Learnix <span id="currentYear"></span>. All rights reserved.
        </p>
      </div>
      <!-- End Copyright -->

      <!-- Socials -->
      <div class="col-md-6 text-center text-md-end">
        <ul class="list-inline mb-0">
          <li class="list-inline-item">
            <a class="btn btn-outline-secondary btn-sm btn-icon rounded-circle" href="#">
              <i class="bi bi-facebook"></i>
            </a>
          </li>
          <li class="list-inline-item">
            <a class="btn btn-outline-secondary btn-sm btn-icon rounded-circle" href="#">
              <i class="bi bi-google"></i>
            </a>
          </li>
          <li class="list-inline-item">
            <a class="btn btn-outline-secondary btn-sm btn-icon rounded-circle" href="#">
              <i class="bi bi-twitter"></i>
            </a>
          </li>
          <li class="list-inline-item">
            <a class="btn btn-outline-secondary btn-sm btn-icon rounded-circle" href="#">
              <i class="bi bi-github"></i>
            </a>
          </li>
        </ul>
      </div>
      <!-- End Socials -->

    </div>
  </div>

  <script>
    // Get the current year
    document.getElementById("currentYear").textContent = new Date().getFullYear();
  </script>
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

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Vimeo -->
<script src="https://player.vimeo.com/api/player.js"></script>
<!-- YouTube -->
<script src="https://www.youtube.com/iframe_api"></script>

<!-- <script src="../assets/vendor/hs-video-player/dist/hs-video-player.min.js"></script> -->


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

      // INITIALIZATION OF LIVE TOAST
// =======================================================
const liveToastElement = document.querySelector('#liveToast');
if (liveToastElement) {
  const liveToast = new bootstrap.Toast(liveToastElement);
  
  // Define the showToast function
  function showToast(title, message) {
    // Update the toast content
    liveToastElement.querySelector('.toast-header h5').textContent = title;
    liveToastElement.querySelector('.toast-body').textContent = message;
    
    // Show the toast
    liveToast.show();
  }
}
      

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