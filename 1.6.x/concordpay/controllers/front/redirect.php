<?php

require_once __DIR__ . '../../../concordpay.php';
require_once __DIR__ . '../../../concordpay.cls.php';

/**
 * @class ConcordpayRedirectModuleFrontController
 *
 * Generates a payment form and redirects to the payment system page.
 * @property Concordpay $module
 * @property ContextCore $context
 */
class ConcordpayRedirectModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * @throws PrestaShopException
     *
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $cart        = $this->context->cart;
        $currency    = new CurrencyCore($cart->id_currency);
        $payCurrency = $currency->iso_code;
        $concordpay  = new Concordpay();
        $cpcCls      = new ConcordPayCls();
        $total       = number_format($cart->getOrderTotal(), 2, '.', '');

        $concordpay->validateOrder((int) $cart->id, _PS_OS_PREPARATION_, $total, $concordpay->displayName);
        $order   = new OrderCore((int) $concordpay->currentOrder);
        $address = new Address((int) $order->id_address_delivery);
        if (trim($address->phone) !== '') {
            $phone = trim($address->phone);
        } elseif (trim($address->phone_mobile) !== '') {
            $phone = trim($address->phone_mobile);
        } else {
            $phone = '';
        }
        $full_name = trim($address->firstname) . ' ' . trim($address->lastname);

        // 'redirect' - The context in which the translation is used, i.e. filename without extension.
        $description = $this->module->l('Payment by card on the site', 'redirect') . ' ' .
            htmlspecialchars($_SERVER['HTTP_HOST']) . ", $full_name, $phone.";

        $customer = new Customer((int) $order->id_customer);

        $option = [];

        $option['operation']    = 'Purchase';
        $option['merchant_id']  = $concordpay->getOption('merchant');
        $option['order_id']     = $concordpay->currentOrder;
        $option['amount']       = $total;
        $option['currency_iso'] = $payCurrency;
        $option['description']  = $description;
        $option['add_params']   = [];
        $option['signature']    = $cpcCls->getRequestSignature($option);
        $option['language']     = $concordpay->getOption('language') ?? 'en';

        $url = ConcordPayCls::URL;
        $option['approve_url'] = $this->context->link->getModuleLink(
            'concordpay',
            'result?&orderReference=' . urlencode($concordpay->currentOrder) .
            '&sessionId=' . urlencode($order->secure_key) .
            '&cId=' . $order->id_customer
        );
        $option['decline_url']  = $this->context->link->getModuleLink('concordpay', 'result');
        $option['cancel_url']   = $this->context->link->getModuleLink('concordpay', 'result');
        $option['callback_url'] = $this->context->link->getModuleLink('concordpay', 'callback');
        // Statistics.
        $option['client_first_name'] = $customer->firstname;
        $option['client_last_name']  = $customer->lastname;
        $option['email']             = $customer->email;
        $option['phone']             = $phone;

        $this->context->smarty->assign(['fields' => $option, 'url' => $url]);
        $this->setTemplate('redirect.tpl');
    }
}
