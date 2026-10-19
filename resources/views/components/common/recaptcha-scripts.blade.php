{{--
    Loads the Google reCAPTCHA API once per page and draws a widget into every
    <x-common.recaptcha /> placeholder, wiring each one back to the Livewire
    component that owns it.

    Rendered from the site footer, so it covers every current and future webform
    without each form having to ship its own script.
--}}

@if (\App\Classes\Recaptcha::enabled())
    <script>
        window.alphaRecaptcha = (function () {
            // placeholder element -> grecaptcha widget id
            var widgets = new WeakMap();

            function ready() {
                return window.grecaptcha && typeof window.grecaptcha.render === 'function';
            }

            // The Livewire component the placeholder lives in, so one page can
            // carry several independent forms.
            function componentFor(element) {
                var root = element.closest('[wire\\:id]');

                if (!root || !window.Livewire) {
                    return null;
                }

                return window.Livewire.find(root.getAttribute('wire:id'));
            }

            function setToken(element, token) {
                var component = componentFor(element);

                if (component) {
                    component.set('captcha', token);
                }
            }

            function renderAll() {
                if (!ready()) {
                    return;
                }

                document.querySelectorAll('[data-recaptcha]').forEach(function (element) {
                    // Already drawn: either we know it, or Google filled it in
                    // during an earlier pass.
                    if (widgets.has(element) || element.childElementCount > 0) {
                        return;
                    }

                    widgets.set(element, window.grecaptcha.render(element, {
                        sitekey: element.dataset.sitekey,
                        callback: function (token) {
                            setToken(element, token);
                        },
                        'expired-callback': function () {
                            setToken(element, null);
                        },
                        'error-callback': function () {
                            setToken(element, null);
                        },
                    }));
                });
            }

            // Clear solved challenges, optionally only inside one component.
            function reset(componentId) {
                if (!ready()) {
                    return;
                }

                var scope = document;

                if (componentId) {
                    scope = document.querySelector('[wire\\:id="' + componentId + '"]') || document;
                }

                scope.querySelectorAll('[data-recaptcha]').forEach(function (element) {
                    if (widgets.has(element)) {
                        window.grecaptcha.reset(widgets.get(element));
                    }
                });
            }

            return { renderAll: renderAll, reset: reset };
        })();

        // Called by the Google script once it has loaded (render=explicit).
        window.alphaRecaptchaOnload = function () {
            window.alphaRecaptcha.renderAll();
        };

        document.addEventListener('livewire:initialized', function () {
            window.alphaRecaptcha.renderAll();

            // Dispatched by App\Livewire\BaseForm after a successful submit.
            window.Livewire.on('recaptcha-reset', function (event) {
                window.alphaRecaptcha.reset(event ? event.componentId : null);
            });
        });

        document.addEventListener('livewire:navigated', function () {
            window.alphaRecaptcha.renderAll();
        });
    </script>

    <script src="{{ config('recaptcha.script_url') }}?onload=alphaRecaptchaOnload&render=explicit" async defer></script>
@endif
