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
        Mage::app()->getResponse()->setRedirect(
            Mage::helper("adminhtml")->getUrl(
                "adminhtml/system_config/edit/section/productfeed_config"
            )
        );
    }
}