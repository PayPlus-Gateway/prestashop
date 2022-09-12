<?php
/* 
 * PayPlus - Payment Gateway (Extension For PrestaShop)
 * Ver. 1.0.0
 * Built By: PayPlus LTD - Development Department
 * All rights reserved © PayPlus LTD
 * Website: https://www.payplus.co.il
 * E-mail: service@payplus.co.il
*/

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
if (!defined('_PS_VERSION_'))
	exit;

class Payplus extends PaymentModule {
    private $_html = '';
	private $_postErrors = array();

	public  $payplusName;
	public  $address;
		    

	public $apiKey;
	public $secretKey;
	public $paymentPageUid;
    public $testMode;
	/* public $pfsPaymentUrl;
	public $pfsPaymentCallbackUrl;
	public $pfsInvoiceLanguage; */

	public function __construct()
	{

		$this->name = 'payplus';
		$this->tab = 'payments_gateways';
		$this->version = '1.0';
		$this->author = 'PayPlus LTD';
        $this->bootstrap = true;
		$this->controllers = ['payment','validation'];
		$this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_); // - *
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';
		parent::__construct();
		$this->img_path = $this->_path.'views/img/';
		$this->displayName = $this->l('PayPlus - Payment Gateway');
		$this->description = $this->l('Module for accepting credit and debit cards using PayPlus - Payment Gateway');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
		$this->refURL = $this->context->link->getModuleLink($this->name, 'validation', array(), true);
	}


	public function install()
	{
		if (!parent::install()
            || !$this->registerHook('paymentOptions')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('header')
            || !$this->registerHook('backOfficeHeader')
            || !$this->registerHook('displayBackOfficeHeader'))
			return false;
		return true;
	}


    public function hookBackOfficeHeader($params)
    {
        if (_PS_VERSION_ < '1.5'){
            Tools::addJS(($this->_path).'views/js/bo.js', 'all');
            Tools::addCss(($this->_path).'views/css/bo.js', 'all');
        }else{
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path.'views/js/bo.js', 'all');
            $this->context->controller->addCss($this->_path.'views/css/bo.css', 'all');
        }
    }


    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        PrestaShopLogger::addLog("hookPaymentOptions",5);
        $newOption = new PaymentOption();
        $newOption->setModuleName($this->name);
        $newOption->setCallToActionText($this->l("PayPlus (Credit card)"));
        $newOption->setAction($this->context->link->getModuleLink($this->name, 'payment', array(), true));

        $payments_options = [
            $newOption,
        ];
        return $payments_options;
	}
	
	public function uninstall()
	{
		if (   !Configuration::deleteByName('APIKEY')
			|| !Configuration::deleteByName('SECRETKEY')
			|| !Configuration::deleteByName('PAYMENTPAGEUID')
            || !Configuration::deleteByName('TESTMODE_1')
            || !Configuration::deleteByName('TRANSACTIONTYPE')
            || !Configuration::deleteByName('INVOICE')
            || !Configuration::deleteByName('DISPLAY')
            || !Configuration::deleteByName('IFRAMEWEIGHT')
            || !Configuration::deleteByName('APPLEPAYSCRIPT_1')
			|| !parent::uninstall())
			return false;
		return true;
	}
	private function  _displayPayplus()
	{
		$this->_html .= '<img src="'._MODULE_DIR_.'payplus/views/img/payplus.jpg" style="float:left; margin-right:15px;"><b>'
						.$this->l('PayPlus credit card clearing service')
						.'</b><br /><br />';
	}

    public function hookHeader()
    {
        if(Dispatcher::getInstance()->getController() === "payment")
        { 

            $this->context->controller->registerJavascript('module-payplus-payment', 'modules/'.$this->name.'/views/js/custom.js', ['position' => 'bottom', 'priority' => 150]);


            $config = Configuration::getMultiple(array('APPLEPAYSCRIPT_1', 'DISPLAY'));
            $this->display = $config['DISPLAY'];
            $this->appleScript = $config['APPLEPAYSCRIPT_1'];

            if($this->display == 1 && $this->appleScript == 1) {
                  $this->context->controller->registerJavascript('module-payplus-payment', 'https://paymentsdev.payplus.co.il/statics/applePay/script.js', ['server' => 'remote', 'position' => 'bottom', 'priority' => 150]);
            }
         
		}
    }


    public function renderForm()
	{
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Settings', array(), 'Admin.Global'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    [
                        'type' => 'checkbox',
                        'label' => $this->trans('Test Mode SandBox', [], 'Modules.PayPlus.Admin'),
                        'name' => 'TESTMODE',
                        'values' => [
                            'query' => [
                                ['id'=>'1', 'name' => '', 'val' => '1'],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                            'value' => '1'
                        ],
                        'hint' => $this->trans('Check if you want to turn on sandbox mode', [], 'Modules.PayPlus.Admin'),
                    ],[
                        'type' => 'text',
                        'label' => $this->trans('API Key', array(), 'Modules.PayPlus.Admin'),
                        'name' => 'APIKEY',
                        'desc' => $this->trans('Enter the api key of your PayPlus Account', array(), 'Modules.PayPlus.Admin'),
                        'required' => false,
                    ],
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Secret Key', array(), 'Modules.PayPlus.Admin'),
                        'name' => 'SECRETKEY',
                        'desc' => $this->trans('Enter the secret key of your PayPlus Account', array(), 'Modules.PayPlus.Admin'),
                        'required' => false,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Payment Page Uid', array(), 'Modules.Banner.Admin'),
                        'name' => 'PAYMENTPAGEUID',
                        'desc' => $this->trans('Enter the Payment Page UID of your PayPlus Account.', array(), 'Modules.PayPlus.Admin'),
                        'required' => false,
                    ),
                    [
                        'type' => 'select',
                        'label' => $this->trans('Transaction Type', array(), 'Modules.PayPlus.Admin'),
                        'desc' => $this->trans('Choose type transaction you want use.', array(), 'Modules.PayPlus.Admin'),
                        'name' => 'TRANSACTIONTYPE',
                        'required' => false,
                        'options' => array(
                            'query' => array(
                                array('id' => 0, 'name' => $this->trans('Default payment page setting', array(), 'Modules.Emailsubscription.Admin')),
                                array('id' => 1, 'name' => $this->trans('Charge', array(), 'Modules.PayPlus.Admin')),
                                array('id' => 2, 'name' => $this->trans('Authorized', array(), 'Modules.PayPlus.Admin')),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->trans('Invoice', array(), 'Modules.PayPlus.Admin'),
                        'desc' => $this->trans('Generate an invoice / receipt for each successful charge.', array(), 'Modules.PayPlus.Admin'),
                        'name' => 'INVOICE',
                        'class' => 'w-100-payplus',
                        'required' => false,
                        'options' => array(
                            'query' => array(
                                array('id' => 0, 'name' => $this->trans('Default payment page setting', array(), 'Modules.PayPlus.Admin')),
                                array('id' => 1, 'name' => $this->trans('True', array(), 'Modules.PayPlus.Admin')),
                                array('id' => 2, 'name' => $this->trans('False', array(), 'Modules.PayPlus.Admin')),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->trans('Display Payment Form', array(), 'Modules.PayPlus.Admin'),
                        'name' => 'DISPLAY',
                        'class' => 'w-100 !important',
                        'required' => false,
                        'options' => array(
                            'query' => array(
                                array('id' => 0, 'name' => $this->trans('New page (Redirect)', array(), 'Modules.PayPlus.Admin')),
                                array('id' => 1, 'name' => $this->trans('Same page (IFrame)', array(), 'Modules.PayPlus.Admin')),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ],
                    array(
                        'type' => 'text',
                        'class' => 'IFRAMEWEIGHT',
                        'label' => $this->trans('Height IFrame (pixels) ', array(), 'Modules.PayPlus.Admin'),
                        'name' => 'IFRAMEWEIGHT',
                        'required' => false,
                    ),
                      array(
                        'type' => 'checkbox',
                        'label' => $this->trans('Use ApplePay Script', [], 'Modules.PayPlus.Admin'),
                        'name' => 'APPLEPAYSCRIPT',
                        'values' => [
                            'query' => [
                                ['id'=>'1', 'name' => '', 'val' => '1'],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                            'value' => '1'
                        ],
                        'hint' => $this->trans('Check if you want to use apple pay in iframe', [], 'Modules.PayPlus.Admin'),
                    )
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions')
                )
            ),
        );

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->fields_value['APIKEY'] = Configuration::get('APIKEY');
        $helper->fields_value['SECRETKEY'] = Configuration::get('SECRETKEY');
        $helper->fields_value['PAYMENTPAGEUID'] = Configuration::get('PAYMENTPAGEUID');
        $helper->fields_value['TESTMODE_1'] = Configuration::get('TESTMODE_1');
        $helper->fields_value['APPLEPAYSCRIPT_1'] = Configuration::get('APPLEPAYSCRIPT_1');
        $helper->fields_value['TRANSACTIONTYPE'] = Configuration::get('TRANSACTIONTYPE');
        $helper->fields_value['INVOICE'] = Configuration::get('INVOICE');
        $helper->fields_value['DISPLAY'] = Configuration::get('DISPLAY');
        $helper->fields_value['IFRAMEWEIGHT'] = Configuration::get('IFRAMEWEIGHT');

        return $helper->generateForm(array($fields_form));
	}

	public function getContent()
	{
        return $this->postProcess().$this->renderForm();
	}

    private function postProcess()
	{
		if (Tools::isSubmit('btnSubmit')) {
			Configuration::updateValue('APIKEY', Tools::getValue('APIKEY'));
			Configuration::updateValue('SECRETKEY', Tools::getValue('SECRETKEY'));
            Configuration::updateValue('PAYMENTPAGEUID', Tools::getValue('PAYMENTPAGEUID'));
            Configuration::updateValue('TESTMODE_1', Tools::getValue('TESTMODE_1'));
            Configuration::updateValue('APPLEPAYSCRIPT_1', Tools::getValue('APPLEPAYSCRIPT_1'));
            Configuration::updateValue('TRANSACTIONTYPE', Tools::getValue('TRANSACTIONTYPE'));
            Configuration::updateValue('INVOICE', Tools::getValue('INVOICE'));
            Configuration::updateValue('DISPLAY', Tools::getValue('DISPLAY'));
            Configuration::updateValue('IFRAMEWEIGHT', Tools::getValue('IFRAMEWEIGHT'));

            return $this->displayConfirmation($this->trans('The settings have been updated.', array(), 'Admin.Notifications.Success'));
		}
        return '';
	}
}