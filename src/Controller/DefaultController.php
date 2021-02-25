<?php

namespace App\Controller;

use App\Helpers\RequestValidator;
use App\Shopify\OAuthService;
use App\Storage\KeyValueStore;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * Shopify API key.
     *
     * @var string
     */
    private $shopifyApiKey;

    /**
     * DefaultController constructor.
     *
     * @param string $shopifyApiKey
     *   Shopify API key.
     */
    public function __construct(string $shopifyApiKey)
    {
        $this->shopifyApiKey = $shopifyApiKey;
    }

    /**
     * Index route for React apps.
     *
     * @Route("/", name="index", methods={"GET"})
     * @Route("/edit-product", name="edit_product", methods={"GET"})
     */
    public function index(Request $request, RequestValidator $requestValidator, KeyValueStore $keyValueStore, OAuthService $authService)
    {
        if (!$requestValidator->validate($request)) {
            return new Response('Bad request', Response::HTTP_BAD_REQUEST);
        }

        $shop = $request->query->get('shop');

        $accessToken = $keyValueStore->get($shop, OAuthService::ACCESS_TOKEN_KEY);
        if (empty($accessToken)) {
            // To install a "custom" app, Shopify will request the home page. If
            // the token doesn't exist then the app has not yet been installed.
            return new RedirectResponse($authService->generateAuthorizeUrl($shop));
        }

        return $this->render('index.html.twig', [
            'appSettings' => json_encode([
                'shopOrigin' => $shop,
                'apiKey' => $this->shopifyApiKey
            ]),
        ]);
    }
}
