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

//no direct access
defined('_JEXEC') or die('Restricted access');

?>

<style type="text/css">
#sagepay_form {
	width: 100%;
}

#sagepay_form td {
	padding: 5px;
}

#sagepay_form .field_name {
	font-weight: bold;
}
</style>
<div class="note">

	<?php echo JText::_($vars->display_name); ?>
	<br />
	<?php echo JText::_($vars->onbeforepayment_text); ?>
</div>
<form id="heart-payment-form"
	action="<?php echo JRoute::_( "index.php?option=com_j2store&view=checkout" ); ?>"
	method="post" name="adminForm" enctype="multipart/form-data">
	<table >
		<tr>
			<td class="field_name"><?php echo JText::_( 'J2STORE_CARDHOLDER_NAME' ) ?></td>
			<td><?php echo $vars->cardholder; ?></td>
		</tr>
		<tr>
			<td class="field_name"><?php echo JText::_( 'J2STORE_CARD_NUMBER' ) ?></td>
			<td>************<?php echo $vars->heartmask; ?></td>
		</tr>
	</table>

    <input type='hidden' name='cardholder' value='<?php echo @$vars->cardholder; ?>'>
    <input type='hidden' name='hearttoken' id="hearttoken" value='<?php echo @$vars->hearttoken; ?>'>
	<input type="button" onclick="doSendRequest()" class="button btn btn-primary" id="heart-submit-button" value="<?php echo JText::_($vars->button_text); ?>" />
	<input type='hidden' name='first_name' value='<?php echo @$vars->first_name; ?>'>
	<input type='hidden' name='currency_code' value='<?php echo @$vars->currency_code; ?>'>
	<input type='hidden' name='orderpayment_amount' value='<?php echo @$vars->orderpayment_amount; ?>'>
	<input type='hidden' name='last_name' value='<?php echo @$vars->last_name; ?>'>
	<input type='hidden' name='email' value='<?php echo @$vars->email; ?>'>
	<input type='hidden' name='address' value='<?php echo @$vars->address_1; ?>'>
	<input type='hidden' name='city' value='<?php echo @$vars->city; ?>'>
	<input type='hidden' name='country' value='<?php echo @$vars->country; ?>'>
	<input type='hidden' name='postal_code' value='<?php echo @$vars->postal_code; ?>'>
	<input type='hidden' name='region' value='<?php echo @$vars->region; ?>'>
	<input type='hidden' name='order_id' value='<?php echo @$vars->order_id; ?>'>
	<input type='hidden' name='orderpayment_id' value='<?php echo @$vars->orderpayment_id; ?>'>
	<input type='hidden' name='orderpayment_type' value='<?php echo @$vars->orderpayment_type; ?>'>
	<input type='hidden' name='cart_session_id' value='<?php echo @$vars->cart_session_id; ?>'>
	<input type='hidden' name='option' value='com_j2store' />
	<input type='hidden' name='view' value='checkout' />
	<input type='hidden' name='task' value='confirmPayment' />
	<input type='hidden' name='paction' value='process' />
    <?php echo JHTML::_( 'form.token' ); ?>
    <br />
	<div class="heart-payment-errors"></div>
	<br />
	<div class="plugin_error_div">
		<span class="plugin_error"></span>
		<span class="plugin_error_instruction"></span>
	</div>
</form>

<script type="text/javascript">
if(typeof(j2store) == 'undefined')
{
	var j2store = {};
}
if(typeof(j2store.jQuery) == 'undefined') {
	j2store.jQuery = jQuery.noConflict();
}

function doSendRequest() {

	(function($) {

    	var button = j2store.jQuery('#heart-submit-button');

    	//token created. But check again
		var token = j2store.jQuery('#hearttoken').val();
		if(token.length == 0) {
			//token is empty
			$(button).val('<?php echo JText::_('J2STORE_HEARTLAND_ERROR_PROCESSING')?>');
		} else {
			//get all form values
			var form = $('#heart-payment-form');
			var values = form.serializeArray();

			//submit the form using ajax
			var jqXHR =	$.ajax({
				url: 'index.php',
				type: 'post',
				data: values,
				dataType: 'json',
				beforeSend: function() {
					$(button).after('<span class="wait">&nbsp;loading...</span>');
				}
			});

			jqXHR.done(function(json) {
				form.find('.j2success, .j2warning, .j2attention, .j2information, .j2error').remove();
				console.log(json);
				if (json['error']) {
					form.find('.plugin_error').after('<span class="j2error">' + json['error']+ '</span>');
					form.find('.plugin_error_instruction').after('<br /><span class="j2error"><?php echo JText::_('J2STORE_HEARTLAND_ON_ERROR_INSTRUCTIONS'); ?></span>');
					$(button).val('<?php echo JText::_('J2STORE_HEARTLAND_ERROR_PROCESSING')?>');
				}

				if (json['redirect']) {
					$(button).val('<?php echo JText::_('J2STORE_HEARTLAND_COMPLETED_PROCESSING')?>');
					window.location.href = json['redirect'];
				}

			});

			jqXHR.fail(function() {
				$(button).val('<?php echo JText::_('J2STORE_HEARTLAND_ERROR_PROCESSING')?>');
			})

			jqXHR.always(function() {
				$('.wait').remove();
			 });
		}
	})(j2store.jQuery);
}

function logResponse(res)
{
    // create console.log to avoid errors in old IE browsers
    if (!window.console) console = {log:function(){}};
    console.log(res);
}
</script>