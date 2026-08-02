<?php

namespace App\Filament\Resources\MenuLink\ResourcePages;

use SolutionForest\FilamentTree\Actions\EditAction;
use SolutionForest\FilamentTree\Actions\DeleteAction;
use App\Filament\Resources\MenuLink\MenuLinkResource;
use App\Traits\HasParentResource;
use CactusGalaxy\FilamentAstrotomic\Resources\Pages\Record\EditTranslatable;
use SolutionForest\FilamentTree\Actions;
use SolutionForest\FilamentTree\Resources\Pages\TreePage as BasePage;
use Illuminate\Database\Eloquent\Collection;

class MenuLinkTree extends BasePage
{
    use EditTranslatable, HasParentResource;

    protected static string $resource = MenuLinkResource::class;

    protected static int $maxDepth = 3;

    public function getRecords(): Collection | null
    {
        if ($this->records) {
            return $this->records;
        }
        return $this->getSortedQuery()->where("menu_id", $this->parent->getKey())->get();

    }

    protected function getActions(): array
    {
        return [

            $this->getCreateAction(),
            // ActionsCreateAction::make()
            // ->url(
            //     fn (): string => static::getParentResource()::getUrl('menu-links.create', [
            //         'parent' => $this->parent,
            //     ])
            // ),


            // SAMPLE CODE, CAN DELETE
            //\Filament\Pages\Actions\Action::make('sampleAction'),
        ];
    }

    protected function getTreeActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function hasDeleteAction(): bool
    {
        return true;
    }

    protected function hasEditAction(): bool
    {
        return true;
    }

    protected function hasViewAction(): bool
    {
        return false;
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    // CUSTOMIZE ICON OF EACH RECORD, CAN DELETE
    // public function getTreeRecordIcon(?\Illuminate\Database\Eloquent\Model $record = null): ?string
    // {
    //     return null;
    // }
}
