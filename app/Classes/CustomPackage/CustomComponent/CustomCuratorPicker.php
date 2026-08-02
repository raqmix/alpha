<?php

namespace App\Classes\CustomPackage\CustomComponent;

use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
// Curator v5 moved its helpers to the global namespace.
use function get_media_items;


class CustomCuratorPicker extends CuratorPicker
{
 
    protected string $view = 'filament.curator.components.forms.picker';
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->registerActions([
            fn (CuratorPicker $component): Action => $component->getDownloadAction(),
            fn (CuratorPicker $component): Action => $component->getEditAction(),
            fn (CuratorPicker $component): Action => $component->getRemoveAction(),
            fn (CuratorPicker $component): Action => $component->getRemoveAllAction(),
            fn (CuratorPicker $component): Action => $component->getReorderAction(),
            fn (CuratorPicker $component): Action => $component->getViewAction(),
            fn (CuratorPicker $component): Action => $component->getPickerAction(),
            fn (CuratorPicker $component): Action => $component->getPickerActionAtDropdown(),
        ]);
    }
    /**
     * "Replace Image" entry shown in the item dropdown, alongside Edit /
     * Download / Remove.
     *
     * Curator v5 no longer opens the panel by dispatching an 'open-modal'
     * event with a settings payload; the picker action renders the panel as
     * its own modal content instead. This mirrors CuratorPicker::getPickerAction()
     * so the panel receives exactly the settings v5 expects, and only differs
     * in presentation (dropdown item rather than a button).
     */
    public function getPickerActionAtDropdown(): Action
    {
        return Action::make('new_open_curator_picker')
            ->label(__("Replace Image"))
            ->color('gray')
            ->icon('heroicon-s-receipt-refund')
            ->hidden(fn (CuratorPicker $component): bool => $component->isDisabled())
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalWidth(Width::Screen)
            ->modalCloseButton(false)
            ->modalContent(fn (CuratorPicker $component): View => view('curator::components.modals.curator-panel', [
                'key' => $component->getKey(),
                'settings' => [
                    'acceptedFileTypes' => $component->getAcceptedFileTypes(),
                    'defaultSort' => $component->getDefaultPanelSort(),
                    'directory' => $component->getDirectory(),
                    'diskName' => $component->getDiskName(),
                    'imageCropAspectRatio' => $component->getImageCropAspectRatio(),
                    'imageResizeMode' => $component->getImageResizeMode(),
                    'imageResizeTargetWidth' => $component->getImageResizeTargetWidth(),
                    'imageResizeTargetHeight' => $component->getImageResizeTargetHeight(),
                    'isLimitedToDirectory' => $component->isLimitedToDirectory(),
                    'isTenantAware' => $component->isTenantAware(),
                    'tenantOwnershipRelationshipName' => $component->getTenantOwnershipRelationshipName(),
                    'isMultiple' => $component->isMultiple(),
                    'maxItems' => $component->getMaxItems(),
                    'maxSize' => $component->getMaxSize(),
                    'maxWidth' => $component->getMaxWidth(),
                    'minSize' => $component->getMinSize(),
                    'pathGenerator' => $component->getPathGenerator(),
                    'rules' => $component->getValidationRules(),
                    'selected' => (array) $component->getState(),
                    'shouldPreserveFilenames' => $component->shouldPreserveFilenames(),
                    'statePath' => $component->getStatePath(),
                    'visibility' => $component->getVisibility(),
                ],
            ]))
            ->action(fn (): null => null);
    }


    public function getState(): mixed
    {
        $state = data_get($this->getLivewire(), $this->getStatePath());

        if (is_array($state)) {
            return $state;
        }

        if (blank($state)) {
            return null;
        }

        if(is_int($state))
        {
            $state = [$state];
            $media = get_media_items($state)->toArray();
            $state = null;
            foreach ($media as $itemData) {
                $state[(string) Str::uuid()] = $itemData;
            }
        }

        return $state;
    }
 
 
}
