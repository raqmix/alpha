<?php

namespace Tests\Feature;

use App\Classes\Recaptcha;
use App\Filament\Pages\Settings\AllSetting;
use App\Livewire\AskQuoteForm;
use App\Livewire\ContactUsForm;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class RecaptchaWebformTest extends TestCase
{
    /** @return array<string, class-string> */
    public static function webformProvider(): array
    {
        return [
            'contact us' => [ContactUsForm::class],
            'ask a quote' => [AskQuoteForm::class],
        ];
    }

    public function test_it_falls_back_to_the_universal_keys(): void
    {
        $this->assertSame(config('recaptcha.universal.site_key'), Recaptcha::siteKey());
        $this->assertSame(config('recaptcha.universal.secret_key'), Recaptcha::secretKey());
        $this->assertTrue(Recaptcha::usingUniversalKeys());
    }

    public function test_the_widget_renders_with_the_configured_site_key(): void
    {
        $html = Blade::render('<x-common.recaptcha />');

        $this->assertStringContainsString('data-recaptcha', $html);
        $this->assertStringContainsString(Recaptcha::siteKey(), $html);
        // Must survive Livewire re-renders, or a solved challenge gets wiped.
        $this->assertStringContainsString('wire:ignore', $html);
    }

    public function test_the_widget_and_script_disappear_when_switched_off(): void
    {
        config(['recaptcha.enabled' => false]);

        $this->assertFalse(Recaptcha::enabled());
        // Blade leaves conditional markers behind; what matters is that no
        // widget and no script survive.
        $this->assertStringNotContainsString('data-recaptcha', Blade::render('<x-common.recaptcha />'));
        $this->assertStringNotContainsString('recaptcha/api.js', Blade::render('<x-common.recaptcha-scripts />'));
    }

    public function test_the_api_script_is_loaded_once_from_the_footer(): void
    {
        $html = Blade::render('<x-common.recaptcha-scripts />');

        $this->assertStringContainsString('recaptcha/api.js', $html);
        $this->assertStringContainsString('render=explicit', $html);
        $this->assertStringContainsString('onload=alphaRecaptchaOnload', $html);
    }

    /** @dataProvider webformProvider */
    public function test_submitting_without_a_captcha_is_rejected(string $component): void
    {
        Http::fake();

        Livewire::test($component)
            ->call('submit')
            ->assertHasErrors(['captcha' => 'required']);

        // Nothing should have been sent to Google: there was no token to check.
        Http::assertNothingSent();
    }

    /** @dataProvider webformProvider */
    public function test_a_token_google_approves_is_accepted(string $component): void
    {
        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true]),
        ]);

        Livewire::test($component)
            ->set('captcha', 'a-token-from-the-browser')
            ->assertHasNoErrors('captcha')
            ->assertSet('captchaVerified', true);
    }

    /** @dataProvider webformProvider */
    public function test_a_token_google_rejects_is_refused(string $component): void
    {
        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ]),
        ]);

        Livewire::test($component)
            ->set('captcha', 'a-forged-token')
            ->assertHasErrors('captcha')
            ->assertSet('captchaVerified', false);
    }

    /**
     * The 'required' rule only proves a token was posted, not that anyone
     * checked it, so submit() consults the verified flag as well. Exercised
     * directly because validate() runs first and would trip on the empty
     * fields long before the captcha gate.
     *
     * @dataProvider webformProvider
     */
    public function test_an_unverified_token_cannot_slip_past_submit(string $component): void
    {
        $form = new $component();

        $gate = new \ReflectionMethod($component, 'assertCaptchaPassed');
        $gate->setAccessible(true);

        // A string that was never sent to Google satisfies 'required' on its own.
        $form->captcha = 'never-checked-by-google';
        $form->captchaVerified = false;

        try {
            $gate->invoke($form);
            $this->fail('An unverified token was allowed through.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('captcha', $e->errors());
        }

        // Once Google has approved it, the same gate lets it through.
        $form->captchaVerified = true;
        $gate->invoke($form);

        $this->assertTrue(true);
    }

    /**
     * The flag that submit() trusts must not be writable from the browser, or
     * the gate above is trivially bypassed.
     *
     * @dataProvider webformProvider
     */
    public function test_the_verified_flag_cannot_be_forged_from_the_browser(string $component): void
    {
        Http::fake();

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test($component)->set('captchaVerified', true);
    }

    public function test_an_outage_at_google_does_not_let_a_token_through(): void
    {
        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response('', 500),
        ]);

        $this->assertFalse(Recaptcha::verify('any-token'));
    }

    /**
     * The browser has to be allowed to fetch the widget. Without the Google
     * origins in script-src the page renders perfectly and the checkbox never
     * appears, which is exactly how this broke in production.
     *
     * config/secure-headers.php returns the real policy only for web requests
     * (it short circuits on php_sapi_name() === 'cli'), so config() under
     * PHPUnit hands back the package defaults instead. Read the file.
     */
    public function test_the_csp_lets_the_browser_load_the_widget(): void
    {
        $config = file_get_contents(config_path('secure-headers.php'));

        $directive = function (string $name) use ($config): string {
            $start = strpos($config, "'{$name}' => [");
            $this->assertNotFalse($start, "CSP directive {$name} is missing.");

            // Each directive is introduced by its own MDN reference comment,
            // so the next one marks where this block ends.
            $end = strpos($config, '// https://developer.mozilla.org', $start);

            return substr($config, $start, $end === false ? 2000 : $end - $start);
        };

        foreach (['script-src', 'img-src', 'connect-src', 'frame-src'] as $name) {
            $block = $directive($name);

            foreach (['https://www.google.com', 'https://www.gstatic.com'] as $origin) {
                if ($name === 'frame-src' && $origin === 'https://www.gstatic.com') {
                    continue; // the challenge iframe is served from www.google.com only
                }

                $this->assertStringContainsString(
                    $origin,
                    $block,
                    sprintf('%s must allow %s or the reCAPTCHA widget cannot load.', $name, $origin),
                );
            }
        }
    }

    /**
     * With the setting never saved, the toggle has to render ON. Filament's
     * ->default() does not apply when fillForm() passes an array, so an unsaved
     * key arrived as null, showed the toggle off, and the next save would have
     * silently disabled reCAPTCHA across the site.
     */
    public function test_the_settings_toggle_defaults_to_on_when_never_saved(): void
    {
        $toggle = null;

        $walk = function ($components) use (&$walk, &$toggle) {
            foreach ($components as $component) {
                if ($toggle) {
                    return;
                }

                if ($component instanceof Toggle && $component->getName() === Recaptcha::ENABLED_SETTING) {
                    $toggle = $component;

                    return;
                }

                foreach (['getChildComponents', 'getDefaultChildComponents'] as $method) {
                    if (method_exists($component, $method)) {
                        try {
                            $walk($component->$method());
                        } catch (\Throwable $e) {
                            // Components that need a live form context: skip.
                        }
                    }
                }
            }
        };

        $walk((new AllSetting())->schema());

        $this->assertNotNull($toggle, 'The reCAPTCHA toggle is missing from Site Settings.');

        $host = new class extends Component implements HasForms {
            use InteractsWithForms;

            public ?array $data = [];

            public function render()
            {
                return '<div></div>';
            }
        };

        $form = Form::make($host)->statePath('data')->schema([$toggle]);

        // Exactly what AllSetting::fillForm() passes when the key was never saved.
        $form->fill([]);

        $this->assertTrue(data_get($form->getState(), Recaptcha::ENABLED_SETTING));
    }

    public function test_a_blank_token_is_never_verified(): void
    {
        Http::fake();

        $this->assertFalse(Recaptcha::verify(null));
        $this->assertFalse(Recaptcha::verify(''));

        Http::assertNothingSent();
    }
}
