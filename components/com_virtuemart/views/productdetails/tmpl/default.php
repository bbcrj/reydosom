<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Eugen Stranz
 * @author RolandD,
 * @todo handle child products
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default.php 5444 2012-02-15 15:31:35Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// addon for joomla modal Box
JHTML::_('behavior.modal');
// JHTML::_('behavior.tooltip');
$url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&task=askquestion&virtuemart_product_id=' . $this->product->virtuemart_product_id . '&virtuemart_category_id=' . $this->product->virtuemart_category_id . '&tmpl=component');
$document = &JFactory::getDocument();
$document->addScriptDeclaration("
	jQuery(document).ready(function($) {
		$('a.ask-a-question').click( function(){
			$.facebox({
				iframe: '" . $url . "',
				rev: 'iframe|590|800'
			});
			return false ;
		});
	/*	$('.additional-images a').mouseover(function() {
			var himg = this.href ;
			var extension=himg.substring(himg.lastIndexOf('.')+1);
			if (extension =='png' || extension =='jpg' || extension =='gif') {
				$('.main-image img').attr('src',himg );
			}
			console.log(extension)
		});*/
	});
");
/* Let's see if we found the product */
if (empty($this->product)) {
    echo JText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND');
    echo '<br /><br />  ' . $this->continue_link_html;
    return;
}
?>

<div class="productdetails-view">
	<?php // PDF - Print - Email Icon
		if (VmConfig::get('show_emailfriend') || VmConfig::get('show_printicon') || VmConfig::get('pdf_button_enable')) { ?>
		 <div class="icons">
			<?php
			//$link = (JVM_VERSION===1) ? 'index2.php' : 'index.php';
			$link = 'index.php?tmpl=component&option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->virtuemart_product_id;
			$MailLink = 'index.php?option=com_virtuemart&view=productdetails&task=recommend&virtuemart_product_id=' . $this->product->virtuemart_product_id . '&virtuemart_category_id=' . $this->product->virtuemart_category_id . '&tmpl=component';
	
			if (VmConfig::get('pdf_icon', 1) == '1') {
			echo $this->linkIcon($link . '&format=pdf', 'COM_VIRTUEMART_PDF', 'pdf_button', 'pdf_button_enable', true);
			}
			echo $this->linkIcon($link . '&print=1', 'COM_VIRTUEMART_PRINT', 'printButton', 'show_printicon', true);
			echo $this->linkIcon($MailLink, 'COM_VIRTUEMART_EMAIL', 'emailButton', 'show_emailfriend');
			?>
			<div class="clear"></div>
			</div>
		<?php }  // PDF - Print - Email Icon END  ?> 
<div class="wrapper">
	<div class="fright">
		 <?php // Product Title  ?>
			<h1 class="title"><?php echo $this->product->product_name ?></h1>
		<?php // Product Title END  ?>
		
		<?php 
		if ($this->product->product_s_desc) { ?>
			<div class="s_desc"><?php echo $this->product->product_s_desc; ?></div>
		<?php } ?>
		<div class="product-box">
			<div class="rating">
			<?php
			if ($this->showRating) {
				$maxrating = VmConfig::get('vm_maximum_rating_scale',5);
	
					if (empty($this->rating)) { ?>
					<span class="vote">
						<span title=" <?php echo (JText::_("COM_VIRTUEMART_RATING_TITLE") . $this->rating->rating . '/' . $maxrating) ?>" class="vmicon ratingbox" style="display:inline-block;">
							<span class="stars-orange" style="width:<?php echo $ratingwidth;?>%">
							</span>
						</span>
					
						<span class="rating-title"><?php echo JText::_('COM_VIRTUEMART_RATING').' '.JText::_('COM_VIRTUEMART_UNRATED') ?></span>
						</span>	
					<?php } else {
						$ratingwidth = ( $this->rating->rating * 100 ) / $maxrating;//I don't use round as percetntage with works perfect, as for me
						?>
						<span class="vote">
							<span title="" class="vmicon ratingbox" style="display:inline-block;">
								<span class="stars-orange" style="width:<?php echo $ratingwidth;?>%">
								</span>
							</span>
							<span class="rating-title"><?php echo JText::_('COM_VIRTUEMART_RATING').' '.round($this->rating->rating, 2) . '/'. $maxrating; ?></span>
						</span>
				<?php	} ?>
				<?php 	} ?>
			</div>	
			<div class="spacer-buy-area">
			<div class="addtocart-area">

    <form method="post" class="product js-recalculate" action="index.php" >
	<?php // Product custom_fields
	if (!empty($this->product->customfieldsCart)) { ?>
    	<div class="product-fields">
		<?php foreach ($this->product->customfieldsCart as $field) { ?>
		    <div class="product-field product-field-type-<?php echo $field->field_type ?>">
			<span class="product-fields-title" ><b><?php echo JText::_($field->custom_title) ?></b></span>
			<?php if ($field->custom_tip)
			    echo JHTML::tooltip($field->custom_tip, JText::_($field->custom_title), 'tooltip.png'); ?>
			<br/>
			<span class="product-field-display"><?php echo $field->display ?></span>
			<span class="product-field-desc"><?php echo $field->custom_field_desc ?></span>
		    </div><br />
		    <?php
		}
		?>
    	</div>
	<?php } ?>
	
	<?php
	/* Product custom Childs
	 * to display a simple link use $field->virtuemart_product_id as link to child product_id
	 * custom_value is relation value to child
	 */

	if (!empty($this->product->customsChilds)) {
	    ?>
    	<div class="product-fields">
    <?php foreach ($this->product->customsChilds as $field) { ?>
		    <div class="product-field product-field-type-<?php echo $field->field->field_type ?>">
			<span class="product-fields-title" ><b><?php echo JText::_($field->field->custom_title) ?></b></span>
			<span class="product-field-desc"><?php echo JText::_($field->field->custom_value) ?></span>
			<span class="product-field-display"><?php echo $field->display ?></span>

		    </div><br />
		<?php } ?>
    	</div>
<?php } ?>


					
				<div class="addtocart-bar">
			
			<?php // Display the quantity box  ?>
									<!-- <label for="quantity<?php echo $this->product->virtuemart_product_id; ?>" class="quantity_box"><?php echo JText::_('COM_VIRTUEMART_CART_QUANTITY'); ?>: </label> -->
			<div class="wrapper">
				<div class="controls">					
					<span class="quantity-box">
					<input type="text" class="quantity-input js-recalculate" name="quantity[]" value="<?php if (isset($this->product->min_order_level) && (int) $this->product->min_order_level > 0) {
				echo $this->product->min_order_level;
			} else {
				echo '1';
			} ?>" />
					</span>
					<span class="quantity-controls js-recalculate">
					<input type="button" class="quantity-controls quantity-plus" />
					<input type="button" class="quantity-controls quantity-minus" />
					</span>
					<?php // Display the quantity box END ?>
					</div>
					<div class="price">
						<?php
							// Product Price
							if ($this->show_prices and (empty($this->product->images[0]) or $this->product->images[0]->file_is_downloadable == 0)) {
								echo $this->loadTemplate('showprices');
							}
						?>

					</div>
			</div>		
					<?php
					// Add the button
					$button_lbl = JText::_('COM_VIRTUEMART_CART_ADD_TO');
					$button_cls = 'addtocart-button cart-click'; //$button_cls = 'addtocart_button';
					$button_name = 'addtocart'; //$button_cls = 'addtocart_button';
					// Display the add to cart button
					$stockhandle = VmConfig::get('stockhandle', 'none');
					if (($stockhandle == 'disableit' or $stockhandle == 'disableadd') and ($this->product->product_in_stock - $this->product->product_ordered) < 1) {
					$button_lbl = JText::_('COM_VIRTUEMART_CART_NOTIFY');
					$button_cls = 'addtocart-button';
					$button_name = 'notifycustomer';
					}
					//vmdebug('$stockhandle '.$stockhandle.' and stock '.$this->product->product_in_stock.' ordered '.$this->product->product_ordered);
					?>
					<div class="wrapper">
					<div class="fright">
					
					<?php
					// Ask a question about this product
						if (VmConfig::get('ask_question', 1) == '1') { ?>
						   <div class="ask-a-question">
								<a class="ask-a-question" href="<?php echo $url ?>" ><?php echo JText::_('COM_VIRTUEMART_PRODUCT_ENQUIRY_LBL') ?></a>
								<!--<a class="ask-a-question modal" rel="{handler: 'iframe', size: {x: 800, y: 750}}" href="<?php echo $url ?>"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_ENQUIRY_LBL') ?></a>-->
								</div>
						<?php } ?>
						<?php
					// Manufacturer of the Product
					if (!empty($this->product->virtuemart_manufacturer_id)) {
					  echo  $this->loadTemplate('manufacturer');
					}
					?>
					</div>	
                    <?php if (!VmConfig::get('use_as_catalog')) {?>
					<span class="addtocart-button">
					<input type="submit" name="<?php echo $button_name ?>"  class="<?php echo $button_cls ?>" value="<?php echo $button_lbl ?>" title="<?php echo $button_lbl ?>" />
					</span>
                    <?php } ?>
					</div>
			
					<div class="clear"></div>
				</div>

	<?php // Display the add to cart button END  ?>
	<input type="hidden" class="pname" value="<?php echo $this->product->product_name ?>" />
	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="view" value="cart" />
	<noscript><input type="hidden" name="task" value="add" /></noscript>
	<input type="hidden" name="virtuemart_product_id[]" value="<?php echo $this->product->virtuemart_product_id ?>" />
