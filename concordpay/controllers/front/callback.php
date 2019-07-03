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

class ConcordpayCallbackModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public $display_header = false;
    public $display_footer = false;
    public $ssl = true;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {

        try {

            $data = json_decode(Tools::file_get_contents("php://input"), true);
            $order_id = !empty($data['orderReference']) ? $data['orderReference'] : null;
            $order = new OrderCore((int)$order_id);
            if (empty($order)) {
                die('Заказ не найден');
            }

            $concordPayCls = new ConcordPayCls();
            $isPaymentValid = $concordPayCls->isPaymentValid($data);
            if ($isPaymentValid !== true) {
                exit($isPaymentValid);
            }

            $history = new OrderHistory();
            $history->id_order = $order_id;
            $history->changeIdOrderState((int)Configuration::get('PS_OS_PAYMENT'), $order_id);
            $history->addWithemail(true, array(
                'order_name' => $order_id
            ));

            echo $concordPayCls->getAnswerToGateWay($data);
            exit();
        } catch (Exception $e) {
            exit(get_class($e) . ': ' . $e->getMessage());
        }
    }
}


