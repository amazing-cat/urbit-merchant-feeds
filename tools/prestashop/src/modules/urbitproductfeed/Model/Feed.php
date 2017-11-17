<?php
/**
 * 2015-2017 Urb-it
 *
 * NOTICE OF LICENSE
 *
 *
 *
 * Do not edit or add to this file if you wish to upgrade Urb-it to newer
 * versions in the future. If you wish to customize Urb-it for your
 * needs please refer to https://urb-it.com for more information.
 *
 * @author    Urb-it SA <parissupport@urb-it.com>
 * @copyright 2015-2017 Urb-it SA
 * @license  http://www.gnu.org/licenses/
 */

include_once(_PS_MODULE_DIR_ . 'urbitproductfeed' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'Feed' . DIRECTORY_SEPARATOR . 'FeedProduct.php');

/**
 * Class Feed
 */
class UrbitProductfeedFeed
{
    /**
     * Schedule intervals
     */
    const SCHEDULE_INTERVAL_HOURLY = 'HOURLY';
    const SCHEDULE_INTERVAL_DAILY = 'DAILY';
    const SCHEDULE_INTERVAL_WEEKLY = 'WEEKLY';
    const SCHEDULE_INTERVAL_MONTHLY = 'MONTHLY';

    const SCHEDULE_INTERVAL_HOURLY_TIME = 1;
    const SCHEDULE_INTERVAL_DAILY_TIME = 24;
    const SCHEDULE_INTERVAL_WEEKLY_TIME = 168;
    const SCHEDULE_INTERVAL_MONTHLY_TIME = 5040;

    /**
     * Feed version
     */
    const FEED_VERSION = '2017-06-28-1';

    /**
     * Valid products for using in feed
     * @var array
     */
    protected $data = array();

    /**
     * Collection of shop's products
     * @var array
     */
    protected $collection = array();

    /**
     * Prestashop Context
     * @var object
     */
    protected $context = null;

    /**
     * Feed constructor.
     * @param $collection
     */
    public function __construct($collection)
    {
        $this->collection = $collection;
        $this->context = Context::getContext();
    }

    /**
     * Process products to use in feed
     */
    protected function process()
    {
        $inventory = array();
        $minimalStockFilterValue = Configuration::get('URBITPRODUCTFEED_MINIMAL_STOCK', null);

        foreach ($this->collection as $product) {
            // get all combinations of product
            $combinations = $this->getCombinations($product['id_product']);

            // simple product
            if (empty($combinations) && $product['name'] != '') {
                if (Product::getQuantity($product['id_product']) <= 0) {
                    continue;
                }

                $feedProduct = new UrbitProductfeedFeedProduct($product);

                if ($feedProduct->process()) {
                    $inventory[] = $feedProduct->toArray();
                }
            //product with variables
            } else {
                foreach ($combinations as $combId => $combination) {
                    $minStock = $minimalStockFilterValue && ($combination['quantity'] < $minimalStockFilterValue);

                    if ($minStock || $combination['quantity'] <= 0) {
                        continue;
                    }

                    $feedProduct = new UrbitProductfeedFeedProduct($product, $combId, $combination);

                    if ($feedProduct->process()) {
                        $inventory[] = $feedProduct->toArray();
                    }
                }
            }
        }

        $this->data = $inventory;
    }

    /**
     * Returns array with feed
     * @return array
     */
    public function toArray()
    {
        if (empty($this->data)) {
            $this->process();
        }

        $lang = (version_compare(_PS_VERSION_, "1.7", "<")) ? $this->context->language->language_code : $this->context->language->locale;
        $version = $this->getFeedVersion();

        $feedArray = array(
            '$schema'            => Configuration::get('URBITPRODUCTFEED_SCHEMA', null) ?: "https://raw.githubusercontent.com/urbitassociates/urbit-merchant-feeds/master/schemas/products/{$version}/products.json",
            'content_language'   => Configuration::get('URBITPRODUCTFEED_CONTENT_LANGUAGE', null) ?: $lang,
            'attribute_language' => Configuration::get('URBITPRODUCTFEED_CONTENT_LANGUAGE', null) ?: $lang,
            'content_type'       => Configuration::get('URBITPRODUCTFEED_CONTENT_TYPE', null) ?: 'products',
            'target_country'     => Configuration::get('URBITPRODUCTFEED_TARGET_COUNTRY', null)
                ? explode(",", Configuration::get('URBITPRODUCTFEED_TARGET_COUNTRY', null)) : array($lang),
            'version'            => Configuration::get('URBITPRODUCTFEED_VERSION', null) ?: $version,
            'feed_format'        => array(
                "encoding" => Configuration::get('URBITPRODUCTFEED_FEED_FORMAT', null) ?: "UTF-8",
            ),
            'schedule'           => array(
                'interval' => $this->getIntervalText(),
            ),
        );

        if ($created_at = Configuration::get('URBITPRODUCTFEED_CREATED_AT', null)) {
            $feedArray['created_at'] = $created_at;
        }

        if ($updated_at = Configuration::get('URBITPRODUCTFEED_UPDATED_AT', null)) {
            $feedArray['updated_at'] = $updated_at;
        }

        $feedArray['entities'] = $this->data;

        return $feedArray;
    }

