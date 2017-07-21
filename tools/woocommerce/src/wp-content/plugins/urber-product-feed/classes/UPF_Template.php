<?php

if (!defined( 'URBER_PRODUCT_FEED_PLUGIN_DIR' )) {
    exit;
}

/**
 * Class UrberProductFeedTemplate
 */
class UPF_Template
{

    /**
     * @var UPF_Core
     */
    protected $core;

    /**
     * UPF_Template constructor.
     * @param UPF_Core $core
     */
    public function __construct(UPF_Core $core)
    {
        $this->core = $core;
        $this->init();
    }

    /**
     * Initialize plugin templates
     */
    protected function init()
    {
        if ($this->core->checkWoocommerce()){
            add_filter('template_include', array($this, 'filter_template_include'), 10, 1);
        }
    }

    /**
     * Check if page is feed and rewrite template
     * @param string $template
     * @return string
     */
    public function filter_template_include($template)
    {
        if (is_page('urber-product-feed')){
            // $this->core->feed->generate();
            // wp_die();
            $template = URBER_PRODUCT_FEED_TEMPLATES_DIR . '/feed.php';
        }

        return $template;
    }
}