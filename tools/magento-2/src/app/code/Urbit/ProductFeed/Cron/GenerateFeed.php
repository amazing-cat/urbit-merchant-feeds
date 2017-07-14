<?php

namespace Urbit\ProductFeed\Cron;

use \Psr\Log\LoggerInterface;

/**
 * Command to execute
 * php ../bin/magento cron:run --group="urbit_crongroup"
 */
class GenerateFeed {


    protected $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

  /**
   * Write to system.log
   *
   * @return void
   */

    public function execute() {

        // TODO: get config
        /** @var Urbit_ProductFeed_Model_Config $config */
        // $config = Mage::getModel("productfeed/config");

        // TODO: get product collection
        // $products = Mage::getModel(
        //     "productfeed/list_product",
        //     $config->filter
        // );

        // TODO: generate feed
        // Mage::helper("productfeed/feed")->generateFeed($products);
    }

}