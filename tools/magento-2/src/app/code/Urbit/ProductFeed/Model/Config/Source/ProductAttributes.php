<?php

namespace Urbit\ProductFeed\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Model\Entity\TypeFactory;

/**
 * Class ProductAttributes
 * @package Urbit\ProductFeed\Model\Config\Source
 */
class ProductAttributes extends AbstractSource
{
    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var TypeFactory
     */
    protected $eavTypeFactory;

    /**
     * ProductAttributes constructor.
     * @param AttributeFactory $attributeFactory
     * @param TypeFactory $typeFactory
     */
    public function __construct(AttributeFactory $attributeFactory, TypeFactory $typeFactory)
    {
        $this->attributeFactory = $attributeFactory;
        $this->eavTypeFactory = $typeFactory;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $arr = [
            '' => 'Not setted',
        ];

        $entityType = $this->eavTypeFactory->create()->loadByCode('catalog_product');

        $collection = $this->attributeFactory->create()->getCollection();
        $collection->addFieldToFilter('entity_type_id', $entityType->getId());
        $collection->setOrder('attribute_code');

        foreach ($collection as $attribute) {
            if (!$attribute->getIsUserDefined()) {
                continue;
            }

            $arr[$attribute->getAttributeId()] = $attribute->getFrontendLabel();
        }

        return $arr;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $arr = $this->toArray();
        $ret = [];

        asort($arr);

        foreach ($arr as $key => $value) {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $ret;
    }
}