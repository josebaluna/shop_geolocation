<?php
/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class Shop_geolocation extends Module implements WidgetInterface
{

    /*
     * Add here countries that have to be displayed as individual
     */
    const DEFAULT_COUNTRIES_TO_BE_DISPLAYED = array('ES');
    /*
     * Every country is not in DEFAULT_COUNTRIES_TO_BE_DISPLAYED will be grouped as "International"
     * and redirected to desired id_shop
     */
    const INTERNATIONAL_ID_SHOP = 1;
    const CHECK_ISO = 'US';

    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'shop_geolocation';
        $this->tab = 'i18n_localization';
        $this->version = '1.0.0';
        $this->author = 'Sebastian Luna';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Shop Geolocation');
        $this->description = $this->l('It shows an information modal if the user enters a store that is not the one defined for the user\'s country.');

        $this->confirmUninstall = $this->l('');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBanner');
    }

    public function getContent()
    {
        if (((bool)Tools::isSubmit('submitShop_GeolocationModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        return $this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitShop_GeolocationModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        $type = array();

        $type[] = array('name' => $this->l('/ES'), 'id' => 'es');
        $type[] = array('name' => $this->l('/EN'), 'id' => 'en');

        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Redirección si la IP es de EEUU'),
                        'desc' => $this->l('Redireccionará a esta tienda si la IP --SI-- es de EEUU'),
                        'name' => 'SHOP_GEOLOCATION_SELECTOR',
                        'required' => true,
                        'class' => 'chosen',
                        'options' => array(
                            'query' => $type,
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getConfigFormValues()
    {
        return array(
            'SHOP_GEOLOCATION_SELECTOR' => Configuration::get('SHOP_GEOLOCATION_SELECTOR'),
        );
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayBanner($params)
    {
        // Force reselect shop if the current shop is not the saved cookie shop
        if (isset($_COOKIE['shop_geolocation'])
            && Context::getContext()->shop->id) {
            return false;
        }

        if(Tools::getValue('disable_geo')) {
            return false;
        }

        if ($this->checkCountry()) {
            $shop_access = $this->getShopAccess();

            if($shop_access['id_shop'] ==
                Configuration::get('PS_SHOP_DEFAULT') &&
                $this->getGeolocateCountry() == self::CHECK_ISO
            ) {
                return false;
            } elseif($shop_access['id_shop'] == self::INTERNATIONAL_ID_SHOP &&
                    $this->getGeolocateCountry() != self::CHECK_ISO) {
                return false;
            } else {
                $this->redirectShops();
                return false;
            }
        }
        return false;
    }

    public function hookDisplayNavCenter($params)
    {

    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }

        $this->smarty->assign(
            $this->getWidgetVariables($hookName, $configuration)
        );

        $ip_location = $this->getGeolocateCountry();
        $id_shop = $this->context->shop->id;

        if($ip_location == 'US' || $ip_location == 'CA') {
            if ($id_shop == 1) {
                return false;
            } else {
                return $this->fetch('module:shop_geolocation/views/templates/widget/modalRedirect.tpl');
            }
        }
        else {
            if ($id_shop == 3) {
                return false;
            } else {
                return $this->fetch('module:shop_geolocation/views/templates/widget/modalRedirect.tpl');
            }
        }
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $variables = [];

        if ($this->context->shop->id == 1) {
            $variables = [
                'url' => $this->getShopURL(3),
                'shop' => $this->context->shop->name,
                'shop_redirec' => "Global"
        
            ];
        } else {
            $variables = [
                'url' => $this->getShopURL(1),
                'shop' => $this->context->shop->name,
                'shop_redirec' => "US/Canada"
            ];
        }

        return $variables;
    }

    public function checkCountry()
    {
        if($this->getGeolocateCountry() != Country::getIsoById(
            Configuration::get(
                'PS_COUNTRY_DEFAULT',
                null,
                null,
                self::INTERNATIONAL_ID_SHOP
            )
        )) {
            return false;
        }

        return true;
    }

    public function getCorrectShop()
    {
        die('function');
        $iso_current_country = $this->getGeolocateCountry();
        if (!$iso_current_country) {
            return false;
        }

        die(dump($iso_current_country));

        $id_current_country = Country::getByIso($iso_current_country);
        $name_current_country = Country::getNameById($this->context->language->id, $id_current_country);
        $shop_countries = Configuration::getMultiShopValues('PS_COUNTRY_DEFAULT');

        $id_shop = array_search($id_current_country, $shop_countries);

        $values = array(
            'iso_code' => $iso_current_country,
            'country_name' => $name_current_country,
            'id_country' => $id_current_country,
            'id_shop' => ($id_shop ?: null)
        );

        die(dump($values));
        return $values;
    }

    public function getGeolocateCountry()
    {
        if (@filemtime(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_)) {
            $reader = new GeoIp2\Database\Reader(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_);

            try {
                if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                    $ipaddress = $_SERVER["HTTP_CF_CONNECTING_IP"];
                  } else {
                    $ipaddress = Tools::getRemoteAddr();
                  }
                $record = $reader->city($ipaddress);
            } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
                $record = null;
            }

            if (is_object($record) && Validate::isLanguageIsoCode($record->country->isoCode) && (int) Country::getByIso(strtoupper($record->country->isoCode)) != 0) {
                return $record->country->isoCode;
            }
        }

        return false;
    }

    public function getTreeShopsCountries($shop_countries)
    {
        $all_related_stores = array();
        foreach ($shop_countries as $key => $relation_shop) {
            $id_current_country = (int)$relation_shop;
            $id_current_shop = (int)$key;
            
            $all_related_stores[$id_current_shop]['id_country'] = $id_current_country;
            $all_related_stores[$id_current_shop]['iso_code'] = Country::getIsoById($id_current_country);
            $all_related_stores[$id_current_shop]['name'] = Country::getNameById($this->context->language->id, $id_current_country);
            
            if($id_current_shop) {
                $shop_obj = new Shop($id_current_shop);
                if(Validate::isLoadedObject($shop_obj)) {
                    $all_related_stores[$id_current_shop]['id_shop'] = $id_current_shop;
                    $params = $_GET;
                    $all_related_stores[$id_current_shop]['url_shop'] = $this->context->link->getPageLink(
                        Tools::getValue('controller'),
                        null,
                        null,
                        $_GET,
                        false,
                        $id_current_shop
                    );
                }
            }
        }

        usort($all_related_stores, function ($a, $b) {
            $collator = collator_create('en');
            $arr = array($a['name'], $b['name']);
            collator_sort($collator, $arr, Collator::SORT_STRING);
            return $arr[1] == $a['name'];
        });

        $relatedStores = (!empty($all_related_stores)? $all_related_stores : null);
        // CUSTOM for Heretics
        //return $relatedStores;
        $groupedCountriesName = $this->l('International');
        foreach ($relatedStores as $key => $relatedStore) {
            if (!in_array($relatedStore['iso_code'], self::DEFAULT_COUNTRIES_TO_BE_DISPLAYED)) {
                if ($relatedStore['id_shop'] != self::INTERNATIONAL_ID_SHOP) {
                    unset($relatedStores[$key]);
                }
            }
            if ($relatedStore['id_shop'] === self::INTERNATIONAL_ID_SHOP) {
                $relatedStores[$key]['iso_code'] = 'international';
                $relatedStores[$key]['name'] = $groupedCountriesName;
            }
        }
        return $relatedStores;

    }

    public function redirectShops()
    {
        $ip_location = $this->getGeolocateCountry();
        $protocol_link = (Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';

        if($ip_location == 'US' || $ip_location == 'CA') {
            $cookie = $this->context->cookie;
            $cookie->shop_geolocation = self::INTERNATIONAL_ID_SHOP;
            setcookie('shop_geolocation', self::INTERNATIONAL_ID_SHOP, time() + (3600 * 24));
            $url = $this->getShopURL(self::INTERNATIONAL_ID_SHOP);
            Tools::redirect($url . $this->context->language->iso_code . DIRECTORY_SEPARATOR);
        } else {
            $cookie = $this->context->cookie;
            $cookie->shop_geolocation = Configuration::get('PS_SHOP_DEFAULT');
            setcookie('shop_geolocation', Configuration::get('PS_SHOP_DEFAULT'), time() + (3600 * 24));
            Tools::redirect($protocol_link . $this->context->shop->domain . DIRECTORY_SEPARATOR . $this->context->language->iso_code . DIRECTORY_SEPARATOR);
        }
    }

    public function getShopURL($shop_id)
    {
        $shop = new Shop($shop_id);
        $force_ssl = (Configuration::get('PS_SSL_ENABLED')
            && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
        $url = ($force_ssl) ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain;

        return $url . $this->getShopBaseURI($shop);
    }

    private function getShopBaseURI($shop)
    {
        return $shop->physical_uri . $shop->virtual_uri;
    }

    public function getShopAccess()
    {
        $id_shop = $this->context->shop->id;
        $virtual_uri = $this->context->shop->virtual_uri;

        return array(
            'id_shop' => $id_shop,
            'virtual_uri' => $virtual_uri
        );
    }
}
