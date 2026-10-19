<?php

namespace App\Livewire;

use App\Filament\Resources\AskQuoteWebform\Model\AskQuoteWebform;
use App\Filament\Resources\DropdownList\Model\DropdownList;
use App\Filament\Resources\Product\Model\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class AskQuoteForm extends BaseForm
{
    use WithFileUploads;

    public $mainModel = AskQuoteWebform::class;

    public $subject = 'Ask Quote Form';
    public $emailList = '';

    public $first_name;
    public $last_name;
    public $email;
    public $company;
    public $country;
    public $phone;
    public $product;
    public $quantity;
    public $file_upload;
    public $message;
    public $captcha;

    public array $productOptions = [];
    public array $countryOptions = [];

    public $mailData = [];

    public $exclude = ['id'];

    public $time = ['created_at'];

    public $fileAttributs = ['file_upload'];

    public $directoryName = 'ask-quote';

    public function mount(): void
    {
        $this->productOptions = $this->resolveProductOptions();
        $this->countryOptions = $this->resolveCountryOptions();
    }

    protected function resolveProductOptions(): array
    {
        if (Schema::hasTable((new Product())->getTable())) {
            $products = Product::active()
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->title => $item->title];
                })
                ->all();

            if (!empty($products)) {
                return $products;
            }
        }

        return [
            'Self-Adhesive Labels' => 'Self-Adhesive Labels',
            'Barcode Labels' => 'Barcode Labels',
            'Tamper Proof Labels' => 'Tamper Proof Labels',
            'Shrink Sleeves' => 'Shrink Sleeves',
            'Stretch Sleeves' => 'Stretch Sleeves',
            'In-Mold Labels' => 'In-Mold Labels',
        ];
    }

    protected function resolveCountryOptions(): array
    {
        return DropdownList::activeWithCategory(DropdownList::Ask_Quote_Country)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->title => $item->title];
            })
            ->all();
    }

    public function setEmailList(): void
    {
        $this->emailList = setting('site.contact_us_email_list');
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'min:2', 'max:255', 'not_regex:/<[^b][^r][^>]*>/'],
            'last_name' => ['required', 'min:2', 'max:255', 'not_regex:/<[^b][^r][^>]*>/'],
            'email' => ['required', 'max:255', 'email:filter', 'not_regex:/<[^b][^r][^>]*>/'],
            'company' => ['required', 'string', 'min:2', 'max:255', 'not_regex:/<[^b][^r][^>]*>/'],
            'country' => ['required', 'string', Rule::in(array_keys($this->countryOptions)), 'not_regex:/<[^b][^r][^>]*>/'],
            'phone' => ['required', 'string', 'max:30', 'not_regex:/<[^b][^r][^>]*>/'],
            'product' => ['required', 'string', Rule::in(array_keys($this->productOptions)), 'not_regex:/<[^b][^r][^>]*>/'],
            'quantity' => ['required', 'integer', 'min:1'],
            'file_upload' => ['nullable', 'file', 'max:20480', 'mimes:pdf,ai,eps,psd,png,jpg,jpeg'],
            'message' => ['nullable', 'max:65535', 'not_regex:/<[^b][^r][^>]*>/'],
            'captcha' => $this->captchaRules(),
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Please enter your first name.',
            'last_name.required' => 'Please enter your last name.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'company.required' => 'Please enter your company name.',
            'country.required' => 'Please select your country.',
            'country.in' => 'Please choose a valid country from the list.',
            'phone.required' => 'Please enter your phone number.',
            'product.required' => 'Please choose a product.',
            'product.in' => 'Please choose a valid product from the list.',
            'quantity.required' => 'Please enter quantity.',
            'quantity.integer' => 'Quantity must be a number.',
            'quantity.min' => 'Quantity must be at least 1.',
            'file_upload.max' => 'File size must be less than 20 MB.',
            'file_upload.mimes' => 'Allowed file types: pdf, ai, eps, psd, png, jpg, jpeg.',
            'captcha.required' => 'Please confirm that you are not a robot.',
        ];
    }

    public function render()
    {
        return view('livewire.ask-quote-form');
    }
}
