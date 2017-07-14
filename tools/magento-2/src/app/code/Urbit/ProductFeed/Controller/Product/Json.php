<?php

namespace Urbit\ProductFeed\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
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
     * @var ProductCollection
     */
    protected $_products;

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
     * @internal param Config $config
     * @internal param ProductCollection $productCollection
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
     *
     */
    public function execute()
    {
        /**
         * Additional time for feed cache to prevent conflict with feed generation cron task
         */
        $this->_config->set("cron/cache_duration", $this->_config->cron["cache_duration"] + 10);

        $productCollection = $this->_productCollectionFactory->create([
            'filter' => $this->_config->filter,
        ]);

        $productCollection->getCollection()
            ->addAttributeToSelect('*')
            ->setPageSize(30)
        ;

        $this->_products = $productCollection;


        $json = $this->getProductsJson();

        header("Content-type: text/json");
        echo $json;

        exit(0);
    }

    /**
     * Return json feed data
     * @return string
     */
    public function getProductsJson()
    {
        return $this->_helper
            ->generateFeed($this->_products)
            ->getDataJson()
        ;
    }
}