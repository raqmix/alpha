<?php

namespace App\Classes\CustomPackage\CustomComponent;

use Filament\Actions\Action;
use Livewire\Component;
use Awcodes\Curator\Components\Forms\CuratorPicker;
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
    public function getPickerActionAtDropdown(): Action
    {
        return Action::make('new_open_curator_picker')
            ->label(__("Replace Image"))
            ->color('gray')
            ->icon('heroicon-s-receipt-refund')
            ->hidden(fn (CuratorPicker $component): bool => $component->isDisabled())
            ->action(function (CuratorPicker $component, Component $livewire) {
                $livewire->dispatch('open-modal', id: 'curator-panel', settings: [
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
                    'tenantOwnershipRelationshipName' => $component->tenantOwnershipRelationshipName(),
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
                    'types' => $component->getAcceptedFileTypes(),
                    'visibility' => $component->getVisibility(),
                ]);
            });
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