    /**
     * return all combinations(variants) of product
     * @param $productId
     * @return array Combinations with quantity, price, attributes information
     */
    public function getCombinations($productId)
    {
        $context = Context::getContext();
        $productEntity = new Product($productId);

        $infoArray = array();

        //get all variants of product
        $combinations = $productEntity->getAttributeCombinations($context->language->id);

        foreach ($combinations as $combination) {
            if (!array_key_exists($combination['id_product_attribute'], $infoArray)) {
                $infoArray[$combination['id_product_attribute']] = array(
                    'quantity'   => $combination['quantity'],
                    'price'      => number_format((float)Product::getPriceStatic($productId, true, $combination['id_product_attribute']), 2, '.', ''),
                    'attributes' => array($combination['group_name'] => $combination['attribute_name']),
                    'product_id' => $combination['id_product'],
                );
            } else {
                $infoArray[$combination['id_product_attribute']]['attributes'][$combination['group_name']] = $combination['attribute_name'];
            }
        }

        return $infoArray;
    }

    /**
     * Get categories filters from config
     * @return array|null
     */
    public static function getCategoryFilters()
    {
        $filterValue = Configuration::get('URBITPRODUCTFEED_FILTER_CATEGORIES', null);

        return $filterValue ? explode(',', $filterValue) : null;
    }

    /**
     * Get tags filters from config
     * @return array|null
     */
    public static function getTagsFilters()
    {
        $filterValue = Configuration::get('URBITPRODUCTFEED_TAGS_IDS', null);

        return $filterValue ? explode(',', $filterValue) : null;
    }

    /**
     * Get Product collection filtered by categories and tags
     * @param $id_lang
     * @param $start
     * @param $limit
     * @param $order_by
     * @param $order_way
     * @param bool $categoriesArray
     * @param bool $tagsArray
     * @param bool $only_active
     * @param Context|null $context
     * @return array|false|mysqli_result|null|PDOStatement|resource
     */
    public static function getProductsFilteredByCategoriesAndTags(
        $id_lang,
        $start,
        $limit,
        $order_by,
        $order_way,
        $categoriesArray = false,
        $tagsArray = false,
        $only_active = false,
        Context $context = null
    ) {

        if (!$context) {
            $context = Context::getContext();
        }

        $front = in_array($context->controller->controller_type, array('front', 'modulefront'));

        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)) {
            die(Tools::displayError());
        }


        if (in_array($order_by, array('id_product', 'price', 'date_add', 'date_upd'))) {
            $order_by_prefix = 'p';
        } elseif ($order_by == 'name') {
            $order_by_prefix = 'pl';
        } elseif ($order_by == 'position') {
            $order_by_prefix = 'c';
        }

        if (strpos($order_by, '.') > 0) {
            $order_by = explode('.', $order_by);
            $order_by_prefix = $order_by[0];
            $order_by = $order_by[1];
        }

        $sql = 'SELECT p.*, product_shop.*, pl.* , m.`name` AS manufacturer_name, s.`name` AS supplier_name
				FROM `' . _DB_PREFIX_ . 'product` p
				' . Shop::addSqlAssociation('product', 'p') . '
				LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ')
				LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
				LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON (s.`id_supplier` = p.`id_supplier`)' .
            ($categoriesArray ? 'LEFT JOIN `' . _DB_PREFIX_ . 'category_product` c ON (c.`id_product` = p.`id_product`)' : '') . ' ' .
            ($tagsArray ? 'LEFT JOIN `' . _DB_PREFIX_ . 'product_tag` pt ON (pt.`id_product` = p.`id_product`)' : '') . ' ' .
            ($tagsArray ? 'LEFT JOIN `' . _DB_PREFIX_ . 'tag` t ON (pt.`id_tag` = t.`id_tag`)' : '') . '
				WHERE pl.`id_lang` = ' . (int)$id_lang .
            ($categoriesArray ? ' AND c.`id_category` in (' . implode(',', $categoriesArray) . ')' : '') .
            ($tagsArray ? ' AND t.`name` in ("' . implode(',', $tagsArray) . '")' : '') .
            ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '') .
            ($only_active ? ' AND product_shop.`active` = 1' : '') . '
				ORDER BY ' . (isset($order_by_prefix) ? pSQL($order_by_prefix) . '.' : '') . '`' . pSQL($order_by) . '` ' . pSQL($order_way) .
            ($limit > 0 ? ' LIMIT ' . (int)$start . ',' . (int)$limit : '')
        ;

        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if ($order_by == 'price') {
            Tools::orderbyPrice($rq, $order_way);
        }

        foreach ($rq as &$row) {
            $row = Product::getTaxesInformations($row);
        }

        return ($rq);
    }

    /**
     * Get schedule interval value
     * @return string
     */
    public function getIntervalText()
    {
        $cacheDuration = Configuration::get('URBITPRODUCTFEED_CACHE_DURATION', null);

        if (!$cacheDuration) {
            return static::SCHEDULE_INTERVAL_HOURLY;
        }

        foreach (array(
            self::SCHEDULE_INTERVAL_HOURLY_TIME  => self::SCHEDULE_INTERVAL_HOURLY,
            self::SCHEDULE_INTERVAL_DAILY_TIME   => self::SCHEDULE_INTERVAL_DAILY,
            self::SCHEDULE_INTERVAL_WEEKLY_TIME  => self::SCHEDULE_INTERVAL_WEEKLY,
            self::SCHEDULE_INTERVAL_MONTHLY_TIME => self::SCHEDULE_INTERVAL_MONTHLY,
        ) as $time => $val) {
            if ($cacheDuration <= $time) {
                return $val;
            }
        }

        return static::SCHEDULE_INTERVAL_HOURLY;
    }

    /**
     * @return string
     */
    public function getFeedVersion()
    {
        return static::FEED_VERSION;
    }
}
