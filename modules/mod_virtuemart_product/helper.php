<?php
defined('_JEXEC') or  die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
* Module Helper
*
* @package VirtueMart
* @copyright (C) 2010 - Patrick Kohl
* @ Email: cyber__fr|at|hotmail.com
*
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/
if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php');
VmConfig::loadConfig();
// Load the language file of com_virtuemart.
JFactory::getLanguage()->load('com_virtuemart');
if (!class_exists( 'calculationHelper' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'calculationh.php');
if (!class_exists( 'CurrencyDisplay' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'currencydisplay.php');
if (!class_exists( 'VirtueMartModelVendor' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'models'.DS.'vendor.php');
if (!class_exists( 'VmImage' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'image.php');
if (!class_exists( 'shopFunctionsF' )) require(JPATH_SITE.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'shopfunctionsf.php');
if (!class_exists( 'calculationHelper' )) require(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'cart.php');
if (!class_exists( 'VirtueMartModelProduct' )){
   JLoader::import( 'product', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models' );
}

class mod_virtuemart_product {

	function addtocart($product) {
            if (!VmConfig::get('use_as_catalog',0)) { ?>
                <div class="addtocart-area">

		<form method="post" class="product" action="index.php">
                  
             <div class="addtocart-bar">

			<?php
                        // Display the quantity box
                        ?>
			<!-- <label for="quantity<?php echo $product->virtuemart_product_id;?>" class="quantity_box"><?php echo JText::_('COM_VIRTUEMART_CART_QUANTITY'); ?>: </label> -->
			<span class="quantity-box">
			<input type="text" class="quantity-input" name="quantity[]" value="1" />
			</span>
			<span class="quantity-controls">
			<input type="button" class="quantity-controls quantity-plus" />
			<input type="button" class="quantity-controls quantity-minus" />
			</span>


			<?php // Add the button
				$button_lbl = JText::_('TM_VIRTUEMART_CART_ADD_TO');
				$button_cls = 'addtocart-button'; //$button_cls = 'addtocart_button';
				if ($product->product_in_stock < 1) {
					$button_lbl = JText::_('COM_VIRTUEMART_CART_NOTIFY');
					$button_cls = 'addtocart-button Notify';
				} ?>
				<?php // Display the add to cart button ?>
				<div class="clear"></div>
				<span class="addtocart-button">
					<input type="submit" name="<?php echo $button_name ?>"  class="<?php echo $button_cls ?>" value="<?php echo $button_lbl ?>" title="<?php echo $button_lbl ?>" />
				</span>

                        <div class="clear"></div>
                    </div>

                    <input type="hidden" class="pname" value="<?php echo $product->product_name ?>"/>
                    <input type="hidden" name="option" value="com_virtuemart" />
                    <input type="hidden" name="view" value="cart" />
                    <noscript><input type="hidden" name="task" value="add" /></noscript>
                    <input type="hidden" name="virtuemart_product_id[]" value="<?php echo $product->virtuemart_product_id ?>" />
                    <input type="hidden" name="virtuemart_category_id[]" value="<?php echo $product->virtuemart_category_id ?>" />
                </form>
		<div class="clear"></div>
            </div>
        <?php }
     }
}
if ($display_style =="list") {
$js="
			jQuery(function () {							   	
			jQuery('#slider').anythingSlider({
			easing: '$effect',        // Anything other than 'linear' or 'swing' requires the easing plugin
			autoPlay: $play,                 // This turns off the entire FUNCTIONALY, not just if it starts running or not.
			delay: $pauseTime,                    // How long between slide transitions in AutoPlay mode
			buildStartStop : false,            // If autoPlay is on, this can force it to start stopped
			animationTime: $animSpeed,             // How long the slide transition takes
			buildArrows: $arrows,
			buildNavigation: $controlNav,          // If true, builds and list of anchor links to link to each slide
			pauseOnHover: $pauseOnHover,             // If true, and autoPlay is enabled, the show will pause on hover
			});
			});
        ";
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($js);	
		
} ?>
