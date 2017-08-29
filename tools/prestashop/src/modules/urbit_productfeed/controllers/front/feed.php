<?php

include_once(_PS_MODULE_DIR_ . 'urbit_productfeed' . DIRECTORY_SEPARATOR . 'Helper' . DIRECTORY_SEPARATOR . 'FeedHelper.php');
include_once(_PS_MODULE_DIR_ . 'urbit_productfeed' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'Feed.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Urbit_ProductfeedFeedModuleFrontController
 */
class Urbit_ProductfeedFeedModuleFrontController extends ModuleFrontController
{
    protected $_products;

    /**
     *
     */
    public function initContent()
    {
        parent::initContent();
        header('Content-Type: application/json');
        $this->setTemplate('module:urbit_productfeed/views/templates/front/feedtemp.tpl');

        $context = Context::getContext();
        $this->_products = Product::getProducts($context->language->id, 0, 0, 'id_product', 'DESC');
        $feed = new Feed($this->_products);

        echo json_encode($feed->toArray(), JSON_PRETTY_PRINT);
    }

    /**
     * @return string
     */
    public function getProductsJson()
    {
        $feedHelper = new FeedHelper();

        $feedHelper->generateFeed($this->_products);

        return $feedHelper->getDataJson();
    }
}
