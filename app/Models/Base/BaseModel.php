<?php

namespace App\Models\Base;

use Normalizer;
use App\Filament\Resources\MicroSite\Model\MicroSite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Facades\Cache;

class BaseModel extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    const STATUS_PUBLISHED = 1;
    const STATUS_PENDING = 0;

    protected $dateFormat = 'U';

    public static function getStatusList()
    {
        return
        [
            self::STATUS_PUBLISHED => __("Published"),
            self::STATUS_PENDING => __("Pending"),
        ];
    }

    public static function getTableName()
    {
        return with(new static)->getTable();
    }


    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
                    ->with("translation")
                    ->orderBy("weight_order", "ASC")
                    ->orderBy("published_at", "DESC");
    }

    public function scopeOnlyActive($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
                    ->with("translation");
    }


    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // auto-sets values on creation
        static::creating(function ($query)
        {
            $query->created_by =  Auth::user()->id;
            $query->updated_by = Auth::user()->id;

            if( isset(static::$modelLang) && static::$modelLang)
            {
                foreach($query->translations as $translation)
                {
                    if( in_array('slug', array_keys($translation->attributes)) &&  ($translation->slug=="" || $translation->slug==null) )
                    {

                        $translation->slug = static::generateUniqueSlug($translation->title);
                    }
                }
            }
            else
            {
                if( in_array('slug', $query->fillable) &&  ($query->slug=="" || $query->slug==null) )
                {
                    $query->slug = static::generateUniqueSlug($query->title);
                }
            }



            if( in_array('published_at', $query->fillable) &&  ($query->published_at=="" || $query->published_at==null) )
            {
                $query->published_at = Carbon::now();
            }

        });

        static::saving(function ($query)
        {
            $query->updated_by = Auth::user()->id?? 1;

            if( isset(static::$modelLang) && static::$modelLang)
            {
                foreach($query->translations as $translation)
                {
                    if( in_array('slug', array_keys($translation->attributes)) &&  ($translation->slug=="" || $translation->slug==null) )
                    {
                        $source = $translation->title;
                        if($translation->title == "")
                        {
                            $source = $translation->language;
                        }
                        if($query->title == $translation->title && $translation->language != "en")
                        {
                            $source .= "-".$translation->language;
                        }

                        $translation->slug = static::generateUniqueSlug($source);
                    }
                }
            }
            else
            {
                if( in_array('slug', $query->fillable) &&  ($query->slug=="" || $query->slug==null) )
                {
                    $query->slug = static::generateUniqueSlug($query->title);
                }
            }

        });

        static::deleted(function ($query)
        {
            if(isset($query->seo) && $query->seo)
            {
                $query->seo->delete();
            }

        });


        static::saved( function(){
            Artisan::call('cache:clear');
        });


    }


    public static function generateUniqueSlug($name)
    {


        if(isset(static::$modelLang) && static::$modelLang)
        {
            $slug = trim($name);


            // Remove Arabic Tashkeel (Diacritics)
            $slug = preg_replace('/\p{M}/u', '', Normalizer::normalize($slug, Normalizer::FORM_D));

            // Lower case everything
            // using mb_strtolower() function is important for non-Latin UTF-8 string | more info: http://goo.gl/QL2tzK
            $slug = mb_strtolower($slug, "UTF-8");

            // Make alphanumeric (removes all other characters)
            // this makes the string safe especially when used as a part of a URL
            // this has been modified to keep arabic charactrs as well
            $slug = preg_replace("/[^a-z0-9_\s\-\p{Arabic}]/u", "", $slug);

            // Remove multiple dashes or whitespaces
            $slug = preg_replace("/[\s-]+/", " ", $slug);

            // Convert whitespaces and underscore to the given separator
            $slug = preg_replace("/[\s_]/", "-", $slug);

            $count = static::$modelLang::where('slug', 'LIKE', "{$slug}%")->count();
        }
        else
        {
            $slug = Str::slug($name);
            $count = static::withTrashed()->where('slug', 'LIKE', "{$slug}%")->count();
        }


        return $count > 0 ? "{$slug}-{$count}" : $slug;
    }


    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function getPublishedAtNormalNameDateAttribute()
    {
        return date("d/m/Y", strtotime($this->published_at) );
    }

    public function getPublishedAtDetailedDateAttribute()
    {
        return date("H:i:s d-m-Y", strtotime($this->published_at));
    }


    public function creator()
	{
		return $this->belongsTo(User::class,'created_by','id');
	}

    public function getCreatorName()
    {
        return $this->creator->email;
    }

    public function updater()
	{
		return $this->belongsTo(User::class,'updated_by','id');
	}

    public static function getAllItemList()
    {
        $items = self::with('translations')->get()->mapWithKeys(function($i) {
            return [$i->id => $i->title];
        });
        return $items;
    }

    public static function getActiveItemList()
    {
        $items = self::with('translations')->active()->get()->mapWithKeys(function($i) {
            return [$i->id => $i->title];
        });
        return $items;
    }


}
