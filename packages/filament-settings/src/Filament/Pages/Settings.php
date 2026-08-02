<?php

namespace Outerweb\FilamentSettings\Filament\Pages;

use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\HasUnsavedDataChangesAlert;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Str;
use Outerweb\Settings\Models\Setting;

use function Filament\Support\is_app_url;

/**
 * @property Schema $form
 */
class Settings extends Page
{
    use HasUnsavedDataChangesAlert;
    use InteractsWithFormActions;

    public ?array $data = [];

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cog';

    public static function getNavigationLabel(): string
    {
        return __('filament-settings::translations.page.navigation_label');
    }

    public function getTitle(): string
    {
        return __('filament-settings::translations.page.title');
    }

    public function schema(): array | Closure
    {
        return [];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->schema())
            ->statePath('data');
    }

    /**
     * Filament v5 renders pages through a content schema rather than a custom
     * Blade view, so the form and its actions are composed here instead.
     */
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make($this->getFormActions())
                            ->alignment($this->getFormActionsAlignment())
                            ->fullWidth($this->hasFullWidthFormActions())
                            ->sticky($this->areFormActionsSticky())
                            ->key('form-actions'),
                    ]),
            ]);
    }

    /**
     * @return array<Action>
     */
    public function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-settings::translations.form.actions.save'))
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }

    public function mount(): void
    {
        $this->fillForm();
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

            $fields = collect($this->form->getFlatFields(true));
            $fieldsWithNestedFields = $fields->filter(fn (Field $field) => count($field->getChildComponents()) > 0);

            $fieldsWithNestedFields->each(function (Field $fieldWithNestedFields, string $fieldWithNestedFieldsKey) use (&$fields) {
                $fields = $fields->reject(function (Field $field, string $fieldKey) use ($fieldWithNestedFieldsKey) {
                    return Str::startsWith($fieldKey, $fieldWithNestedFieldsKey . '.');
                });
            });

            $data = $fields->mapWithKeys(function (Field $field, string $fieldKey) {
                return [$fieldKey => data_get($this->form->getState(), $fieldKey)];
            })->toArray();

            $this->callHook('afterValidate');

            $this->callHook('beforeSave');

            foreach ($data as $key => $value) {
                Setting::set($key, $value);
            }

            $this->callHook('afterSave');
        } catch (Halt $exception) {
            return;
        }

        $this->getSavedNotification()?->send();

        if ($redirectUrl = $this->getRedirectUrl()) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }

    protected function getSavedNotification(): ?Notification
    {
        $title = $this->getSavedNotificationTitle();

        if (blank($title)) {
            return null;
        }

        return Notification::make()
            ->success()
            ->title($title);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('filament-settings::translations.notifications.saved');
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }
}
