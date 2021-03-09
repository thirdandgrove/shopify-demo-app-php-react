<?php

namespace App\Controller;

use App\Helpers\RequestValidator;
use App\Shopify\OAuthService;
use App\Storage\KeyValueStore;
use Psr\Log\LoggerInterface;
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
     * Request validator.
     *
     * @var \App\Helpers\RequestValidator
     */
    private $requestValidator;

    /**
     * Key / value store.
     *
     * @var \App\Storage\KeyValueStore
     */
    private $keyValueStore;

    /**
     * Oauth service.
     *
     * @var \App\Shopify\OAuthService
     */
    private $authService;

    /**
     * Logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * DefaultController constructor.
     *
     * @param \App\Helpers\RequestValidator $requestValidator
     *   Request validator.
     * @param \App\Storage\KeyValueStore $keyValueStore
     *   Key / value store.
     * @param \App\Shopify\OAuthService $authService
     *   Oauth service.
     * @param \Psr\Log\LoggerInterface $appLogger
     *   Logger.
     * @param string $shopifyApiKey
     *   Shopify API key.
     */
    public function __construct(
        RequestValidator $requestValidator,
        KeyValueStore $keyValueStore,
        OAuthService $authService,
        LoggerInterface $appLogger,
        string $shopifyApiKey
    )
    {
        $this->requestValidator = $requestValidator;
        $this->keyValueStore = $keyValueStore;
        $this->authService = $authService;
        $this->logger = $appLogger;
        $this->shopifyApiKey = $shopifyApiKey;
    }

    /**
     * Index route for React apps.
     *
     * @Route("/", name="index", methods={"GET"})
     * @Route("/edit-product", name="edit_product", methods={"GET"})
     */
    public function index(Request $request)
    {
        if (!$this->requestValidator->validate($request)) {
            return new Response('Bad request', Response::HTTP_BAD_REQUEST);
        }

        $shop = $request->query->get('shop');

        $accessToken = $this->keyValueStore->get($shop, OAuthService::ACCESS_TOKEN_KEY);
        if (empty($accessToken)) {
            // To install a "custom" app, Shopify will request the home page. If
            // the token doesn't exist then the app has not yet been installed.
            $this->logger->info('Install process started for {shop}', ['shop' => $shop]);

            return new RedirectResponse($this->authService->generateAuthorizeUrl($shop));
        }

        return $this->render('index.html.twig', [
            'appSettings' => json_encode([
                'shopOrigin' => $shop,
                'apiKey' => $this->shopifyApiKey
            ]),
        ]);
    }
}
