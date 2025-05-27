<?php 

include '../includes/department/header.php'; 
// a connection file is included in the header and it is $conn so no need to create a new connection here
// these are the sessions we have $_SESSION['user_id'], $_SESSION['email'] ,$_SESSION['first_name'], $_SESSION['last_name'] , $_SESSION['role'] , $_SESSION['staff_id'] , $_SESSION['department_id'] , $_SESSION['department_name'] , $_SESSION['signin'] 
?>
<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Navbar -->
    <nav class="js-nav-scroller navbar navbar-expand-lg navbar-sidebar navbar-vertical navbar-light bg-white border-end" data-hs-nav-scroller-options='{
            "type": "vertical",
            "target": ".navbar-nav .active",
            "offset": 80
           }'>

        <?php include '../includes/department/sidebar.php'; ?>
    </nav>
    <!-- End Navbar -->

    <!-- Content -->
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-5">
        
    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<?php include '../includes/department/footer.php'; ?>