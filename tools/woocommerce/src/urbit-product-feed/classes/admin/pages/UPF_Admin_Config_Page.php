<?php

if (!defined( 'URBIT_PRODUCT_FEED_PLUGIN_DIR' )) {
    exit;
}

/**
 * Class UPF_Admin_Config_Page
 */
class UPF_Admin_Config_Page extends UPF_Admin_Page_Abstract
{
    /**
     * Page slug
     */
    const SLUG = 'product-feed';

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
        $this->menuElement = new UPF_Admin_Menu_Element(
            'Urbit Product Feed Settings',
            'Product Feed',
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
        $optionGroup = 'productfeed_group';

        // parameters: $option_group, $option_name, $sanitize_callback
        register_setting($optionGroup, UPF_Config::CONFIG_KEY);

        //add sections to view
        $this->viewVars['option_group'] = $optionGroup;
        $this->viewVars['sections'] = array();

        $this->initSectionCron();
        $this->initSectionFilter();
        $this->initSectionAttributes();
    }

    protected function initSectionCron()
    {
        $cacheSection = new UPF_Admin_Settings_Section('productfeed_cache', 'Feed Cache');

        $cacheSection->addField(new UPF_Admin_Settings_Field(
            'urbit_feed_cache_field',
            'Cache Duration (in hours)',
            $cacheSection->getPageId(),
            'admin/fields/input',
            array(
                'type'  => 'number',
                'name'  => UPF_Config::CONFIG_KEY . '[cron][cache_duration]',
                'value' => esc_attr($this->getConfig("cron/cache_duration", 1)),
            )
        ));

        $cacheSection->registerSection();
        $this->viewVars['sections'][] = $cacheSection;
    }

    protected function initSectionFilter()
    {
        $filterSection = new UPF_Admin_Settings_Section('productfeed_filter', 'Product Filter');


        $filterSection->addField(new UPF_Admin_Settings_Field(
            'urbit_product_filter_categories_field',
            'Categories',
            $filterSection->getPageId(),
            'admin/fields/multiselect',
            array(
                'name' => UPF_Config::CONFIG_KEY . '[filter][categories][]',
                'size' => count($this->getCategoriesWithSelected()),
                'elements' => $this->getCategoriesWithSelected()
            )
        ));

        $filterSection->addField(new UPF_Admin_Settings_Field(
            'urbit_product_filter_tags_field',
            'Tags',
            $filterSection->getPageId(),
            'admin/fields/multiselect',
            array(
                'name'     => UPF_Config::CONFIG_KEY . '[filter][tags][]',
                'size'     => count($this->getTagsWithSelected()),
                'elements' => $this->getTagsWithSelected(),
            )
        ));

        $filterSection->registerSection();
        $this->viewVars['sections'][] = $filterSection;
    }

    protected function initSectionAttributes()
    {
        $attributesSection = new UPF_Admin_Settings_Section('productfeed_attributes', 'Product Attributes');

        foreach (array('size', 'sizeType', 'sizeSystem', 'color', 'gender', 'material', 'pattern', 'age group', 'condition') as $name) {
            $key = str_replace(" ", "_", $name);
            $name = $this->splitAtUpperCase($name);

            $attributesSection->addField(new UPF_Admin_Settings_Field(
                "urbit_product_attribute_{$key}_field",
                ucfirst($name),
                $attributesSection->getPageId(),
                'admin/fields/select',
                array(
                    'name'     => UPF_Config::CONFIG_KEY . "[attributes][{$key}]",
                    'elements' => array_merge(array(
                        '' => array(
                            'value' => '',
                            'text' => 'Not selected',
                        ),
                    ), $this->getAttributes()),
                    'value'    => $this->getConfig("attributes/{$key}")
                )
            ));
        }

        $attributesSection->addField(new UPF_Admin_Settings_Field(
            'urbit_product_additional_fields_field',
            'Additional attributes',
            $attributesSection->getPageId(),
            'admin/fields/multiselect',
            array(
                'name'     => UPF_Config::CONFIG_KEY . '[attributes][additional][]',
                'elements' => $this->selectedAdditionAttributes($this->getAttributes()),
                'size'     => '10',
            )
        ));

        $attributesSection->registerSection();
        $this->viewVars['sections'][] = $attributesSection;
    }

    /**
     * define selected items
     * @param  array $attributes
     * @return array
     */
    protected function selectedAdditionAttributes($attributes)
    {
        $selectedAttributes = $this->core->getConfig()->getSelect("attributes/additional", []);

        foreach ($attributes as $attribute) {

            $param = '';

            if (!empty($selectedAttributes)) {
                $param = in_array($attribute['text'], $selectedAttributes) ? 'selected="selected"' : '';
            }

            $result[] = array(
                'value' => $attribute['text'],
                'param' => $param,
                'text'  => $attribute['text']
            );
        }

        return $result;
    }

    /**
     * explode string based on upper-case characters
     * @param  string $s
     * @return string
     */
    protected function splitAtUpperCase($s)
    {
        $name = preg_split('/(?=[A-Z])/', $s, -1, PREG_SPLIT_NO_EMPTY);

        if (!isset($name[0])) {
            $name[0] = '';
        }

        if (!isset($name[1])) {
            $name[1] = '';
        }

        return trim($name[0] . ' ' . $name[1]);
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
     * @return array
     */
    public function getAttributes()
    {
        $result = array();

        foreach (wc_get_attribute_taxonomies() as $tax) {
            $result[] = array(
                'value' => $tax->attribute_name,
                'param' => '',
                'text'  => $tax->attribute_label,
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