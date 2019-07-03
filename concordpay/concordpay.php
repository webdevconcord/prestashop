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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2019 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class Concordpay extends PaymentModule
{
    private $settingsList = array(
        'CONCORDPAY_MERCHANT',
        'CONCORDPAY_SECRET_KEY'
    );

    private $_postErrors = array();

    public function __construct()
    {
        $this->name = 'concordpay';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Concord';

        parent::__construct();
        $this->displayName = $this->l('Платежи Visa или Mastercard (ConcordPay)');
        $this->description = $this->l('Оплата картой VISA или Mastercard (ConcordPay)');
        $this->confirmUninstall = $this->l('Действительно хотите удалить модуль?');
    }

    public function install()
    {
        if (!parent::install() or !$this->registerHook('payment')) {
            return false;
        }
        return true;
    }

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

    public function getOption($name)
    {
        return trim(Configuration::get("CONCORDPAY_" . Tools::strtoupper($name)));
    }

    private function _displayForm()
    {
        $this->_html .=
            '<form action="' . Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']) . '" method="post">
			<fieldset>
			<legend><img src="../img/admin/AdminTools.gif" />' . $this->l('Merchant Settings') . '</legend>
				<table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
					<tr><td colspan="2">' . $this->l('Please specify the ConcordPay account details for customers') . '.<br /><br /></td></tr>

					<tr>
						<td width="130" style="height: 35px;">' . $this->l('Merchant Account') . '</td>
						<td><input type="text" name="merchant" value="' . $this->getOption("merchant") . '" style="width: 300px;" /></td>
					</tr>
					<tr>
						<td width="130" style="height: 35px;">' . $this->l('Secret key') . '</td>
						<td><input type="text" name="secret_key" value="' . $this->getOption("secret_key") . '" style="width: 300px;" /></td>
					</tr>
					<tr><td colspan="2" align="center"><input class="button" name="btnSubmit" value="' . $this->l('Update settings') . '" type="submit" /></td></tr>
				</table>
			</fieldset>
		</form>';
    }

    private function _displayConcordPay()
    {
        $this->_html .= '<img src="../modules/concordpay/views/img/concord_bank.png" style="float:left; margin-right:15px;"><b>' .
            $this->l('This module allows you to accept payments by ConcordPay.') . '</b><br /><br />' .
            $this->l('If the client chooses this payment mode, the order will change its status into a \'Waiting for payment\' status.') .
            '<br /><br /><br />';
    }

    public function getContent()
    {
        $this->_html = '<h2>' . $this->displayName . '</h2>';

        if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            if (!sizeof($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= '<div class="alert error">' . $err . '</div>';
                }
            }
        } else {
            $this->_html .= '<br />';
        }
        $this->_displayConcordPay();
        $this->_displayForm();
        return $this->_html;
    }


    private function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            /*$this->_postErrors[] = $this->l('Account details are required.');*/
        }
    }

    private function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('CONCORDPAY_MERCHANT', Tools::getValue('merchant'));
            Configuration::updateValue('CONCORDPAY_SECRET_KEY', Tools::getValue('secret_key'));
        }
        $this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('ok') . '" /> ' . $this->l('Settings updated') . '</div>';
    }

    # Display

    public function hookPayment($params)
    {
        if (!$this->active) return;
        if (!$this->_checkCurrency($params['cart'])) return;

        $this->context->smarty->assign(array(
            'this_path' => $this->_path,
            'id' => (int)$params['cart']->id,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
            'this_description' => 'Оплата картой VISA или Mastercard (ConcordPay)'
        ));

        return $this->display(__FILE__, 'views/templates/front/concordpay.tpl');
    }

    private function _checkCurrency($cart)
    {
        $currency_order = new Currency((int)($cart->id_currency));
        $currencies_module = $this->getCurrency((int)$cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }
}


