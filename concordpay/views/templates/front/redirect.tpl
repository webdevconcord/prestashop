{*
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
*}

{l s='Ожидание перенаправления' mod='concordpay'}

<form id="concordpay_payment" method="post" action="{$url|escape:'htmlall':'UTF-8'}">
    {foreach from=$fields  key=key item=field}
        {if $field|is_array}
            {foreach from=$field  key=k item=v}<input type="hidden" name="{$key|escape:'htmlall':'UTF-8'}[]" value="{$v|escape:'htmlall':'UTF-8'}" />{/foreach}
        {else}
			<input type="hidden" name="{$key|escape:'htmlall':'UTF-8'}" value="{$field|escape:'htmlall':'UTF-8'}" />
        {/if}
    {/foreach}

	<input type="submit" value="{l s='Оплатить' mod='concordpay'}">
</form>

<script type="text/javascript">
	$('#concordpay_payment').submit();
</script>

