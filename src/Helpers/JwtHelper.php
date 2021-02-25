<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;

/**
 * Class to provide JWT functionality.
 */
class JwtHelper
{
    /**
     * Shopify API key.
     *
     * @var string
     */
    private $shopifyApiKey;

    /**
     * Shopify API secret.
     *
     * @var string
     */
    private $shopifyApiSecret;


    /**
     * JwtHelper constructor.
     *
     * @param string $shopifyApiKey
     *   Shopify API key.
     * @param string $shopifyApiSecret
     *   Shopify API secret.
     */
    public function __construct(string $shopifyApiKey, string $shopifyApiSecret)
    {
        $this->shopifyApiKey = $shopifyApiKey;
        $this->shopifyApiSecret = $shopifyApiSecret;
    }

    /**
     * Validate authorization header.
     *
     * @param string $shop
     *   Shop origin.
     * @param string $authHeader
     *   Authorization header from request.
     *
     * @return bool
     *   True if successfully authenicated.
     */
    public function validateAuthorizationHeader(string $shop, string $authHeader) {
        // Remove the word "Bearer".
        $jwt = explode(' ', $authHeader)[1] ?? null;
        $jwtData  = null;

        try {
            $jwtData = JWT::decode($jwt, $this->shopifyApiSecret, ['HS256']);
        }
        catch (SignatureInvalidException $exception) {
            return false;
        }

        $issUrl = parse_url($jwtData->iss);
        $destUrl = parse_url($jwtData->dest);
        $now = time();

        return ($jwtData->exp > $now)
            && ($jwtData->nbf <= $now)
            && ($issUrl['host'] === $shop)
            && ($destUrl['host'] === $shop)
            && ($jwtData->aud === $this->shopifyApiKey);
    }
}
