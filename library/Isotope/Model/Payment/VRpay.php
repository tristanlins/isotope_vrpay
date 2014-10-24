<?php

namespace Isotope\Model\Payment;

use Haste\Http\Response\Response;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Config;
use Isotope\Model\ProductCollection;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\ProductCollectionItem;
use Isotope\Module\Checkout;
use Symfony\Component\Form\Exception\RuntimeException;

/**
 * VR pay payment module for Isotope eCommerce.
 */
class VRpay extends Postsale implements IsotopePayment
{
    /**
     * The live api url.
     *
     * @see http://www.vr-epay.info/?sub=api-vrpaypage#zug_svp API access point documentation.
     * @internal
     * @var string
     */
    const LIVE_API_URL = 'https://pay.vr-epay.de/service/trx';

    /**
     * The testing api url.
     *
     * @see http://www.vr-epay.info/?sub=api-vrpaypage#zug_svp API access point documentation.
     * @internal
     * @var string
     */
    const TESTING_API_URL = 'https://payinte.vr-epay.de/service/trx';

    /**
     * Currencies that are supported by VR pay.
     *
     * @var array
     */
    public static $currencies = array(
        'EUR',
        'USD',
        'CHF',
        'GBP',
        'CAD',
        'PLN',
        'CZK',
        'DKK',
        'ALL',
        'BAM',
        'BGN',
        'BYR',
        'EEK',
        'GEL',
        'GIP',
        'HRK',
        'HUF',
        'LTL',
        'LVL',
        'NOK',
        'RON',
        'RSD',
        'RUB',
        'SEK',
        'TRY',
        'UAH'
    );

    /**
     * sofortueberweisung.de only supports these currencies
     *
     * @return  true
     */
    public function isAvailable()
    {
        if (!in_array(Isotope::getConfig()->currency, array('EUR', 'CHF', 'GBP'))) {
            return false;
        }

        return parent::isAvailable();
    }


    /**
     * {@inheritdoc}
     *
     * @param IsotopeProductCollection|Order $order The order.
     */
    public function processPostsale(IsotopeProductCollection $order)
    {
        \System::log('VR pay: post sale started for order ' . $order->id, __METHOD__, TL_GENERAL);

        $paymentData = deserialize($order->payment_data, true);

        if ($paymentData['VRPAY_SECRET'] != \Input::post('NOTIFY_SECRET')) {
            \System::log('VR pay: invalid secret for order ' . $order->id, __METHOD__, TL_ERROR);

            // secret is invalid
            $objResponse = new Response('STATUS=ERROR_WRONG_SECRET', 403);
            $objResponse->send();
        }

        if (!$order->checkout()) {
            \System::log('VR pay: checkout of order ' . $order->id . ' failed', __METHOD__, TL_ERROR);

            // secret is invalid
            $objResponse = new Response('STATUS=ERROR_CHECKOUT_FAILED', 500);
            $objResponse->send();
        }

        if ('RESERVED' != \Input::post('RES_STATE') && 'PURCHASED' != \Input::post('RES_STATE')) {
            return;
        }

        // update order
        $paymentData['POSTSALE'][] = $_POST;
        $order->payment_data       = $paymentData;
        $order->date_paid          = time();
        $order->updateOrderStatus($this->new_order_status);
        $order->save();

        // send success
        $objResponse = new Response('STATUS=SUCCESS');
        $objResponse->send();
    }

    /**
     * {@inheritdoc}
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(\Input::post('TRX_REFNO'));
    }

    /**
     * {@inheritdoc}
     *
     * @param IsotopeProductCollection|Order $order          The order.
     * @param \Module|Checkout               $checkoutModule The checkout module.
     */
    public function checkoutForm(IsotopeProductCollection $order, \Module $checkoutModule)
    {
        $objTemplate = new \Isotope\Template('iso_payment_vrpay');
        $objTemplate->setData($this->row());

        // start the payment
        if ('pay' == \Input::post('vrpay')) {
            try {
                $location = $this->startPayment($order, $checkoutModule);

                header('Location: ' . $location);
                exit;
            } catch (RuntimeException $exception) {
                $objTemplate->error = $exception->getMessage();
            }
        }

        return $objTemplate->parse();
    }

