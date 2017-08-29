<?php

include_once(_PS_MODULE_DIR_ . 'urbit_inventoryfeed' . DIRECTORY_SEPARATOR . 'Helper' . DIRECTORY_SEPARATOR . 'FeedHelper.php');
include_once(_PS_MODULE_DIR_ . 'urbit_inventoryfeed' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'Feed.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Urbit_InventoryfeedFeedModuleFrontController
 */
class Urbit_InventoryfeedFeedModuleFrontController extends ModuleFrontController
{
    /**
     * @var
     */
    protected $_products;

    /**
     *
     */
    public function initContent()
    {
        parent::initContent();

        header('Content-Type: application/json');
        $this->setTemplate('module:urbit_inventoryfeed/views/templates/front/feedtemp.tpl');

        $context = Context::getContext();
        $this->_products = Product::getProducts($context->language->id, 0, 0, 'id_product', 'DESC');

        if (isset($_GET['cron'])) {
            $this->generateByCron();
        } else {
            echo $this->getProductsJson();
        }
    }

    /**
     * @return string
     */
    public function getProductsJson()
    {
        $feedHelper = new FeedHelper();

        if (!$feedHelper->checkCache()) {
            $feedHelper->generateFeed($this->_products);
        }

        return $feedHelper->getDataJson();
    }

    /**
     *
     */
    public function generateByCron()
    {
        $feedHelper = new FeedHelper();

        if (!$feedHelper->checkCache()) {
            $feedHelper->generateFeed($this->_products);
        }
    }
}
