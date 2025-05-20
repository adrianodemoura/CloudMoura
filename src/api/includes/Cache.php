<?php

namespace CloudMoura\Api\Includes;

class Cache {
    private static array $cache = [];
    
    public static function set(string $key, $value, int $ttl = 3600): void {
        self::$cache[$key] = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
    }
    
    public static function get(string $key) {
        if (isset(self::$cache[$key]) && self::$cache[$key]['expires'] > time()) {
            return self::$cache[$key]['value'];
        }
        return null;
    }
    
    public static function delete(string $key): void {
        unset(self::$cache[$key]);
    }
    
    public static function clear(): void {
        self::$cache = [];
    }
} 