    /**
     * Start the payment process against VR pay and return the location url to the payment system.
     *
     * @param IsotopeProductCollection|Order $order          The order.
     * @param \Module|Checkout               $checkoutModule The checkout module.
     *
     * @return string
     *
     * @throws \RuntimeException If the payment could not processed or the payment system does not answer correctly.
     */
    protected function startPayment(IsotopeProductCollection $order, \Module $checkoutModule)
    {
        $url   = ($this->debug ? self::TESTING_API_URL : self::LIVE_API_URL);
        $query = $this->buildPostData($order, $checkoutModule);

        // configuration for HTTP-client cURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, 1.1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded; charset=utf-8'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        curl_setopt($curl, CURLOPT_SSLVERSION, 3);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_SSLv3);

        // send request to VR pay virtuell
        $response = curl_exec($curl);

        // check the response
        if ($response == false) {
            curl_close($curl);

            throw new \RuntimeException('Could not connect to payment server.');
        } else {
            $info     = curl_getinfo($curl);
            $redirect = $this->getLocationUrl(curl_multi_getcontent($curl));
            curl_close($curl);

            if ($redirect) {
                if ($this->debug) {
                    $order->date_paid = time();
                    $order->updateOrderStatus($this->new_order_status);
                    $order->save();
                }

                return $redirect;
            } else {
                $message = str_replace("\r", '', $response);
                $content = explode("\n\n", $message);

                \System::log(
                    sprintf(
                        'VR pay: [%s] %s',
                        $info['http_code'],
                        trim(strip_tags($content[1]))
                    ),
                    __METHOD__,
                    TL_ERROR
                );

                if ($this->debug) {
                    $body     = "Request:\n" . $query . "\n\nResponse:\n" . $response;
                    $response = new Response($body, 500);
                    $response->send();
                }

                throw new \RuntimeException('Could not process payment.');
            }
        }
    }

    /**
     * Receive and validate the shop configuration currency, then return it.
     *
     * @param IsotopeProductCollection|Order $order The order.
     *
     * @return string
     *
     * @throws \RuntimeException If the currency is not supported.
     */
    protected function getCurrency(IsotopeProductCollection $order)
    {
        /** @var Config $config */
        $config = $order->getRelated('config_id');

        $currency = $config->currency;

        // determine if the currency is supported by VR pay
        if (!in_array($currency, self::$currencies)) {
            throw new \RuntimeException(
                sprintf(
                    'The currency "%s" is not supported by VR pay, it must be one of ["%s"]',
                    $currency,
                    implode('", "', self::$currencies)
                )
            );
        }

        return $currency;
    }

    /**
     * Return the preferred interface language.
     *
     * @return null|string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getLanguage()
    {
        $language = strtoupper(substr($GLOBALS['TL_LANGUAGE'], 0, 2));
        if (!in_array($language, array('DE', 'EN', 'FR', 'ES', 'IT', 'NL', 'PL', 'CS', 'DK', 'TR'))) {
            $language = null;
        }
        return $language;
    }

    /**
     * Get the terms page url.
     *
     * @return null|string
     */
    public function getTermsUrl()
    {
        $page     = \PageModel::findByPk($this->vrpay_shop_urlterms);
        $termsUrl = \Controller::generateFrontendUrl($page->row());

        if (!preg_match('~^\w:~', $termsUrl)) {
            $termsUrl = \Environment::get('base') . $termsUrl;
        }

        return $termsUrl;
    }

    /**
     * Generate the secret, store it in the orders payment data and return it.
     *
     * @param IsotopeProductCollection|Order $order The order.
     *
     * @return string
     */
    public function generateSecret(IsotopeProductCollection $order)
    {
        $paymentData                 = deserialize($order->payment_data, true);
        $paymentData['VRPAY_SECRET'] = base64_encode(openssl_random_pseudo_bytes(24));
        $order->payment_data         = $paymentData;
        $order->save();
        return $paymentData['VRPAY_SECRET'];
    }