<?php /** @todo Handle the manufacturer view */ ?>
	<input type="hidden" name="virtuemart_manufacturer_id" value="<?php echo $this->product->virtuemart_manufacturer_id ?>" />
	<input type="hidden" name="virtuemart_category_id[]" value="<?php echo $this->product->virtuemart_category_id ?>" />
    </form>

    <div class="clear"></div>
</div>
					
			</div>
		</div>
		
		 <div class="share"><!-- AddThis Button BEGIN -->
		<div class="addthis_toolbox addthis_default_style addthis_32x32_style">
		<a class="addthis_button_preferred_1"></a>
		<a class="addthis_button_preferred_2"></a>
		<a class="addthis_button_preferred_3"></a>
		<a class="addthis_button_preferred_4"></a>
		<a class="addthis_button_compact"></a>
		<a class="addthis_counter addthis_bubble_style"></a>
		</div>
		<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4f3e457550cc1084"></script>
		<!-- AddThis Button END -->
       </div>
		
		
		
		
	</div> 
	<div class="fleft">
		<?php
			echo $this->loadTemplate('images');
		?>
		 <div class="share1">
			<!-- AddThis Button BEGIN -->
			<div class="addthis_toolbox addthis_default_style ">
			<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
			<a class="addthis_button_tweet"></a>
			<a class="addthis_button_google_plusone" g:plusone:size="medium"></a>
			</div>
			<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js"></script>
			<!-- AddThis Button END -->
		</div>
			<?php
					// Availability Image
					/* TO DO add width and height to the image */
					if (!empty($this->product->product_availability)) {
					   $stockhandle = VmConfig::get('stockhandle', 'none');
			if ($stockhandle == 'risetime' and ($this->product->product_in_stock - $this->product->product_ordered) < 1) {
				?>	<div class="availability">
				<?php echo JHTML::image(JURI::root() . VmConfig::get('assets_general_path') . 'images/availability/' . VmConfig::get('rised_availability', '7d.gif'), VmConfig::get('rised_availability', '7d.gif'), array('class' => 'availability')); ?>
				</div>
			<?php } else {
				?>
				<div class="availability">
				<?php echo JHTML::image(JURI::root() . VmConfig::get('assets_general_path') . 'images/availability/' . $this->product->product_availability, $this->product->product_availability, array('class' => 'availability')); ?>
				</div>
			<?php
			}
					}
					?>
	</div>
  </div> 
  		  
    <?php
    if (!empty($this->product->customfieldsSorted['normal'])) {
	$this->position='normal';
    } // Product custom_fields END
	
    // Product Packaging
    $product_packaging = '';
    if ($this->product->packaging || $this->product->box) { ?>
	  <div class="product-packaging">

	    <?php
	    if ($this->product->packaging) {
		$product_packaging .= JText::_('COM_VIRTUEMART_PRODUCT_PACKAGING1') . $this->product->packaging;
		if ($this->product->box)
		    $product_packaging .= '<br />';
	    }
	    if ($this->product->box)
		$product_packaging .= JText::_('COM_VIRTUEMART_PRODUCT_PACKAGING2') . $this->product->box;
	    echo str_replace("{unit}", $this->product->product_unit ? $this->product->product_unit : JText::_('COM_VIRTUEMART_PRODUCT_FORM_UNIT_DEFAULT'), $product_packaging);
	    ?>
        </div>
   <?php } // Product Packaging END
    ?>

    <?php
    // Product Files
    // foreach ($this->product->images as $fkey => $file) {
    // Todo add downloadable files again
    // if( $file->filesize > 0.5) $filesize_display = ' ('. number_format($file->filesize, 2,',','.')." MB)";
    // else $filesize_display = ' ('. number_format($file->filesize*1024, 2,',','.')." KB)";

    /* Show pdf in a new Window, other file types will be offered as download */
    // $target = stristr($file->file_mimetype, "pdf") ? "_blank" : "_self";
    // $link = JRoute::_('index.php?view=productdetails&task=getfile&virtuemart_media_id='.$file->virtuemart_media_id.'&virtuemart_product_id='.$this->product->virtuemart_product_id);
    // echo JHTMl::_('link', $link, $file->file_title.$filesize_display, array('target' => $target));
    // }
   
    
    ?>
        
