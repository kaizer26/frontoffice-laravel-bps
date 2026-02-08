<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UrlHelper
{
    /**
     * Shorten a URL using TinyURL's free anonymous API.
     * 
     * @param string|null $url
     * @return string|null
     */
    public static function shorten($url)
    {
        if (empty($url)) {
            return null;
        }

        try {
            // Use is.gd API for direct redirection without confirmation pages
            $response = Http::timeout(5)->get('https://is.gd/create.php', [
                'format' => 'simple',
                'url' => $url
            ]);

            if ($response->successful()) {
                return trim($response->body());
            }

            Log::warning('is.gd shortening failed for URL: ' . $url . ' Status: ' . $response->status() . ' Reason: ' . $response->reason() . ' Body: ' . $response->body());
            return $url; // Fallback to original URL
        } catch (\Exception $e) {
            Log::error('UrlHelper shorten error for URL: ' . $url . ' Error: ' . $e->getMessage());
            return $url; // Fallback to original URL
        }
    }
}
