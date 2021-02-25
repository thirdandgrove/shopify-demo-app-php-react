<?php


namespace App\Controller;

use App\Helpers\AnalyticsService;
use App\Helpers\HttpClientFactory;
use App\Helpers\JwtHelper;
use App\Shopify\ShopifyAdminApi;
use GuzzleHttp\RequestOptions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller to provide a http proxy for GraphQL requests.
 */
class ProxyController extends AbstractController
{
    /**
     * Http client factory.
     *
     * @var HttpClientFactory
     */
    private $clientFactory;

    /**
     * Jwt helper.
     *
     * @var JwtHelper
     */
    private $jwtHelper;

    /**
     * ProxyController constructor.
     *
     * @param HttpClientFactory $clientFactory
     *   Http client factory.
     * @param JwtHelper $jwtHelper
     *   Jwt helper.
     */
    public function __construct(HttpClientFactory $clientFactory, JwtHelper $jwtHelper)
    {
        $this->clientFactory = $clientFactory;
        $this->jwtHelper = $jwtHelper;
    }

    /**
     * Http proxy route for GraphQL.
     *
     * We need this to enable client code to be able to call the admin API
     * without exposing the shared secret or requiring CORS. This is similar to
     * https://www.npmjs.com/package/@shopify/koa-shopify-graphql-proxy
     *
     * @Route("/graphql", name="graphql", methods={"POST"})
     */
    public function graphQL(Request $request) {
        $response = $this->authenticateApiRequest($request);
        if ($response !== null) {
            return $response;
        }

        $httpClient = $this->clientFactory->createForShopifyApi($request->query->get('shop'), 'application/json');

        // We're transparently passing a JSON string.
        $response = $httpClient->post('graphql.json', [
            RequestOptions::BODY => $request->getContent(),
        ]);

        return new JsonResponse($response->getBody(), 200, [], true);
    }

    /**
     * Get product GraphQL ID for client app.
     *
     * @Route("/api/products/{productId}/info.json", name="api_products_info")
     */
    public function getProductInfo(Request $request, $productId, ShopifyAdminApi $adminApi) {
        $response = $this->authenticateApiRequest($request);
        if ($response !== null) {
            return $response;
        }

        $adminApi->setShop($request->query->get('shop'));
        $product = $adminApi->getProduct($productId, ['admin_graphql_api_id']);

        return new JsonResponse([
            'graphQlId' => $product['admin_graphql_api_id'],
        ]);
    }

    /**
     * Authenticate an API request.
     *
     * @param Request $request
     *   Request.
     *
     * @return JsonResponse|null
     *   Response if unauthorized.
     */
    private function authenticateApiRequest(Request $request) {
        $shop = $request->query->get('shop');
        $authHeader = $request->headers->get('authorization');

        if (!$this->jwtHelper->validateAuthorizationHeader($shop, $authHeader)) {
            return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
        }

        return null;
    }
}
