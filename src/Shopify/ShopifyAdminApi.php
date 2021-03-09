<?php

namespace App\Shopify;

use App\Helpers\ConfigHelper;
use App\Helpers\HttpClientFactory;
use App\Storage\KeyValueStore;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
     * Config helper.
     *
     * @var \App\Helpers\ConfigHelper
     */
    private $configHelper;

    /**
     * Url generator.
     *
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Shopify API shared secret.
     *
     * @var string
     */
    private $shopifyApiSecret;

    /**
     * ShopifyAdminApi constructor.
     *
     * @param \App\Helpers\HttpClientFactory $httpClientFactory
     *   Http client factory.
     * @param KeyValueStore $keyValueStore
     *   Key / value store.
     * @param \App\Helpers\ConfigHelper $configHelper
     *   Config helper.
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     *   Url generator.
     * @param \Psr\Log\LoggerInterface $appLogger
     *   Logger.
     * @param string $shopifyApiSecret
     *   Shopify API shared secret.
     */
    public function __construct(
        HttpClientFactory $httpClientFactory,
        KeyValueStore $keyValueStore,
        ConfigHelper $configHelper,
        UrlGeneratorInterface $urlGenerator,
        LoggerInterface $appLogger,
        string $shopifyApiSecret
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->keyValueStore = $keyValueStore;
        $this->configHelper = $configHelper;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $appLogger;
        $this->shopifyApiSecret = $shopifyApiSecret;
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
     * @param array $fields
     *   Array of fields.
     * @return string.
     *    Decoded data.
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
            $this->logger->error('Error retrieving product from Shopify REST API.');
            throw $exception;
        }

        return json_decode($response->getBody(), TRUE)['product'];
    }

    /**
     * Get webhooks.
     *
     * @return \stdClass
     *   Decoded response.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getWebhooks()
    {
        try {
            $response = $this->getHttpClient()->get('webhooks.json');
        } catch (RequestException $exception) {
          $this->logger->error('Error getting webhooks for {shop}.', ['shop' => $this->shop]);
          throw $exception;
        }

        return json_decode($response->getBody());
    }

    /**
     * Register required webhooks.
     */
    public function registerWebhooks()
    {
        $topics = [
            // Add webhooks as required.
            'app/uninstalled'=> 'webhook_app_uninstalled',
        ];

        foreach ($topics as $topic => $route) {
            $this->unregisterWebhooks($topic);
            $this->registerWebhook($topic, $route);
        }
    }

    /**
     * Register webhook.
     *
     * @param string $topic
     *   Topic.
     * @param string $route
     *   Symfony route to receive the hook.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function registerWebhook(string $topic, string $route)
    {
        $data = [
            'webhook' => [
                'topic' => $topic,
                'address' => $this->configHelper->getBaseUrl() . $this->urlGenerator->generate($route),
                'format' => 'json',
            ]
        ];

        try {
            $this->getHttpClient()->post('webhooks.json', [
                'json' => $data,
            ]);
        } catch (RequestException $exception) {
            $this->logger->error('Error registering webhook for {shop}.', ['shop' => $this->shop]);
            throw $exception;
        }
    }

    /**
     * Unregister webhooks.
     *
     * @param string $topic
     *    Topic or "all" to unregister all.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function unregisterWebhooks(string $topic)
    {
        $webhooks = $this->getWebhooks()->webhooks;

        if ($topic !== 'all') {
            $webhooks = array_filter($webhooks, function ($hook) use ($topic) {
                return ($hook->topic === $topic);
            });
        }

        foreach ($webhooks as $webhook) {
            $this->unregisterWebhookById($webhook->id);
        }
    }

    /**
     * Unregister webhook by id.
     *
     * @param int $id
     *   Webhook ID.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function unregisterWebhookById(int $id)
    {
        try {
            $this->getHttpClient()->delete("webhooks/{$id}.json");
        } catch (RequestException $exception) {
        }
    }

    /**
     * Validate the hmac of a received webhook.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   Request.
     *
     * @return bool
     *   True if valid.
     */
    public function validateWebhook(Request $request)
    {
        $calculatedHmac = base64_encode(hash_hmac('sha256', $request->getContent(), $this->shopifyApiSecret, true));
        return hash_equals($request->headers->get('X-Shopify-Hmac-SHA256'), $calculatedHmac);
    }

    /**
     * Uninstall app.
     */
    public function uninstallApp() {
        $this->keyValueStore->delete($this->shop, OAuthService::ACCESS_TOKEN_KEY);
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
