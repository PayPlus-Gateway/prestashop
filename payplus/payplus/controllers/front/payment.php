<?php
/* 
 * PayPlus - Payment Gateway (Extension For PrestaShop)
 * Ver. 1.0.0
 * Built By: PayPlus LTD - Development Department
 * All rights reserved © PayPlus LTD
 * Website: https://www.payplus.co.il
 * E-mail: service@payplus.co.il
*/

if (!defined('_PS_VERSION_')) {
    exit;
}
class PayplusPaymentModuleFrontController extends ModuleFrontController {
    
    public function __construct()
    {
        $config = Configuration::getMultiple(array('APIKEY', 'SECRETKEY', 'PAYMENTPAGEUID', 'TESTMODE_1', 'TRANSACTIONTYPE', 'INVOICE', 'DISPLAY', 'IFRAMEWEIGHT'));

        if (isset($config['APIKEY']))
            $this->apiKey = $config['APIKEY'];
        if (isset($config['SECRETKEY']))
            $this->secretKey = $config['SECRETKEY'];
        if (isset($config['PAYMENTPAGEUID']))
            $this->paymentPageUid = $config['PAYMENTPAGEUID'];
        if (isset($config['TESTMODE_1']))
            $this->testMode = $config['TESTMODE_1'];
        if (isset($config['TRANSACTIONTYPE']))
            $this->transactionType = $config['TRANSACTIONTYPE'];
        if (isset($config['INVOICE']))
            $this->invoice = $config['INVOICE'];
        if (isset($config['DISPLAY']))
            $this->display = $config['DISPLAY'];
        if (isset($config['IFRAMEWEIGHT']))
            $this->iframeWeight = $config['IFRAMEWEIGHT'];
        parent::__construct();
        
    }

    public function initContent()
    {
        parent::initContent();
        global $cookie, $smarty, $cart, $customer;
        $payplus = new Payplus;
        $uniqNum = (int)$cart->id;

        if ($cart->id_customer) {
            $customer = new Customer((int)$cart->id_customer);
        }

        $address = new Address(intval($cart->id_address_delivery));
        $country = new Country(intval($address->id_country));
        $currency = new Currency(intval($cart->id_currency));
        $productArray = $cart->getProducts();


        $customerArray = [
            'email' => $customer->email,
            'customer_name' => $customer->firstname . ' ' .$customer->lastname,
            'customer_type' => 1,
            'vat_number' => $address->vat_number ? $address->vat_number : null,
            'city' => $address->city,
            'postal_code' => $address->postcode,
            'address' => $address->address1 . ' ' .$address->address2,
            "country_iso" => $country->iso_code,
        ];

        $itemsArray = array();
        $barcode = NULL;
        foreach ($productArray as $key => $item) {
            if($item['ean13']) {
                $barcode = $item['ean13'];
            } else if($item['upc']) {
                $barcode = $item['upc'];
            }

            $itemsArray[$key]['name'] = $item['name'];
            $itemsArray[$key]['quantity'] = $item['cart_quantity'];
            $itemsArray[$key]['barcode'] = $barcode ? $barcode : $item['id_product'];
            $itemsArray[$key]['price'] = Tools::ps_round($item['price_with_reduction'], 2);
        }

        $shipping_price = $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);

        $shippingItems = [
            'name' => 'Shipping',
            'shipping' => true,
            'quantity' => 1,
            'price' => Tools::ps_round($shipping_price, 2)
        ];

        $discount_price = $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);

        $discountItems = [
            'name' => 'Discount',
            'discount' => true,
            'quantity' => 1,
            'price' => -(round($discount_price, 2))
        ];

        if ($shipping_price != 0) {
            array_push($itemsArray, $shippingItems);
        }

        if ($discount_price != 0) {
            array_push($itemsArray, $discountItems);
        }


        $inital_invoice = null;

        if ($this->invoice == 1) {
            $inital_invoice = true;
        } else if ($this->invoice == 2) {
            $inital_invoice = false;
        }

        $paying_vat = true;
        if ($cart->getOrderTotal(true, Cart::BOTH) == $cart->getOrderTotal(false, Cart::BOTH)) {
            $paying_vat = false;
        }

        $postFields = [
            'payment_page_uid' => $this->paymentPageUid,
            'more_info' => $uniqNum,
            'refURL_success' => $payplus->refURL,
            'refURL_failure' => $payplus->refURL,
            'refURL_callback' => null,
            'customer' => $customerArray,
            'amount' => Tools::ps_round($cart->getOrderTotal(true, Cart::BOTH),2)  ,
            'currency_code' => $currency->iso_code,
            'items' => $itemsArray,
            'charge_method' => $this->transactionType != '0' ? $this->transactionType : null,
            'initial_invoice' => $inital_invoice,
            'paying_vat' => $paying_vat, 
            'language_code' => $this->context->language->iso_code
        ];

        $newPostData = json_encode($postFields);
        $url = 'https://restapi' . ($this->testMode ? 'dev' : '') . '.payplus.co.il/api/v1.0/PaymentPages/generateLink';

        $ch = curl_init($url);
        $headers = array('Content-Type: application/json', 'Authorization:{"api_key":"' . $this->apiKey . '","secret_key":"' . $this->secretKey . '"}');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "PrestaShop " .$_SERVER["HTTP_USER_AGENT"]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $newPostData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($return, 0, $header_size);
        $body = json_decode(substr($return, $header_size));
        curl_close($ch);


        if ($body && $body->results && $body->results->status == 'success' && $body->data->payment_page_link) {
            $smarty->assign(array(
                'nbProducts' => $cart->nbProducts(),
                'iframeWeight' => $this->iframeWeight ? $this->iframeWeight : '600',
                'display' => $this->display,
                'paymentPageLink' => $body->data->payment_page_link
            ));

            $smarty->clearCache('module:payplus/views/templates/front/payment_execution.tpl');
            return $this->setTemplate('module:payplus/views/templates/front/payment_execution.tpl');
        } else {
            print_r('cannot-proccess-the-order');
        }

    }
}