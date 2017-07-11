<?php

/**
 * Class Urbit_InventoryFeed_Adminhtml_InventoryfeedbackendController
 */
class Urbit_InventoryFeed_Adminhtml_InventoryfeedbackendController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Redirect to system configuration page of plugin
     */
	public function indexAction()
    {
        Mage::app()->getResponse()->setRedirect(
            Mage::helper("adminhtml")->getUrl(
                "adminhtml/system_config/edit/section/inventoryfeed_config"
            )
        );
    }
}