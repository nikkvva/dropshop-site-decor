<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Once;

/** Настройка сайта «ключ → значение». Все читаются одним запросом на страницу. */
class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function read(string $key): ?string
    {
        return once(fn () => self::pluck('value', 'key')->all())[$key] ?? null;
    }

    public static function write(string $key, ?string $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        Once::flush();
    }

    /** URL картинки, загруженной в админке, а если её нет — заглушки шаблона. */
    public static function image(string $key, string $default): string
    {
        $path = self::read($key);

        return asset($path ? 'uploads/'.$path : $default);
    }
}
