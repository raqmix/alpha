<?php

namespace Tests\Feature;

use App\Classes\Recaptcha;
use App\Livewire\AskQuoteForm;
use App\Livewire\ContactUsForm;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
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
        $this->assertSame('', trim(Blade::render('<x-common.recaptcha />')));
        $this->assertSame('', trim(Blade::render('<x-common.recaptcha-scripts />')));
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

    public function test_a_blank_token_is_never_verified(): void
    {
        Http::fake();

        $this->assertFalse(Recaptcha::verify(null));
        $this->assertFalse(Recaptcha::verify(''));

        Http::assertNothingSent();
    }
}
