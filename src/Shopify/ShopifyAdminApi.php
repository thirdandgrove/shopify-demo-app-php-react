<?php

namespace App\Shopify;

use App\Helpers\HttpClientFactory;
use App\Storage\KeyValueStore;
use GuzzleHttp\Exception\RequestException;

/**
 * Functionality to interact with the Shopify admin REST API.
 */
class ShopifyAdminApi
{
    /**
     * ID / host name for the store.
     * To conform with what seems to be a loose Shopify convention, the variable
     * is called "shop" but the comments etc tend to use "store".
     *
     * @var string
     */
    private $shop;

    /**
     * Http client configured for shop.
     *
     * @var \GuzzleHttp\Client
     */
    private $httpClient;

    /**
     * Http client factory.
     *
     * @var \App\Helpers\HttpClientFactory
     */
    private $httpClientFactory;

    /**
     * Key / value store.
     *
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * ShopifyAdminApi constructor.
     *
     * @param \App\Helpers\HttpClientFactory $httpClientFactory
     *   Http client factory.
     * @param KeyValueStore $keyValueStore
     *   Key / value store.
     */
    public function __construct(
        HttpClientFactory $httpClientFactory,
        KeyValueStore $keyValueStore
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->keyValueStore = $keyValueStore;
    }

    /**
     * Set the store.
     *
     * This must be set before making an API calls.
     *
     * @param string $shop
     *   Store id / host name.
     */
    public function setShop(string $shop)
    {
        $this->shop = $shop;
    }

    /**
     * Get a product by ID.
     *
     * @param string $productId
     *   Product ID.
     * @return string.
     *    GraphQL id..
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getProduct(string $productId, array $fields = [])
    {
        $options = [];
        if (!empty($fields)) {
            $options = [
                'query' => ['fields' => implode(',', $fields)],
            ];
        }

        try {
            $response = $this->getHttpClient()->get("products/{$productId}.json", $options);
        } catch (RequestException $exception) {
            throw $exception;
        }

        return json_decode($response->getBody(), TRUE)['product'];
    }

    /**
     * Get http client configured for admin API for set store.
     *
     * @return \GuzzleHttp\Client
     *   Http client.
     *
     * @throws \Exception
     */
    private function getHttpClient()
    {
        if (empty($this->httpClient)) {
            $this->httpClient = $this->httpClientFactory->createForShopifyApi($this->shop);
        }

        return $this->httpClient;
    }
}
