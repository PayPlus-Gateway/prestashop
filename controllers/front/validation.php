<?php
/* 
 * PayPlus - Payment Gateway (Extension For PrestaShop)
 * Ver. 1.0.0
 * Built By: PayPlus LTD - Development Department
 * All rights reserved © PayPlus LTD
 * Website: https://www.payplus.co.il
 * E-mail: service@payplus.co.il
*/

use Doctrine\Common\Util\Debug;

if (!defined('_PS_VERSION_')) {
    exit;
}
class PayplusValidationModuleFrontController extends ModuleFrontController {

    public function initContent() {
        parent::initContent();
        $action = (isset($_GET['action'])) ? $_GET['action']:null;
        $data = $_REQUEST;
        if(isset($data['more_info']) && $data['more_info'] > 0 && $data['more_info'] !='') {
            $this->returnPayment($data);
        } elseif($action == 'success') {
            $this->success();
        } elseif($action == 'failure'){
            $this->failure();
        }
    }

    private function returnPayment($data) {
        $failureURL = 'index.php?fc=module&module=payplus&controller=validation&action=failure';
        $successURL = 'index.php?controller=order-confirmation&id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$this->context->customer->secure_key;
        $payplus = new Payplus;
        $id_cart = $this->context->cart->id;
        if (!$id_cart) {
            Tools::redirect( $failureURL );
        }
        if ($data['more_info']) {
            $cart = new Cart(intval($id_cart ));
            if (isset($data['status_code']) && $data['status_code'] == "000") {
                $status = _PS_OS_PAYMENT_;
            } else {
                $status = _PS_OS_ERROR_;
            }
            
            $extraVars = [];
            if (isset($data['transaction_uid'])) {
                $extraVars['transaction_id'] = $data['transaction_uid'];
            }
            
            $totalCart = $cart->getOrderTotal(true, 3);
            if (!$totalCart){
                Tools::redirect( $failureURL );
            }
            $payplus->validateOrder(intval($id_cart ), $status , number_format($totalCart, 2, '.', ''),$payplus->name,null,$extraVars);
            $order = Order::getByCartId(intval($id_cart));

            $payments = OrderPayment::getByOrderReference($order->reference);
            foreach($payments as $payment) {
                if ($payment->transaction_id == $data['transaction_uid']) {
                    if (isset($data['four_digits'])) {
                        $payment->card_number = 'XXXX-'.$data['four_digits'];
                    }
                    if (isset($data['expiry_month']) && isset($data['expiry_year'])) {
                        $payment->card_expiration = $data['expiry_month'].'/'.$data['expiry_year'];
                    }
                    $payment->save();
                    break;
                }
            }

            if ($status == _PS_OS_PAYMENT_) {
                Tools::redirect( $successURL );
            }
        }
        Tools::redirect($failureURL);

    }

    private function success() {
        $this->setTemplate('module:payplus/views/templates/front/success.tpl');
    } 
    
    private function failure() {
        $this->setTemplate('module:payplus/views/templates/front/failure.tpl');
    } 
}