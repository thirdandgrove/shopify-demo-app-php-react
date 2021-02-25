<?php

namespace App\Helpers;

use Symfony\Component\HttpFoundation\Request;

/**
 * Code to validate a request.
 */
class RequestValidator
{
    private $shopifyApiSecret;

    public function __construct($shopifyApiSecret)
    {
        $this->shopifyApiSecret = $shopifyApiSecret;
    }

    /**
     * Validate HMAC in a request.
     *
     * This is used to valid GET request which occur during installation and if
     * the app link is clicked in Shopify admin.
     * Note that webhooks are validated differently.
     *
     * @param Request $request
     *   Request
     *
     * @return bool
     *   True if valid.
     */
    public function validate(Request $request)
    {
        $hmac = $request->query->get('hmac');

        if (empty($hmac)) {
            return false;
        }

        $queryVars = $request->query->all();
        unset($queryVars['hmac']);

        // Build message like a query string. We could probably use
        // http_build_query here but the string must be sorted the key.
        $message = [];
        foreach ($queryVars as $key => $value) {
            $message[] = "$key=$value";
        }

        sort($message);
        $message = implode('&', $message);

        $digest = hash_hmac('sha256', $message, $this->shopifyApiSecret, false);
        return hash_equals($hmac, $digest);
    }
}