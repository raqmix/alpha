<?php

namespace App\Filament\Resources\BaseResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use CactusGalaxy\FilamentAstrotomic\Resources\Pages\Record\ListTranslatable;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBase extends ListRecords
{
    use ListTranslatable;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return
        [
            'all' => Tab::make('All')
                ->icon('fas-globe')
                ->badge($this->getModel()::query()->count()),

            'active' => Tab::make('Published')
                ->icon('fas-toggle-on')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $this->getModel()::STATUS_PUBLISHED))
                ->badge($this->getModel()::query()->where('status', $this->getModel()::STATUS_PUBLISHED)->count())
                ->badgeColor('success'),

            'inactive' => Tab::make('Pending')
                ->icon('fas-toggle-off')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $this->getModel()::STATUS_PENDING))
                ->badge($this->getModel()::query()->where('status', $this->getModel()::STATUS_PENDING)->count())
                ->badgeColor('danger'),
        ];
    }

}
