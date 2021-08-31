<p class="payment_module">
  <a href="{$link->getModuleLink('concordpay', 'redirect', ['id_cart' => {$id}])|escape:'htmlall':'UTF-8'}" title="{l s='Payment Visa, Mastercard, Google Pay, Apple Pay' mod='concordpay'}">
    <img src="{$this_path|escape:'htmlall':'UTF-8'}views/img/concordpay.png" rel='concordpay'/>
      {$this_description|escape:'htmlall':'UTF-8'}
  </a>
</p>