    /**
     * Build the post data for the payment platform.
     *
     * @param IsotopeProductCollection|Order $order          The order.
     * @param \Module|Checkout               $checkoutModule The checkout module.
     *
     * @return string
     */
    protected function buildPostData(IsotopeProductCollection $order, \Module $checkoutModule)
    {
        $currency = $this->getCurrency($order);
        $language = $this->getLanguage();
        $termsUrl = $this->getTermsUrl();
        $baseUrl  = \Environment::get('base');
        $tokens   = $order->row();
        $secret   = $this->generateSecret($order);

        $parameters = array(
            'service'         => 'VRPAYPAGE',
            // Partnernummer, Benutzername, Passwort
            'auth_partnerno'  => ($this->vrpay_partnerno),
            'auth_user'       => 'sendpay',
            'auth_password'   => ($this->vrpay_password),
            // Partnernummer (in der Regel gleich dem Wert von auth_partnerno)
            'trx_partnerno'   => ($this->vrpay_partnerno),
            // eindeutige Referenznummer
            'trx_refno'       => $order->id,
            // Gesamtbetrag in kleinster Einheit (z.B. Euro-Cent)
            'trx_amount'      => (int)($order->getTotal() * 100),
            // Währungscode: EUR | USD | CHF | GBP | CAD | PLN | CZK | DKK | ALL | BAM | BGN | BYR
            // EEK | GEL | GIP | HRK | HUF | LTL | LVL | NOK | RON | RSD | RUB | SEK | TRY | UAH
            'trx_currency'    => $currency,
            // Buchungstyp: RESERVE | PURCHASE
            'trx_action'      => 'PURCHASE',
            // Transaktionstyp
            'trx_type'        => 'ECOM',
            // Verwendungszweck 1 bis 5
            'trx_adddata1'    => \String::parseSimpleTokens($this->vrpay_adddata1, $tokens),
            'trx_adddata2'    => \String::parseSimpleTokens($this->vrpay_adddata2, $tokens),
            'trx_adddata3'    => \String::parseSimpleTokens($this->vrpay_adddata3, $tokens),
            'trx_adddata4'    => \String::parseSimpleTokens($this->vrpay_adddata4, $tokens),
            'trx_adddata5'    => \String::parseSimpleTokens($this->vrpay_adddata5, $tokens),
            // Infotext Händlerkasse
            'trx_infotext'    => \String::parseSimpleTokens($this->vrpay_infotext, $tokens),
            // Weitere Informationen zur Transaktion 1 bis 5
            'trx_addinfo1'    => \String::parseSimpleTokens($this->vrpay_addinfo1, $tokens),
            'trx_addinfo2'    => \String::parseSimpleTokens($this->vrpay_addinfo2, $tokens),
            'trx_addinfo3'    => \String::parseSimpleTokens($this->vrpay_addinfo3, $tokens),
            'trx_addinfo4'    => \String::parseSimpleTokens($this->vrpay_addinfo4, $tokens),
            'trx_addinfo5'    => \String::parseSimpleTokens($this->vrpay_addinfo5, $tokens),
            // URL nach Zahlungserfolg
            'shop_urlsuccess' => $baseUrl . $checkoutModule->generateUrlForStep('complete', $order),
            // URL nach Zahlungsmisserfolg
            'shop_urlfailure' => $baseUrl . $checkoutModule->generateUrlForStep('failed'),
            // URL nach Zahlungsabbruch
            'shop_urlcancel'  => $baseUrl . $checkoutModule->generateUrlForStep('failed'),
            // URL für AGBs
            'shop_urlterms'   => $termsUrl,
            // URL für Notifikation
            'notify_shopurl'  => preg_replace('~^http:~', 'https:', $baseUrl) .
                                 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id,
            // Benachrichtigungsgeheimnis
            'notify_secret'   => $secret,
            // Benachrichtigungstyp: PAY | PAY+BAT | SHOP | NONE
            'notify_profile'  => 'PAY',
            // Sprache: DE | EN | FR | ES | IT | NL | PL | CS | DK | TR
            'page_lang'       => $language,
            // Einschränkung der Zahlungsweisen auf die übergeben Werte.
            // Mehrere Werte müssen mit einem Semikolon getrennt werden.
            // VISA | ECMC | DINERS | AMEX | JCB | GIROPAY | SEPADD
            'page_brands'     => implode(';', deserialize($this->vrpay_brands, true)),
            // Layout: A1 | B1
            'page_layout'     => $this->vrpay_page_layout ?: null,
            // die Belegseite wird angezeigt: Y | N
            'page_receipt'    => $this->vrpay_page_receipt ? 'Y' : 'N',
        );

        // add articles to parameters
        $items = $order->getItems();
        foreach (array_values($items) as $index => $item) {
            /** @var ProductCollectionItem $item */
            $product  = $item->getProduct();
            $position = ($index + 1);

            $parameters['cart_articleno' . $position]   = $product->sku;
            $parameters['cart_articledesc' . $position] = $product->name;
            $parameters['cart_quantitiy' . $position]   = $item->quantity;
            $parameters['cart_unitprice' . $position]   = (int)($product->getPrice($order)->getAmount() * 100);
        }

        // filter out empty (null) parameters
        $parameters = array_filter($parameters);

        // build POST from parameter-array
        $query = http_build_query($parameters, '', '&');

        return $query;
    }

    /**
     * Get the location url from the headers.
     *
     * @param string $headers The response headers.
     *
     * @return string
     */
    protected function getLocationUrl($headers)
    {
        $returnUrl = '';
        $rows      = explode("\r\n", $headers);
        if (!isset($rows[1])) {
            $rows = explode("\n", $headers);
        }

        foreach ($rows as $header) {
            if (substr($header, 0, 9) == 'Location:') {
                $parts     = explode(': ', $header);
                $returnUrl = $parts[1];
            }
        }

        return $returnUrl;
    }
}
