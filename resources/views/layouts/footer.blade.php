<footer id="footer-section" class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <img src="{{ asset('frontend-static/Assets/logo33.png') }}" alt="Alpha Pack" class="footer-logo">
                <p class="footer-tagline">Label It Right</p>
                <p>High-quality adhesive labels for food, retail, pharmaceutical, and industrial applications.</p>
                <x-common.social-media-links />
            </div>

            <x-common.footer-menu categorySlug="footer-menu" />

            <div class="footer-col">
                <h4>Get In Touch</h4>
                <ul class="footer-contact">
                    <li>
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:{{ setting('site.management_email') }}">{{ setting('site.management_email') }}</a>
                    </li>
                    <li>
                        <i class="fas fa-phone"></i>
                        <a href="tel:{{ setting('site.phone') }}">{{ setting('site.phone') }}</a>
                    </li>
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>{{ $locationTitle }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} Alpha Pack. All Rights Reserved.</p>
        </div>
    </div>
</footer>

<script type="text/javascript" src="{{ asset('js/main.js') }}"></script>

@livewireScripts

<x-common.recaptcha-scripts />

@stack('js')

<script type="text/javascript" src="{{ asset('js/custom.js') }}"></script>
