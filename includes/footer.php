    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="footer-logo">
                        <img src="<?= BASE_URL ?>/admin/uploads/sm_vertical_light_logo.png" alt="SNA logo" class="footer-logo">
                    </div>
                    <br>
                    <p>A furniture brand by Shree Nanjundeshwara Associates</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="https://wa.me/9900825976" target="_blank"><i class="fa-brands fa-whatsapp"></i> </a>
                    </div>
                </div>

                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="<?= BASE_URL ?>">Home</a></li>
                        <li><a href="<?= BASE_URL ?>categories.php">Categories</a></li>
                        <li><a href="<?= BASE_URL ?>products.php">Products</a></li>
                        <li><a href="<?= BASE_URL ?>about.php">About Us</a></li>
                        <li><a href="<?= BASE_URL ?>contact.php">Contact</a></li>
                    </ul>
                </div>

                <div class="col-md-4">
                    <h5>Contact Info</h5>
                    <p>
                        <i class="fas fa-map-marker-alt"></i> Ground Floor, 53/1, Outer Ring Rd, opposite Vokkaligara Sangha High School, Kottigepalya, Bengaluru, Karnataka 560091
                    </p>
                    <p>
                        <i class="fas fa-phone"></i> +91 99008 25976
                    </p>
                    <p>
                        <i class="fas fa-envelope"></i> info@snacatalogue.com
                    </p>
                </div>
            </div>

            <hr>

            <div class="text-center">
                <p>&copy; 2026 Shree Nanjundeshwara Associates. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (if you need it elsewhere; not required for Swiper) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper@9/swiper-bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Swiper init (single instance)
        var homeSwiper = new Swiper('.homeSliderSwiper', {
            loop: true,
            speed: 600,
            slidesPerView: 1.2,        // base for very small screens
            slidesPerGroup: 1,
            spaceBetween: 12,
            centeredSlides: false,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false
            },
            navigation: {
                nextEl: '.home-swiper-button.swiper-button-next',
                prevEl: '.home-swiper-button.swiper-button-prev',
            },
            breakpoints: {
                480: {
                    slidesPerView: 2,
                    spaceBetween: 14
                },
                768: {
                    slidesPerView: 3,
                    spaceBetween: 18
                },
                1200: {
                    slidesPerView: 4,
                    spaceBetween: 24
                }
            }
        });

        // Preview modal logic
        var previewModalEl = document.getElementById('sliderImagePreviewModal');
        var previewModal   = previewModalEl ? new bootstrap.Modal(previewModalEl) : null;
        var previewImageEl = document.getElementById('sliderPreviewImage');
        var previewTitleEl = document.getElementById('sliderPreviewTitle');

        if (previewModal && previewImageEl) {
            document.querySelectorAll('.home-slide-img').forEach(function (img) {
                img.addEventListener('click', function () {
                    var src   = img.getAttribute('src');
                    var title = img.closest('.home-slide-card')
                                   .querySelector('.home-slide-title');
                    previewImageEl.src = src;
                    if (previewTitleEl) {
                        previewTitleEl.textContent = title ? title.textContent : '';
                    }
                    previewModal.show();
                });
            });
        }
    });
    </script>
    <script>
    (function () {
        var storageKey = 'sna-theme';
        var htmlEl = document.documentElement;
        var toggleBtn = document.querySelector('.theme-toggle-btn');
        var iconEl = toggleBtn ? toggleBtn.querySelector('i') : null;

        function applyTheme(theme) {
            htmlEl.setAttribute('data-theme', theme);

            // Toggle icon
            if (iconEl) {
                iconEl.classList.toggle('fa-moon', theme === 'light');
                iconEl.classList.toggle('fa-sun', theme === 'dark');
            }

            // Swap logo images based on data attributes
            var siteLogo   = document.getElementById('siteLogo');
            var footerLogo = document.getElementById('footerLogo');
            var aboutImage = document.getElementById('aboutImage');

            if (siteLogo) {
                var src = theme === 'dark'
                    ? siteLogo.getAttribute('data-logo-dark')
                    : siteLogo.getAttribute('data-logo-light');
                if (src) siteLogo.src = src;
            }

            if (footerLogo) {
                var fsrc = theme === 'dark'
                    ? footerLogo.getAttribute('data-logo-dark')
                    : footerLogo.getAttribute('data-logo-light');
                if (fsrc) footerLogo.src = fsrc;
            }

            if (aboutImage) {
                var asrc = theme === 'dark'
                    ? aboutImage.getAttribute('data-img-dark')
                    : aboutImage.getAttribute('data-img-light');
                if (asrc) aboutImage.src = asrc;
            }
        }

        // Apply saved theme or system preference
        var savedTheme = localStorage.getItem(storageKey);
        if (!savedTheme) {
            var prefersDark = window.matchMedia &&
                window.matchMedia('(prefers-color-scheme: dark)').matches;
            savedTheme = prefersDark ? 'dark' : 'light';
        }
        applyTheme(savedTheme);

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                var current = htmlEl.getAttribute('data-theme') || 'light';
                var next = current === 'light' ? 'dark' : 'light';
                localStorage.setItem(storageKey, next);
                applyTheme(next);
            });
        }
    })();
    </script>
</body>
</html>