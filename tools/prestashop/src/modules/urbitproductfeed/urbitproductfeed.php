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
 
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/Model/Feed/FeedProduct.php';
require_once dirname(__FILE__) . '/Model/Feed/Fields/Factory.php';
require_once dirname(__FILE__) . '/Helper/UrbitHelperForm.php';

/**
 * Class UrbitProductfeed
 */
class UrbitProductfeed extends Module
{
    const NAME = 'urbitproductfeed';

    /**
     * @var bool
     */
    protected $config_form = false;

    /**
     * @var array
     */
    protected $fields = array();

    /**
     * UrbitProductfeed constructor.
     */
    public function __construct()
    {
        $this->name = 'urbitproductfeed';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Urbit';
        $this->module_key = 'a28ee08818efc46aecb78bc6ef2c9b3c';
        $this->need_instance = 1;

        $this->fields = array(
            'factory' => new UrbitProductfeedFieldsFactory(),
        );

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Urbit Product Feed');
        $this->description = $this->l('Urbit Product Feed Module');
    }

    /**
     * @return Module
     */
    public static function getInstance()
    {
        return Module::getInstanceByName(static::NAME);
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
            && $this->registerHook('backOfficeHeader');
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
        $output = '';
        $this->context->smarty->assign('active', 'intro');

        if (((bool)Tools::isSubmit('submitProductfeedModule')) == true) {
              $output = $this->postProcess();
              $this->context->smarty->assign('active', 'account');
        }

        $config = $this->renderForm();
        $this->context->smarty->assign(array('config' => $config,));

        return  $output.$this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
    }


    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new UrbitProductfeedUrbitHelperForm();

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

