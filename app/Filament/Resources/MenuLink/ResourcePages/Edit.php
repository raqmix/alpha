<?php

namespace App\Filament\Resources\MenuLink\ResourcePages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\BaseResource\Pages\EditBase;
use App\Filament\Resources\MenuLink\MenuLinkResource;
use App\Traits\HasParentResource;
use Filament\Actions;

class Edit extends EditBase
{
    use HasParentResource;

    protected static string $resource = MenuLinkResource::class;


    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? static::getParentResource()::getUrl('menu-links.index', [
            'parent' => $this->parent,
        ]);
    }

    protected function configureDeleteAction(DeleteAction $action): void
    {
        $resource = static::getResource();

        $action->authorize($resource::canDelete($this->getRecord()))
            ->successRedirectUrl(static::getParentResource()::getUrl('menu-links.index', [
                'parent' => $this->parent,
            ]));
    }


}
