<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Urbit_inventoryfeed
 */
class Urbit_inventoryfeed extends Module
{
    /**
     * @var bool
     */
    protected $config_form = false;

    /**
     * Urbit_inventoryfeed constructor.
     */
    public function __construct()
    {
        $this->name = 'urbit_inventoryfeed';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Urbit';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Urbit Inventory Feed');
        $this->description = $this->l('Urbit Inventory Feed Module');
    }

    /**
     * @return bool
     */
    public function install()
    {
        Configuration::updateValue('URBIT_INVENTORYFEED_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader');
    }

    /**
     * @return mixed
     */
    public function uninstall()
    {
        Configuration::deleteByName('URBIT_INVENTORYFEED_LIVE_MODE');

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
        if (((bool)Tools::isSubmit('submitUrbit_inventoryfeedModule')) == true) {
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
        $helper->submit_action = 'submitUrbit_inventoryfeedModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $valueArray = $this->getConfigFormValues();
        $valueArray['URBIT_INVENTORYFEED_TAGS_IDS[]'] = explode(',', Configuration::get('URBIT_INVENTORYFEED_TAGS_IDS', null));
        $valueArray['URBIT_INVENTORYFEED_FILTER_CATEGORIES[]'] = explode(',', Configuration::get('URBIT_INVENTORYFEED_FILTER_CATEGORIES', null));

        $helper->tpl_vars = [
            'fields_value' => $valueArray,
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm($this->getInventoryFeedConfigForm());
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
                        'name'    => 'URBIT_INVENTORYFEED_LIVE_MODE',
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
                        'name'   => 'URBIT_INVENTORYFEED_ACCOUNT_EMAIL',
                        'label'  => $this->l('Email'),
                    ],
                    [
                        'type'  => 'password',
                        'name'  => 'URBIT_INVENTORYFEED_ACCOUNT_PASSWORD',
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
        $arr[] = ['id' => $category['id_category'], 'name' => $pref . $category['name']];

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
                'id'   => 60,
                'name' => '1 hour',
            ],
            [
                'id'   => 45,
                'name' => '45 min',
            ],
            [
                'id'   => 30,
                'name' => '30 min',
            ],
            [
                'id'   => 15,
                'name' => '15 min',
            ],
            [
                'id'   => 5,
                'name' => '5 min',
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getInventoryFeedConfigForm()
    {
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
                    'name'    => 'URBIT_INVENTORYFEED_CACHE_DURATION',
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
                'title' => $this->l('Inventory Filter'),
                'icon'  => 'icon-cogs',
            ],
            'input'  => [
                [
                    'type'     => 'select',
                    'label'    => $this->l('Categories'),
                    'name'     => 'URBIT_INVENTORYFEED_FILTER_CATEGORIES[]',
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
                    'name'     => 'URBIT_INVENTORYFEED_TAGS_IDS[]',
                    'multiple' => true,
                    'options'  => [
                        'query' => $optionsForTagSelect,
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
            'URBIT_INVENTORYFEED_CACHE_DURATION'    => Configuration::get('URBIT_INVENTORYFEED_CACHE_DURATION', null),
            'URBIT_INVENTORYFEED_FILTER_CATEGORIES' => explode(',', Configuration::get('URBIT_INVENTORYFEED_FILTER_CATEGORIES', null)),
            'URBIT_INVENTORYFEED_TAGS_IDS'          => explode(',', Configuration::get('URBIT_INVENTORYFEED_TAGS_IDS', null)),
        ];
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            if (($key == 'URBIT_INVENTORYFEED_TAGS_IDS') || ($key == 'URBIT_INVENTORYFEED_FILTER_CATEGORIES')) {
                if ($value = Tools::getValue($key)) {
                    Configuration::updateValue($key, implode(',', $value));
                } else {
                    Configuration::updateValue($key,  null);
                }
            } else {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }
}
