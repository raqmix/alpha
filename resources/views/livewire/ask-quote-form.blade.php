<form wire:submit.prevent="submit" class="quote-form" enctype="multipart/form-data">
    @csrf

    @if (session()->has('success'))
        <div class="contact-submit-success" role="status" aria-live="polite" style="margin-bottom: 24px;">
            <div class="contact-submit-success__icon" aria-hidden="true">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="contact-submit-success__body">
                <h3>Request Sent Successfully</h3>
                <p>{{ Session::get('success') }}</p>
                <small>Our team will contact you shortly.</small>
            </div>
        </div>
    @endif

    <div class="form-row">
        <div class="form-group">
            <label for="aq_first_name">First Name <span style="color: red;">*</span></label>
            <input type="text" id="aq_first_name" wire:model.blur="first_name" @class(['field-error' => $errors->has('first_name')])>
            @error('first_name') <span class="field-error-text">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="aq_last_name">Last Name <span style="color: red;">*</span></label>
            <input type="text" id="aq_last_name" wire:model.blur="last_name" @class(['field-error' => $errors->has('last_name')])>
            @error('last_name') <span class="field-error-text">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="aq_email">Email <span style="color: red;">*</span></label>
            <input type="email" id="aq_email" wire:model.blur="email" @class(['field-error' => $errors->has('email')])>
            @error('email') <span class="field-error-text">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="aq_company">Company <span style="color: red;">*</span></label>
            <input type="text" id="aq_company" wire:model.blur="company" @class(['field-error' => $errors->has('company')])>
            @error('company') <span class="field-error-text">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="aq_country">Country <span style="color: red;">*</span></label>
            <select id="aq_country" wire:model.blur="country" @class(['field-error' => $errors->has('country')])>
                <option value="">Select Country</option>
                @foreach($countryOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('country') <span class="field-error-text">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="aq_phone">Phone <span style="color: red;">*</span></label>
            <input type="tel" id="aq_phone" wire:model.blur="phone" @class(['field-error' => $errors->has('phone')])>
            @error('phone') <span class="field-error-text">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="aq_product">Product offered <span style="color: red;">*</span></label>
            <select id="aq_product" wire:model.blur="product" @class(['field-error' => $errors->has('product')])>
                <option value="">Select Product</option>
                @foreach($productOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('product') <span class="field-error-text">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="aq_quantity">Quantity <span style="color: red;">*</span></label>
            <input type="number" id="aq_quantity" wire:model.blur="quantity" min="1" placeholder="Number of units" @class(['field-error' => $errors->has('quantity')])>
            @error('quantity') <span class="field-error-text">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-row">
        <div class="form-group full-width">
            <label style="font-weight: 600; display: block; margin-bottom: 12px;">Upload file if it is up to 20 MB</label>
            <div class="file-upload">
                <input type="file" id="fileUpload" wire:model="file_upload" accept=".pdf,.ai,.eps,.psd,.png,.jpg,.jpeg">
                <label for="fileUpload" class="file-label">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>{{ $file_upload ? 'File Selected' : 'Select Files' }}</span>
                </label>
            </div>
            @error('file_upload') <span class="field-error-text">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-row">
        <div class="form-group full-width">
            <label for="aq_message">Message</label>
            <textarea id="aq_message" wire:model.blur="message" rows="4" placeholder="Tell us more about your project..." @class(['field-error' => $errors->has('message')])></textarea>
            @error('message') <span class="field-error-text">{{ $message }}</span> @enderror
        </div>
    </div>

    <x-common.recaptcha class="form-group full-width" />

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Submit Request</button>
        <button type="button" class="btn btn-outline" wire:click="$refresh">Cancel</button>
    </div>
</form>
