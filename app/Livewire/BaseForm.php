<?php

namespace App\Livewire;

use App\Classes\Recaptcha;
use App\Classes\Utility;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class BaseForm extends Component
{
    public $mainModel;
    public $emailList;
    public $subject;
    public $mailData;

    public $exclude = [];
    public $time = [];
    public $relation = [];


    public $fileAttributs;
    public $directoryName;


    public $firsTime = true;
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }


    /** Token handed over by the reCAPTCHA widget in the browser. */
    public $captcha = null;

    /**
     * Set once Google has confirmed the token above is genuine.
     *
     * #[Locked] matters here: public Livewire properties are writable from the
     * browser, so without it a crafted request could simply set this to true
     * and post straight past the check.
     */
    #[Locked]
    public $captchaVerified = false;

    /**
     * Verify as soon as the visitor solves the challenge, so they get told about
     * a bad token while they are still looking at the form rather than on submit.
     *
     * Tokens are single use, hence the flag: submit() trusts this result instead
     * of asking Google about the same token a second time.
     */
    public function updatedCaptcha($token)
    {
        if (! Recaptcha::enabled()) {
            $this->captchaVerified = true;

            return;
        }

        $this->captchaVerified = Recaptcha::verify($token, request()->ip());

        if (! $this->captchaVerified) {
            throw ValidationException::withMessages([
                'captcha' => __('site.Google thinks, you are a bot, please refresh and try again!'),
            ]);
        }

        $this->resetErrorBag('captcha');
    }

    /**
     * Validation rules for the captcha field, for child forms to spread into
     * their own rules() so the requirement follows the Site Settings toggle.
     */
    protected function captchaRules(): array
    {
        return Recaptcha::enabled() ? ['required'] : ['nullable'];
    }

    /**
     * Server side gate. The rules only prove a token was posted; this proves we
     * actually checked it with Google, so a crafted request cannot skip past by
     * sending any non-empty string.
     */
    protected function assertCaptchaPassed(): void
    {
        if (! Recaptcha::enabled() || $this->captchaVerified) {
            return;
        }

        throw ValidationException::withMessages([
            'captcha' => __('site.Google thinks, you are a bot, please refresh and try again!'),
        ]);
    }

    /** Clear the solved challenge here and in the browser after a submit. */
    protected function resetCaptcha(): void
    {
        $this->captcha = null;
        $this->captchaVerified = false;

        $this->dispatch('recaptcha-reset', componentId: $this->getId());
    }

    public function submit()
    {
        try
        {
            $this->firsTime = false;

            $this->validate();

            $this->assertCaptchaPassed();

            $this->uploadFilesIfExists($this);

            $item = $this->mainModel::create($this->all());

            $this->prepareForEmail($item);

            Utility::sendEmailToAdmin($this->subject, $this->mailData, $this->emailList);


            $this->firsTime = true;

            session()->flash('success', 'Thank you. Your form was submitted successfully.');

            $this->reset($this->getResettableFields());

            $this->resetCaptcha();
        }
        catch (\Exception $th)
        {
            throw $th;
            // dd($th->getMessage());
            // exit(); // EXIT_
        }

    }


    public function uploadFilesIfExists($item)
    {


        if(isset($this->fileAttributs) )
        {
            foreach($this->fileAttributs as $attributeName)
            {
                if (!empty($this->$attributeName )) {
                    $filename = Str::random(6) . '_' . time() . '_' . $this->$attributeName->getClientOriginalName();
                    $path = $this->$attributeName->storeAs( $this->directoryName, $filename, "public" );
                    $this->$attributeName = $path;
                }
                else{
                    $this->$attributeName = null;
                    $this->fileAttributs = null;
                }

            }
        }

    }

    public function prepareForEmail($item)
    {
        $this->setEmailList();

        foreach($item->getAttributes() as $key => $loopItem)
        {
            if( in_array( $key, $this->exclude ))
            {
                continue;
            }
            if( in_array( $key, $this->time ))
            {
                $this->mailData[ $key ] = date( "d/m/Y h:m" , $loopItem ) ;
                continue;
            }
            if( $this->fileAttributs && in_array( $key, $this->fileAttributs ))
            {
                $this->mailData[$key] = asset('storage/' . $loopItem);
                continue;
            }
            if( in_array( $key, array_keys($this->relation) ))
            {
                $currentRelation = $this->relation[$key]["relation"];
                $currentTitle = $this->relation[$key]["relationTitle"];
                $currentValue = $item->$currentRelation;
                $currentValue = $currentValue->$currentTitle;
                $this->mailData[ $this->relation[$key]["key"] ] = $currentValue;
                continue;
            }
            $this->mailData[$key] = $loopItem;
        }

    }

    public function rulesExcept()
    {
        return $this->rules();
    }

    protected function getResettableFields(): array
    {
        return collect(array_keys($this->rulesExcept()))
            ->filter(function ($field) {
                return is_string($field)
                    && !str_contains($field, '*')
                    && !str_contains($field, '.')
                    && property_exists($this, $field);
            })
            ->values()
            ->all();
    }

}
