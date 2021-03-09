<?php

namespace App\Controller;

use App\Shopify\OAuthService;
use App\Shopify\ShopifyAdminApi;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for OAuth / install functions.
 */
class InstallController
{
    /**
     * OAuth service.
     *
     * @var \App\Shopify\OAuthService
     */
    private $oAuthService;

    /**
     * Shopify admin Api.
     *
     * @var \App\Shopify\ShopifyAdminApi
     */
    private $shopifyAdminApi;

    /**
     * Logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * InstallController constructor.
     *
     * @param OAuthService $oAuthService
     *   OAuth service.
     * @param \Psr\Log\LoggerInterface $appLogger
     *   Logger.
     */
    public function __construct(OAuthService $oAuthService, ShopifyAdminApi $shopifyAdminApi, LoggerInterface $appLogger) {
        $this->oAuthService = $oAuthService;
        $this->shopifyAdminApi = $shopifyAdminApi;
        $this->logger = $appLogger;
    }

    /**
     * The OAuth callback called during install.
     *
     * @Route("/shopify/oauth-callback", name="shopify_oauth_callback", methods={"GET"})
     */
    public function oAuthCallback(Request $request)
    {
        // Check HMAC and nonce.
        if (!$this->oAuthService->validateCallback($request)) {
            return new Response('Bad request', Response::HTTP_BAD_REQUEST);
        }

        $shop = $request->query->get('shop');
        $this->oAuthService->requestAccessToken($shop, $request->query->get('code'));

        $this->shopifyAdminApi->setShop($shop);
        $this->shopifyAdminApi->registerWebhooks();

        $this->logger->info('App installed on {shop}', ['shop' => $shop]);

        return new RedirectResponse("https://$shop/admin/apps");
    }
}