<div class="Fly-tabs">
		 <?php
			jimport('joomla.html.pane');
			$myTabs = & JPane::getInstance('tabs', array('startOffset'=>0));
			$output = '';
			$output .= $myTabs->startPane( 'pane' );
				if ($this->product->product_desc) {
					$output .= $myTabs->startPanel( JText::_('COM_VIRTUEMART_PRODUCT_DESC_TITLE') , 'tab1' );
					$output .= '<div class="desc">' .$this->product->product_desc.'</div>';
					$output .= $myTabs->endPanel();
				}
				
				if ($this->product->customfieldsSorted['normal']) {
					$output .= $myTabs->startPanel( JText::_('TM_VIRTUEMART_PRODUCT_SPECIFICATIONS_TITLE') , 'tab2' );
					$output .=  '<div>' .$this->loadTemplate('customfields').'</div>';
					$output .= $myTabs->endPanel();
				}
				
				if ($this->loadTemplate('reviews')) {
					$output .= $myTabs->startPanel(  JText::_('COM_VIRTUEMART_REVIEWS') , 'tab3' ); 
					$output  .= '<div>'.$this->loadTemplate('reviews').'</div>'; 
					$output .= $myTabs->endPanel();
				}
				if (!empty($this->product->customfieldsRelatedProducts)) {
					$output .= $myTabs->startPanel(  JText::_('COM_VIRTUEMART_RELATED_PRODUCTS') , 'tab4' ); 
					$output  .= '<div style="overflow:hidden;">'. $this->loadTemplate('relatedproducts') .'</div>';
					$output .= $myTabs->endPanel();
				} // Product customfieldsRelatedProducts END
				if (!empty($this->product->customfieldsRelatedCategories)) {
					$output .= $myTabs->startPanel(  JText::_('COM_VIRTUEMART_RELATED_CATEGORIES') , 'tab5' ); 
					$output  .= '<div style="overflow:hidden;">'. $this->loadTemplate('relatedcategories') .'</div>';
					$output .= $myTabs->endPanel();
				} // Product customfieldsRelatedCategories END
				 // Show child categories
				if ($this->loadTemplate('showcategory')) {
					$output .= $myTabs->startPanel( '<span>subcategory</span>', 'tab6' ); 
					$output  .= '<div>'. $this->loadTemplate('showcategory') .'</div>';
					$output .= $myTabs->endPanel();
				 }
					$output .= $myTabs->startPanel( 'Video', 'tab7' ); 
					$output  .= '<div class="desc2">' .$this->product->product_desc.'</div>';
					$output .= $myTabs->endPanel();
			$output .= $myTabs->endPane();
			echo $output;
		?>
	</div>
 <?php
    // Product Navigation
    if (VmConfig::get('product_navigation', 1)) { ?>
	 <div class="product-neighbours">
	    <?php
		$next = JText::_('TM_VIRTUEMART_NEXTPRODUCT');
		$prev = JText::_('TM_VIRTUEMART_PREVPRODUCT');

	    if (!empty($this->product->neighbours ['previous'][0])) {
		$prev_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->neighbours ['previous'][0] ['virtuemart_product_id'] . '&virtuemart_category_id=' . $this->product->virtuemart_category_id);
		echo JHTML::_('link', $prev_link, $prev, 'class="previous-page"');
	    }
	    if (!empty($this->product->neighbours ['next'][0])) {
		$next_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->neighbours ['next'][0] ['virtuemart_product_id'] . '&virtuemart_category_id=' . $this->product->virtuemart_category_id);
		echo JHTML::_('link', $next_link, $next , 'class="next-page"');
	    }
	    ?>
    	<div class="clear"></div>
        </div>
    <?php } // Product Navigation END
    ?>
 </div>