        $valueArray['URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW[]'] = json_decode(Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW', null), true);
        $valueArray['URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE[]'] = explode(',', Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE', null));
        $valueArray['URBITPRODUCTFEED_TAGS_IDS[]'] = explode(',', Configuration::get('URBITPRODUCTFEED_TAGS_IDS', null));
        $valueArray['URBITPRODUCTFEED_FILTER_CATEGORIES[]'] = explode(',', Configuration::get('URBITPRODUCTFEED_FILTER_CATEGORIES', null));

        $attributeTypes = $this->getAttributeTypes();

        $helper->tpl_vars = array(
            'fields_value'    => $valueArray,
            'languages'       => $this->context->controller->getLanguages(),
            'id_language'     => $this->context->language->id,
            'attribute_types' => $attributeTypes,
        );

        return $helper->generateForm($this->getProductFeedConfigForm());
    }

    /**
     * @return array
     */
    protected function getAttributeTypes()
    {
        return array(
           array(

                'name'  => 'String',
                'value' => 'string',
            ),
           array(

                'name'  => 'Number',
                'value' => 'number',
            ),
           array(
                'name'  => 'Boolean',
                'value' => 'boolean',
            ),
            array(
                'name'  => 'Datetimerange',
                'value' => 'datetimerange',
            ),
            array(
                'name'  => 'Float',
                'value' => 'float',
            ),
            array(
                'name'  => 'Text',
                'value' => 'text',
            ),
            array(
                'name'  => 'Time',
                'value' => 'time',
            ),
            array(
                'name'  => 'URL',
                'value' => 'url',
            ),
        );
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return  array(
            'form' =>  array(
                'legend' =>  array(
                    'title' => $this->l('Settings'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  =>  array(
                     array(
                        'type'    => 'switch',
                        'label'   => $this->l('Live mode'),
                        'name'    => 'URBITPRODUCTFEED_LIVE_MODE',
                        'is_bool' => true,
                        'desc'    => $this->l('Use this module in live mode'),
                        'values'  =>  array(
                             array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'col'    => 3,
                        'type'   => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc'   => $this->l('Enter a valid email address'),
                        'name'   => 'URBITPRODUCTFEED_ACCOUNT_EMAIL',
                        'label'  => $this->l('Email'),
                    ),
                    array(
                        'type'  => 'password',
                        'name'  => 'URBITPRODUCTFEED_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' =>  array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * return options for attributes selects
     * @param bool $withNotSetted
     * @return array
     */
    protected function getAttributesOptions($withNotSetted = false)
    {
        $optionsForAttributeSelect = array();

        if ($withNotSetted) {
            $optionsForAttributeSelect[] = array(
                'id'   => '',
                'name' => 'Not Setted',
            );
        }


        $attributes = Attribute::getAttributes($this->context->language->id);
        foreach ($attributes as $attribute) {
            $optionsForAttributeSelect[] = array(
                'id'   => $attribute['id_attribute_group'],
                'name' => $attribute['attribute_group'],
            );
        }

        return array_unique($optionsForAttributeSelect, SORT_REGULAR);
    }

    /**
     * return options for categories selects
     */
    protected function getCategoriesOptions()
    {
        $categories = Category::getNestedCategories(null, $this->context->language->id);

        $resultArray = array();

        foreach ($categories as $category) {
            $arr = array();
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
        $arr[] = array(
            'id'   => $category['id_category'],
            'name' => $pref . $category['name'],
        );

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
        $optionsForTagSelect = array();

        $tags = Tag::getMainTags($this->context->language->id);
        foreach ($tags as $tag) {
            $optionsForTagSelect[] = array('id' => $tag['name'], 'name' => $tag['name']);
        }

        return $optionsForTagSelect;
    }

    /**
     * @return array
     */
    protected function getCacheOptions()
    {
        return  array(
            array(
                'id'   => 0.00000001,
                'name' => 'DISABLE CACHE',
            ),
            array(
                'id'   => 1,
                'name' => 'Hourly',
            ),
            array(
                'id'   => 24,
                'name' => 'Daily',
            ),
            array(
                'id'   => 168,
                'name' => 'Weekly',
            ),
            array(
                'id'   => 5040,
                'name' => 'Monthly',
            ),
        );
    }

    /**
     * @param bool $withNotSetted
     * @return array
     */
    protected function getCountriesOptions($withNotSetted = false)
    {
        $optionsForTaxesSelect = array();

        if ($withNotSetted) {
            $optionsForTaxesSelect[] = array(
                'id'   => '',
                'name' => 'Not Setted',
            );
        }


        $countries = Country::getCountries($this->context->language->id);

        foreach ($countries as $country) {
            $optionsForTaxesSelect[] = array('id' => $country['id_country'], 'name' => $country['name']);
        }

        return $optionsForTaxesSelect;
    }

    /**
     * @return array
     */
    protected function getProductFeedConfigForm()
    {
        $optionsForCategorySelect = $this->getCategoriesOptions();
        $optionsForTagSelect = $this->getTagsOptions();
        $optionsForCacheSelect = $this->getCacheOptions();
        $optionsForTaxes = $this->getCountriesOptions(true);

        $fields_form = array();

        //Feed Cache
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Feed Cache'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => array(
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Cache duration'),
                    'name'    => 'URBITPRODUCTFEED_CACHE_DURATION',
                    'options' => array(
                        'query' => $optionsForCacheSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        // Product Filters
        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Product Filters'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => array(
                array(
                    'type'     => 'select',
                    'label'    => $this->l('Categories'),
                    'name'     => 'URBITPRODUCTFEED_FILTER_CATEGORIES[]',
                    'multiple' => true,
                    'options'  => array(
                        'query' => $optionsForCategorySelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'    => 'fixed-width-xxl',
                ),
                array(
                    'type'     => 'select',
                    'label'    => $this->l('Tags'),
                    'name'     => 'URBITPRODUCTFEED_TAGS_IDS[]',
                    'multiple' => true,
                    'options'  => array(
                        'query' => $optionsForTagSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'    => 'fixed-width-xxl',
                ),
                array(
                    'type'  => 'text',
                    'label' => $this->l('Minimal Stock'),
                    'name'  => 'URBITPRODUCTFEED_MINIMAL_STOCK',
                    'class' => 'fixed-width-xxl',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        //Product Dimentions
        $fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->l('Product Fields - Product Dimentions'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => $this->fields['factory']->getInputs(),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        //Product parameters
        $fields_form[3]['form'] = array(
            'legend' => array(
                'title' => $this->l('Product Fields - Product parameters'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => array(
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Color'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_COLOR',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Size'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_SIZE',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Gender'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_GENDER',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Material'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_MATERIAL',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Pattern'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_PATTERN',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Age Group'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_AGE_GROUP',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Condition'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_CONDITION',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Size Type'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_SIZE_TYPE',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Brands'),
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_BRANDS',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        //Prices
        $fields_form[5]['form'] = array(
            'legend' => array(
                'title' => $this->l('Product Fields - Prices'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => $this->fields['factory']->getPriceInputs(),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        //Taxes
        $fields_form[6]['form'] = array(
            'legend' => array(
                'title' => $this->l('Taxes'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => array(
                array(
                    'type'     => 'select',
                    'label'    => $this->l('Country'),
                    'name'     => 'URBITPRODUCTFEED_TAX_COUNTRY',
                    'multiple' => false,
                    'options'  => array(
                        'query' => $optionsForTaxes,
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'    => 'fixed-width-xxl',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
              ),
        );

        $fields_form[7]['form'] = array(
            'legend' => array(
                'title' => $this->l('Urbit attributes'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => array(
                array(
                    'type'    => 'urbit_additional_attributes',
                    'name'    => 'URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        $fields_form[8]['form'] = array(
            'legend' => array(
                'title' => $this->l('Custom Inventory List'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => $this->fields['factory']->getInventoryListInputs(),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        return $fields_form;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array_merge(
            array(
            'URBITPRODUCTFEED_CACHE_DURATION'                     => Configuration::get('URBITPRODUCTFEED_CACHE_DURATION', null),
            'URBITPRODUCTFEED_TAX_COUNTRY'                        => Configuration::get('URBITPRODUCTFEED_TAX_COUNTRY', null),
            'URBITPRODUCTFEED_MINIMAL_STOCK'                      => Configuration::get('URBITPRODUCTFEED_MINIMAL_STOCK', null),
            'URBITPRODUCTFEED_DIMENSION_HEIGHT'                   => Configuration::get('URBITPRODUCTFEED_DIMENSION_HEIGHT', null),
            'URBITPRODUCTFEED_DIMENSION_LENGTH'                   => Configuration::get('URBITPRODUCTFEED_DIMENSION_LENGTH', null),
            'URBITPRODUCTFEED_DIMENSION_WIDTH'                    => Configuration::get('URBITPRODUCTFEED_DIMENSION_WIDTH', null),
            'URBITPRODUCTFEED_DIMENSION_WEIGHT'                   => Configuration::get('URBITPRODUCTFEED_DIMENSION_WEIGHT', null),
            'URBITPRODUCTFEED_DIMENSION_UNIT'                     => Configuration::get('URBITPRODUCTFEED_DIMENSION_UNIT', null),
            'URBITPRODUCTFEED_WEIGHT_UNIT'                        => Configuration::get('URBITPRODUCTFEED_WEIGHT_UNIT', null),
            'URBITPRODUCTFEED_ATTRIBUTE_EAN'                      => Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_EAN', null),
            'URBITPRODUCTFEED_ATTRIBUTE_MPN'                      => Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_MPN', null),
            'URBITPRODUCTFEED_ATTRIBUTE_COLOR'                    => Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_COLOR', null),
            'URBITPRODUCTFEED_ATTRIBUTE_SIZE'                     => Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_SIZE', null),
            'URBITPRODUCTFEED_ATTRIBUTE_GENDER'                   => Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_GENDER', null),
            'URBITPRODUCTFEED_ATTRIBUTE_MATERIAL'                 => Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_MATERIAL', null),
            'URBITPRODUCTFEED_ATTRIBUTE_PATTERN'                  => Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_PATTERN', null),
            'URBITPRODUCTFEED_ATTRIBUTE_AGE_GROUP'                => Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_AGE_GROUP', null),
            'URBITPRODUCTFEED_ATTRIBUTE_CONDITION'                => Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_CONDITION', null),
            'URBITPRODUCTFEED_ATTRIBUTE_SIZE_TYPE'                => Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_SIZE_TYPE', null),
            'URBITPRODUCTFEED_ATTRIBUTE_BRANDS'                   => Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_BRANDS', null),
            'URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE'     => explode(',', Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE', null)),
            'URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW' => json_decode(Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW', null), true),
            'URBITPRODUCTFEED_FILTER_CATEGORIES'                  => explode(',', Configuration::get('URBITPRODUCTFEED_FILTER_CATEGORIES', null)),
            'URBITPRODUCTFEED_TAGS_IDS'                           => explode(',', Configuration::get('URBITPRODUCTFEED_TAGS_IDS', null)),
            ),
            $this->fields['factory']->getInputsConfig(),
            $this->fields['factory']->getPriceInputsConfig(),
            $this->fields['factory']->getInventoryListInputsConfig()
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            if (in_array($key, array('URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW'))) {
                $value = Tools::getValue($key) ?: null;
                Configuration::updateValue($key, $value ? json_encode($value) : $value);

                continue;
            }

            if (in_array($key, array('URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE', 'URBITPRODUCTFEED_TAGS_IDS', 'URBITPRODUCTFEED_FILTER_CATEGORIES'))) {
                $value = Tools::getValue($key) ?: null;
                Configuration::updateValue($key, $value ? implode(',', $value) : null);
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
        $optionsForDimensionsSelect = array();

        if ($withNotSetted) {
            $optionsForDimensionsSelect[] = array(
                'id'   => '',
                'name' => 'Not Setted',
            );
        }

        $features = Feature::getFeatures($this->context->language->id);

        foreach ($features as $feature) {
            $optionsForDimensionsSelect[] = array(
                'id'   => $feature['id_feature'],
                'name' => $feature['name'],
            );
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
        $options = array();

        if ($withNotSetted) {
            $options[] = array(
                'id'   => '',
                'name' => 'Not Setted',
            );
        }

        $attributes = Attribute::getAttributes($this->context->language->id);

        foreach ($attributes as $attribute) {
            $options[] = array(
                'id'   => 'a' . $attribute['id_attribute_group'],
                'name' => $attribute['attribute_group'],
            );
        }

        $features = Feature::getFeatures($this->context->language->id);

        foreach ($features as $feature) {
            $options[] = array(
                'id'   => 'f' . $feature['id_feature'],
                'name' => $feature['name'],
            );
        }

        return array_unique($options, SORT_REGULAR);
    }
}
