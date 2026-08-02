<?php

namespace App\Livewire;

use Exception;
use App\Classes\Utility;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
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


    public $captcha = null;

    public function updatedCaptcha($token)
    {
        $response = Http::post(
            'https://www.google.com/recaptcha/api/siteverify?secret='.
            setting("general.google_recaptcha_secret").
            '&response='.$token
        );

        $success = $response->json()['success'];

        if (!$success) {
            throw ValidationException::withMessages([
                'captcha' => __('site.Google thinks, you are a bot, please refresh and try again!'),
            ]);
        } else {
            $this->captcha = true;
        }
    }

    public function submit()
    {
        try
        {
            $this->firsTime = false;

            $this->validate();

            $this->uploadFilesIfExists($this);

            $item = $this->mainModel::create($this->all());

            $this->prepareForEmail($item);

            Utility::sendEmailToAdmin($this->subject, $this->mailData, $this->emailList);


            $this->firsTime = true;

            session()->flash('success', 'Thank you. Your form was submitted successfully.');

            $this->reset($this->getResettableFields());
        }
        catch (Exception $th)
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
