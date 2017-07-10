<?php

/**
 * Class Urber_ProductFeed_Helper_Feed
 */
class Urber_ProductFeed_Helper_Feed extends Mage_Core_Helper_Abstract
{
    /**
     * feed file generation
     * @param Urber_ProductFeed_Model_List_Product $collection product collection
     * @return string
     */
    public function generateFeed($collection)
    {
        $validated_products = [];

        foreach ($collection as $product) {
            $model = Mage::getModel('catalog/product')->load($product->getId());

            $res = $model->getResource();
            
            if ($model->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                $entities = [];

                /**
                 * Product categories
                 * @var array
                 */
                $categories = [];
                $category_ids = $model->getCategoryIds();

                foreach ($category_ids as $category_id) {
                    $category = (object) Mage::getModel('catalog/category')->load($category_id);
                    $categories[] = [
                        'id' => (int) $category->getId(),
                        'parentId' => (int) $category->getParentId(),
                        'name' => $category->getName(),
                    ];
                }

                /**
                 * list of image URL's
                 * @var array
                 */
                $additional_image_links = [];
                $gallery_images = $model->getMediaGalleryImages();

                foreach($gallery_images as $g_image) {
                    $additional_image_links[] = $g_image['url'];
                }

                $entities['categories'] = $categories;
                $entities['additional_image_links'] = $additional_image_links;

                $entities["image_link"]  = Mage::helper('catalog/image')->init($model, 'image');
                $entities["additional_image_links"]  = $additional_image_links;
                $entities["link"]  = $model->getProductUrl();

                /**
                 * item_group_id
                 */
                $parent_ids = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($model->getId());

                if (!$parent_ids) {
                    $parent_ids = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($model->getId());
                }

                if (isset($parent_ids[0])){
                    $entities['item_group_id'] = Mage::getModel('catalog/product')->load($parent_ids[0])->getSku();
                }

                $entities['name'] = $model->getName();
                $entities['description'] = $model->getDescription();
                $entities['id'] = (string) $model->getId();

                $entities['prices'] = array(
                    array(
                        "currency" => Mage::app()->getStore()->getCurrentCurrencyCode(),
                        "value" => number_format($model->getPrice(), 2),
                        "type" => "regular",
                    ),
                );

                // TODO: special price or discount rules ?

                if (!is_null($model->getSpecialPrice())) {
                    $from = new DateTime($model->getSpecialFromDate());
                    $to = new DateTime($model->getSpecialToDate());
                    $value = number_format($model->getSpecialPrice(), 2);

                    $entities['prices'][] = array(
                        "currency" => Mage::app()->getStore()->getCurrentCurrencyCode(),
                        "value" => $value,
                        "type" => "sale",
                        'price_effective_date' => $from->format('c').'/'.$to->format('c')
                    );
                } else if (Mage::getModel('catalogrule/rule')->calcProductPriceRule($model, $model->getPrice())) {
                    $entities['prices'][] = array(
                        "currency" => Mage::app()->getStore()->getCurrentCurrencyCode(),
                        "value" => Mage::getModel('catalogrule/rule')->calcProductPriceRule($model, $model->getPrice()),
                        "type" => "sale",
                    );
                }

                $fields = Mage::getModel("productfeed/config")->get('fields');

                // TODO: need mpn

                if ($attr = $res->getAttribute($fields['ean'])) {
                    $entities['gtin'] = $attr
                        ->getFrontend()
                        ->getValue($model)
                    ;
                }

                // TODO: need unit for weigh
                $entities['dimensions'] = array(
                    'weight' => array(
                        'value' => (float) $model->getWeigth(),
                        'unit'  => ''
                    ),
                );

                foreach (array('height', 'length', 'width') as $key) {
                    $keyField = "dimention_{$key}";

                    if (!isset($fields[$keyField]) || !$fields[$keyField]) {
                        continue;
                    }

                    $attr = $res->getAttribute($fields[$keyField]);

                    if (!$attr) {
                        continue;
                    }

                    // TODO: dimensions unit value
                    $entities['dimensions'][$key] = array(
                        'value' => (int) $attr->getFrontend()->getValue($model),
                        'unit'  => '',
                    );
                }

                foreach (array("sizeType", "size", "color", "gender", "material", "pattern", "age_group", "condition") as $key) {
                    if (!isset($fields[$key]) || !$fields[$key]) {
                        continue;
                    }

                    $attr = $res->getAttribute($fields[$key]);

                    if (!$attr) {
                        continue;
                    }

                    $entities[$key]  = $attr->getFrontend()->getValue($model);
                }

                array_push($validated_products, $entities);
            }
        }

        $this->setDataJson(
            json_encode($validated_products, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Check cache on expire. True if it still valid
     * @return bool
     */
    public function checkCache()
    {
        // TODO: check cache on expire
        return false;
    }

    /**
     * Get feed cache file path
     * @return string
     */
    public function getCacheFileName()
    {
        return Mage::getBaseDir('cache') . DS . "productfeed.json";
    }

    /**
     * Get data from cache file
     * @return mixed
     */
    public function getData()
    {
        return json_decode($this->getDataJson(), true);
    }

    /**
     * Get plain json from cache file
     * @return string
     */
    public function getDataJson()
    {
        return file_get_contents($this->getCacheFileName());
    }

    /**
     * Set data to cache file
     * @param mixed $data
     * @return bool|int
     */
    public function setData($data)
    {
        return $this->setDataJson(json_encode($data));
    }

    /**
     * Set json data to cache file
     * @param string $json
     * @return bool|string
     */
    public function setDataJson($json)
    {
        return file_put_contents($this->getCacheFileName(), $json);
    }
}