<?php

namespace Urbit\ProductFeed\Model\Feed;

use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Store\Api\Data\StoreInterface as MagentoStore;
use Magento\Framework\App\Config\ScopeConfigInterface as MagentoConfig;
use Magento\Directory\Api\CountryInformationAcquirerInterface as MagentoCountryInformation;
use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;
use Urbit\ProductFeed\Model\Collection\Product as ProductCollection;
use Urbit\ProductFeed\Model\Config\Config;
use Urbit\ProductFeed\Model\Config\ConfigFactory;

use Magento\Framework\Exception\NoSuchEntityException;

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
     * @var MagentoStore
     */
    protected $_store;

    /**
     * @var LocaleResolver
     */
    protected $_locale;

    /**
     * @var MagentoConfig
     */
    protected $_scopeConfig;

    /**
     * @var MagentoCountryInformation
     */
    protected $_countryInformation;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @var array
     */
    protected $_data = [];

    /**
     * Feed constructor.
     * @param ProductCollection $products
     * @param ConfigFactory $configFactory
     * @param FeedProductFactory $feedProductFactory
     * @param MagentoStore $store
     * @param LocaleResolver $locale
     * @param MagentoConfig $scopeConfig
     * @param MagentoCountryInformation $countryInformation
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ProductCollection $products,
        ConfigFactory $configFactory,
        FeedProductFactory $feedProductFactory,
        MagentoStore $store,
        LocaleResolver $locale,
        MagentoConfig $scopeConfig,
        MagentoCountryInformation $countryInformation,
        ProductRepository $productRepository
    ) {
        $this->_products = $products;
        $this->_config   = $configFactory->create();
        $this->_store    = $store;
        $this->_locale   = $locale;
        $this->_scopeConfig = $scopeConfig;
        $this->_countryInformation = $countryInformation;
        $this->_feedProductFactory = $feedProductFactory;
        $this->_productRepository  = $productRepository;

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

        $lang = $this->_locale->getLocale();
        $countryCode = null;

        try {
            $countryID = $this->_scopeConfig->getValue('general/store_information/country_id');
            $country = $this->_countryInformation->getCountryInfo($countryID);
            $countryCode = $country->getTwoLetterAbbreviation() ?: $lang;
        } catch (NoSuchEntityException $e) {
            $countryCode = $lang;
        }

        $version = $this->getFeedVersion();

        return array(
            '$schema' => "https://raw.githubusercontent.com/urbitassociates/urbit-merchant-feeds/master/schemas/inventory/{$version}/product.json",
            'content_language' => $lang,
            'attribute_language' => $lang,
            'content_type' => 'products',
            'target_country' => array(
                $countryCode,
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