<?php

namespace App\Storage;

use Predis\Client;

/**
 * Provides shop specific persistent key/value storage.
 */
class KeyValueStore
{
    /**
     * Predis Client.
     *
     * @var \Predis\Client
     */
    private $client;

    /**
     * App prefix.
     *
     * @var string
     */
    private $appPrefix;

    /**
     * KeyValueStore constructor.
     *
     * @param Client $client
     *   Predis client.
     * @param string $appPrefix
     *   App prefix.
     */
    public function __construct(Client $client, string $appPrefix)
    {
        $this->client = $client;
        $this->appPrefix = $appPrefix;
    }

    /**
     * Set a value.
     *
     * @param string $prefix
     *   Key prefix. Use shop id where appropriate.
     * @param string $key
     *   Key.
     * @param string $value
     *   Value.
     */
    public function set(string $prefix, string $key, string $value)
    {
        $this->client->set("{$this->appPrefix}|{$prefix}|{$key}", $value);
    }

    /**
     * Get a value.
     *
     * @param string $prefix
     *   Key prefix. Use shop id where appropriate.
     * @param string $key
     *   Key.
     * @return string
     *   Value.
     */
    public function get(string $prefix, string $key)
    {
        return $this->client->get("{$this->appPrefix}|{$prefix}|{$key}");
    }

    /**
     * Delete a value.
     *
     * @param string $prefix
     *   Key prefix. Use shop id where appropriate.
     * @param string $key
     *   Key.
     */
    public function delete(string $prefix, string $key)
    {
        $this->client->del(["{$this->appPrefix}|{$prefix}|{$key}"]);
    }
}
