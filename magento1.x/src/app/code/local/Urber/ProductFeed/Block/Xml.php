<?php   

use Urber_ProductFeed_Helper_Data as Helper;

class Urber_ProductFeed_Block_Xml extends Mage_Core_Block_Template
{
    /**
     * @var Urber_ProductFeed_Model_List_Product
     */
    protected $_products;

    /**
     * @return string
     */
    public function getProductsJson()
    {
        return $this->showCacheFeed();
    }

    public function showCacheFeed()
    {
        $filename = 'feed';
        if (!file_exists(Mage::getBaseDir('cache') . DS . $filename)) {
            file_put_contents(Mage::getBaseDir('cache') . DS . $filename, print_r(Helper::generateFeed(), true));
        }
        $feed = file_get_contents(Mage::getBaseDir('cache') . DS . $filename);
        echo '<pre>';
        echo $feed;
    }

    /**
     * @param array $filter
     */
    public function setProductsByFilter($filter = array())
    {
        $this->_products = Mage::getModel("productfeed/list_product", $filter);
    }
}