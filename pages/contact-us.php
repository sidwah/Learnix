<?php include '../includes/header.php'; ?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">

<!-- Heading -->
<div class="w-lg-65 text-center mx-lg-auto mb-5 mb-md-5 mt-7">
    <h1 class="display-5 fw-bold">Get in Touch with Us</h1>
    <p class="text-muted mt-2">Have questions or need support? We're here to help you with anything related to Learnix.</p>
</div>
<!-- End Heading -->

    <!-- Map -->
<div id="contactsSection" class="bg-light position-relative rounded-2 mx-3 mx-md-8">
    
    <div class="container content-space-1 content-space-lg-3">
        <div class="row justify-content-md-end">
            <div class="col-md-6 col-lg-5">
                <!-- Card -->
                <div class="card card-lg position-relative zi-999">
                    <div class="card-body">
                        <!-- Heading -->
                        <div class="mb-5">
                            <h4 class="card-title">East Legon,</h4>
                            <h2 class="card-title h1">Accra - Ghana</h2>
                        </div>
                        <!-- End Heading -->

                        <!-- Media -->
                        <div class="d-flex mb-5">
                            <div class="flex-shrink-0">
                                <span class="svg-icon svg-icon-sm text-primary me-3"></span>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1">Call us</h5>
                                <span class="d-block small">+233 25 779 9736</span>
                            </div>
                        </div>
                        <!-- End Media -->

                        <!-- Media -->
                        <div class="d-flex mb-5">
                            <div class="flex-shrink-0">
                                <span class="svg-icon svg-icon-sm text-primary me-3"></span>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1">Email us</h5>
                                <span class="d-block small">info@learnix.com</span>
                            </div>
                        </div>
                        <!-- End Media -->

                        <!-- Media -->
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <span class="svg-icon svg-icon-sm text-primary me-3"></span>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1">Visit us</h5>
                                <span class="d-block small">East Legon, Accra</span>
                            </div>
                        </div>
                        <!-- End Media -->
                    </div>
                </div>
                <!-- End Card -->
            </div>
            <!-- End Col -->
        </div>
        <!-- End Row -->
    </div>

    <!-- Gmap -->
    <div class="position-md-absolute top-0 start-0 bottom-0 end-0">
        <div id="mapEg1" class="leaflet"
            data-hs-leaflet-options='{
                "map": {
                    "scrollWheelZoom": false,
                    "coords": [5.629265, -0.147769]
                },
                "marker": [
                    {
                        "coords": [5.629265, -0.147769],
                        "icon": {
                            "iconUrl": "https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/images/marker-icon.png",
                            "iconSize": [25, 41]
                        },
                        "popup": {
                            "text": "Learnix, East Legon, Accra, Ghana",
                            "title": "Visit Us"
                        }
                    }
                ]
            }'>
            <!-- Map Container -->
            <div id="mapEg1" class="leaflet" style="height: 400px; width: 100%;"></div>

            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    const coords = [5.629265, -0.147769]; // East Legon, Accra

                    const map = L.map('mapEg1').setView(coords, 13);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    L.marker(coords, {
                        icon: L.icon({
                            iconUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/images/marker-icon.png',
                            iconSize: [25, 41]
                        })
                    }).addTo(map)
                        .bindPopup('Learnix, East Legon, Accra, Ghana')
                        .openPopup();
                });
            </script>
        </div>
    </div>
    <!-- End Gmap -->
</div>
<!-- End Map -->

</main>


<?php include '../includes/footer.php'; ?>
<?php include '../includes/student-auth.php'; ?>

