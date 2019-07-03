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

class ConcordpayResultModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {

        $data = $_GET;
        $order_id = !empty($data['orderReference']) ? $data['orderReference'] : null;
        $order = new OrderCore((int)$order_id);
 
        //если данный ордер не найден то делаем редирект на корзину
        if (!Validate::isLoadedObject($order)) {
            return  Tools::redirect('index.php?controller=order&step=1');
        }

        //если secure_key не совпадает делаем редирект на корзину
        if ($data['sessionId'] !== $order->secure_key){
            return  Tools::redirect('index.php?controller=order&step=1');
        }

        //если id покупателя не совпадает делаем редирект на корзину
        if ($order->id_customer !== $data['cId']){
            return  Tools::redirect('index.php?controller=order&step=1');
        }

        $customer = new CustomerCore($order->id_customer);

        //если не найден такой клиент то делаем редирект на корзину
        if (!Validate::isLoadedObject($customer)) {
            return  Tools::redirect('index.php?controller=order&step=1');
        }

        //когда результат успешный делаем редирект на страницу всех заказов пользователя
        return Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $order->id_cart . '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder);


    }
}

