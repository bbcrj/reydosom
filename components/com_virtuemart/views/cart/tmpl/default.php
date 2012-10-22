<?php
/**
*
* Layout for the shopping cart
*
* @package	VirtueMart
* @subpackage Cart
* @author Max Milbers
*
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: cart.php 2551 2010-09-30 18:52:40Z milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
JHTML::script('facebox.js', 'components/com_virtuemart/assets/js/', false);
JHTML::stylesheet('facebox.css', 'components/com_virtuemart/assets/css/', false);

JHtml::_('behavior.formvalidation');
$document = &JFactory::getDocument();
$document->addScriptDeclaration("
	jQuery(document).ready(function($) {
		$('div#full-tos').hide();
		$('span.terms-of-service').click( function(){
			//$.facebox({ span: '#full-tos' });
			$.facebox( { div: '#full-tos' }, 'my-groovy-style');
		});
	});
");
$document->addStyleDeclaration('#facebox .content {display: block !important; height: 480px !important; overflow: auto; width: 560px !important; }');
//  vmdebug('cart',$this->cart);
?>
<?php if (VmConfig::get('oncheckout_show_steps', 1) && $this->checkout_task==='confirm'){
		vmdebug('checkout_task',$this->checkout_task);
		echo '<h1 class="checkoutStep" id="checkoutStep4">'.JText::_('COM_VIRTUEMART_USER_FORM_CART_STEP4').'</h1>';
	} ?>
<div class="cart-view">
		<h3><span><span><?php echo JText::_('TM_VIRTUEMART_CART_TITLE'); ?></span></span></h3>
	<div class="login-box">
	<div class="right-link">
		<?php // Continue Shopping Button
		if ($this->continue_link_html != '') {
			echo $this->continue_link_html;
		} ?>
	</div>
	<?php echo shopFunctionsF::getLoginForm($this->cart,false);
//echo $this->loadTemplate('login');


//
//
//// Continue and Checkout Button
/* The problem here is that we use a form for the quantity boxes and so we cant let the form start here,
 * because we would have then a form in a form.
 *
 * But we cant make an extra form here, because then pressing the above checkout button would not send the
 * user notices for exampel. The solution is to write a javascript which checks and unchecks both tos checkboxes simultan
 * The upper checkout button should than just fire the form below.
 *
<div class="checkout-button-top">

	<?php // Terms Of Service Checkbox
	if(!class_exists('VmHtml'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');
	echo VmHtml::checkbox('tosAccepted',$this->cart->tosAccepted,1,0,'class="terms-of-service"');
	$checked = '';
	//echo '<input class="terms-of-service" type="checkbox" name="tosAccepted" value="1" ' . $this->cart->tosAccepted . '/>

	echo '<span class="tos">'. JText::_('COM_VIRTUEMART_CART_TOS_READ_AND_ACCEPTED').'</span>';
	?>

	<?php // Checkout Button
	echo $this->checkout_link_html;
	$text = JText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU');
	?>

</div>
	<form method="post" id="checkoutForm" name="checkoutForm" action="<?php echo JRoute::_( 'index.php?option=com_virtuemart' ); ?>">

	<input type='hidden' name='task' value='<?php echo $this->checkout_task; ?>'/>
	<input type='hidden' name='option' value='com_virtuemart'/>
	<input type='hidden' name='view' value='cart'/>
*/
	// This displays the pricelist MUST be done with tables, because it is also used for the emails
	
	?>
	</div>
