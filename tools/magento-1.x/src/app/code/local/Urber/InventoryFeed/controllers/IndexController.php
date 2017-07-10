<?php

class Urber_InventoryFeed_IndexController extends Mage_Core_Controller_Front_Action
{
    public function IndexAction()
    {
        // TODO: Get cached feed file and return it data to output
        $this->loadLayout();
        $this->renderLayout();
    }
}