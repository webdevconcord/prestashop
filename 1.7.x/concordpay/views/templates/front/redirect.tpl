{l s='Waiting for redirection' mod='concordpay'}

<form id="concordpay_payment" method="post" action="{$url|escape:'htmlall':'UTF-8'}">
    {foreach from=$fields  key=key item=field}
        {if $field|is_array}
            {foreach from=$field  key=k item=v}
              <input type="hidden" name="{$key|escape:'htmlall':'UTF-8'}[]" value="{$v|escape:'htmlall':'UTF-8'}" />
            {/foreach}
        {else}
			<input type="hidden" name="{$key|escape:'htmlall':'UTF-8'}" value="{$field|escape:'htmlall':'UTF-8'}" />
        {/if}
    {/foreach}
	<input type="submit" value="{l s='Pay' mod='concordpay'}">
</form>

<script type="text/javascript">
  window.addEventListener('DOMContentLoaded', function () {
    document.getElementById('concordpay_payment').submit();
  });
</script>

