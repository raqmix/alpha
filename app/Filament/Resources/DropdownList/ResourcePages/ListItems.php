<?php

namespace App\Filament\Resources\DropdownList\ResourcePages;

use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BaseResource\Pages\ListBase;
use App\Filament\Resources\DropdownList\DropdownListResource;
use App\Filament\Resources\DropdownList\Model\DropdownList;

class ListItems extends ListBase
{
    protected static string $resource = DropdownListResource::class;

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make(__('All'))
                ->icon('fas-globe')
                ->badge(DropdownList::query()->count()),
        ];
        foreach (DropdownList::getCategoryList() as $slug => $label) {
            $tabs['cat_' . $slug] = Tab::make($label)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', $slug))
                ->badge(DropdownList::query()->where('category', $slug)->count());
        }
        return $tabs;
    }
}
