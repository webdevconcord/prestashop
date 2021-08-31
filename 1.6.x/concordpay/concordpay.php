<?php

/**
 * 2007-2019 PrestaShop
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @class Concordpay
 *
 * Admin page setting class.
 * @property $currentOrder
 */
class Concordpay extends PaymentModule
{
    /**
     * @var string[]
     */
    private $settingsList = [
        'CONCORDPAY_MERCHANT',
        'CONCORDPAY_SECRET_KEY',
        'CONCORDPAY_APPROVE_ORDER_STATUS',
        'CONCORDPAY_APPROVE_DECLINE_STATUS',
        'CONCORDPAY_APPROVE_REFUNDED_STATUS',
        'CONCORDPAY_LANGUAGE',
    ];

    /**
     * @var string[]
     */
    protected $languages = [
        'en' => 'en',
        'ru' => 'ru',
        'uk' => 'uk',
    ];

    /**
     * @var array
     */
    private $_postErrors = [];

    public function __construct()
    {
        $this->name    = 'concordpay';
        $this->tab     = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author  = 'ConcordPay';

        $this->bootstrap = true;
        parent::__construct();
        $this->displayName      = $this->l('ConcordPay Payment Gateway');
        $this->description      = $this->l('Payment Visa, Mastercard, Google Pay, Apple Pay');
        $this->confirmUninstall = $this->l('Are you sure you want to remove the module?');
    }

    /**
     * @return bool
     */
    public function install()
    {
        if (!parent::install() || !$this->registerHook('payment')) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        foreach ($this->settingsList as $val) {
            if (!Configuration::deleteByName($val)) {
                return false;
            }
        }
        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }

    /**
     * @param $name
     *
     * @return string
     */
    public function getOption($name)
    {
        return trim(Configuration::get('CONCORDPAY_' . Tools::strtoupper($name)));
    }

    private function _displayForm()
    {
        $this->_html .=
            '<form class="defaultForm form-horizontal" action="' . Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']) . '" method="post">
                <div class="panel" id="fieldset_0">
                  <div class="panel-heading">
                    <i class="icon-wrench"></i><span style="margin-left: 5px;">' . $this->l('Merchant Settings') . '</span>
                  </div>
                  <div class="form-wrapper">
                    <div class="form-group">
                      <label class="control-label col-lg-3 required">' . $this->l('Merchant ID') . '</label>
                      <div class="col-lg-3">
                        <input type="text" name="merchant" value="' . $this->getOption('merchant') . '"/>
                        <p class="help-block">' . $this->l('Given to Merchant by ConcordPay') . '</p>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3 required">' . $this->l('Secret key') . '</label>
                      <div class="col-lg-3">
                        <input type="text" name="secret_key" value="' . $this->getOption('secret_key') . '"/>
                        <p class="help-block">' . $this->l('Given to Merchant by ConcordPay') . '</p>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">' . $this->l('Payment successful order status') . '</label>
                      <div class="col-lg-3">
                        <select name="approve_order_status">' . $this->getSelectOptions($this->getOption('approve_order_status')) . '</select>
                        <p class="help-block">' . $this->l('The default order status after successful payment') . '</p>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">' . $this->l('Payment failed order status') . '</label>
                      <div class="col-lg-3">
                        <select name="decline_order_status">' . $this->getSelectOptions($this->getOption('decline_order_status')) . '</select>
                        <p class="help-block">' . $this->l('Order status when payment was declined') . '</p>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">' . $this->l('Payment refunded order status') . '</label>
                      <div class="col-lg-3">
                        <select name="refunded_order_status">' . $this->getSelectOptions($this->getOption('refunded_order_status')) . '</select>
                        <p class="help-block">' . $this->l('Order status when payment was refunded') . '</p>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">' . $this->l('Payment page language') . '</label>
                      <div class="col-lg-3">
                        <select name="language">' . $this->getLanguageOptions($this->getOption('language')) . '</select>
                        <p class="help-block">' . $this->l('ConcordPay payment page language') . '</p>
                      </div>
                    </div>
                  </div>
                  <div class="panel-footer">
                    <button type="submit" value="1" id="configuration_form_submit_btn" name="btnSubmit" class="btn btn-default pull-right">
					  <i class="process-icon-save"></i>' . $this->l('Save') . '
					</button>
				  </div>
		    </form>';
    }

