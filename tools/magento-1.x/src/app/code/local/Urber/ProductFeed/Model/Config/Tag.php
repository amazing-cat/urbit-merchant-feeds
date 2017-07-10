<?php

class Urber_ProductFeed_Model_Config_Tag extends Urber_ProductFeed_Model_Config_Abstract
{
    /**
     * Provide available product tags as a value/label array
     *
     * @return array
     */
    public function toOptionArray()
    {
        // TODO: implement fetching tags from db
        return [
            ['value' => 1, 'label' => 'Tag 1'],
            ['value' => 2, 'label' => 'Tag 2'],
            ['value' => 3, 'label' => 'Tag 3'],
            ['value' => 4, 'label' => 'Tag 4']
        ];
    }
}