<?php

namespace App\Command;

use App\Shopify\ShopifyAdminApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to get a list of registered webhooks. Used mostly for debugging.
 */
class GetWebhooksCommand extends Command
{
    protected static $defaultName = 'shopify-demo:get-webhooks';

    /**
     * Shopify admin API.
     *
     * @var \App\Shopify\ShopifyAdminApi
     */
    private $shopifyAdminApi;

    /**
     * GetWebhooksCommand constructor.
     *
     * @param \App\Shopify\ShopifyAdminApi $shopifyAdminApi
     *   Shopify admin API.
     */
    public function __construct(ShopifyAdminApi $shopifyAdminApi)
    {
        parent::__construct();
        $this->shopifyAdminApi = $shopifyAdminApi;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Get a list of registered webhooks')
            ->addArgument('shop', InputArgument::REQUIRED, 'Store host name');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->shopifyAdminApi->setShop($input->getArgument('shop'));
        $data = $this->shopifyAdminApi->getWebhooks();
        $output->writeln(json_encode($data));
        return 0;
    }
}
