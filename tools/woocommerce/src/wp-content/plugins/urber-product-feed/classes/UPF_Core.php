<?php

if (!defined( 'URBER_PRODUCT_FEED_PLUGIN_DIR' )) {
    exit;
}

/**
 * Class UrberProductFeedCore
 */
class UPF_Core
{
    /**
     * @var UPF_Template
     */
    protected $template;

    /**
     * @var UPF_Cache
     */
    protected $cache;

    /**
     * @var UPF_Feed
     */
    protected $feed;

    /**
     * @var UPF_Query
     */
    protected $query;

    /**
     * @var UPF_Product
     */
    protected $product;

    /**
     * UrberProductFeedCore constructor.
     * @param string $pluginFile
     */
    public function __construct($pluginFile)
    {
        register_activation_hook($pluginFile, array($this, '_install'));
        register_deactivation_hook($pluginFile, array($this, '_uninstall'));

        $this->init();
    }

    /**
     * Plugin initialization
     */
    protected function init()
    {
        $this->template = new UPF_Template($this);
        $this->cache    = new UPF_Cache($this);
        $this->feed     = new UPF_Feed($this);
        $this->query    = new UPF_Query($this);
        $this->product  = new UPF_Product($this);
    }

    /**
     * @return UPF_Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return UPF_Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return UPF_Feed
     */
    public function getFeed()
    {
        return $this->feed;
    }

    /**
     * @return UPF_Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return UPF_Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Plugin installation hook
     */
    public function _install()
    {
        if (!self::checkWoocommerce()) {
            wp_die('Wocoommerce not active! Deactivate plugin.');
        }

        $newPageTitle = URBER_PRODUCT_FEED_PAGE_NAME;
        $pageCheck = get_page_by_title($newPageTitle);

        $newPage = array(
            'post_type' => 'page',
            'post_title' => $newPageTitle,
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 1,
        );

        if(!isset($pageCheck->ID)){
            wp_insert_post($newPage);
        }

        //setup default cache duration
        update_option(URBER_PRODUCTFEED_CONFIG, array(
            'cache' => 1
        ));
    }

    /**
     * Plugin uninstallation hook
     */
    public function _uninstall()
    {
        $pageCheck = get_page_by_title(URBER_PRODUCT_FEED_PAGE_NAME);

        if(! empty($pageCheck->ID)){
            wp_delete_post($pageCheck->ID, true);
        }
    }

    /**
     * @return bool
     */
    public function checkWoocommerce()
    {
        return in_array(
            'woocommerce/woocommerce.php',
            apply_filters( 'active_plugins', get_option( 'active_plugins'))
        );
    }
}

