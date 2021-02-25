<?php

namespace App\Helpers;

use App\Shopify\OAuthService;
use App\Storage\KeyValueStore;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;

/**
 * Create configured http clients.
 */
class HttpClientFactory
{
    /**
     * Shopify API version.
     *
     * @var string
     */
    private $shopifyApiVersion;

    /**
     * Key / Value store.
     *
     * @var \App\Storage\KeyValueStore
     */
    private $keyValueStore;

    /**
     * HttpClientFactory constructor.
     *
     * @param string $shopifyApiVersion
     *   Shopify API version.
     * @param \App\Storage\KeyValueStore $keyValueStore
     *   Key / Value store.
     */
    public function __construct(
        string $shopifyApiVersion,
        KeyValueStore $keyValueStore
    ) {
        $this->shopifyApiVersion = $shopifyApiVersion;
        $this->keyValueStore = $keyValueStore;
    }

    /**
     * Create client for Shopify API.
     *
     * @param string|null $shop
     *   Store id / host name.
     *
     * @return \GuzzleHttp\Client
     *   Http client.
     * @param string}null $contentType
     *   Content type.
     *
     * @throws \Exception
     */
    public function createForShopifyApi(string $shop = null, $contentType = null)
    {
        if (empty($shop)) {
            $message = 'Attempt to create http client before setting store.';
            throw new \InvalidArgumentException($message);
        }

        $accessToken = $this->keyValueStore->get($shop, OAuthService::ACCESS_TOKEN_KEY);

        if (empty($accessToken)) {
            $message = sprintf('Attempt to create http client for %s without access token', $shop);
            throw new \InvalidArgumentException($message);
        }

        $headers = ['X-Shopify-Access-Token' => $accessToken];
        if (!empty($contentType)) {
            $headers['Content-Type'] = $contentType;
        }

        $client = new Client([
            'base_uri' => "https://{$shop}/admin/api/{$this->shopifyApiVersion}/",
            RequestOptions::HEADERS => $headers,
            RequestOptions::TIMEOUT => 30,
            RequestOptions::CONNECT_TIMEOUT => 30,
        ]);

        return $client;
    }

    /**
     * Create client for Shopify OAuth handshake.
     *
     * @param string|null $shop
     *   Store id / host name.
     *
     * @return \GuzzleHttp\Client
     *   Http client.
     *
     * @return \GuzzleHttp\Client
     */
    public function createForShopifyOauth(string $shop)
    {
        return new Client([
           'base_uri' => "https://$shop/admin/oauth/",
        ]);
    }
}
