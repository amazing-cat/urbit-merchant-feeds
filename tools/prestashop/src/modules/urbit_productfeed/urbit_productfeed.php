<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Urbit_productfeed
 */
class Urbit_productfeed extends Module
{
    /**
     * @var bool
     */
    protected $config_form = false;

    /**
     * Urbit_productfeed constructor.
     */
    public function __construct()
    {
        $this->name = 'urbit_productfeed';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Urbit';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Urbit Product Feed');
        $this->description = $this->l('Urbit Product Feed Module');
    }

    /**
     * @return bool
     */
    public function install()
    {
        Configuration::updateValue('PRODUCTFEED_LIVE_MODE', false);

        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install()
            && $this->registerHook('header')
            && $this->registerHook('backOfficeHeader')
        ;
    }

    /**
     * @return mixed
     */
    public function uninstall()
    {
        Configuration::deleteByName('PRODUCTFEED_LIVE_MODE');

        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitProductfeedModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitProductfeedModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $valueArray = $this->getConfigFormValues();

        $valueArray['URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE[]'] = explode(',', Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE', null));
        $valueArray['URBIT_PRODUCTFEED_TAGS_IDS[]'] = explode(',', Configuration::get('URBIT_PRODUCTFEED_TAGS_IDS', null));
        $valueArray['URBIT_PRODUCTFEED_FILTER_CATEGORIES[]'] = explode(',', Configuration::get('URBIT_PRODUCTFEED_FILTER_CATEGORIES', null));

        $helper->tpl_vars = [
            'fields_value' => $valueArray,
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm($this->getProductFeedConfigForm());
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon'  => 'icon-cogs',
                ],
                'input'  => [
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Live mode'),
                        'name'    => 'URBIT_PRODUCTFEED_LIVE_MODE',
                        'is_bool' => true,
                        'desc'    => $this->l('Use this module in live mode'),
                        'values'  => [
                            [
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col'    => 3,
                        'type'   => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc'   => $this->l('Enter a valid email address'),
                        'name'   => 'URBIT_PRODUCTFEED_ACCOUNT_EMAIL',
                        'label'  => $this->l('Email'),
                    ],
                    [
                        'type'  => 'password',
                        'name'  => 'URBIT_PRODUCTFEED_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * return options for attributes selects
     * @param bool $withNotSetted
     * @return array
     */
    protected function getAttributesOptions($withNotSetted = false)
    {
        $optionsForAttributeSelect = [];

        if ($withNotSetted) {
            $optionsForAttributeSelect[] = [
                'id'   => '',
                'name' => 'Not Setted',
            ];
        }


        $attributes = Attribute::getAttributes($this->context->language->id);
        foreach ($attributes as $attribute) {
            $optionsForAttributeSelect[] = [
                'id'   => $attribute['id_attribute_group'],
                'name' => $attribute['attribute_group'],
            ];
        }

        return array_unique($optionsForAttributeSelect, SORT_REGULAR);
    }

    /**
     * return options for categories selects
     */
    protected function getCategoriesOptions()
    {
        $categories = Category::getNestedCategories(null, $this->context->language->id);

        $resultArray = [];

        foreach ($categories as $category) {
            $arr = [];
            $resultArray = array_merge($resultArray, $this->getCategoryInfo($category, $arr, ''));
        }

        return $resultArray;
    }

    /**
     * @param $category
     * @param $arr
     * @param $pref
     * @return array
     */
    protected function getCategoryInfo($category, $arr, $pref)
    {
        $arr[] = [
            'id'   => $category['id_category'],
            'name' => $pref . $category['name'],
        ];

        if (array_key_exists('children', $category)) {
            foreach ($category['children'] as $child) {
                $arr = $this->getCategoryInfo($child, $arr, $pref . $category['name'] . ' / ');
            }
        }

        return $arr;
    }

    /**
     * return options for tags selects
     * @return array
     */
    protected function getTagsOptions()
    {
        $optionsForTagSelect = [];

        $tags = Tag::getMainTags($this->context->language->id);
        foreach ($tags as $tag) {
            $optionsForTagSelect[] = ['id' => $tag['name'], 'name' => $tag['name']];
        }

        return $optionsForTagSelect;
    }

    protected function getCacheOptions()
    {
        return [
            [
                'id'   => 1,
                'name' => 'Hourly',
            ],
            [
                'id'   => 24,
                'name' => 'Daily',
            ],
            [
                'id'   => 168,
                'name' => 'Weekly',
            ],
            [
                'id'   => 5040,
                'name' => 'Monthly',
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getProductFeedConfigForm()
    {
        $optionsForAttributeSelect = $this->getAttributesOptions(true);
        $optionsForFeaturesAndAttributesSelect = $this->getFeaturesAndAttributesOptions(true);
        $optionsForAttributeMultiSelect = $this->getFeaturesAndAttributesOptions(false);;
        $optionsForCategorySelect = $this->getCategoriesOptions();
        $optionsForTagSelect = $this->getTagsOptions();
        $optionsForCacheSelect = $this->getCacheOptions();

        $fields_form = [];

        //Feed Cache
        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('Feed Cache'),
                'icon'  => 'icon-cogs',
            ],
            'input'  => [
                [
                    'type'    => 'select',
                    'label'   => $this->l('Cache duration'),
                    'name'    => 'URBIT_PRODUCTFEED_CACHE_DURATION',
                    'options' => [
                        'query' => $optionsForCacheSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
            ],
        ];

        // Product Filters
        $fields_form[1]['form'] = [
            'legend' => [
                'title' => $this->l('Product Filters'),
                'icon'  => 'icon-cogs',
            ],
            'input'  => [
                [
                    'type'     => 'select',
                    'label'    => $this->l('Categories'),
                    'name'     => 'URBIT_PRODUCTFEED_FILTER_CATEGORIES[]',
                    'multiple' => true,
                    'options'  => [
                        'query' => $optionsForCategorySelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'    => 'fixed-width-xxl',
                ],
                [
                    'type'     => 'select',
                    'label'    => $this->l('Tags'),
                    'name'     => 'URBIT_PRODUCTFEED_TAGS_IDS[]',
                    'multiple' => true,
                    'options'  => [
                        'query' => $optionsForTagSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'    => 'fixed-width-xxl',
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Minimal Stock'),
                    'name'  => 'URBIT_PRODUCTFEED_MINIMAL_STOCK',
                    'class' => 'fixed-width-xxl',
                ],
            ],
        ];

        //Product Dimentions
        $fields_form[2]['form'] = [
            'legend' => [
                'title' => $this->l('Product Fields - Product Dimentions'),
                'icon'  => 'icon-cogs',
            ],
            'input'  => [
                [
                    'type'    => 'select',
                    'label'   => $this->l('Height'),
                    'name'    => 'URBIT_PRODUCTFEED_DIMENSION_HEIGHT',
                    'options' => [
                        'query' => $optionsForFeaturesAndAttributesSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Length'),
                    'name'    => 'URBIT_PRODUCTFEED_DIMENSION_LENGTH',
                    'options' => [
                        'query' => $optionsForFeaturesAndAttributesSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Width'),
                    'name'    => 'URBIT_PRODUCTFEED_DIMENSION_WIDTH',
                    'options' => [
                        'query' => $optionsForFeaturesAndAttributesSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Weight'),
                    'name'    => 'URBIT_PRODUCTFEED_DIMENSION_WEIGHT',
                    'options' => [
                        'query' => $optionsForFeaturesAndAttributesSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
            ],
        ];

        //Units
        $fields_form[3]['form'] = [
            'legend' => [
                'title' => $this->l('Product Fields - Units'),
                'icon'  => 'icon-cogs',
            ],
            'input'  => [
                [
                    'type'  => 'text',
                    'label' => $this->l('Dimension unit'),
                    'name'  => 'URBIT_PRODUCTFEED_DIMENSION_UNIT',
                    'class' => 'fixed-width-xxl',
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Weight unit'),
                    'name'  => 'URBIT_PRODUCTFEED_WEIGHT_UNIT',
                    'class' => 'fixed-width-xxl',
                ],
            ],
        ];

        //Product Inventory
        $fields_form[4]['form'] = [
            'legend' => [
                'title' => $this->l('Product Fields - Product Inventory'),
                'icon'  => 'icon-cogs',
            ],
            'input'  => [
                [
                    'type'    => 'select',
                    'label'   => $this->l('EAN/UPC code'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_EAN',
                    'options' => [
                        'query' => $optionsForFeaturesAndAttributesSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('MPN'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_MPN',
                    'options' => [
                        'query' => $optionsForFeaturesAndAttributesSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
            ],
        ];

        //Product parameters
        $fields_form[5]['form'] = [
            'legend' => [
                'title' => $this->l('Product Fields - Product parameters'),
                'icon'  => 'icon-cogs',
            ],
            'input'  => [
                [
                    'type'    => 'select',
                    'label'   => $this->l('Color'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_COLOR',
                    'options' => [
                        'query' => $optionsForFeaturesAndAttributesSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Size'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_SIZE',
                    'options' => [
                        'query' => $optionsForFeaturesAndAttributesSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Gender'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_GENDER',
                    'options' => [
                        'query' => $optionsForFeaturesAndAttributesSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Material'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_MATERIAL',
                    'options' => [
                        'query' => $optionsForFeaturesAndAttributesSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Pattern'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_PATTERN',
                    'options' => [
                        'query' => $optionsForFeaturesAndAttributesSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Age Group'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_AGE_GROUP',
                    'options' => [
                        'query' => $optionsForFeaturesAndAttributesSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Condition'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_CONDITION',
                    'options' => [
                        'query' => $optionsForFeaturesAndAttributesSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Size Type'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_SIZE_TYPE',
                    'options' => [
                        'query' => $optionsForFeaturesAndAttributesSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Brands'),
                    'name'    => 'URBIT_PRODUCTFEED_ATTRIBUTE_BRANDS',
                    'options' => [
                        'query' => $optionsForFeaturesAndAttributesSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'   => 'fixed-width-xxl',
                ],
            ],
        ];

        //Additional attributes
        $fields_form[6]['form'] = [
            'legend' => [
                'title' => $this->l('Additional attributes'),
                'icon'  => 'icon-cogs',
            ],
            'input'  => [
                [
                    'type'     => 'select',
                    'label'    => $this->l('Additional attributes'),
                    'name'     => 'URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE[]',
                    'multiple' => true,
                    'options'  => [
                        'query' => $optionsForAttributeMultiSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'class'    => 'fixed-width-xxl',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        return $fields_form;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return [
            'URBIT_PRODUCTFEED_CACHE_DURATION'                 => Configuration::get('URBIT_PRODUCTFEED_CACHE_DURATION', null),
            'URBIT_PRODUCTFEED_MINIMAL_STOCK'                  => Configuration::get('URBIT_PRODUCTFEED_MINIMAL_STOCK', null),
            'URBIT_PRODUCTFEED_DIMENSION_HEIGHT'               => Configuration::get('URBIT_PRODUCTFEED_DIMENSION_HEIGHT', null),
            'URBIT_PRODUCTFEED_DIMENSION_LENGTH'               => Configuration::get('URBIT_PRODUCTFEED_DIMENSION_LENGTH', null),
            'URBIT_PRODUCTFEED_DIMENSION_WIDTH'                => Configuration::get('URBIT_PRODUCTFEED_DIMENSION_WIDTH', null),
            'URBIT_PRODUCTFEED_DIMENSION_WEIGHT'               => Configuration::get('URBIT_PRODUCTFEED_DIMENSION_WEIGHT', null),
            'URBIT_PRODUCTFEED_DIMENSION_UNIT'                 => Configuration::get('URBIT_PRODUCTFEED_DIMENSION_UNIT', null),
            'URBIT_PRODUCTFEED_WEIGHT_UNIT'                    => Configuration::get('URBIT_PRODUCTFEED_WEIGHT_UNIT', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_EAN'                  => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_EAN', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_MPN'                  => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_MPN', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_COLOR'                => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_COLOR', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_SIZE'                 => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_SIZE', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_GENDER'               => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_GENDER', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_MATERIAL'             => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_MATERIAL', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_PATTERN'              => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_PATTERN', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_AGE_GROUP'            => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_AGE_GROUP', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_CONDITION'            => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_CONDITION', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_SIZE_TYPE'            => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_SIZE_TYPE', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_BRANDS'               => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_BRANDS', null),
            'URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE' => explode(',', Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE', null)),
            'URBIT_PRODUCTFEED_FILTER_CATEGORIES'              => explode(',', Configuration::get('URBIT_PRODUCTFEED_FILTER_CATEGORIES', null)),
            'URBIT_PRODUCTFEED_TAGS_IDS'                       => explode(',', Configuration::get('URBIT_PRODUCTFEED_TAGS_IDS', null)),
        ];
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            if (in_array($key, ['URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE', 'URBIT_PRODUCTFEED_TAGS_IDS', 'URBIT_PRODUCTFEED_FILTER_CATEGORIES'])) {
                if ($value = Tools::getValue($key)) {
                    Configuration::updateValue($key, implode(',', $value));
                } else {
                    Configuration::updateValue($key, null);
                }
            } else {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }
    }

    /**
     * return options for dimensions selects
     * @param bool $withNotSetted
     * @return array
     */
    protected function getDimensionsOptions($withNotSetted = false)
    {
        $optionsForDimensionsSelect = [];

        if ($withNotSetted) {
            $optionsForDimensionsSelect[] = [
                'id'   => '',
                'name' => 'Not Setted',
            ];
        }

        $features = Feature::getFeatures($this->context->language->id);

        foreach ($features as $feature) {
            $optionsForDimensionsSelect[] = [
                'id'   => $feature['id_feature'],
                'name' => $feature['name'],
            ];
        }

        return array_unique($optionsForDimensionsSelect, SORT_REGULAR);
    }

    /**
     * return options for selects
     * @param bool $withNotSetted
     * @return array
     */
    protected function getFeaturesAndAttributesOptions($withNotSetted = false)
    {
        $options = [];
        if ($withNotSetted) {
            $options[] = [
                'id'   => '',
                'name' => 'Not Setted',
            ];
        }

        $attributes = Attribute::getAttributes($this->context->language->id);

        foreach ($attributes as $attribute) {
            $options[] = [
                'id'   => 'a' . $attribute['id_attribute_group'],
                'name' => $attribute['attribute_group'],
            ];
        }

        $features = Feature::getFeatures($this->context->language->id);

        foreach ($features as $feature) {
            $options[] = [
                'id'   => 'f' . $feature['id_feature'],
                'name' => $feature['name'],
            ];
        }

        return array_unique($options, SORT_REGULAR);
    }
}
