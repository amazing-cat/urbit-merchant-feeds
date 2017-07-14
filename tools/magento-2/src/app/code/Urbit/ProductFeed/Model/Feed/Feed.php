<?php

namespace Urbit\ProductFeed\Model\Feed;

use Urbit\ProductFeed\Model\Collection\Product as ProductCollection;
use Urbit\ProductFeed\Model\Config\Config;
use Urbit\ProductFeed\Model\Config\ConfigFactory;

/**
 * Class Feed
 * @package Urbit\ProductFeed\Model
 */
class Feed
{
    const SCHEDULE_INTERVAL_HOURLY  = 'HOURLY';
    const SCHEDULE_INTERVAL_DAILY   = 'DAILY';
    const SCHEDULE_INTERVAL_WEEKLY  = 'WEEKLY';
    const SCHEDULE_INTERVAL_MONTHLY = 'MONTHLY';

    const SCHEDULE_INTERVAL_HOURLY_TIME  = 1;
    const SCHEDULE_INTERVAL_DAILY_TIME   = 24;
    const SCHEDULE_INTERVAL_WEEKLY_TIME  = 168;
    const SCHEDULE_INTERVAL_MONTHLY_TIME = 5040;

    const FEED_VERSION = '2017-06-28-1';

    /**
     * @var Config
     */
    protected $_config;

    /**
     * @var ProductCollection
     */
    protected $_products;

    /**
     * @var FeedProductFactory
     */
    protected $_feedProductFactory;

    /**
     * @var array
     */
    protected $_data = [];

    /**
     * Feed constructor.
     * @param ProductCollection $products
     * @param ConfigFactory $configFactory
     * @param FeedProductFactory $feedProductFactory
     */
    public function __construct(
        ProductCollection $products,
        ConfigFactory $configFactory,
        FeedProductFactory $feedProductFactory
    ) {
        $this->_products = $products;
        $this->_config   = $configFactory->create();
        $this->_feedProductFactory = $feedProductFactory;
    }

    /**
     * Process product
     */
    public function process()
    {
        $products = [];

        foreach ($this->_products as $product) {
            $feedProduct = $this->_feedProductFactory->create([
                'product' => $product,
            ]);

            if ($feedProduct->process()) {
                $products[] = $feedProduct->toArray();
            }
        }

        $this->_data = $products;
    }

    /**
     * @return array
     */
    public function toArray()
    {

        if (empty($this->_data)) {
            $this->process();
        }

        // TODO: get current store lang
        $lang = 'en';

        $version = $this->getFeedVersion();

        return array(
            '$schema' => "https://raw.githubusercontent.com/urbitassociates/urbit-merchant-feeds/master/schemas/inventory/{$version}/product.json",
            'content_language' => $lang,
            'attribute_language' => $lang,
            'content_type' => 'products',
            'target_country' => array(
                $lang,
            ),
            'version' => $version,
            'feed_format' => array(
                "encoding" => "UTF-8",
            ),
            'schedule' => array(
                'interval' => $this->getIntervalText(),
            ),
            'entities' => $this->_data,
        );
    }

    /**
     * Get schedule interval value
     * @return string
     */
    public function getIntervalText()
    {
        foreach (array(
            static::SCHEDULE_INTERVAL_HOURLY_TIME  => static::SCHEDULE_INTERVAL_HOURLY,
            static::SCHEDULE_INTERVAL_DAILY_TIME   => static::SCHEDULE_INTERVAL_DAILY,
            static::SCHEDULE_INTERVAL_WEEKLY_TIME  => static::SCHEDULE_INTERVAL_WEEKLY,
            static::SCHEDULE_INTERVAL_MONTHLY_TIME => static::SCHEDULE_INTERVAL_MONTHLY,
        ) as $time => $val) {
            if ($this->_config->cron['cache_duration'] <= $time) {
                return $val;
            }
        }

        return static::SCHEDULE_INTERVAL_HOURLY;
    }

    public function getFeedVersion()
    {
        return static::FEED_VERSION;
    }
}