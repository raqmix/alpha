<?php

namespace App\Filament\Pages\Settings;

use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use App\Classes\CustomPackage\CustomComponent\CustomCuratorPicker;
use App\Models\Setting\Setting;
use Awcodes\Curator\PathGenerators\DatePathGenerator;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Closure;
use Filament\Forms\Components\TextInput;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;

use App\Filament\Forms\Components\TranslatableTabsNoArabic;
use CactusGalaxy\FilamentAstrotomic\TranslatableTab;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Illuminate\Support\HtmlString;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Str;
use Filament\Forms\Components\Field;
use App\Jobs\ClearCaches;
use Filament\Forms\Components\Textarea;

class AllSetting extends BaseSettings
{
    use HasPageShield;

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __("Site Settings");
    }

    public static function getNavigationGroup(): ?string
    {
        return __("Setting");
    }

    public function schema(): array|Closure
    {
        return [

            TranslatableTabsNoArabic::make()
                ->columnSpan(2)
                ->localeTabSchema(fn(TranslatableTab $tab) => [

                    Tabs::make('Settings')
                        ->schema([
                            Tab::make('General')
                                ->schema([

                                    TextInput::make($tab->makeName('general.title'))
                                        ->label(__("Title[" . $tab->getLocale() . "]"))
                                        ->required()
                                        ->notRegex('/<[^b][^r][^>]*>/')
                                        ->maxLength(255)
                                        ->validationMessages([
                                            'not_regex' => 'HTML is invalid',
                                        ]),

                                    TextInput::make($tab->makeName('general.description'))
                                        ->label(__("Description[" . $tab->getLocale() . "]"))
                                        ->required()
                                        ->maxLength(255)
                                        ->notRegex('/<[^b][^r][^>]*>/')
                                        ->validationMessages([
                                            'not_regex' => 'HTML is invalid',
                                        ]),


                                    TextInput::make($tab->makeName('general.keywords'))
                                        ->label(__("Keywords [" . $tab->getLocale() . "]"))
                                        ->required()
                                        ->maxLength(255)
                                        ->notRegex('/<[^b][^r][^>]*>/')
                                        ->validationMessages([
                                            'not_regex' => 'HTML is invalid',
                                        ]),

                                    CustomCuratorPicker::make($tab->makeName("general.seo_image"))
                                        ->label(__("Seo Image [" . $tab->getLocale() . "]"))
                                        ->pathGenerator(DatePathGenerator::class)
                                        ->size(40)
                                        ->color('primary')
                                        ->outlined(true)
                                        ->size('md')
                                        ->constrained(true)
                                        ->orderColumn('order')
                                        ->required(),


                                    // Checkbox::make('general.activate_otp'),

                                    // Checkbox::make('general.Disable_InternShips'),


                                    Select::make('general.date_format')
                                        ->label(__('Date Format'))
                                        ->required()
                                        ->notRegex('/<[^b][^r][^>]*>/')
                                        ->validationMessages([
                                            'not_regex' => 'HTML is invalid',
                                        ])
                                        ->options(Setting::getDateFormats())
                                        ->placeholder(__('Select Date Format')),


                                    Select::make('general.time_format')
                                        ->label(__('Time Format'))
                                        ->required()
                                        ->notRegex('/<[^b][^r][^>]*>/')
                                        ->validationMessages([
                                            'not_regex' => 'HTML is invalid',
                                        ])
                                        ->options(Setting::getTimeFormats())
                                        ->placeholder(__('Select Time Format')),

                                    Select::make('general.time_zone')
                                        ->label(__('Time Zone'))
                                        ->required()
                                        ->notRegex('/<[^b][^r][^>]*>/')
                                        ->validationMessages([
                                            'not_regex' => 'HTML is invalid',
                                        ])
                                        ->options(Setting::getTimezones())
                                        ->placeholder(__('Select Time Zone'))
                                        ->searchable(),

                                    TextInput::make('general.google_site_verification')
                                        ->maxLength(255)
                                        ->notRegex('/<[^b][^r][^>]*>/')
                                        ->validationMessages([
                                            'not_regex' => 'HTML is invalid',
                                        ]),



                                    TextInput::make('general.filament_id')
                                        ->required()
                                        ->notRegex('/<[^b][^r][^>]*>/')
                                        ->maxLength(255)
                                        ->validationMessages([
                                            'not_regex' => 'HTML is invalid',
                                        ]),

                                    TextInput::make('general.filament_path')
                                        ->required()
                                        ->notRegex('/<[^b][^r][^>]*>/')
                                        ->maxLength(255)
                                        ->validationMessages([
                                            'not_regex' => 'HTML is invalid',
                                        ]),



                                    Section::make('Google Recaptcha')
                                        ->collapsed()
                                        ->schema([

                                            TextInput::make('general.google_recaptcha_site_key')
                                                ->label(__("Google Recaptcha Site Key"))
                                                ->maxLength(255)
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),

                                            TextInput::make('general.google_recaptcha_secret')
                                                ->label(__("Google Recaptcha Secret"))
                                                ->maxLength(255)
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),


                                        ])->columns(2),



                                    Section::make('SMTP Setting')
                                        ->collapsed()
                                        ->schema([

                                            TextInput::make('general.smtp_host')
                                                ->label(__("SMTP Host"))
                                                ->maxLength(255)
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),

                                            TextInput::make('general.smtp_port')
                                                ->label(__("SMTP PORT"))
                                                ->integer()
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),


                                            TextInput::make('general.smtp_encryption')
                                                ->label(__("SMTP Encryption"))
                                                ->maxLength(5)
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),

                                            TextInput::make('general.smtp_username')
                                                ->label(__("SMTP User Name"))
                                                ->maxLength(255)
                                                ->email()
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),


                                            TextInput::make('general.smtp_password')
                                                ->label(__("SMTP Password"))
                                                ->password()
                                                ->maxLength(255)
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),

                                        ])->columns(2),



                                    Section::make('Relate Your site with External Analytics')
                                        ->collapsed()
                                        ->schema([

                                            TextInput::make('general.google_analytics_code')
                                                ->label(__("Google Analytics Code"))
                                                ->maxLength(255)
                                                ->helperText(new HtmlString('<p> For More Info  <a style="color:rgb(var(--primary-600));" href="https://chartio.com/learn/marketing-analytics/how-to-add-google-analytics-tracking-to-a-website/#google-analytics-tracking-code" target="blank" rel="noopener noreferrer nofollow" > Click Here</a> </p>'))
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),

                                            TextInput::make('general.google_tag_code')
                                                ->label(__("Google Tag Code"))
                                                ->maxLength(255)
                                                ->helperText(new HtmlString('<p> For More Info  <a style="color:rgb(var(--primary-600));" href="https://chartio.com/learn/marketing-analytics/how-to-add-google-analytics-tracking-to-a-website/#google-tag-manager" target="blank" rel="noopener noreferrer nofollow" > Click Here</a> </p>'))
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),


                                            TextInput::make('general.meta_pixel_code')
                                                ->label(__("Meta Pixel Code"))
                                                ->maxLength(255)
                                                ->helperText(new HtmlString('<p> For More Info  <a style="color:rgb(var(--primary-600));" href="https://disruptiveadvertising.com/blog/social-media/how-to-set-up-install-meta-pixel/" target="blank" rel="noopener noreferrer nofollow" > Click Here</a> </p>'))
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),



                                            TextInput::make('general.clarity_code')
                                                ->label(__("Clarity Code"))
                                                ->maxLength(255)
                                                ->helperText(new HtmlString('<p> For More Info  <a style="color:rgb(var(--primary-600));" href="https://learn.microsoft.com/en-us/clarity/setup-and-installation/about-clarity" target="blank" rel="noopener noreferrer nofollow" > Click Here</a> </p>'))
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),



                                            TextInput::make('general.hotjar_code')
                                                ->label(__("Hot Jar Code"))
                                                ->maxLength(255)
                                                ->helperText(new HtmlString('<p> For More Info  <a style="color:rgb(var(--primary-600));" href="https://www.hotjar.com/" target="blank" rel="noopener noreferrer nofollow" > Click Here</a> </p>'))
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),


                                        ])->columns(2),



                                ]),
                            Tab::make('Site')
                                ->schema([
                                    Section::make('Logo, Favicon and Main Color')
                                        ->collapsed()
                                        ->schema([

                                            CustomCuratorPicker::make($tab->makeName("site.logo"))
                                                ->label(__("Logo[" . $tab->getLocale() . "]"))
                                                ->pathGenerator(DatePathGenerator::class)
                                                ->size(40)
                                                ->color('primary')
                                                ->outlined(true)
                                                ->size('md')
                                                ->constrained(true)
                                                ->orderColumn('order')
                                                ->required(),


                                            CustomCuratorPicker::make($tab->makeName("site.icon"))
                                                ->label(__("Icon[" . $tab->getLocale() . "]"))
                                                ->pathGenerator(DatePathGenerator::class)
                                                ->size(40)
                                                ->color('primary')
                                                ->outlined(true)
                                                ->size('md')
                                                ->constrained(true)
                                                ->orderColumn('order')
                                                ->required(),


                                            ColorPicker::make('site.admin_panel_color')
                                                ->label(__("Admin Panel Color"))
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),


                                            CustomCuratorPicker::make($tab->makeName("site.footer_logo"))
                                                ->label(__("Footer Logo [" . $tab->getLocale() . "]"))
                                                ->pathGenerator(DatePathGenerator::class)
                                                ->size(40)
                                                ->color('primary')
                                                ->outlined(true)
                                                ->size('md')
                                                ->constrained(true)
                                                ->orderColumn('order')
                                                ->required(),


                                        ])->columns(2),






                                    Section::make('Webform Email List')
                                        ->collapsed()
                                        ->schema([

                                            TextInput::make('site.contact_us_email_list')
                                                ->label(__("Contact Us Email List"))
                                                ->maxLength(255)
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),



                                        ])->columns(2),


                                    Section::make('Contact Information')
                                        ->collapsed()
                                        ->schema([

                                            TextInput::make('site.phone')
                                                ->label(__("phone"))
                                                ->maxLength(255)
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),

                                            TextInput::make('site.fax')
                                                ->label(__("Fax"))
                                                ->maxLength(255)
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),



                                            TextInput::make('site.management_email')
                                                ->label(__("Management Email"))
                                                ->maxLength(255)
                                                ->email()
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),


                                            TextInput::make($tab->makeName('site.location'))
                                                ->label(__("Location[" . $tab->getLocale() . "]"))
                                                ->maxLength(255)
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),

                                            TextInput::make('site.location_url')
                                                ->label(__("Location Url"))
                                                ->maxLength(255)
                                                // ->activeUrl()
                                                ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),
                                            TextInput::make('site.location_coordinate')
                                                ->label(__("Location Coordiante"))
                                                // ->maxLength(255)
                                                // ->activeUrl()
                                                // ->notRegex('/<[^b][^r][^>]*>/')
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),

                                            Textarea::make('site.business_hours')
                                                ->label(__("Business Hours"))
                                                ->rows(3)
                                                ->maxLength(500)
                                                ->validationMessages([
                                                    'not_regex' => 'HTML is invalid',
                                                ]),


                                        ])->columns(2),

                                    Section::make('Catalogue Download')
                                        ->collapsed()
                                        ->schema([
                                            CustomCuratorPicker::make($tab->makeName('site.catalogue_file'))
                                                ->label(__("Catalogue File[" . $tab->getLocale() . "]"))
                                                ->helperText(__('Upload the catalogue media file used on the Contact Us page download button.'))
                                                ->pathGenerator(DatePathGenerator::class)
                                                ->size(40)
                                                ->color('primary')
                                                ->outlined(true)
                                                ->size('md')
                                                ->constrained(true)
                                                ->orderColumn('order'),
                                        ])->columns(1),


                                ]),
                        ]),



                ]),

        ];
    }


    protected function fillForm(): void
    {
        $data = Setting::get();

        $this->callHook('beforeFill');

        $this->form->fill($data);

        $this->callHook('afterFill');
    }

    public function save(): void
    {
        try {
            $this->callHook('beforeValidate');

            $settings = Setting::all()->mapWithKeys(function ($i) {
                return [$i->key => $i->value];
            })->toArray();


            $fields = collect($this->form->getFlatFields(true));
            $fieldsWithNestedFields = $fields->filter(fn(Field $field) => count($field->getDefaultChildComponents()) > 0);

            $fieldsWithNestedFields->each(function (Field $fieldWithNestedFields, string $fieldWithNestedFieldsKey) use (&$fields) {
                $fields = $fields->reject(function (Field $field, string $fieldKey) use ($fieldWithNestedFields, $fieldWithNestedFieldsKey) {
                    return Str::startsWith($fieldKey, $fieldWithNestedFieldsKey . '.');
                });
            });

            $data = $fields->mapWithKeys(function (Field $field, string $fieldKey) {
                return [$fieldKey => data_get($this->form->getState(), $fieldKey)];
            })->toArray();

            $data = array_diff_assoc($data, $settings);


            $this->callHook('afterValidate');

            $this->callHook('beforeSave');

            foreach ($data as $key => $value) {
                Setting::set($key, $value);
            }

            dispatch(new ClearCaches())->afterResponse();

            $this->callHook('afterSave');
        } catch (Halt $exception) {
            return;
        }

        $this->getSavedNotification()?->send();

        if ($redirectUrl = $this->getRedirectUrl()) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }
}
