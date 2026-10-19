{{--
    Google reCAPTCHA v2 checkbox for a Livewire webform.

    Renders nothing when reCAPTCHA is switched off in Site Settings. The widget
    itself is drawn by <x-common.recaptcha-scripts /> (loaded once in the
    footer), which finds every [data-recaptcha] placeholder on the page, so this
    component needs no per-form ids and can be dropped into any number of forms.

    The placeholder sits inside wire:ignore so Livewire re-renders never wipe out
    a challenge the visitor has already solved. The error message lives outside
    it so it can still update.
--}}

@props(['class' => 'form-group'])

@php
    // $errors is not shared outside a session (console rendering, mail views),
    // so resolve it defensively rather than through @error.
    $captchaError = isset($errors) && $errors->has('captcha')
        ? $errors->first('captcha')
        : null;
@endphp

@if (\App\Classes\Recaptcha::enabled())
    <div {{ $attributes->class([$class, 'recaptcha-field', 'recaptcha-field--error' => (bool) $captchaError]) }}>
        <div wire:ignore>
            <div class="recaptcha-widget" data-recaptcha data-sitekey="{{ \App\Classes\Recaptcha::siteKey() }}"></div>
        </div>

        @if ($captchaError)
            <span class="field-error-text">{{ $captchaError }}</span>
        @endif
    </div>
@endif
