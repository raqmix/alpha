<?php

namespace App\Models\Media;

use Awcodes\Curator\Models\Media as ModelsMedia;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;


class Media extends ModelsMedia implements Auditable
{

    use \OwenIt\Auditing\Auditable;

    /**
     * Curator v5 renamed its default table from `media` to `curator`. This
     * project's data lives in `media`, so keep pointing at it.
     */
    protected $table = 'media';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($query) 
        {
            if(!$query->created_by)
            {
                $query->created_by =  Auth::user()->id;
            }
            if(!$query->updated_by)
            {
                $query->updated_by = Auth::user()->id;
            }

        });

        static::updating(function ($query) 
        {
            if(!$query->updated_by)
            {
                $query->updated_by = Auth::user()->id;
            }
        });


    }

}
