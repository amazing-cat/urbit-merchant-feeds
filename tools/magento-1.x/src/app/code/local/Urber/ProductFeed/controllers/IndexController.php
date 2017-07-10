<?php

class Urber_ProductFeed_IndexController extends Mage_Core_Controller_Front_Action
{
    public function IndexAction()
    {
	    $this->loadLayout();

	    /** @var Urber_ProductFeed_Model_Config $config */
        $config = Mage::getModel("productfeed/config");

        /** @var Urber_ProductFeed_Block_Xml $block */
        $block = $this->getLayout()->getBlock("xml");

        $block->setProductsByFilter($config->filter);

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json', true)
        ;

        $this->renderLayout();
    }
}