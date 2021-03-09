<?php

namespace App\Controller;

use App\Shopify\ShopifyAdminApi;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController
{
    /**
     * Receive app/uninstalled webhook.
     *
     * @Route("/webhook/app-uninstalled", name="webhook_app_uninstalled", methods={"POST"})
     */
    public function appUninstalled(Request $request, ShopifyAdminApi $shopifyAdminApi, LoggerInterface $appLogger)
    {
        if (!$shopifyAdminApi->validateWebhook($request)) {
            return new Response('Bad request', Response::HTTP_BAD_REQUEST);
        }

        $shop = $request->headers->get('x-shopify-shop-domain');
        $shopifyAdminApi->setShop($shop);
        $shopifyAdminApi->uninstallApp();

        $appLogger->info('App uninstalled from {shop}.', ['shop' => $shop]);

        return new Response('OK');
    }
}
