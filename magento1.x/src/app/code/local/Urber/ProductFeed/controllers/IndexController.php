<?php

class Urber_ProductFeed_IndexController extends Mage_Core_Controller_Front_Action
{
    public function IndexAction()
    {
	    $this->loadLayout();

	    // TODO: Get filter data from config
        $config = Mage::getModel("productfeed/config");
	    $filter = array();

        $this->getLayout()->getBlock("xml")->setProductsByFilter($filter);

        header('Content-Type: application/json');

        $this->renderLayout();
    }
}