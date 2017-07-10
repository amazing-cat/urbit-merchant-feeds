<?php

class Urber_ProductFeed_Model_Config_Manufacturer extends Urber_ProductFeed_Model_Config_Abstract
{
    /**
     * Provide available manufacturers/brands as a value/label array
     *
     * @return array
     */
    public function toOptionArray()
    {
        // TODO: implement fetching manufacturers from db
        return [
            ['value' => 1, 'label' => 'Manufacturer 1'],
            ['value' => 2, 'label' => 'Manufacturer 2'],
            ['value' => 3, 'label' => 'Manufacturer 3'],
            ['value' => 4, 'label' => 'Manufacturer 4']
        ];
    }
}