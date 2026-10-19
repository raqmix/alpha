<?php

namespace App\Livewire;

use App\Filament\Resources\ContactUsWebform\Model\ContactUsWebform;
use App\Filament\Resources\DropdownList\Model\DropdownList;
use Illuminate\Validation\Rule;

class ContactUsForm extends BaseForm
{
    public $mainModel = ContactUsWebform::class;


    public $subject = "Contact Us Form";
    public $emailList = "";

    public $first_name, $last_name, $company, $product, $email, $phone, $internation_phone_country, $message, $captcha;

    public array $productOptions = [];

    public $mailData = [];

    public $exclude = ["id"];

    public $time = ["created_at"];

    // public $relation =
    // [
    //     'reason_to_contact_id ' =>
    //     [
    //         "key" => "Resoan To Contact",
    //         "relation" => "reasonToContact",
    //         "relationTitle" => "title"
    //     ]

    // ];


    public function mount()
    {
        if (empty($this->internation_phone_country)) {
            $this->internation_phone_country = 'LB';
        }

        $this->productOptions = DropdownList::activeWithCategory(DropdownList::Contact_Us_CATEGORY)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->title => $item->title];
            })
            ->all();
    }


    public function setEmailList()
    {
        $this->emailList = setting("site.contact_us_email_list");
    }



    public function rules()
    {
        return
            [
                'first_name' => ['required', 'min:3', 'max:255', 'regex:/^(?:[a-zA-Z ]+|[\p{Arabic} ]+)$/u', 'not_regex:/<[^b][^r][^>]*>/'],
                'last_name' => ['required', 'min:3', 'max:255', 'regex:/^(?:[a-zA-Z ]+|[\p{Arabic} ]+)$/u', 'not_regex:/<[^b][^r][^>]*>/'],
                'email' => ['required', 'max:255', 'email:filter', 'not_regex:/<[^b][^r][^>]*>/'],
                'internation_phone_country' => ['nullable', 'string', 'size:2'],
                'phone' => ['required', 'regex:/^\+961(?:\s?\d){7,8}$/', 'not_regex:/<[^b][^r][^>]*>/'],
                'company' => ['nullable', 'string', 'max:255', 'not_regex:/<[^b][^r][^>]*>/'],
                'product' => ['required', 'string', 'min:2', 'max:255', Rule::in(array_keys($this->productOptions)), 'not_regex:/<[^b][^r][^>]*>/'],
                'message' => ['nullable', 'min:3', 'max:65535', 'not_regex:/<[^b][^r][^>]*>/'],
                'captcha' => $this->captchaRules(),
            ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Please enter your first name.',
            'first_name.min' => 'First name must be at least 3 characters.',
            'first_name.max' => 'First name is too long.',
            'first_name.regex' => 'First name should contain letters only.',
            'first_name.not_regex' => 'Please remove invalid characters from first name.',

            'last_name.required' => 'Please enter your last name.',
            'last_name.min' => 'Last name must be at least 3 characters.',
            'last_name.max' => 'Last name is too long.',
            'last_name.regex' => 'Last name should contain letters only.',
            'last_name.not_regex' => 'Please remove invalid characters from last name.',

            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email address is too long.',
            'email.not_regex' => 'Please remove invalid characters from email.',

            'phone.required' => 'Please enter your phone number.',
            'phone.regex' => 'Please enter a valid Lebanese phone number (example: +961 70 123 456).',
            'phone.not_regex' => 'Please remove invalid characters from phone number.',

            'company.max' => 'Company name is too long.',
            'company.not_regex' => 'Please remove invalid characters from company name.',

            'product.required' => 'Please choose a product of interest.',
            'product.in' => 'Please choose a valid product from the list.',
            'product.min' => 'Please choose a valid product from the list.',
            'product.max' => 'Product name is too long.',
            'product.not_regex' => 'Please remove invalid characters from product.',

            'message.min' => 'Message must be at least 3 characters when provided.',
            'message.max' => 'Message is too long.',
            'message.not_regex' => 'Please remove invalid characters from your message.',

            'captcha.required' => 'Please confirm that you are not a robot.',
        ];
    }

    public function render()
    {
        return view('livewire.contact-us-form');
    }
}
