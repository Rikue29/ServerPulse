<?php

use App\Models\Configuration;

if (!function_exists('getNotificationEmails')) {
    function getNotificationEmails(string $team): array
    {
        $key = "{$team}_team_emails";
        $raw = Configuration::where('key', $key)->value('value');

        return $raw ? array_map('trim', explode(',', $raw)) : [];
    }
}