    private function _displayConcordPay()
    {
        $this->_html .=
            '<div style="display: flex; align-content: center; margin-bottom: 10px;">' .
                '<img src="../modules/concordpay/views/img/concordpay.png" style="margin: auto 15px auto 0; max-width: 100%; height: auto; display: block">' .
                '<div style="margin: auto 0; font-weight: bold">' . $this->l('This module allows you to accept payments by ConcordPay.') . '</div>' .
            '</div>';
    }

    /**
     * @return string
     */
    public function getContent()
    {
        $this->_html = '<h2>' . $this->displayName . '</h2>';

        if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            if (!count($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        } else {
            $this->_html .= '<br />';
        }
        $this->_displayConcordPay();
        $this->_displayForm();

        return $this->_html;
    }

    /**
     * Validation module setting fields.
     */
    private function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $merchant_id = trim(Tools::getValue('merchant'));
            $secret_key = trim(Tools::getValue('secret_key'));
            if (!$merchant_id) {
                $this->_postErrors[] = $this->l('Merchant ID are required.');
            }

            if (!$secret_key) {
                $this->_postErrors[] = $this->l('Secret key are required.');
            }
        }
    }

    /**
     * Update merchant settings.
     */
    private function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('CONCORDPAY_MERCHANT', Tools::getValue('merchant'));
            Configuration::updateValue('CONCORDPAY_SECRET_KEY', Tools::getValue('secret_key'));
            Configuration::updateValue('CONCORDPAY_APPROVE_ORDER_STATUS', Tools::getValue('approve_order_status'));
            Configuration::updateValue('CONCORDPAY_DECLINE_ORDER_STATUS', Tools::getValue('decline_order_status'));
            Configuration::updateValue('CONCORDPAY_REFUNDED_ORDER_STATUS', Tools::getValue('refunded_order_status'));
            Configuration::updateValue('CONCORDPAY_LANGUAGE', Tools::getValue('language'));
        }
        $this->_html .=
            '<div class="conf confirm" style="display: flex; margin-bottom: 10px;"><img src="' .
                Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/ok.png') . '" alt="' . $this->l('ok') . '" />
                <span style="margin: auto 0">' . $this->l('Settings updated') . '</span>
            </div>';
    }

    // Display

    /**
     * @param $params
     * @return void
     */
    public function hookPayment($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->_checkCurrency($params['cart'])) {
            return;
        }

        $this->context->smarty->assign(array(
            'this_path' => $this->_path,
            'id' => (int)$params['cart']->id,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
            'this_description' => 'Payment Visa, Mastercard, Google Pay, Apple Pay'
        ));

        return $this->display(__FILE__, 'views/templates/front/concordpay.tpl');
    }

    /**
     * @param $cart
     *
     * @return bool
     */
    private function _checkCurrency($cart)
    {
        $currency_order = new Currency((int) ($cart->id_currency));
        $currencies_module = $this->getCurrency((int) $cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id === (int) $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getOrderStatuses()
    {
        global $cookie;

        $id_lang = $cookie->id_lang ?? '1';
        $states = OrderState::getOrderStates((int) $id_lang);
        $statuses = [];
        foreach ($states as $state) {
            $statuses[$state['id_order_state']] = $state['name'];
        }

        return $statuses;
    }

    /**
     * @param $html
     *
     * @return mixed|string
     */
    protected function getSelectOptions($savedStatus)
    {
        $options = '';
        foreach ($this->getOrderStatuses() as $key => $status) {
            if ($key === (int) $savedStatus) {
                $options .= '<option value="' . $key . '" selected="selected">' . $status . '</option>';
            } else {
                $options .= '<option value="' . $key . '">' . $status . '</option>';
            }
        }

        return $options;
    }

    /**
     * @param $saved
     * @return mixed|string
     */
    protected function getLanguageOptions($saved)
    {
        $options = '';
        foreach ($this->languages as $key => $item) {
            if ($item === $saved) {
                $options .= '<option value="' . $key . '" selected="selected">' . $item . '</option>';
            } else {
                $options .= '<option value="' . $key . '">' . $item . '</option>';
            }
        }

        return $options;
    }
}
