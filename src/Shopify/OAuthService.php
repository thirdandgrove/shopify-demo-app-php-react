<?php

namespace App\Shopify;

use App\Helpers\HttpClientFactory;
use App\Helpers\LogHelper;
use App\Helpers\RequestValidator;
use App\Storage\KeyValueStore;
use GuzzleHttp\RequestOptions;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides OAuth services.
 */
class OAuthService
{
    // Required scopes.
    const SCOPES = ['read_products', 'write_products'];

    const NONCE_KEY = 'shopify_nonce';
    const ACCESS_TOKEN_KEY = 'shopify_access_token';

    /**
     * Shopify API key.
     *
     * @var string
     */
    private $shopifyApiKey;

    /**
     * Shopify shared secret.
     *
     * @var string
     */
    private $shopifyApiSecret;

    /**
     * Url generator.
     *
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * Key/value store service.
     *
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * Request validator.
     *
     * @var \App\Helpers\RequestValidator
     */
    private $requestValidator;

    /**
     * Http client factory.
     *
     * @var \App\Helpers\HttpClientFactory
     */
    private $httpClientFactory;

    /**
     * OAuthService constructor.
     *
     * @param string $shopifyApiKey
     *   Shopify API key.
     * @param string $shopifyApiSecret
     *   Shopify shared secret.
     * @param UrlGeneratorInterface $urlGenerator
     *  Url generator.
     * @param KeyValueStore $keyValueStore
     *   Key/value store service.
     * @param \App\Helpers\RequestValidator $requestValidator
     *   Request validator.
     * @param \App\Helpers\HttpClientFactory $httpClientFactory
     *   Http client factory.
     */
    public function __construct(
        string $shopifyApiKey,
        string $shopifyApiSecret,
        UrlGeneratorInterface $urlGenerator,
        KeyValueStore $keyValueStore,
        RequestValidator $requestValidator,
        HttpClientFactory $httpClientFactory
    ) {
        $this->shopifyApiKey = $shopifyApiKey;
        $this->shopifyApiSecret = $shopifyApiSecret;
        $this->urlGenerator = $urlGenerator;
        $this->keyValueStore = $keyValueStore;
        $this->requestValidator = $requestValidator;
        $this->httpClientFactory = $httpClientFactory;
    }

    /**
     * Generate authorize / install URL.
     *
     * @param string $shop
     *   Shop id.
     *
     * @return string
     *   Url string.
     *
     * @throws \Exception
     */
    public function generateAuthorizeUrl(string $shop)
    {
        $oauthRedirectUrl = $this->urlGenerator->generate('shopify_oauth_callback', [], UrlGeneratorInterface::ABSOLUTE_URL);

        // Generate URL safe nonce.
        $nonce = bin2hex(random_bytes(32));
        $this->keyValueStore->set($shop, self::NONCE_KEY, $nonce);

        $query = http_build_query([
            'client_id' => $this->shopifyApiKey,
            'scope' => implode(',', self::SCOPES),
            'state' => $nonce,
            'redirect_uri' => $oauthRedirectUrl,
        ]);

        return sprintf('https://%s/admin/oauth/authorize?%s', $shop, $query);
    }

    /**
     * Validate the OAuth callback request.
     *
     * @param Request $request
     *   Request.
     *
     * @return bool
     *   True if valid.
     */
    public function validateCallback(Request $request)
    {
        if (!$this->requestValidator->validate($request)) {
            return false;
        }

        $shop = $request->query->get('shop');

        if ($request->query->get('state') !== $this->keyValueStore->get($shop, self::NONCE_KEY)) {
            return false;
        }

        $this->keyValueStore->delete($shop, self::NONCE_KEY);

        return true;
    }

    /**
     * Request access token. Store result for future use.
     *
     * @param string $shop
     *   The shop id.
     * @param string $code
     *   Code from the callback request.
     */
    public function requestAccessToken(string $shop, string $code)
    {
        $client = $this->httpClientFactory->createForShopifyOauth($shop);

        try {
            $response = $client->post('access_token', [
                RequestOptions::JSON => [
                    'client_id' => $this->shopifyApiKey,
                    'client_secret' => $this->shopifyApiSecret,
                    'code' => $code,
                ]
            ]);

        }
        catch (RequestException $exception) {
            $this->logHelper->logRequestException($exception, 'Error requesting access token', $this->loggerChannel);
            throw $exception;
        }

        $data = json_decode($response->getBody());

        // Save permanent access token for later use.
        $this->keyValueStore->set($shop, self::ACCESS_TOKEN_KEY, $data->access_token);
    }
}