</div>
<div class="cart-view">
		<h3><span><span><?php echo JText::_('TM_VIRTUEMART_CART_BILLING'); ?></span></span></h3>
		<div class="billing-box">
			<div class="billto-shipto">
	<div class="width50 floatleft">

		<span><span class="vmicon vm2-billto-icon"></span>
		<?php echo JText::_('COM_VIRTUEMART_USER_FORM_BILLTO_LBL'); ?></span>
		<?php // Output Bill To Address ?>
		<div class="output-billto">
		<?php

		foreach($this->cart->BTaddress['fields'] as $item){
			if(!empty($item['value'])){
				if($item['name']==='agreed'){
					$item['value'] =  ($item['value']===0) ? JText::_('COM_VIRTUEMART_USER_FORM_BILLTO_TOS_NO'):JText::_('COM_VIRTUEMART_USER_FORM_BILLTO_TOS_YES');
				}
				?><!-- span class="titles"><?php echo $item['title'] ?></span -->
					<span class="values vm2<?php echo '-'.$item['name'] ?>" ><?php echo $this->escape($item['value']) ?></span>
				<?php if ($item['name'] != 'title' and $item['name'] != 'first_name' and $item['name'] != 'middle_name' and $item['name'] != 'zip') { ?>
					<br class="clear" />
				<?php
				}
			}
		} ?>
		<div class="clear"></div>
		</div>

		<a class="details" href="<?php echo JRoute::_('index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=BT',$this->useXHTML,$this->useSSL) ?>">
		<?php echo JText::_('COM_VIRTUEMART_USER_FORM_EDIT_BILLTO_LBL'); ?>
		</a>

		<input type="hidden" name="billto" value="<?php echo $this->cart->lists['billTo']; ?>"/>
	</div>

	<div class="width50 floatleft">

		<span><span class="vmicon vm2-shipto-icon"></span>
		<?php echo JText::_('COM_VIRTUEMART_USER_FORM_SHIPTO_LBL'); ?></span>
		<?php // Output Bill To Address ?>
		<div class="output-shipto">
		<?php
		if(empty($this->cart->STaddress['fields'])){
			echo JText::sprintf('TM_VIRTUEMART_USER_FORM_EDIT_BILLTO_EXPLAIN',JText::_('COM_VIRTUEMART_USER_FORM_ADD_SHIPTO_LBL') );
		} else {
			if(!class_exists('VmHtml'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');
			echo JText::_('COM_VIRTUEMART_USER_FORM_ST_SAME_AS_BT'). VmHtml::checkbox('STsameAsBT',$this->cart->STsameAsBT).'<br />';
			foreach($this->cart->STaddress['fields'] as $item){
				if(!empty($item['value'])){ ?>
					<!-- <span class="titles"><?php echo $item['title'] ?></span> -->
					<?php
					if ($item['name'] == 'first_name' || $item['name'] == 'middle_name' || $item['name'] == 'zip') { ?>
						<span class="values<?php echo '-'.$item['name'] ?>" ><?php echo $this->escape($item['value']) ?></span>
					<?php } else { ?>
						<span class="values" ><?php echo $this->escape($item['value']) ?></span>
						<br class="clear" />
					<?php
					}
				}
			}
		}
 		?>
		<div class="clear"></div>
		</div>
		<?php if(!isset($this->cart->lists['current_id'])) $this->cart->lists['current_id'] = 0; ?>
		<a class="details" href="<?php echo JRoute::_('index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=ST&cid[]='.$this->cart->lists['current_id'],$this->useXHTML,$this->useSSL) ?>">
		<?php echo JText::_('COM_VIRTUEMART_USER_FORM_ADD_SHIPTO_LBL'); ?>
		</a>

	</div>

	<div class="clear"></div>
</div>
		</div>
</div>

<div class="cart-view">
		<h3><span><span><?php echo JText::_('TM_VIRTUEMART_CART_ORDER'); ?></span></span></h3>
		<div class="billing-box">

<fieldset>
	<table
		class="cart-summary"
		cellspacing="0"
		cellpadding="0"
		border="0"
		width="100%">
		<tr>
			<th align="left"><?php echo JText::_('COM_VIRTUEMART_CART_NAME') ?></th>
			<th align="left"><?php echo JText::_('COM_VIRTUEMART_CART_SKU') ?></th>
			<th
				align="center"
				width="60px"><?php echo JText::_('COM_VIRTUEMART_CART_PRICE') ?></th>
			<th
				align="right"
				width="140px"><?php echo JText::_('COM_VIRTUEMART_CART_QUANTITY') ?>
				/ <?php echo JText::_('COM_VIRTUEMART_CART_ACTION') ?></th>


                                        <?php if ( VmConfig::get('show_tax')) { ?>
                                <th align="right" width="60px"><?php  echo "<span  class='priceColor2'>".JText::_('COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT') ?></th>
				<?php } ?>
                                <th align="right" width="60px"><?php echo "<span  class='priceColor2'>".JText::_('COM_VIRTUEMART_CART_SUBTOTAL_DISCOUNT_AMOUNT') ?></th>
				<th align="right" width="70px"><?php echo JText::_('COM_VIRTUEMART_CART_TOTAL') ?></th>
			</tr>



		<?php
		$i=2;
		foreach( $this->cart->products as $pkey =>$prow ) { ?>
			<tr valign="top" class="sectiontableentry<?php echo $i ?>">
				<td align="center" >
					<?php if ( $prow->virtuemart_media_id) {  ?>
						<span class="cart-images">
						 <?php
						 if(!empty($prow->image)) echo '<a href="'.$prow->url.'" >'.$prow->image->displayMediaThumb('',false).'</a>';
						 ?>
						</span>
					<?php } ?>
					<span class="cart-title"><?php echo JHTML::link($prow->url, $prow->product_name); ?></span>
					
					<?php
					if ( $prow->customfields) 
					{
					echo $prow->customfields; 
					}
					?>
					

				</td>
				<td align="center" ><?php  echo $prow->product_sku ?></td>
				<td align="center" >
				<?php
					echo $prow->salesPrice .'<br />';
					if (VmConfig::get('checkout_show_origprice',1) && !empty($this->cart->pricesUnformatted[$pkey]['basePriceWithTax']) && $prow->basePriceWithTax != $prow->salesPrice ) {
						echo '<span class="line-through">'.$prow->basePriceWithTax .'</span>' ;
					}
					?>
				</td>
				<td align="center" ><form action="index.php" method="post" class="inline">
				<input type="hidden" name="option" value="com_virtuemart" />
				<input type="text" title="<?php echo  JText::_('COM_VIRTUEMART_CART_UPDATE') ?>" class="inputbox" size="3" maxlength="4" name="quantity" value="<?php echo $prow->quantity ?>" />
				<input type="hidden" name="view" value="cart" />
				<input type="hidden" name="task" value="update" />
				<input type="hidden" name="cart_virtuemart_product_id" value="<?php echo $prow->cart_item_id  ?>" />
				<input type="submit" class="vmicon vm2-add_quantity_cart" name="update" title="<?php echo  JText::_('COM_VIRTUEMART_CART_UPDATE') ?>" align="middle" value=" "/>
			  </form>
					<a class="vmicon vm2-remove_from_cart" title="<?php echo JText::_('COM_VIRTUEMART_CART_DELETE') ?>" align="middle" href="<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart&task=delete&cart_virtuemart_product_id='.$prow->cart_item_id  ) ?>"> </a>
				</td>

				<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="center"><?php echo "<span  class='priceColor2'>".$prow->subtotal_tax_amount."</span>" ?></td>
                                <?php } ?>
				<td align="center"><?php if ($prow->subtotal_discount[1])
				{
					echo "<span  class='priceColor2'>".$prow->subtotal_discount."</span>" ;
				} else 
				{
					echo "--";
				}
					?>
				</td>
				<td colspan="1" align="center"><strong><?php echo $prow->subtotal_with_tax ?></strong></td>
			</tr>
		<?php
			$i = 1 ? 2 : 1;
		} ?>
		<!--Begin of SubTotal, Tax, Shipment, Coupon Discount and Total listing -->
                  <?php if ( VmConfig::get('show_tax')) { $colspan=3; } else { $colspan=2; } ?>
		<tr class="pad">
		 <td></td>
		</tr>
		  <tr class="sectiontableentry1 bg-top">
			<td colspan="4" align="center"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_PRICES_TOTAL'); ?></td>

                        <?php if ( VmConfig::get('show_tax')) { ?>
			<td align="center"><?php echo "<span  class='priceColor2'>".$this->cart->prices['taxAmount']."</span>" ?></td>
                        <?php } ?>
			<td align="center"><?php if ($this->cart->prices['discountAmount'][1]){
				echo "<span  class='priceColor2'>".$this->cart->prices['discountAmount']."</span>" ;
			} else {
				echo "--";
				}
			?>
			</td>
			<td align="center"><?php echo $this->cart->prices['priceWithoutTax'] ?></td>
		  </tr>
		  
		<?php
		foreach($this->cart->cartData['DBTaxRulesBill'] as $rule){ ?>
			<tr class="sectiontableentry<?php $i ?>">
				<td colspan="4" align="center"><?php echo $rule['calc_name'] ?> </td>

                                   <?php if ( VmConfig::get('show_tax')) { ?>
				<td align="center"> </td>
                                <?php } ?>
				<td align="center"> <?php echo $this->cart->prices[$rule['virtuemart_calc_id'].'Diff'];  ?></td>
				<td align="center"><?php echo $this->cart->prices[$rule['virtuemart_calc_id'].'Diff'];   ?> </td>
			</tr>
			<?php
			if($i) $i=1; else $i=0;
		} ?>

		<?php

		foreach($this->cart->cartData['taxRulesBill'] as $rule){ ?>
			<tr class="sectiontableentry<?php $i ?>">
				<td colspan="4" align="right"><?php echo $rule['calc_name'] ?> </td>
				<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"><?php echo $this->cart->prices[$rule['virtuemart_calc_id'].'Diff']; ?> </td>
				 <?php } ?>
				<td align="right"><?php    ?> </td>
				<td align="right"><?php echo $this->cart->prices[$rule['virtuemart_calc_id'].'Diff'];   ?> </td>
			</tr>
			<?php
			if($i) $i=1; else $i=0;
		}

		foreach($this->cart->cartData['DATaxRulesBill'] as $rule){ ?>
			<tr class="sectiontableentry<?php $i ?>">
				<td colspan="4" align="right"><?php echo   $rule['calc_name'] ?> </td>

                                     <?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"> </td>

                                <?php } ?>
				<td align="right"><?php  echo  $this->cart->prices[$rule['virtuemart_calc_id'].'Diff'];   ?>  </td>
				<td align="right"><?php echo $this->cart->prices[$rule['virtuemart_calc_id'].'Diff'];   ?> </td>
			</tr>
			<?php
			if($i) $i=1; else $i=0;
		} ?>


	<tr class="sectiontableentry1">
                    <?php if (!$this->cart->automaticSelectedShipment) { ?>

		<?php	/*	<td colspan="2" align="right"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING'); ?> </td> */?>
				<td colspan="4" align="center">
				<?php echo $this->cart->cartData['shipmentName']; ?>
				    <br />
				<?php
				if(!empty($this->layoutName) && $this->layoutName=='default' && !$this->cart->automaticSelectedShipment  )
					echo JHTML::_('link', JRoute::_('index.php?view=cart&task=edit_shipment',$this->useXHTML,$this->useSSL), $this->select_shipment_text,'class=""');
				else {
				    JText::_('COM_VIRTUEMART_CART_SHIPPING');
				}
				} else { ?>
                                <td colspan="4" align="left">
				<?php echo $this->cart->cartData['shipmentName']; ?>
				</td>
                                 <?php } ?>

                                     <?php if ( VmConfig::get('show_tax')) { ?>
				<td align="center">
				<?php if ($this->cart->prices['shipmentTax'][1])
				{
				echo "<span  class='priceColor2'>".$this->cart->prices['shipmentTax']."</span>"; 
				} else 
				{
					echo "--";
				}
				?> 
				</td>
				
                                <?php } ?>
				<td></td>
				<td align="center">
				<?php if ($this->cart->prices['salesPriceShipment'][1])
				{
				 echo $this->cart->prices['salesPriceShipment']; 
				} else 
				{
					echo "--";
				}
				?> 
		</tr>

		<tr class="sectiontableentry1">
                          <?php if (!$this->cart->automaticSelectedPayment) { ?>

				<td colspan="4" align="center">
				<?php echo $this->cart->cartData['paymentName']; ?>
				     <br />
				<?php if(!empty($this->layoutName) && $this->layoutName=='default') echo JHTML::_('link', JRoute::_('index.php?view=cart&task=editpayment',$this->useXHTML,$this->useSSL), $this->select_payment_text,'class=""'); else JText::_('COM_VIRTUEMART_CART_PAYMENT'); ?> </td>

				</td>
                         <?php } else { ?>
                                    <td colspan="4" align="center"><?php echo $this->cart->cartData['paymentName']; ?> </td>
                                 <?php } ?>
                                     <?php if ( VmConfig::get('show_tax')) { ?>
									 
				<td align="center">
				<?php if ($this->cart->prices['paymentTax'][1])
				{
				 echo "<span  class='priceColor2'>".$this->cart->prices['paymentTax']."</span>";
				} else 
				{
					echo "--";
				}
				?> 
				<?php  ?> </td>
                                <?php } ?>
				<td align="center"><?php //echo "<span  class='priceColor2'>".$this->cart->prices['paymentDiscount']."</span>"; ?></td>
				<td align="center">
				<?php if ($this->cart->prices['salesPricePayment'][1])
				{
				 echo $this->cart->prices['salesPricePayment']; 
				} else 
				{
					echo "--";
				}
				?> 
			</tr>
		  <tr class="pad">
		 <td></td>
		</tr>
		  <tr class="sectiontableentry2 bg-top">
			<td colspan="4" align="right"><?php echo JText::_('COM_VIRTUEMART_CART_TOTAL') ?>: </td>

                        <?php if ( VmConfig::get('show_tax')) { ?>
			<td align="center"> <?php echo "<span  class='priceColor2'>".$this->cart->prices['billTaxAmount']."</span>" ?> </td>
                        <?php } ?>
			<td align="center"> <?php if ($this->cart->prices['billDiscountAmount'][1]) { echo "<span  class='priceColor2'>".$this->cart->prices['billDiscountAmount']."</span>";}else { echo "--";} ?> </td>
			<td align="center" class="total"><strong><?php echo $this->cart->prices['billTotal'] ?></strong></td>
		  </tr>
		    <?php
		    if ( $this->totalInPaymentCurrency) {
			?>

		       <tr class="sectiontableentry2 bg-top">
					    <td colspan="4" align="right"><?php echo JText::_('COM_VIRTUEMART_CART_TOTAL_PAYMENT') ?>: </td>

					    <?php if ( VmConfig::get('show_tax')) { ?>
					    <td align="center">  </td>
					    <?php } ?>
					    <td align="center">  </td>
					    <td align="center"><strong><?php echo $this->totalInPaymentCurrency ?></strong></td>
				      </tr>
				      <?php
		    }
		    ?>
	</table>
		<?php
		if (VmConfig::get('coupons_enable')) {
		?>
			<div class="userFormcoupon">

				    <?php if(!empty($this->layoutName) && $this->layoutName=='default') {
					   // echo JHTML::_('link', JRoute::_('index.php?view=cart&task=edit_coupon',$this->useXHTML,$this->useSSL), JText::_('COM_VIRTUEMART_CART_EDIT_COUPON'));
					    echo $this->loadTemplate('coupon');
				    }
				?>

				<?php if (!empty($this->cart->cartData['couponCode'])) { ?>
					 <?php
						echo $this->cart->cartData['couponCode'] ;
						echo $this->cart->cartData['couponDescr'] ? (' (' . $this->cart->cartData['couponDescr'] . ')' ): '';
						?>
<?php } ?>
	</div>	

		<?php } ?>

