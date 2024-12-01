    <footer class="footer-section py-4">
        <div class="container text-center">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <a href="index.php" class="text-white text-decoration-none">
                        <img src="../assets/images/logo.png" alt="Logo" height="60" width="60">
                    </a>
                </div>
                <div class="col-md-6">
                    <ul class="list-inline text-white" style="font-family: 'Open Sans', sans-serif;">
                        <li class="list-inline-item"><a href="#" class="text-white text-decoration-none">FAQ</a></li>
                        <li class="list-inline-item"><a href="#" class="text-white text-decoration-none">Contact Us</a></li>
                        <li class="list-inline-item"><a href="#" class="text-white text-decoration-none">Privacy Policy</a></li>
                        <li class="list-inline-item"><a href="#" class="text-white text-decoration-none">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-3">
                <p class="mb-0" style="font-family: 'Open Sans', sans-serif;">&copy; <?php echo date("Y"); ?> Advanced Schedule Builder. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="../assets/js/scripts.js"></script>
    
    <?php
        if (isset($pageJS)) {
            foreach ($pageJS as $js) {
                echo '<script src="' . $js . '"></script>';
            }
        }
    ?>
    
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
</body>
</html>
