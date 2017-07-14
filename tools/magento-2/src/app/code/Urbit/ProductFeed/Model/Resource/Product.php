<?php

namespace Urbit\ProductFeed\Model\Resource;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Product extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_entity','entity_id');
    }
}