<?php

namespace App\Controller;

use App\Shopify\OAuthService;
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
     * InstallController constructor.
     *
     * @param OAuthService $oAuthService
     *   OAuth service.
     */
    public function __construct(OAuthService $oAuthService) {
        $this->oAuthService = $oAuthService;
    }

    /**
     * Call this with ?shop= to install in a store.
     *
     * @Route("/shopify/install", name="shopify_install", methods={"GET"})
     */
    public function install(Request $request)
    {
        $shop = $request->query->get('shop');

        if (empty($shop)) {
            return new Response('Bad request', Response::HTTP_BAD_REQUEST);
        }

        if (!empty($this->allowedShops) && !in_array($shop, explode('|', $this->allowedShops))) {
            return new Response('Not authorized', Response::HTTP_UNAUTHORIZED);
        }

        return new RedirectResponse($this->oAuthService->generateAuthorizeUrl($shop));
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

        return new RedirectResponse("https://$shop/admin/apps");
    }
}
