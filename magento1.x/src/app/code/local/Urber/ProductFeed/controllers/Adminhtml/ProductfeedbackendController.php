<?php

class Urber_ProductFeed_Adminhtml_ProductfeedbackendController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed()
	{
		//return Mage::getSingleton('admin/session')->isAllowed('productfeed/productfeedbackend');
		return true;
	}

	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Urber Product Feed"));
	   $this->renderLayout();
    }
}