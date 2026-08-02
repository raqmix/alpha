<?php

namespace App\Filament\Resources\BaseResource\Pages;

use Filament\Actions\DeleteAction;
use CactusGalaxy\FilamentAstrotomic\Resources\Pages\Record\EditTranslatable;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBase extends EditRecord
{
    use EditTranslatable;

    protected $listeners = ['refresh'=>'refreshForm'];

    public function refreshForm()
    {
        $this->fillForm();
    }
    
    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()->formId('form'),
            DeleteAction::make(),
        ];
    }

    public function afterSave()
    {
        $this->refreshForm();
    }
}
