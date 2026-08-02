<?php

namespace App\Models\Setting;

use App\Models\Base\BaseModelNotForAdmin;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Lang;
use Outerweb\Settings\Models\Setting as ModelsSetting;
use OwenIt\Auditing\Contracts\Auditable;

class Setting extends ModelsSetting implements Auditable
{

    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'key',
        'language',
        'value',
    ];

    public static function get(string $key = '*', mixed $default = null): mixed
    {
        $settings = cache()->rememberForever(config('settings.cache_key'), function () {
            $settings = [];

            self::all()->each(function ($setting) use (&$settings) {
                data_set($settings, $setting->key, $setting->attributes['value']);
            });

            return $settings;
        });

        if ($key === '*') {
            return $settings;
        }

        return data_get($settings, $key, $default);
    }

    public static function set(string $key, mixed $value): mixed
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            // ['language' => $language],
            ['value' => $value]
        );

        cache()->forget(config('settings.cache_key'));

        return $setting->value;
    }

    public function getTable(): string
    {
        return config('settings.database_table_name', 'settings');
    }



    public static function getDateFormats()
    {
        $date = strtotime(date("Y") . '-01-25');
        return [
            'M d, Y' => Carbon::parse($date)->format('M d, Y'), // Example: Jan 22, 2025
            'F d, Y' => Carbon::parse($date)->format('F d, Y'), // Example: January 22, 2025
            'l, d F, Y' => Carbon::parse($date)->format('l, d F, Y'), // Example: Wednesday, January 22, 2025
            'd F Y' => Carbon::parse($date)->format('d F Y'), // Example: 22 January 2025
            'Y-m-d' => Carbon::parse($date)->format('Y-m-d'), // Example: 2025-01-22
            'd/m/Y' => Carbon::parse($date)->format('d/m/Y'), // Example: 22/01/2025
            'm/d/Y' => Carbon::parse($date)->format('m/d/Y'), // Example: 01/22/2025
            'd.m.Y' => Carbon::parse($date)->format('d.m.Y'), // Example: 22.01.2025
        ];
    }

    public static function getTimeFormats()
    {
        $time = Carbon::create(2025, 1, 14, 9, 45, 59);
        return [
            'g:i a' => $time->format('g:i a'), // Example: 9:45 am
            'h:i a' => $time->format('h:i a'), // Example: 09:45 am
            'H:i' => $time->format('H:i'), // Example: 09:45
            'G:i' => $time->format('G:i'), // Example: 9:45
        ];
    }

    public static function getTimezones()
    {
        return [
            "Pacific/Midway" => "(GMT-11:00) Midway Island, Samoa",
            "Etc/GMT+10" => "(GMT-10:00) Hawaii",
            "Pacific/Marquesas" => "(GMT-09:30) Marquesas Islands",
            "America/Anchorage" => "(GMT-09:00) Alaska",
            "America/Los_Angeles" => "(GMT-08:00) Pacific Time (US & Canada)",
            "America/Denver" => "(GMT-07:00) Mountain Time (US & Canada)",
            "America/Chihuahua" => "(GMT-07:00) Chihuahua, La Paz, Mazatlan",
            "America/Dawson_Creek" => "(GMT-07:00) Arizona",
            "America/Belize" => "(GMT-06:00) Saskatchewan, Central America",
            "America/Cancun" => "(GMT-06:00) Guadalajara, Mexico City, Monterrey",
            "Chile/EasterIsland" => "(GMT-06:00) Easter Island",
            "America/Chicago" => "(GMT-06:00) Central Time (US & Canada)",
            "America/New_York" => "(GMT-05:00) Eastern Time (US & Canada)",
            "America/Havana" => "(GMT-05:00) Cuba",
            "America/Bogota" => "(GMT-05:00) Bogota, Lima, Quito, Rio Branco",
            "America/Caracas" => "(GMT-04:30) Caracas",
            "America/Santiago" => "(GMT-04:00) Santiago",
            "America/La_Paz" => "(GMT-04:00) La Paz",
            "America/Campo_Grande" => "(GMT-04:00) Brazil",
            "America/Goose_Bay" => "(GMT-04:00) Atlantic Time (Goose Bay)",
            "America/Glace_Bay" => "(GMT-04:00) Atlantic Time (Canada)",
            "America/St_Johns" => "(GMT-03:30) Newfoundland",
            "America/Araguaina" => "(GMT-03:00) UTC-3",
            "America/Montevideo" => "(GMT-03:00) Montevideo",
            "America/Godthab" => "(GMT-03:00) Greenland",
            "America/Argentina/Buenos_Aires" => "(GMT-03:00) Buenos Aires",
            "America/Sao_Paulo" => "(GMT-03:00) Brasilia",
            "America/Noronha" => "(GMT-02:00) Mid-Atlantic",
            "Atlantic/Cape_Verde" => "(GMT-01:00) Cape Verde Is.",
            "Atlantic/Azores" => "(GMT-01:00) Azores",
            "Europe/London" => "(GMT) Greenwich Mean Time : London",
            "Africa/Abidjan" => "(GMT) Monrovia, Reykjavik",
            "Europe/Amsterdam" => "(GMT+01:00) Western & Central Europe",
            "Africa/Algiers" => "(GMT+01:00) West Central Africa",
            "Africa/Windhoek" => "(GMT+01:00) Windhoek",
            "Africa/Cairo" => "(GMT+02:00) Kiev, Cairo, Pretoria, Jerusalem",
            "Asia/Amman" => "(GMT+02:00) Amman",
            "Europe/Moscow" => "(GMT+03:00) Nairobi, Moscow",
            "Asia/Tehran" => "(GMT+03:30) Tehran",
            "Asia/Dubai" => "(GMT+04:00) Abu Dhabi, Muscat",
            "Asia/Yerevan" => "(GMT+04:00) Yerevan",
            "Asia/Kabul" => "(GMT+04:30) Kabul",
            "Asia/Tashkent" => "(GMT+05:00) Tashkent",
            "Asia/Kolkata" => "(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi",
            "Asia/Katmandu" => "(GMT+05:45) Kathmandu",
            "Asia/Dhaka" => "(GMT+06:00) Astana, Dhaka",
            "Asia/Novosibirsk" => "(GMT+06:00) Novosibirsk",
            "Asia/Rangoon" => "(GMT+06:30) Yangon (Rangoon)",
            "Asia/Bangkok" => "(GMT+07:00) Bangkok, Hanoi, Jakarta",
            "Asia/Hong_Kong" => "(GMT+08:00) Beijing, Hong Kong",
            "Asia/Irkutsk" => "(GMT+08:00) Irkutsk, Ulaan Bataar",
            "Australia/Eucla" => "(GMT+08:45) Eucla",
            "Asia/Tokyo" => "(GMT+09:00) Osaka, Sapporo, Tokyo",
            "Asia/Seoul" => "(GMT+09:00) Seoul",
            "Australia/Adelaide" => "(GMT+09:30) Adelaide",
            "Australia/Brisbane" => "(GMT+10:00) Brisbane",
            "Australia/Hobart" => "(GMT+10:00) Hobart",
            "Asia/Vladivostok" => "(GMT+10:00) Vladivostok",
            "Australia/Lord_Howe" => "(GMT+10:30) Lord Howe Island",
            "Etc/GMT-11" => "(GMT+11:00) Solomon Is., New Caledonia",
            "Pacific/Norfolk" => "(GMT+11:30) Norfolk Island",
            "Asia/Anadyr" => "(GMT+12:00) Anadyr, Kamchatka",
            "Pacific/Auckland" => "(GMT+12:00) Auckland, Wellington",
            "Etc/GMT-12" => "(GMT+12:00) Fiji, Kamchatka, Marshall Is.",
            "Pacific/Chatham" => "(GMT+12:45) Chatham Islands",
            "Pacific/Tongatapu" => "(GMT+13:00) Nuku'alofa",
            "Pacific/Kiritimati" => "(GMT+14:00) Kiritimati",
        ];
    }


}
