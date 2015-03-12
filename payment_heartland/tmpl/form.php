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

defined('_JEXEC') or die('Restricted access'); ?>


<div class="note">
	<p><?php echo JText::_($vars->onselection_text); ?></p>
</div>

<input type="hidden" id="heart-token" name="heartToken" />
<input type="hidden" id="heart-mask" name="heartMask" />

<table id="sagepay_form" class="table">
    <tr>
        <td class="field_name"><?php echo JText::_( 'J2STORE_CARDHOLDER_NAME' ) ?></td>
        <td><input type="text" class="required" name="cardholder"
        size="35"
        
        title="<?php echo JText::_('J2STORE_CARDHOLDER_VALIDATION_ERROR_NAME'); ?>"
        />
         <div class="j2error"></div>
        </td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_( 'J2STORE_CARD_NUMBER' ) ?></td>
        <td><input type="text" class="required number"
        id="cardnum"
        size="35"
       
        title="<?php echo JText::_('J2STORE_CARDHOLDER_VALIDATION_ERROR_NUMBER'); ?>"
        />
         <div class="j2error"></div>
        </td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_( 'J2STORE_EXPIRY_DATE' ) ?></td>
        <td>
        <select id="month" class="required number"
         title="<?php echo JText::_('J2STORE_EXPIRY_VALIDATION_ERROR_MONTH'); ?>"
        >
        	<option value=""><?php echo JText::_('J2STORE_EXPIRY_MONTH'); ?></option>
        	<option value="01">01</option>
        	<option value="02">02</option>
        	<option value="03">03</option>
        	<option value="04">04</option>
        	<option value="05">05</option>
        	<option value="06">06</option>
        	<option value="07">07</option>
        	<option value="08">08</option>
        	<option value="09">09</option>
        	<option value="10">10</option>
        	<option value="11">11</option>
        	<option value="12">12</option>
        </select>
         <div class="j2error"></div>
        <select id="year" class="required number"
        title="<?php echo JText::_('J2STORE_EXPIRY_VALIDATION_ERROR_YEAR'); ?>"
        >
        	<option value=""><?php echo JText::_('J2STORE_EXPIRY_YEAR'); ?></option>
        	<?php
        	$two_digit_year = date('y');
        	$four_digit_year = date('Y');
        	?>
        	<?php for($i=$two_digit_year;$i<$two_digit_year+50;$i++) {?>
        		<option value="<?php echo $four_digit_year;?>"><?php echo $four_digit_year;?></option>
        	<?php
        	$four_digit_year++;
        	} ?>
        	</select>
        	<div class="j2error"></div>
        <input type="hidden" class="" id="cardexp" size="10"  />
        </td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_( 'J2STORE_CARD_CVV' ) ?></td>
        <td>
        <input type="text" class="required number" id="cardcvv" size="10" value=""
         title="<?php echo JText::_('J2STORE_CARD_VALIDATION_ERROR_CVV'); ?>"
         />
         <div class="j2error"></div>
        </td>
    </tr>
</table>
<script type="text/javascript" src="<?php echo $vars->securescript;?>"></script>
<script type="text/javascript">
(function($) {
    function bindButton() {
        $('#button-payment-method').bind('click', function() {
            try {
                $('#button-payment-method').hide();
                hps.tokenize({
                    data: {
                        public_key: '<?php echo $vars->public_key; ?>',
                        number: $('#cardnum').val(),
                        cvc: $('#cardcvv').val(),
                        exp_month: $('#month').val(),
                        exp_year: $('#year').val()
                    },
                    success: function(response) {
                        var token = response.token_value;

                        j2store.jQuery('#heart-token').val(token);
                        j2store.jQuery('#heart-mask').val(response.card.number);
                        $('#button-payment-method').unbind('click');
                        $('#button-payment-method').show();
                        $('#button-payment-method').click();
                    },
                    error: function(response) {
                        alert(response.message);
                        $('#button-payment-method').unbind('click');
                        $('#button-payment-method').show();
                        bindButton();
                    }
                  });
            } catch(e) {
                alert('error');
                jQuery(".j2error").text(e);
                $('#button-payment-method').show();
                return false;
            }
            return false;
        });
    }

    bindButton();
})(j2store.jQuery);
</script>
