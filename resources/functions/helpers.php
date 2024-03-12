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

if (!function_exists('site_config')) {
    /**
     * function site_config
     *
     * @param string $configKey
     * Using dot notation like `site_config('admin.logo')`
     *
     * @param mixed $default
     * @param int $limit
     * if `$limit === 0` has no limit
     *
     * @return mixed
     */
    function site_config(
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

        if (!$group || !$key || in_array($invalidString, [$group , $key])) {
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
