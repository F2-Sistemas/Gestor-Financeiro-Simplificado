<?php

use App\Models\SiteSetting;

if (!function_exists('site_setting')) {
    /**
     * function site_setting
     *
     * @param string $group
     * @param ?string $key
     * @param mixed $default
     * @param int $limit
     * if `$limit === 0` has no limit
     *
     * @return mixed
     */
    function site_setting(
        string $group,
        ?string $key = null,
        mixed $default = null,
        int $limit = 30,
    ): mixed {
        return SiteSetting::getSetting(
            $group,
            $key,
            $default,
            $limit,
        );
    }
}

if (!function_exists('site_config_get')) {
    /**
     * function site_config_get
     *
     * @param string $configKey
     * Using dot notation like `site_config_get('admin.logo')`
     *
     * @param mixed $default
     *
     * @return mixed
     */
    function site_config_get(
        string $configKey,
        mixed $default = null,
    ): mixed {
        $configKey = trim($configKey);

        if (!$configKey || !str_contains($configKey, '.')) {
            return $default;
        }

        $explodedConfigKey = explode('.', $configKey, 2);

        $invalidString = 'INVALID';
        $group = trim($explodedConfigKey[0] ?? $invalidString);
        $key = trim($explodedConfigKey[1] ?? $invalidString);

        if (!$group || !$key || in_array($invalidString, [$group, $key])) {
            return $default;
        }

        $value = SiteSetting::getSetting(
            $group,
            $key,
            $invalidString,
        );

        if (is_string($value) && $value === $invalidString) {
            return config($configKey, $default);
        }

        return $value;
    }
}

if (!function_exists('site_config_delete')) {
    /**
     * function site_config_delete
     *
     * @param string $configKey
     * Using dot notation like `site_config_delete('admin.logo')`
     *
     * @param bool $softDelete
     * If `true`, just set 'active' to `false`
     *
     * @return bool
     */
    function site_config_delete(
        string $configKey,
        bool $softDelete = false,
    ): bool {
        $configKey = trim($configKey);

        if (!$configKey || !str_contains($configKey, '.')) {
            return false;
        }

        $explodedConfigKey = explode('.', $configKey, 2);

        $invalidString = 'INVALID';
        $group = trim($explodedConfigKey[0] ?? $invalidString);
        $key = trim($explodedConfigKey[1] ?? $invalidString);

        if (!$group || !$key || in_array($invalidString, [$group, $key])) {
            return false;
        }

        $record = SiteSetting::query()
            ->where('group', $group)
            ->where('key', $key)
            ?->first();

        if (!$record) {
            return false;
        }

        if ($softDelete) {
            return !! $record->update([
                'active' => false,
            ]);
        }

        return $record?->delete() ?? false;
    }
}

if (!function_exists('site_config_set')) {
    /**
     * function site_config_set
     *
     * @param string $configKey
     * Using dot notation like `site_config_set('admin.logo', 'route', ['name' => 'my_route_name', 'params' => [1]])`
     *
     * @param string $type
     * Example: url|route|string|int|float|...
     *
     * @param mixed $value
     * @param array $extraData
     * @param bool $active
     *
     * @return mixed
     */
    function site_config_set(
        string $configKey,
        string $type,
        mixed $value,
        array $extraData = [],
        bool $active = true,
        ?string $castValueUsing = null,
    ): bool {
        $configKey = trim($configKey);

        if (!in_array($type, SiteSetting::VALID_TYPES)) {
            throw new Exception('Invalid "type".', 150);
        }

        if (!$configKey || !str_contains($configKey, '.')) {
            throw new Exception('Invalid "configKey". The "configKey" must be a dot on notation', 250);
        }

        $explodedConfigKey = explode('.', $configKey, 2);

        $invalidString = 'INVALID';
        $group = trim($explodedConfigKey[0] ?? $invalidString);
        $key = trim($explodedConfigKey[1] ?? $invalidString);

        if (!$group || !$key || in_array($invalidString, [$group, $key])) {
            throw new Exception('Invalid "group" or "key". Both need be a valid string', 350);
        }

        $dataToSave = [
            'group' => $group,
            'key' => $key,
            'content' => [
                'type' => $type,
                'value' => $value,
                'castValueUsing' => $castValueUsing && is_callable($castValueUsing) ? $castValueUsing : null,
                'extraData' => $extraData,
            ],
            'active' => $active,
        ];

        $success = SiteSetting::updateOrCreate([
            'group' => $dataToSave['group'],
            'key' => $dataToSave['key'],
        ], $dataToSave);

        return boolval($success);
    }
}
