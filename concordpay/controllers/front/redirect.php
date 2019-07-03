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

require_once(dirname(__FILE__) . '../../../concordpay.php');
require_once(dirname(__FILE__) . '../../../concordpay.cls.php');

class ConcordpayRedirectModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $cart = $this->context->cart;

        $currency = new CurrencyCore($cart->id_currency);
        $payCurrency = $currency->iso_code;
        $cdp = new Concordpay();
        $cpcCls = new ConcordPayCls();
        $total = number_format($cart->getOrderTotal(), 2, '.', '');

        $option = array();
        $option['merchant_id'] = $cdp->getOption('merchant');
        $option['currency_iso'] = $payCurrency;
        $option['amount'] = $total;
        $option['operation'] = 'Purchase';
        $option['description'] = 'Оплата товара на сайте '.$_SERVER["HTTP_HOST"];
        $option['add_params'] = ['merchantAccount', 'orderReference', 'transactionId', 'transactionStatus', 'reason'];
        $cdp->validateOrder((int)$cart->id, _PS_OS_PREPARATION_, $total, $cdp->displayName);
        $order = new OrderCore((int)$cdp->currentOrder);
        $option['order_id'] = $cdp->currentOrder;
        $option['signature'] = $cpcCls->getRequestSignature($option);
        $url = ConcordPayCls::URL;
        $option['approve_url'] = $this->context->link->getModuleLink('concordpay', "result?merchantAccount=".urlencode($cdp->getOption('merchant'))."&orderReference="
            .urlencode($cdp->currentOrder)."&amount=".urlencode($total)."&currency=".urlencode($payCurrency)."&sessionId="
            .urlencode($order->secure_key)."&cId=".$order->id_customer."&transactionStatus=Approved");
        $option['callback_url'] = $this->context->link->getModuleLink('concordpay', 'callback');
        $option['decline_url'] = $this->context->link->getModuleLink('concordpay', 'result');
        $option['cancel_url'] = $this->context->link->getModuleLink('concordpay', 'result');
        $this->context->smarty->assign(array('fields' => $option, 'url' => $url));
        $this->setTemplate('redirect.tpl');
    }
}

