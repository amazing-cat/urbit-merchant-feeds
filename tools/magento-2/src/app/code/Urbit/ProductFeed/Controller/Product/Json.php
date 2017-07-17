<?php

namespace Urbit\ProductFeed\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http\Interceptor as HttpResponse;
use Magento\Framework\Controller\Result\JsonFactory;

use Urbit\ProductFeed\Model\Collection\Product as ProductCollection;
use Urbit\ProductFeed\Model\Collection\ProductFactory as ProductCollectionFactory;
use Urbit\ProductFeed\Model\Config\Config;
use Urbit\ProductFeed\Model\Config\ConfigFactory;
use Urbit\ProductFeed\Helper\Feed as FeedHelper;

class Json extends Action
{
    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

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
     * Json Controller constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ConfigFactory $configFactory
     * @param FeedHelper $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductCollectionFactory $productCollectionFactory,
        ConfigFactory $configFactory,
        FeedHelper $helper
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_config = $configFactory->create();
        $this->_helper = $helper;

        parent::__construct($context);
    }

    /**
     * Get feed for current store
     */
    public function execute()
    {
        // Additional time for feed cache to prevent conflict with feed generation cron task
        $this->_config->set("cron/cache_duration", $this->_config->cron["cache_duration"] + 1);

        /** @var ProductCollection $productCollection */
        $productCollection = $this->_productCollectionFactory->create([
            'filter' => $this->_config->filter,
        ]);

        $feedHelper = $this->_helper;

        if (!$feedHelper->checkCache()) {
            $feedHelper->generateFeed($productCollection);
        }

        /** @var HttpResponse $response */
        $response = $this->getResponse();

        $response
            ->setHeader("Content-type", "text/json", true)
            ->setBody($feedHelper->getDataJson())
            ->send()
        ;
    }
}