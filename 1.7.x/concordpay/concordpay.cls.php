<?php

/**
 * @class ConcordPayCls
 *
 * Class of interaction with the API of the payment system.
 */
class ConcordPayCls
{
    const ORDER_APPROVED        = 'Approved';
    const ORDER_DECLINED        = 'Declined';
    const ORDER_SEPARATOR       = '#';
    const SIGNATURE_SEPARATOR   = ';';
    const RESPONSE_TYPE_REVERSE = 'reverse';
    const RESPONSE_TYPE_PAYMENT = 'payment';

    const URL = 'https://pay.concord.ua/api/';

    /**
     * @var string[]
     */
    protected $keysForResponseSignature = [
        'merchantAccount',
        'orderReference',
        'amount',
        'currency',
    ];

    /**
     * @var string[]
     */
    protected $keysForSignature = [
        'merchant_id',
        'order_id',
        'amount',
        'currency_iso',
        'description',
    ];

    /**
     * @param $option
     * @param $keys
     *
     * @return string
     */
    public function getSignature($option, $keys)
    {
        $hash = [];
        foreach ($keys as $dataKey) {
            if (!isset($option[$dataKey])) {
                continue;
            }
            if (is_array($option[$dataKey])) {
                foreach ($option[$dataKey] as $v) {
                    $hash[] = $v;
                }
            } else {
                $hash[] = $option[$dataKey];
            }
        }

        $hash = implode(self::SIGNATURE_SEPARATOR, $hash);

        return hash_hmac('md5', $hash, $this->getSecretKey());
    }

    /**
     * @param $options
     *
     * @return string
     */
    public function getRequestSignature($options)
    {
        return $this->getSignature($options, $this->keysForSignature);
    }

    /**
     * @param $options
     *
     * @return string
     */
    public function getResponseSignature($options)
    {
        return $this->getSignature($options, $this->keysForResponseSignature);
    }

    /**
     * @param $response
     *
     * @return bool|string
     */
    public function isPaymentValid($response)
    {
        $sign = $this->getResponseSignature($response);

        if ($sign !== $response['merchantSignature']) {
            return 'An error has occurred during payment. Signature is not valid.';
        }

        return true;
    }

    /**
     * @return string
     */
    protected function getSecretKey()
    {
        $wp = new Concordpay();

        return $wp->getOption('secret_key');
    }
}
