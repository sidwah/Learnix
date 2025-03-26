<!-- JS Implementing Plugins -->
<script src="../node_modules/tom-select/dist/js/tom-select.complete.min.js"></script>


<!-- JS Front -->
<script src="../assets/js/hs.tom-select.js"></script>

<!-- JS Plugins Init. -->
<script>
  (function() {
    // INITIALIZATION OF SELECT
    // =======================================================
    HSCore.components.HSTomSelect.init('.js-select', {
      render: {
        'option': function(data, escape) {
          return data.optionTemplate
        },
        'item': function(data, escape) {
          return data.optionTemplate
        }
      }
    })

    // INITIALIZATION OF LIVE TOAST
    // =======================================================
    const liveToast = new bootstrap.Toast(document.querySelector('#liveToast'))
    document.querySelector('#liveToastBtn').addEventListener('click', () => liveToast.show())
    
  })()

  
</script>

<!-- ========== SECONDARY CONTENTS ========== -->
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

  <!-- JS Front -->
  <script src="../assets/js/theme.min.js"></script>

  <!-- JS Plugins Init. -->
  <script>
    (function() {
      // INITIALIZATION OF HEADER
      // =======================================================
      new HSHeader('#header').init()


      // INITIALIZATION OF NAV SCROLLER
      // =======================================================
      new HsNavScroller('.js-nav-scroller', {
        delay: 400,
        offset: 140
      })


      // INITIALIZATION OF LISTJS COMPONENT
      // =======================================================
      const docsSearch = HSCore.components.HSList.init('#docsSearch')


      // GET JSON FILE RESULTS
      // =======================================================
      fetch('../assets/json/docs-search.json')
      .then(response => response.json())
      .then(data => {
        docsSearch.getItem(0).add(data)
      })

      // INITIALIZATION OF GO TO
      // =======================================================
      new HSGoTo('.js-go-to')
    })()
  </script>