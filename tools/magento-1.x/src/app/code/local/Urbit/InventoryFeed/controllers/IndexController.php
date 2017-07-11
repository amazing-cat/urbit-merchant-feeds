<?php

/**
 * Class Urbit_InventoryFeed_IndexController
 */
class Urbit_InventoryFeed_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action for plugin frontend
     * Show product feed in json format
     */
    public function IndexAction()
    {
	    $this->loadLayout();

	    /** @var Urbit_InventoryFeed_Model_Config $config */
        $config = Mage::getModel("inventoryfeed/config");

        // Additional time for feed cache to prevent conflict with feed generation cron task
        $config->set("cron/cache_duration", $config->cron["cache_duration"] + 10);

        /** @var Urbit_InventoryFeed_Block_Xml $block */
        $block = $this->getLayout()->getBlock("xml");

        $block->setProductsByFilter($config->filter);

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json', true)
        ;

        $this->renderLayout();
    }
}