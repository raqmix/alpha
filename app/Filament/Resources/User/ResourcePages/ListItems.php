<?php

namespace App\Filament\Resources\User\ResourcePages;

use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BaseResource\Pages\ListBase;
use App\Filament\Resources\User\UserResource;

class ListItems extends ListBase
{
    protected static string $resource = UserResource::class;

    public function getTabs(): array
    {
        return
        [
            'all' => Tab::make('All')
                ->icon('fas-globe')
                ->badge($this->getModel()::query()->count()),

            'active' => Tab::make('Active')
                ->icon('fas-toggle-on')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $this->getModel()::STATUS_ACTIVE))
                ->badge($this->getModel()::query()->where('status', $this->getModel()::STATUS_ACTIVE)->count())
                ->badgeColor('success'),

            'inactive' => Tab::make('Inactive')
                ->icon('fas-toggle-off')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $this->getModel()::STATUS_INACTIVE))
                ->badge($this->getModel()::query()->where('status', $this->getModel()::STATUS_INACTIVE)->count())
                ->badgeColor('warning'),

            'banned' => Tab::make('Banned')
                ->icon('fas-ban')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $this->getModel()::STATUS_BANNED))
                ->badge($this->getModel()::query()->where('status', $this->getModel()::STATUS_BANNED)->count())
                ->badgeColor('danger'),
        ];
    }
}
