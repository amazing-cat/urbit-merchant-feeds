<?php

if (!defined( 'URBIT_INVENTORY_FEED_PLUGIN_DIR' )) {
    exit;
}

/**
 * Class UIF_Admin_Config_Page
 */
class UIF_Admin_Config_Page extends UIF_Admin_Page_Abstract
{
    /**
     * Page slug
     */
    const SLUG = 'inventory-feed';

    /**
     * Setup template
     */
    const BASE_TEMPLATE = 'admin/config_page';

    /**
     * @var array
     */
    protected $viewVars = array();

    /**
     * Init menu element
     */
    protected function init()
    {
        $this->menuElement = new UIF_Admin_Menu_Element(
            'Urbit Inventory Feed Settings',
            'Inventory Feed',
            'manage_options',
            static::SLUG
        );

        //init hooks
        add_action('admin_init', array($this, 'registerSettings'));
    }

    /**
     * Register settings
     */
    public function registerSettings()
    {
        $optionGroup = 'inventoryfeed_group';

        // parameters: $option_group, $option_name, $sanitize_callback
        register_setting($optionGroup, UIF_Config::CONFIG_KEY);

        //add sections to view
        $this->viewVars['option_group'] = $optionGroup;
        $this->viewVars['sections'] = array();

        $this->initSectionCron();
        $this->initSectionFilter();
    }

    protected function initSectionCron()
    {
        $cacheSection = new UIF_Admin_Settings_Section('inventoryfeed_cache', 'Feed Cache');

        $cacheSection->addField(new UIF_Admin_Settings_Field(
            'urbit_feed_cache_field',
            'Cache Duration (in minutes)',
            $cacheSection->getPageId(),
            'admin/fields/input',
            array(
                'type'  => 'number',
                'name'  => UIF_Config::CONFIG_KEY . '[cron][cache_duration]',
                'value' => esc_attr($this->getConfig("cron/cache_duration", 5)),
            )
        ));

        $cacheSection->registerSection();
        $this->viewVars['sections'][] = $cacheSection;
    }

    protected function initSectionFilter()
    {
        $filterSection = new UIF_Admin_Settings_Section('inventoryfeed_filter', 'Product Filter');


        $filterSection->addField(new UIF_Admin_Settings_Field(
            'urbit_product_filter_categories_field',
            'Categories',
            $filterSection->getPageId(),
            'admin/fields/multiselect',
            array(
                'name' => UIF_Config::CONFIG_KEY . '[filter][categories][]',
                'size' => count($this->getCategoriesWithSelected()),
                'elements' => $this->getCategoriesWithSelected()
            )
        ));

        $filterSection->addField(new UIF_Admin_Settings_Field(
            'urbit_product_filter_tags_field',
            'Tags',
            $filterSection->getPageId(),
            'admin/fields/multiselect',
            array(
                'name'     => UIF_Config::CONFIG_KEY . '[filter][tags][]',
                'size'     => count($this->getTagsWithSelected()),
                'elements' => $this->getTagsWithSelected(),
            )
        ));

        $filterSection->addField(new UIF_Admin_Settings_Field(
            'urbit_product_filter_stock_field',
            'Minimal Stock',
            $filterSection->getPageId(),
            'admin/fields/input',
            array(
                'type' => 'number',
                'name' => UIF_Config::CONFIG_KEY . '[filter][stock]',
                'value' => esc_attr($this->getConfig("filter/stock"))
            )
        ));

        $filterSection->registerSection();
        $this->viewVars['sections'][] = $filterSection;
    }

        /**
     * @return array
     */
    protected function getCategoriesWithSelected()
    {
        $result     = array();
        $categories = get_categories(array('taxonomy' => 'product_cat'));

        $selectedCategories = $this->core->getConfig()->getSelect("filter/categories", []);

        foreach ($categories as $category) {
            $param = '';

            if (!empty($selectedCategories)) {
                $param = in_array($category->term_id, $selectedCategories) ? 'selected="selected"' : '';
            }

            $result[] = array(
                'value' => $category->term_id,
                'param' => $param,
                'text' => $category->cat_name
            );
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getTagsWithSelected()
    {
        $result = array();
        $tags   = get_terms(array('taxonomy' => 'product_tag'));

        $selectedTags = $this->core->getConfig()->getSelect("filter/tags", []);

        foreach ($tags as $tag){
            $param = '';

            if (!empty($selectedTags)) {
                $param = in_array($tag->term_id, $selectedTags) ? 'selected="selected"' : '';
            }

            $result[] = array(
                'value' => $tag->term_id,
                'param' => $param,
                'text'  => $tag->name
            );
        }

        return $result;
    }

    /**
     * Override parent class function
     * Add view vars to print function
     *
     * @param array $vars
     * @param string|null $template
     */
    public function printTemplate($vars = array(), $template = null)
    {
        $vars = array_merge((array) $vars, $this->viewVars);

        parent::printTemplate($vars, $template);
    }

    /**
     * Helper function
     * Get config param
     * @param string $name
     * @return mixed
     */
    protected function getConfig($name)
    {
        return $this->core->getConfig()->get($name, '');
    }
}