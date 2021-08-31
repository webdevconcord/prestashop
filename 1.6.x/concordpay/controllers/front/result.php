<?php

require_once __DIR__ . '../../../concordpay.php';
require_once __DIR__ . '../../../concordpay.cls.php';

/**
 * @class ConcordpayResultModuleFrontController
 *
 * Redirects the buyer to the desired page depending on the response of the payment system.
 */
class ConcordpayResultModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $data     = array_map('htmlspecialchars', $_GET);
        $order_id = !empty($data['orderReference']) ? $data['orderReference'] : null;
        $order    = new OrderCore((int) $order_id);

        $redirect_url = 'index.php?controller=order&step=1';

        // If this order is not found, we redirect to the cart.
        if (!Validate::isLoadedObject($order)) {
           Tools::redirect($redirect_url);
        }

        // If the secure_key does not match, we redirect to the cart.
        if ($data['sessionId'] !== $order->secure_key) {
            Tools::redirect($redirect_url);
        }

        // If the customer id does not match, we redirect to the cart.
        if ($order->id_customer !== $data['cId']) {
            Tools::redirect($redirect_url);
        }

        $customer = new CustomerCore($order->id_customer);

        // If such a client is not found, then we redirect to the cart.
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect($redirect_url);
        }

        // When the result is successful, we redirect to the page of all user orders.
        Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $order->id_cart .
            '&id_module=' . $this->module->id .
            '&id_order=' . $this->module->currentOrder
        );
    }
}
