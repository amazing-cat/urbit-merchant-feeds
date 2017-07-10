<?php

use Urber_ProductFeed_Helper_Data as Helper;

class Urber_ProductFeed_Model_Cron extends Mage_Core_Helper_Abstract
{
	/**
	 * Generate feed file to cache
	 * @return [type] [description]
	 */
	public function generateFeed()
    {
		$filename = 'feed';
		file_put_contents(Mage::getBaseDir('cache') . DS . $filename, print_r(Helper::generateFeed(), true));
	}
}