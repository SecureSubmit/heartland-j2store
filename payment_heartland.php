<?php
/**
 * --------------------------------------------------------------------------------
 * Payment Plugin - Heartland
 * --------------------------------------------------------------------------------
 * @package     Joomla 2.5 -  3.x
 * @subpackage  J2 Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2014-19 J2Store . All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR . '/components/com_j2store/library/plugins/payment.php';
require_once JPATH_SITE . '/components/com_j2store/helpers/utilities.php';
require_once JPATH_SITE.'/components/com_j2store/helpers/orders.php';
require_once JPATH_SITE.'/components/com_j2store/helpers/cart.php';
require_once JPATH_SITE.'/plugins/j2store/payment_heartland/library/vendor/autoload.php';
jimport('joomla.application.component.helper');
class plgJ2StorePayment_heartland extends J2StorePaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element    	= 'payment_heartland';

	private $_isLog = false;
	var $_j2version = null;
	private $_public_key = '';
	private $_secret_key = '';


	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgJ2StorePayment_heartland(& $subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
		$this->_j2version = $this->getVersion();
		$this->_isLog = $this->params->get('debug');
		$this->_public_key = $this->params->get('public_key', '');
		$this->_secret_key = $this->params->get('secret_key', '');
	}

	/**
	 * @param $order     object    Order table object
	 */
	function onJ2StoreGetPaymentOptions($element, $order)
	{
		// Check if this is the right plugin
		if (!$this->_isMe($element))
		{
			return null;
		}

		$status = true;
		// if this payment method should be available for this order, return true
		// if not, return false.
		// by default, all enabled payment methods are valid, so return true here,
		// but plugins may override this
		if( version_compare( $this->_j2version, '2.6.7', 'ge' ) ) {
			//$order = JTable::getInstance('Orders', 'Table');
			$order->setAddress();
			$address = $order->getBillingAddress();
			$geozone_id = $this->params->get('geozone_id', '0');
			//get the geozones
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('gz.*,gzr.*')->from('#__j2store_geozones AS gz')
			->innerJoin('#__j2store_geozonerules AS gzr ON gzr.geozone_id = gz.geozone_id')
			->where('gz.geozone_id='.$geozone_id )
			->where('gzr.country_id='.$db->q($address['country_id']).' AND (gzr.zone_id=0 OR gzr.zone_id='.$db->q($address['zone_id']).')');
			$db->setQuery($query);
			$grows = $db->loadObjectList();

			if (!$geozone_id ) {
				$status = true;
			} elseif ($grows) {
				$status = true;
			} else {
				$status = false;
			}
		}

		return $status;
	}

	/**
     * Prepares the payment form
     * and returns HTML Form to be displayed to the user
     * generally will have a message saying, 'confirm entries, then click complete order'
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _prePayment( $data )
    {
    	$app = JFactory::getApplication();
    	// prepare the payment form
    	$vars = new JObject();

    	//now we have everthing in the data. We need to generate some more HEARTLAND specific things.
    	//action url
    	$vars->url = JRoute::_( "index.php?option=com_j2store&view=checkout" );
    	$vars->order_id = $data['order_id'];
    	$vars->orderpayment_id = $data['orderpayment_id'];
    	//order details from order table
    	JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
    	$order = JTable::getInstance('Orders', 'Table');
    	$order->load($data['orderpayment_id']);
    	$vars->orderpayment_amount = $order->orderpayment_amount;
    	$vars->orderpayment_type = $this->_element;
    	//get card data from from tmpl
    	$vars->cardholder = $app->input->getString("cardholder", '');
    	$vars->heartmask = $app->input->getString("heartMask");
    	$vars->hearttoken = $app->input->getString("heartToken");

    	 //get client details from data
    	if(!empty($data['orderinfo']['billing_address_1']))
    	{
    		$vars->address_1    = $data['orderinfo']['billing_address_1'];
    	}else{
    		$vars->address_1    = $data['orderinfo']['billing_address_2'];
    	}

    	$vars->first_name   = $data['orderinfo']['billing_first_name'];
    	$vars->last_name    = $data['orderinfo']['billing_last_name'];
    	$vars->email        = $data['orderinfo']['user_email'];
    	$vars->city         = $data['orderinfo']['billing_city'];
    	$vars->country      = $data['orderinfo']['billing_country_name'];
    	$vars->region       = $data['orderinfo']['billing_zone_name'];
    	$vars->postal_code  = $data['orderinfo']['billing_zip'];
    	$vars->display_name = $this->params->get('display_name', 'PLG_J2STORE_PAYMENTS_HEARTLAND');
    	$vars->onbeforepayment_text = $this->params->get('onbeforepayment', '');
    	$vars->button_text = $this->params->get('button_text', 'J2STORE_PLACE_ORDER');
    	$vars->cart_session_id = JFactory::getSession()->getId();
    	
    	$html = $this->_getLayout('prepayment', $vars);
    	return $html;

    }
    /**
     * Processes the payment form
     * and returns HTML to be displayed to the user
     * generally with a success/failed message
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _postPayment( $data )
    {
    	// Process the payment
    	$vars = new JObject();

    	$app =JFactory::getApplication();
    	$paction = $app->input->getString('paction' );

    	switch ($paction)
    	{
    		case 'display':
    			//return url from response
    			$html = JText::_($this->params->get('onafterpayment', ''));
    			$html .= $this->_displayArticle();
    			break;
    		case 'process':
    			//notify url response
    			$result = $this->_process();
    			echo json_encode($result);
    			$app->close();
    			break;
    		default:
    			//error
    			$vars->message = JText::_($this->params->get('onerrorpayment', ''));
    			$html = $this->_getLayout('message', $vars);
    			break;
    	}

    	return $html;
    }


	function _process() {
		$app = JFactory::getApplication ();
		$data = $app->input->getArray ( $_POST );
		$api_key = $this->_secret_key;
		// get order data from table using response data
		$json = array ();
		if (! JRequest::checkToken ()) {
			$json ['error'] = JText::_ ( 'J2STORE_HEARTLAND_INVALID_TOKEN' );
		}
		JTable::addIncludePath ( JPATH_ADMINISTRATOR . '/components/com_j2store/tables' );
		$order = JTable::getInstance ( 'Orders', 'Table' );
		$order->load ( $data ['orderpayment_id'] );
		$error = false;
		// validate order and params

		if (empty ( $data ['hearttoken'] )) {
			$json ['error'] = JText::_ ( 'J2STORE_HEARTLAND_TOKEN_MISSING' );
			$error = true;
		}

		if (empty ( $order->order_id )) {
			$json ['error'] = JText::_ ( 'J2STORE_HEARTLAND_INVALID_ORDER' );
			$error = true;
		}

		if (empty ( $api_key )) {
			$json ['error'] = JText::_ ( 'J2STORE_HEARTLAND_MESSAGE_MISSING_LOGIN_APIKEY' );
			$error = true;
		}

		// erroring:
		if (!$error) {
			try {
				// heartland config code
				$config = new HpsServicesConfig ();
				$config->secretApiKey = $api_key;
				$config->versionNumber = '1929';
				$config->developerId = '002914';
				$chargeService = new HpsCreditService ( $config );

				// add cardholder details
				$address = new HpsAddress ();
				$address->address = html_entity_decode ( $data ['address'], ENT_QUOTES, 'UTF-8' );
				$address->city = html_entity_decode ( $data ['city'], ENT_QUOTES, 'UTF-8' );
				$address->state = html_entity_decode ( $data ['region'], ENT_QUOTES, 'UTF-8' );
				$address->country = html_entity_decode ( $data ['country'], ENT_QUOTES, 'UTF-8' );
				$address->zip = html_entity_decode ( $data ['postal_code'], ENT_QUOTES, 'UTF-8' );

				$cardHolder = new HpsCardHolder ();
				$cardHolder->firstName = html_entity_decode ( $data ['first_name'], ENT_QUOTES, 'UTF-8' );
				$cardHolder->lastName = html_entity_decode ( $data ['last_name'], ENT_QUOTES, 'UTF-8' );
				$cardHolder->email = $data ['email'];
				$cardHolder->address = $address;

				$currency_values= $this->getCurrency($order);
				$orderpayment_amount = $this->getAmount ( $data ['orderpayment_amount'], $currency_values['currency_code'], $curency_value, true );

				$suToken = new HpsTokenData();
				$suToken->tokenValue = $data ['hearttoken'];
				$response = $chargeService->charge( $orderpayment_amount, strtolower($currency_values['currency_code']), $suToken, $cardHolder );
				$data['transaction_details'] = $response;

				if(isset($response->transactionId))
				{
					$data ['transaction_id'] = ( string ) $response->transactionId;
				}
				if(isset($response->responseText))
				{
					$data ['status'] = ( string ) $response->responseText;
				}
			} catch ( HpsException $e ) {
				$json ['error'] = $e;
			}
		}

		if (empty ( $data ['transaction_id'] )) {
			$json ['error'] = JText::_ ( 'J2STORE_HEARTLAND_MESSAGE_MISSING_TRANSACTION_ID' );
		}

		if (empty ( $data ['status'] )) {
			$json ['error'] = JText::_ ( 'J2STORE_HEARTLAND_MESSAGE_MISSING_TRANSACTION_STATUS' );
		}

		if (! $json) {
			// change order status and log data
			$order->transaction_details = $data ['transaction_details'];
			$order->transaction_id = $data ['transaction_id'];
			$order->transaction_status = $data ['status'];
			// TODO:we have to check confirm status message
			if ($data ['status'] == "APPROVAL") {
				$order->order_state = trim ( JText::_ ( 'J2STORE_CONFIRMED' ) ); // CONFIRMED
				$order->order_state_id = 1; // CONFIRMED
				$order->paypal_status = @$data ['status']; // Paypal's original status
				JLoader::register ( 'J2StoreHelperCart', JPATH_SITE . '/components/com_j2store/helpers/cart.php' );
				// remove items from cart
				// J2StoreHelperCart::removeOrderItems( $orderpayment->id );
				if (! empty ( $data ['cart_session_id'] )) {
					// load session with id
					$options = array (
							'id' => $data ['cart_session_id']
					);
					$session = JFactory::getSession ( $options );
					$session->set ( 'j2store_cart', array () );
				}
			} else { // failed status
				$order->order_state = JText::_ ( 'J2STORE_FAILED' ); // FAILED
				$order->order_state_id = 3; // FAILED
			}
			if ($order->store ()) {
			} else {
				$json ['error'] = $order->getError ();
			}
		}

		if (!$json) {
			$json ['success'] = JText::_ ( $this->params->get ( 'onafterpayment', '' ) );
			$json ['redirect'] = JRoute::_ ( 'index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=' . $this->_element . '&paction=display' );
			require_once (JPATH_SITE . '/components/com_j2store/helpers/orders.php');
			J2StoreOrdersHelper::sendUserEmail ( $order->user_id, $order->order_id, $data ['status'], $order->order_state, $order->order_state_id );
		}else {
			$this->_sendErrorEmails ( $error, $data ['transaction_details'] );
		}

		return $json;
	}

	//display form for get card details
    function _renderForm( $data )
    {
    	$user = JFactory::getUser();
    	$vars = new JObject();

    	$vars->securescript = JURI::base( true ).'/plugins/j2store/payment_heartland/library/js/secure.submit-1.0.2.js';
    	$vars->public_key = $this->_public_key;
    	$vars->onselection_text =$this->params->get('onselection', '');
    	$html = $this->_getLayout('form', $vars);

    	return $html;
    }

    /**
     * Verifies that all the required form fields are completed
     * if any fail verification, set
     * $object->error = true
     * $object->message .= '<li>x item failed verification</li>'
     *
     * @param $submitted_values     array   post data
     * @return unknown_type
     */
    function _verifyForm( $submitted_values )
    {

    	//verfy form data
    	$object = new JObject();
    	$object->error = false;
    	$object->message = '';
    	$user = JFactory::getUser();


    	foreach ($submitted_values as $key=>$value)
    	{
    		switch ($key)
    		{
    			case "cardholder":
    				if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
    				{
    					$object->error = true;
    					$object->message .= "<li>".JText::_( "J2STORE_HEARTLAND_MESSAGE_CARD_HOLDER_NAME_REQUIRED" )."</li>";
    				}
    				break;

    			case "cardnum":
    				if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
    				{
    					$object->error = true;
    					$object->message .= "<li>".JText::_( "J2STORE_HEARTLAND_MESSAGE_CARD_NUMBER_INVALID" )."</li>";
    				}
    				break;
    			case "month":
    				if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
    				{
    					$object->error = true;
    					$object->message .= "<li>".JText::_( "J2STORE_HEARTLAND_MESSAGE_CARD_EXPIRATION_MONTH_INVALID" )."</li>";
    				}
    				break;
    			case "year":
    				if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
    				{
    					$object->error = true;
    					$object->message .= "<li>".JText::_( "J2STORE_HEARTLAND_MESSAGE_CARD_EXPIRATION_YEAR_INVALID" )."</li>";
    				}
    				break;
    			case "cardcvv":
    				if (!isset($submitted_values[$key]) || !JString::strlen($submitted_values[$key]))
    				{
    					$object->error = true;
    					$object->message .= "<li>".JText::_( "J2STORE_HEARTLAND_MESSAGE_CARD_CVV_INVALID" )."</li>";
    				}
    				break;
    			default:
    				break;
    		}
    	}

    	return $object;
    }

    // send error email
    function _sendErrorEmails($message, $paymentData) {
    	$mainframe = JFactory::getApplication ();

    	// grab config settings for sender name and email
    	$config = JComponentHelper::getParams ( 'com_j2store' );
    	$mailfrom = $config->get ( 'emails_defaultemail', $mainframe->getCfg ( 'mailfrom' ) );
    	$fromname = $config->get ( 'emails_defaultname', $mainframe->getCfg ( 'fromname' ) );
    	$sitename = $config->get ( 'sitename', $mainframe->getCfg ( 'sitename' ) );
    	$siteurl = $config->get ( 'siteurl', JURI::root () );

    	$recipients = $this->_getAdmins ();

    	$subject = JText::sprintf ( 'J2STORE_HEARTLAND_EMAIL_PAYMENT_NOT_VALIDATED_SUBJECT', $sitename );

    	foreach ( $recipients as $recipient ) {
    		$mailer = JFactory::getMailer ();
    		$mailer->addRecipient ( $recipient->email );

    		$mailer->setSubject ( $subject );
    		$mailer->setBody ( JText::sprintf ( 'J2STORE_HEARTLAND_EMAIL_PAYMENT_FAILED_BODY', $recipient->name, $sitename, $siteurl, $message, $paymentData ) );
    		$mailer->setSender ( array (
    				$mailfrom,
    				$fromname
    		) );
    		$sent = $mailer->send ();
    	}

    	return true;
    }

    //amount conversion
    function getAmount($value, $currency_code, $currency_value, $convert=false) {

    	if( version_compare( $this->_j2version, '2.6.7', 'lt' ) ) {
    		return J2StoreUtilities::number( $value, array( 'thousands'=>'', 'num_decimals'=>'2', 'decimal'=>'.') );
    	} else {
    		include_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/base.php');
    		$currencyObject = J2StoreFactory::getCurrencyObject();
    		$amount = $currencyObject->format($value, $currency_code, $currency_value, false);
    		return $amount;
    	}

    }
    //convert to USD
	function getCurrency($order) {
		$results = array ();
		$convert = false;
		$params = JComponentHelper::getParams ( 'com_j2store' );

		if (version_compare ( $this->_j2version, '2.6.7', 'lt' )) {
			$currency_code = $params->get ( 'currency_code', 'USD' );
			$currency_value = 1;
		} else {

			include_once (JPATH_ADMINISTRATOR . '/components/com_j2store/library/base.php');
			$currencyObject = J2StoreFactory::getCurrencyObject ();

			$currency_code = $order->currency_code;
			$currency_value = $order->currency_value;

			// accepted currencies
			$currencies = $this->getAcceptedCurrencies ();
			if (! in_array ( $order->currency_code, $currencies )) {
				$default_currency = 'USD';
				if ($currencyObject->has ( $default_currency )) {
					$currencyObject->set ( $default_currency );
					$currency_code = $default_currency;
					$currency_value = $currencyObject->getValue ( $default_currency );
					$convert = true;
				}
			}
		}
		$results ['currency_code'] = $currency_code;
		$results ['currency_value'] = $currency_value;
		$results ['convert'] = $convert;

		return $results;
	}

	/**
	 * Gets admins data
	 *
	 * @return array|boolean
	 * @access protected
	 */
	function _getAdmins()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('u.name,u.email');
		$query->from('#__users AS u');
		$query->join('LEFT', '#__user_usergroup_map AS ug ON u.id=ug.user_id');
		$query->where('u.sendEmail = 1');
		$query->where('ug.group_id = 8');

		$db->setQuery($query);
		$admins = $db->loadObjectList();
		if ($error = $db->getErrorMsg()) {
			JError::raiseError(500, $error);
			return false;
		}
		return $admins;
	}

	/**
	 * Simple logger
	 *
	 * @param string $text
	 * @param string $type
	 * @return void
	 */
	function _log($text, $type = 'message')
	{
		if ($this->_isLog) {
			$file = JPATH_ROOT . "/cache/{$this->_element}.log";
			$date = JFactory::getDate();

			$f = fopen($file, 'a');
			fwrite($f, "\n\n" . $date->format('Y-m-d H:i:s'));
			fwrite($f, "\n" . $type . ': ' . $text);
			fclose($f);
		}
	}

	// Accepted currency code for Heartland
	function getAcceptedCurrencies() {
		$currencies = array (
				'USD'
		);
		return $currencies;
	}
    //get j2store version
	function getVersion() {

		if(is_null($this->_j2version)) {
			$xmlfile = JPATH_ADMINISTRATOR.'/components/com_j2store/manifest.xml';
			$xml = JFactory::getXML($xmlfile);
			$this->_j2version=(string)$xml->version;
		}
		return $this->_j2version;
	}

}