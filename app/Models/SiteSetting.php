<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

#[\AllowDynamicProperties]
class SiteSetting extends Model
{
    use HasFactory;

    public const VALID_TYPES = [
        'bool',
        'int',
        'float',
        'url',
        'email',
        'domain',
        'string',
        'route',
        'asset',
        'array',
        'collection',
        'serialized',
    ];

    protected $fillable = [
        'group',
        'key',
        'content',
        'active',
    ];

    protected $casts = [
        'content' => AsCollection::class,
        'active' => 'boolean',
    ];

    protected $appends = [
        'dotNotation',
    ];

    public function getDotNotationAttribute()
    {
        return implode('.', [
            $this->group,
            $this->key,
        ]);
    }

    public function getParsedValueAttribute()
    {
        return $this->getValue(true, null);
    }

    public function getValueAttribute()
    {
        return $this->getValue(true, null);
    }

    public function getTypeAttribute()
    {
        return $this->getContent()?->get('type') ?? null;
    }

    public function getExtraDataAttribute()
    {
        return $this->getContent()?->get('extraData') ?? null;
    }

    public function getValue(bool $parsedValue = true, mixed $defaultValue = null): mixed
    {
        if ($parsedValue) {
            return $this->getParsedValue($defaultValue);
        }

        return $this->getContent()?->get('value') ?? null;
    }

    public function getContent(?Collection $defaultContent = null): Collection
    {
        return ($this->{'content'} ?? $defaultContent) ?: collect();
    }

    public function getParsedValue(mixed $defaultValue = null): mixed
    {
        $content = $this->getContent();

        if (!$content) {
            return $defaultValue;
        }

        $type = $content?->get('type'); // WIP
        $value = $content?->get('value'); // WIP
        $extraData = Arr::wrap($content?->get('extraData') ?: []); // WIP
        $castValueUsing = $content?->get('castValueUsing'); // WIP

        if ($castValueUsing && is_callable($castValueUsing)) {
            return call_user_func($castValueUsing, $value);
        }

        return match (strtolower("{$type}")) {
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE),
            'int' => filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE),
            'float' => filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE),
            'url' => filter_var($value, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE),
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE),
            'domain' => filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_NULL_ON_FAILURE),
            'string' => filter_var($value, FILTER_DEFAULT, FILTER_NULL_ON_FAILURE),
            'route' => static::inlineCasterGet('route', $value, $extraData),
            'asset' => static::inlineCasterGet('asset', $value, $extraData),
            'array' => $value && is_string($value) ? json_decode($value, true) : [],
            'collection' => collect($value && is_string($value) ? json_decode($value, true) : []),
            'serialized' => static::inlineCasterGet('serialized', $value, $extraData),
            'callable' => is_callable(
                filter_var($value, FILTER_DEFAULT, FILTER_NULL_ON_FAILURE)
            ) ? filter_var($value, FILTER_DEFAULT, FILTER_NULL_ON_FAILURE) : $defaultValue,
            default => $value,
        }; // WIP
    }

    public static function inlineCasterGet(string $type, mixed $value, array $extraData = []): mixed
    {
        if ($type === 'route') {
            $value = filter_var($value, FILTER_DEFAULT, FILTER_NULL_ON_FAILURE);

            return $value && Route::has($value) ? route($value, $extraData) : null;
        }

        if ($type === 'asset') {
            $value = filter_var($value, FILTER_DEFAULT, FILTER_NULL_ON_FAILURE);

            return $value ? asset($value) : null;
        }

        if ($type === 'serialized') { // TODO implement try_unserialize
            return is_string($value)
                && str_contains($value, ':')
                && str_contains($value, ';')
                ? unserialize($value) : null;
        }

        return $value;
    }

    public static function inlineCasterSet(string $type, mixed $value): mixed
    {
        if (!$type) {
            return $value;
        }

        return match ($type) {
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE),
            'int' => filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE),
            'float' => filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE),
            'url' => filter_var($value, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE),
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE),
            'domain' => filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_NULL_ON_FAILURE),
            'string' => filter_var($value, FILTER_DEFAULT, FILTER_NULL_ON_FAILURE),
            'route' => Route::has($value) ? $value : null,
            'array' => json_encode($value, 64),
            'collection' => json_encode($value, 64),
            'serialized' => serialize($value),
            default => $value,
        };
    }

    /**
     * function getSetting
     *
     * @param string $group
     * @param ?string $key
     * @param mixed $default
     * @param int $limit
     * if `$limit === 0` has no limit
     *
     * @param bool $activeOnly
     * @param bool $parsedValue
     *
     * @return mixed
     */
    public static function getSetting(
        string $group,
        ?string $key = null,
        mixed $default = null,
        int $limit = 30,
        bool $activeOnly = true,
        bool $parsedValue = true,
    ): mixed {
        $query = static::query()
            ->where('group', $group);

        if ($activeOnly) {
            $query = $query->where('active', true);
        }

        if (!is_null($key)) {
            $noData = 'NO-DATA';

            /**
             * @var string|static $record
             */
            $record = $query->where('key', $key)?->first() ?? $noData;

            if (!$record || $record === $noData) {
                return $default;
            }

            $record = is_object($record) && $parsedValue ? $record?->getValue($parsedValue, $noData) : $record;

            return ($record && $record !== $noData) ? $record : $default;
        }

        if ($limit > 0) {
            $query = $query->limit($limit);
        }

        return $query->withCasts(['parsedValue'])->get();
    }
}
