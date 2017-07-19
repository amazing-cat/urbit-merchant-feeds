<?php

namespace Urbit\ProductFeed\Cron;

use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Store\Model\Store;
use Urbit\ProductFeed\Model\Collection\Product as ProductCollection;
use Urbit\ProductFeed\Model\Collection\ProductFactory as ProductCollectionFactory;
use Urbit\ProductFeed\Model\Config\Config;
use Urbit\ProductFeed\Model\Config\ConfigFactory;
use Urbit\ProductFeed\Helper\Feed as FeedHelper;

/**
 * Class GenerateFeed
 * @package Urbit\ProductFeed\Helper
 *
 * Command to execute
 * php bin/magento cron:run --group="urbit_crongroup"
 */
class GenerateFeed
{
    /**
     * @var Config
     */
    protected $_config;

    /**
     * @var ProductCollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var FeedHelper
     */
    protected $_helper;

    /**
     * @var StoreManager
     */
    protected $_storeManager;

    /**
     * GenerateFeed constructor.
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ConfigFactory $configFactory
     * @param FeedHelper $helper
     * @param StoreManager $storeManager
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        ConfigFactory $configFactory,
        FeedHelper $helper,
        StoreManager $storeManager
    ) {
        $this->_helper = $helper;
        $this->_config = $configFactory->create();
        $this->_storeManager = $storeManager;
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Run feeds generation for all stores
     */
    public function execute()
    {
        /** @var Store $store */
        foreach ($this->_storeManager->getStores() as $store) {
            $this->processStore($store);
        }
    }

    /**
     * Process single web store
     * @param Store $store
     */
    protected function processStore(Store $store)
    {
        $this->_storeManager->setCurrentStore($store->getId());

        $feedHelper = $this->_helper;

        /** @var ProductCollection $productCollection */
        $productCollection = $this->_productCollectionFactory->create([
            'filter' => $this->_config->filter,
        ]);

        if (!$feedHelper->checkCache()) {
            $feedHelper->generateFeed($productCollection);
        }
    }

}