</fieldset>
<form method="post" id="checkoutForm" name="checkoutForm" action="<?php echo JRoute::_( 'index.php?option=com_virtuemart&view=cart'.$taskRoute,$this->useXHTML,$this->useSSL ); ?>">

		<?php // Leave A Comment Field ?>
		<div class="customer-comment marginbottom15">
			<span class="comment"><?php echo JText::_('COM_VIRTUEMART_COMMENT'); ?></span><br />
			<textarea class="customer-comment" name="customer_comment" cols="50" rows="4"><?php echo $this->cart->customer_comment; ?></textarea>
		</div>
		<?php // Leave A Comment Field END ?>



		<?php // Continue and Checkout Button ?>
		<div class="checkout-button-top">

			<?php // Terms Of Service Checkbox
			if (!class_exists('VirtueMartModelUserfields')){
				require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'userfields.php');
			}
			$userFieldsModel = VmModel::getModel('userfields');
			if($userFieldsModel->getIfRequired('agreed')){
			    ?>
			    <label for ="tosAccepted">
			    <?php
				if(!class_exists('VmHtml'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');
				echo VmHtml::checkbox('tosAccepted',$this->cart->tosAccepted,1,0,'class="terms-of-service"');

		if(VmConfig::get('oncheckout_show_legal_info',1)){
		?>
		<div class="terms-of-service">
			<span class="terms-of-service" rel="facebox"><span class="vmicon vm2-termsofservice-icon"></span><?php echo JText::_('COM_VIRTUEMART_CART_TOS_READ_AND_ACCEPTED'); ?><span class="vm2-modallink"></span></span>
			<div id="full-tos">
				<h2><?php echo JText::_('COM_VIRTUEMART_CART_TOS'); ?></h2>
				<?php echo $this->cart->vendor->vendor_terms_of_service;?>

			</div>
		</div>
		<?php
		} // VmConfig::get('oncheckout_show_legal_info',1)
				//echo '<span class="tos">'. JText::_('COM_VIRTUEMART_CART_TOS_READ_AND_ACCEPTED').'</span>';
				?>
			    </label>
		    <?php
			}
			echo $this->checkout_link_html;
			$text = JText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU');
			?>
		</div>
		<?php //vmdebug('my cart',$this->cart);// Continue and Checkout Button END ?>

		<input type='hidden' name='task' value='<?php echo $this->checkout_task; ?>'/>
		<input type='hidden' name='option' value='com_virtuemart'/>
		<input type='hidden' name='view' value='cart'/>
	</form>
</div>
</div>

