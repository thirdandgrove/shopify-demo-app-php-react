<?php

namespace App\Helpers;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Helper for some config.
 */
class ConfigHelper {

    /**
     * Base URL.
     *
     * @var string
     */
    private $baseUrl;

    /**
     * Request stack.
     *
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * ConfigHelper constructor.
     *
     * @param string $baseUrl
     *   Base URL.
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     *   Request stack.
     */
    public function __construct(string $baseUrl, RequestStack $requestStack) {
        $this->baseUrl = $baseUrl;
        $this->requestStack = $requestStack;
    }

    /**
     * Get base URL from config or request.
     *
     * @return string
     *   Base URL.
     */
    public function getBaseUrl()
    {
        return !empty($this->baseUrl)
            ? $this->baseUrl
            : $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();
    }
}
