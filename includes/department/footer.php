<?php
// department/footer.php - Updated version with dark mode support
?>
<!-- ========== FOOTER SCRIPTS ========== -->
<!-- JS Implementing Plugins -->
<!-- Latest Tom Select JS from CDN -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<!-- <script src="../assets/node_modules/tom-select/dist/js/tom-select.complete.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/vendor.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script> -->
<!-- JS Front -->
<!-- <script src="https://cdn.jsdelivr.net/npm/tom-select@2.0.0/dist/js/tom-select.complete.min.js"></script> -->
<script src="../assets/js/theme.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Dark Mode Script - Add this before other initialization scripts -->
<!-- <script src="../assets/js/dark-mode.js"></script> -->

<!-- JS Plugins Initialization -->
<script>
  (function() {
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
      try {
        // INITIALIZATION OF SELECT
        if (typeof HSCore !== 'undefined' && HSCore.components.HSTomSelect) {
          HSCore.components.HSTomSelect.init('.js-select', {
            render: {
              'option': function(data, escape) {
                return data.optionTemplate;
              },
              'item': function(data, escape) {
                return data.optionTemplate;
              }
            }
          });
        }
        // Ensure HSBsDropdownComponent is only initialized once
        if (typeof HSBsDropdownComponent !== 'undefined' && typeof window.HSBsDropdown === 'undefined') {
          window.HSBsDropdown = new HSBsDropdownComponent();
        }
        // INITIALIZATION OF HEADER
        if (document.querySelector('#header')) {
          new HSHeader('#header').init();
        }
        // INITIALIZATION OF NAV SCROLLER (with safety checks)
        const navScroller = document.querySelector('.js-nav-scroller');
        if (navScroller) {
          new HsNavScroller(navScroller, {
            delay: 400,
            offset: 140
          });
        }
        // INITIALIZATION OF LISTJS COMPONENT
        if (typeof HSCore !== 'undefined' && HSCore.components.HSList) {
          const docsSearch = HSCore.components.HSList.init('#docsSearch');
          // GET JSON FILE RESULTS
          if (docsSearch) {
            fetch('../assets/json/docs-search.json')
              .then(response => response.json())
              .then(data => {
                if (data && docsSearch.getItem(0)) {
                  docsSearch.getItem(0).add(data);
                }
              })
              .catch(error => console.error('Error loading search data:', error));
          }
        }
        // INITIALIZATION OF GO TO
        const goToEl = document.querySelector('.js-go-to');
        if (goToEl) {
          new HSGoTo(goToEl).init();
        }
      } catch (error) {
        console.error('Initialization error:', error);
      }
    });
  })();
</script>
<!-- ========== END FOOTER SCRIPTS ========== -->
</body>

</html>