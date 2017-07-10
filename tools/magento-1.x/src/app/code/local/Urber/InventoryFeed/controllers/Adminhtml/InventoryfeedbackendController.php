<?php

class Urber_InventoryFeed_Adminhtml_InventoryfeedbackendController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed()
	{
		//return Mage::getSingleton('admin/session')->isAllowed('inventoryfeed/inventoryfeedbackend');
		return true;
	}

	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Urber Inventory Feed"));
	   $this->renderLayout();
    }
}