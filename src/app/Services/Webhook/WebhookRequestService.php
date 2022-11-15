<?php

namespace App\Services\Webhook;

use App\Services\Guzzle\GuzzleService as ApiService;
use App\Services\Guzzle\GuzzleAsyncService;

class WebhookRequestService
{
    private const BASE_USER_URL = "https://webhook.site/d5ece900-7ab0-491e-ae1a-3727a74aab10";

    public function request($body)
    {
        $api = new ApiService();
        $api->url = self::BASE_USER_URL;
        $api->body = $body;

        $api->post();
        
        return true;
    }
}