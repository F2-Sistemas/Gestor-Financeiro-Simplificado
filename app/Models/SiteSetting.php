<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[\AllowDynamicProperties]
class SiteSetting extends Model
{
    use HasFactory;

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
        return $this->getParsedValue(null);
    }

    public function getValueAttribute()
    {
        return $this->getParsedValue(null);
    }

    public function getTypeAttribute()
    {
        return $this->content?->get('type') ?? null;
    }

    public function getParsedValue(mixed $defaultValue = null): mixed
    {
        $content = $this->{'content'} ?? null;

        if (!$content) {
            return $defaultValue;
        }

        $type = $content?->get('type'); // WIP
        $value = $content?->get('value'); // WIP
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
            'callable' => is_callable(
                filter_var($value, FILTER_DEFAULT, FILTER_NULL_ON_FAILURE)
            ) ? filter_var($value, FILTER_DEFAULT, FILTER_NULL_ON_FAILURE) : $defaultValue,
            default => $value,
        }; // WIP
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
     * @return mixed
     */
    public static function getSetting(
        string $group,
        ?string $key = null,
        mixed $default = null,
        int $limit = 30,
    ): mixed {
        $query = static::query()
            ->where('group', $group)
            ->where('active', true);

        if (!is_null($key)) {
            $noData = 'NO-DATA';
            $value = $query->where('key', $key)?->first()?->getParsedValue($noData) ?? $noData;

            return ($value && $value !== $noData) ? $value : $default;
        }

        if ($limit > 0) {
            $query = $query->limit($limit);
        }

        return $query->withCasts(['parsedValue'])->get();
    }
}
