<form wire:submit.prevent="submit">
    @csrf

    @if (session()->has('success'))
        <div class="contact-submit-success" role="status" aria-live="polite">
            <div class="contact-submit-success__icon" aria-hidden="true">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="contact-submit-success__body">
                <h3>Message Sent Successfully</h3>
                <p>{{ Session::get('success') }}</p>
                <small>Our team will contact you shortly.</small>
            </div>
        </div>
    @endif

    <div class="form-row">
        <div class="form-group">
            <label for="cf_first_name">First Name *</label>
            <input type="text" id="cf_first_name" wire:model.blur="first_name" placeholder="Enter your first name" @class(['field-error' => $errors->has('first_name')])>
            @error('first_name') <span class="field-error-text">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="cf_last_name">Last Name *</label>
            <input type="text" id="cf_last_name" wire:model.blur="last_name" placeholder="Enter your last name" @class(['field-error' => $errors->has('last_name')])>
            @error('last_name') <span class="field-error-text">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-group">
        <label for="cf_email">Email Address *</label>
        <input type="email" id="cf_email" wire:model.blur="email" placeholder="Enter your email address" @class(['field-error' => $errors->has('email')])>
        @error('email') <span class="field-error-text">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label for="cf_phone">Phone Number *</label>
        <input type="tel" id="cf_phone" wire:model.blur="phone" placeholder="+961 70 123 456" @class(['field-error' => $errors->has('phone')])>
        @error('phone') <span class="field-error-text">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label for="cf_company">Company Name</label>
        <input type="text" id="cf_company" wire:model.blur="company" placeholder="Your company name" @class(['field-error' => $errors->has('company')])>
        @error('company') <span class="field-error-text">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label for="cf_product">Product of Interest *</label>
        <select id="cf_product" wire:model.blur="product" @class(['field-error' => $errors->has('product')])>
            <option value="">Select a category</option>
            @foreach($productOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('product') <span class="field-error-text">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label for="cf_message">Message</label>
        <textarea id="cf_message" wire:model.blur="message" rows="5" placeholder="Tell us about your labeling requirements..." @class(['field-error' => $errors->has('message')])></textarea>
        @error('message') <span class="field-error-text">{{ $message }}</span> @enderror
    </div>

    <x-common.recaptcha />

    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; justify-content: center;">
        <i class="fas fa-paper-plane"></i> Send Message
    </button>
</form>
