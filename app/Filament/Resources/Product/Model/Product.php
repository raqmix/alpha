<?php

namespace App\Filament\Resources\Product\Model;

use App\Filament\Resources\DropdownList\Model\DropdownList;
use App\Models\Base\BaseTranslationModel;
use App\Models\Media\Media;
use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

class Product extends BaseTranslationModel
{
    use HasFactory, HasSeo, Searchable;

    protected $translationForeignKey = 'product_id';

    protected $table = 'product';

    public $translatedAttributes = [
        'title',
        'hero_title',
        'brief',
        'image_id',
        'gallery_items',
        'description_sections',
        'client_logos',
    ];

    protected $fillable = [
        'category_id',
        'sku',
        'slug',
        'status',
        'is_campaign',
        'published_at',
        'revision',
        'changed',
        'reject_note',
        'weight_order',
        'views',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(DropdownList::class, 'category_id', 'id');
    }

    public static function getProductCategoryList()
    {
        return DropdownList::with('translations')->active()
            ->where('category', DropdownList::PRODUCT_CATEGORY)
            ->get()
            ->mapWithKeys(fn ($item) => [$item->id => $item->title]);
    }

    public function mainImage()
    {
        $locale = app()->getLocale();

        return $this->hasOneThrough(
            Media::class,
            ProductLang::class,
            'product_id',
            'id',
            'id',
            'image_id'
        )->where('language', $locale);
    }

    public function makeSearchableUsing(Collection $models): Collection
    {
        return $models->load('translations');
    }

    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'brief' => $this->brief,
            'sku' => $this->sku,
        ];
    }

    public function getResolvedGalleryItems(): array
    {
        return collect($this->gallery_items ?? [])
            ->map(function ($item) {
                $url = $this->resolveImageUrl($item, 'image_id', 'image_url');

                if (!$url) {
                    return null;
                }

                return [
                    'url' => $url,
                    'alt' => $item['alt'] ?? $this->title,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function getResolvedDescriptionSections(): array
    {
        return collect($this->description_sections ?? [])
            ->map(function ($section) {
                $section['resolved_image_url'] = $this->resolveImageUrl($section, 'image_id', 'image_url');
                $section['resolved_image_1_url'] = $this->resolveImageUrl($section, 'image_1_id', 'image_1_url');
                $section['resolved_image_2_url'] = $this->resolveImageUrl($section, 'image_2_id', 'image_2_url');
                $section['resolved_image_3_url'] = $this->resolveImageUrl($section, 'image_3_id', 'image_3_url');

                return $section;
            })
            ->values()
            ->all();
    }

    public function getResolvedClientLogos(): array
    {
        return collect($this->client_logos ?? [])
            ->filter(fn ($item) => !empty($item['label']))
            ->values()
            ->all();
    }

    protected function resolveImageUrl(array $item, string $mediaKey, string $urlKey): ?string
    {
        $mediaId = $item[$mediaKey] ?? null;

        // Some sections store this as an array; find() would then return a
        // Collection instead of a Media model and reading ->url would fail.
        if (is_array($mediaId)) {
            $mediaId = Arr::first($mediaId);
        }

        if (!empty($mediaId)) {
            $media = Media::find($mediaId);

            if ($media instanceof Media) {
                return $media->url ?? $media->getUrl();
            }
        }

        return $item[$urlKey] ?? null;
    }
}
