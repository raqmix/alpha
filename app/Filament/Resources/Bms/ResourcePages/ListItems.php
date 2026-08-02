<?php

namespace App\Filament\Resources\Bms\ResourcePages;

use Filament\Schemas\Components\Tabs\Tab;
use App\Filament\Resources\Bms\Model\Bms;
use App\Filament\Resources\BaseResource\Pages\ListBase;
use App\Filament\Resources\Bms\BmsResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ListItems extends ListBase
{
    protected static string $resource = BmsResource::class;

    public function updatedActiveTab(): void
    {
        // Tabs are cached by Filament; clear them so sub-tabs refresh immediately.
        unset($this->cachedTabs);

        parent::updatedActiveTab();
    }

    public function getTabs(): array
    {
        $tabs = [];
        $grouped = Bms::getGroupedCategories();
        $activeTab = (string) ($this->activeTab ?? 'all');

        $allCategorySlugs = collect($grouped)
            ->flatMap(fn (array $categories) => array_keys($categories))
            ->values()
            ->all();

        $tabs['all'] = Tab::make(__('All'))
            ->icon('fas-globe')
            ->extraAttributes(['class' => 'bms-main-tab'])
            ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('category', $allCategorySlugs))
            ->badge(
                Bms::query()
                    ->whereIn('category', $allCategorySlugs)
                    ->count()
            );

        $groupIcons = [
            'HomePage' => 'fas-house',
            'Cultures' => 'fas-people-group',
            'About Us' => 'fas-circle-info',
            'Services' => 'fas-briefcase',
            'Careers' => 'fas-user-tie',
            'Internship' => 'fas-user-graduate',
        ];

        $activeGroupKey = null;
        if (Str::startsWith($activeTab, 'group_')) {
            $activeGroupKey = $activeTab;
        } elseif (Str::startsWith($activeTab, 'sub_') && str_contains($activeTab, '__')) {
            $activeGroupSuffix = Str::between($activeTab, 'sub_', '__');
            $activeGroupKey = 'group_' . $activeGroupSuffix;
        }

        foreach ($grouped as $groupLabel => $categories) {
            $categorySlugs = array_keys($categories);
            $tabKey = 'group_' . Str::slug((string) $groupLabel, '_');

            $tabs[$tabKey] = Tab::make((string) $groupLabel)
                ->icon($groupIcons[(string) $groupLabel] ?? 'fas-folder')
                ->extraAttributes(['class' => 'bms-main-tab'])
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('category', $categorySlugs))
                ->badge(
                    Bms::query()
                        ->whereIn('category', $categorySlugs)
                        ->count()
                );

            // Show second-level subcategory tabs only for the selected main tab.
            if ($activeGroupKey === $tabKey) {
                foreach ($categories as $categorySlug => $categoryLabel) {
                    $subTabKey = 'sub_' . Str::after($tabKey, 'group_') . '__' . $categorySlug;

                    $tabs[$subTabKey] = Tab::make('• ' . (string) $categoryLabel)
                        ->icon('fas-tag')
                        ->extraAttributes(['class' => 'bms-sub-tab'])
                        ->modifyQueryUsing(fn (Builder $query) => $query->where('category', $categorySlug))
                        ->badge(
                            Bms::query()
                                ->where('category', $categorySlug)
                                ->count()
                        );
                }
            }
        }

        return $tabs;
    }
}
