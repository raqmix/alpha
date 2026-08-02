<?php

namespace App\Filament\Resources\MenuLink\ResourcePages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\BaseResource\Pages\ListBase;
use App\Filament\Resources\MenuLink\MenuLinkResource;
use App\Traits\HasParentResource;
use Filament\Actions;


class ListItems extends ListBase
{
    use HasParentResource;

    protected static string $resource = MenuLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->url(
                fn (): string => static::getParentResource()::getUrl('menu-links.create', [
                    'parent' => $this->parent,
                ])
            ),
        ];
    }

}
