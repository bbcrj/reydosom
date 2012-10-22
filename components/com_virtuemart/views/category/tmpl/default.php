<?php
/**
*
* Show the products in a category
*
* @package	VirtueMart
* @subpackage
* @author RolandD
* @author Max Milbers
* @todo add pagination
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default.php 5007 2011-12-10 00:05:41Z electrocity $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
JHTML::_( 'behavior.modal' );
/* javascript for list Slide
  Only here for the order list
  can be changed by the template maker
*/
$js = "
jQuery(document).ready(function () {
	jQuery('.orderlistcontainer').hover(
		function() { jQuery(this).find('.orderlist').stop().show()},
		function() { jQuery(this).find('.orderlist').stop().hide()}
	)
});
";

$document = JFactory::getDocument();
$document->addScriptDeclaration($js);
?>
<div class="marg">
<?php 
if ($this->category->category_name) 
{ ?>
	<h1 class="browse-view"><span><span><?php echo $this->category->category_name; ?></span></span></h1>
	
<?php } ?>
<?php
					/* Show child categories */
					
					if ( VmConfig::get('showCategory',1) && $this->search ==null && $this->category->category_name) {
						if ($this->category->haschildren) {
							// Category and Columns Counter
							$iCol = 1;
							$iCategory = 1;
					
							// Calculating Categories Per Row
							$categories_per_row = 4;
							$category_cellwidth = ' width'.floor ( 100 / $categories_per_row );
					
							// Separator
							$verticalseparator = " vertical-separator";
							?>
							<div class="category-view">
							<?php // Start the Output
								if ($this->category->children ) {
									foreach ( $this->category->children as $category ) {
								
										// Show the horizontal seperator
										if ($iCol == 1 && $iCategory > $categories_per_row) { ?>
										<div class="horizontal-separator"></div>
										<?php }
								
										// this is an indicator wether a row needs to be opened or not
										if ($iCol == 1) { ?>
										<div class="row">
										<?php }
								
										// Show the vertical seperator
										if ($iCategory == $categories_per_row or $iCategory % $categories_per_row == 0) {
											$show_vertical_separator = ' ';
										} else {
											$show_vertical_separator = $verticalseparator;
										}
								
										// Category Link
										$caturl = JRoute::_ ( 'index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $category->virtuemart_category_id );
								
											// Show Category ?>
											<div class="category floatleft<?php echo $show_vertical_separator ?>">
											<div class="spacer">
												<h2>
													<a href="<?php echo $caturl ?>" title="<?php echo $category->category_name ?>">
													<div class="category-border">
													<?php // if ($category->ids) {
														echo $category->images[0]->displayMediaThumb("",false);
													//} ?>
													</div>
													<div class="category-title"><?php echo $category->category_name ?></div>
													</a>
												</h2>
											</div>
										</div>
										<?php
										$iCategory ++;
								
										// Do we need to close the current row now?
										if ($iCol == $categories_per_row) { ?>
										<div class="clear"></div>
										</div>
											<?php
											$iCol = 1;
										} else {
											$iCol ++;
										}
									}
								}
								?>
								
					<?php }
					}
					
// Show child categories
if (!empty($this->products)) {
						if (!empty($this->keyword)) {
							?>
							<h3><?php echo $this->keyword; ?></h3>
						<?php }	?>
					
						<?php if ($this->search == null ) { ?>
							<?php if ($this->category->category_description ): ?>
							<div class="category_description">
								<?php echo $this->category->category_description ; ?>
							</div>
							<?php endif; ?>
						<?php } ?>
					
<?php } ?>
					
 		<?php if ($this->search !==null ) { ?>
		
			<?php if (empty($this->products)) { ?>
				<h2><?php echo JText::_('COM_VIRTUEMART_NO_RESULT');?></h2>
			<?php } ?>
			
		    <form action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=category&limitstart=0&virtuemart_category_id='.$this->category->virtuemart_category_id ); ?>" method="get">

		    <!--BEGIN Search Box --><div class="virtuemart_search">
		    <?php echo $this->searchcustomvalues ?>
		    <input name="keyword" class="inputbox" type="text" size="20" value="<?php echo $this->keyword ?>" />
		    <input type="submit" value="<?php echo JText::_('COM_VIRTUEMART_SEARCH') ?>" class="button" onclick="this.form.keyword.focus();"/>
		    </div>
				    <input type="hidden" name="search" value="true" />
				    <input type="hidden" name="view" value="category" />

		</form>
		<!-- End Search Box -->
		<?php } ?>
		
		
<?php if (!empty($this->products)) { ?>

			<div class="orderby-displaynumber">
				<div class="width90">
					<?php echo $this->orderByList['orderby']; ?>
					<?php echo $this->orderByList['manufacturer']; ?>
					<div class="Results">
						<div class="floatleft display-number"><span><?php echo $this->vmPagination->getResultsCounter();?></span><?php echo $this->vmPagination->getLimitBox(); ?></div>
					</div>
				</div>
			<div class="clear"></div>
			</div>
			 <!-- end of orderby-displaynumber -->
	<div id="tabs" class="tabs-position">
			<ul class="tabs">
				<li class="first"><a href="#tabs-1">&nbsp;</a></li>
				<li class="second"><a href="#tabs-2">&nbsp;</a></li>
				<li class="three"><a href="#tabs-3">&nbsp;</a></li>
			</ul>
	
	<div id="bottom-pagination" class="pag-bot"><?php echo $this->vmPagination->getPagesLinks(); ?></div>	
	
	<div class="tab_container">
		<div id="tabs-1" class="tab_content">
			<div id="product_list">
					<?php // Category and Columns Counter
					$counter = 0;
					$iBrowseCol = 1;
					$iBrowseProduct = 1;
					
					// Calculating Products Per Row
					$BrowseProducts_per_row = 1;
					$Browsecellwidth = ' width'.floor ( 100 / $BrowseProducts_per_row );
					
					// Separator
					$verticalseparator = " vertical-separator";
					?>
					<div class="browse-view">
					
					<?php // Start the Output
					foreach ( $this->products as $product ) {
					
						// Show the horizontal seperator
						if ($iBrowseCol == 1 && $iBrowseProduct > $BrowseProducts_per_row) { ?>
						<div class="horizontal-separator2"></div>
						<?php }
					
						// this is an indicator wether a row needs to be opened or not
						if ($counter%2) $cls = "even"; else $cls = "odd";
						if ($iBrowseCol == 1) { ?>
						
						<div class="row <?php echo $cls ?>">
						<?php }
					
						// Show the vertical seperator
						if ($iBrowseProduct == $BrowseProducts_per_row or $iBrowseProduct % $BrowseProducts_per_row == 0) {
							$show_vertical_separator = ' ';
						} else {
							$show_vertical_separator = $verticalseparator;
						}
					
							// Show Products ?>
							<div class="product1 floatleft<?php echo $Browsecellwidth . $show_vertical_separator ?>">
								<div class="spacer">
									<div class="floatright col-2">
										<div class="product-price marginbottom12" id="productPrice<?php echo $product->virtuemart_product_id ?>">
											<?php
											if ($this->show_prices == '1') {
												echo $this->currency->createPriceDiv('salesPrice','',$product->prices);
												//print_r($product->prices);
												if ($product->prices[discountAmount] > 0) {
													echo $this->currency->createPriceDiv('priceWithoutTax','',$product->prices);
												}
												//echo $this->currency->createPriceDiv('discountAmount','COM_VIRTUEMART_PRODUCT_DISCOUNT_AMOUNT',$product->prices);
												
												//echo $this->currency->createPriceDiv('salesPrice','COM_VIRTUEMART_PRODUCT_SALESPRICE',$product->prices);
												//echo $this->currency->createPriceDiv('priceWithoutTax','COM_VIRTUEMART_PRODUCT_SALESPRICE_WITHOUT_TAX',$product->prices);
												
												//echo $this->currency->createPriceDiv('variantModification','COM_VIRTUEMART_PRODUCT_VARIANT_MOD',$product->prices);
												//echo $this->currency->createPriceDiv('basePriceWithTax','COM_VIRTUEMART_PRODUCT_BASEPRICE_WITHTAX',$product->prices);
												//echo $this->currency->createPriceDiv('discountedPriceWithoutTax','COM_VIRTUEMART_PRODUCT_DISCOUNTED_PRICE',$product->prices);
												//echo $this->currency->createPriceDiv('salesPriceWithDiscount','COM_VIRTUEMART_PRODUCT_SALESPRICE_WITH_DISCOUNT',$product->prices);
												//echo $this->currency->createPriceDiv('taxAmount','COM_VIRTUEMART_PRODUCT_TAX_AMOUNT',$product->prices);
											} ?>
											
										</div>
									<?php if (!VmConfig::get('use_as_catalog')) {?>
									<div class="addtocart-area">
										<form method="post" class="product" action="index.php" id="addtocartproduct<?php echo $product->virtuemart_product_id ?>">
										<div class="addtocart-bar">
										<label for="quantity<?php echo $this->product->virtuemart_product_id;?>" class="quantity_box"><?php echo JText::_('COM_VIRTUEMART_CART_QUANTITY'); ?>: </label>
										<span class="quantity-box">
											<input type="text" class="quantity-input" name="quantity[]" value="1" />
											</span>
											<span class="quantity-controls">
											<input type="button" class="quantity-controls quantity-plus" />
											<input type="button" class="quantity-controls quantity-minus" />
											</span>
											<?php // Add the button
											$button_lbl = JText::_('COM_VIRTUEMART_CART_ADD_TO');
											$button_cls = 'addtocart-button cart-click'; //$button_cls = 'addtocart_button';
											if ($product->product_in_stock < 1) {
												$button_lbl = JText::_('COM_VIRTUEMART_CART_NOTIFY');
												$button_cls = 'addtocart-button Notify';
											} ?>
											<?php // Display the add to cart button ?>
											<div class="clear"></div>
											<span class="addtocart-button">
												<input type="submit" name="<?php echo $button_name ?>"  class="<?php echo $button_cls ?>" value="<?php echo $button_lbl ?>" title="<?php echo $button_lbl ?>" />
											</span>
										</div>
										<input type="hidden" class="pname" value="<?php echo $product->product_name ?>"/>
										<input type="hidden" name="option" value="com_virtuemart" />
										<input type="hidden" name="view" value="cart" />
										<noscript><input type="hidden" name="task" value="add" /></noscript>
										<input type="hidden" name="virtuemart_product_id[]" value="<?php echo $product->virtuemart_product_id ?>" />
										<input type="hidden" name="virtuemart_category_id[]" value="<?php echo $product->virtuemart_category_id ?>" />
									</form>
									</div>
								<?php }?>
									</div>
									<div class="floatleft col-1">
										<div class="browseProductImageContainer"><?php /** @todo make image popup */
												echo $product->images[0]->displayMediaThumb('class="browseProductImage" border="0" title="'.$product->product_name.'" ',true,'class="modal"');
											?>
										</div>
										<?php if (!VmConfig::get('use_as_catalog') and !(VmConfig::get('stockhandle','none')=='none') && (VmConfig::get ( 'display_stock', 1 )) ){?>
											<div class="padding-stock">
												<span class="stock-level"><?php echo JText::_('COM_VIRTUEMART_STOCK_LEVEL_DISPLAY_TITLE_TIP') ?></span>
												<span class="vmicon vm2-<?php echo $product->stock->stock_level ?>" title="<?php echo $product->stock->stock_tip ?>"></span>
											</div>
										<?php }?>
											
									</div>
					
									<div class="floatleft col-3">
										<div class="title-indent">
											<h2><?php echo JHTML::link($product->link, $product->product_name); ?></h2>
												<?php // Product Short Description
												if(!empty($product->product_s_desc)) { ?>
												<?php echo shopFunctionsF::limitStringByWord($product->product_s_desc, 100, '...') ?>
												<?php } ?>
												
											<div class="rating">
												<!-- The "Average Customer Rating" Part -->
												<?php
												if ($this->showRating || !empty($product->rating)) {
													$maxrating = VmConfig::get('vm_maximum_rating_scale',5);
													$ratingwidth = ( $product->rating->rating * 100 ) / $maxrating;//I don't use round as percetntage with works perfect, as for me
												?>
													<span class="vote">
														<span title="" class="vmicon ratingbox" style="display:inline-block;">
															<span class="stars-orange" style="width:<?php echo $ratingwidth;?>%">
															</span>
														</span>
													</span>
												<?php
												}
												?>
											</div>	
											<div class="detal">
											<?php // Product Details Button
											echo JHTML::link($product->link, JText::_('COM_VIRTUEMART_PRODUCT_DETAILS'), array('title' => $product->product_name,'class' => 'product-details'));
											?>
											</div>
										</div>
									</div>
								<div class="clear"></div>
								</div>
							</div>
						<?php
						$iBrowseProduct ++;
						$counter++;	
					
						// Do we need to close the current row now?
						if ($iBrowseCol == $BrowseProducts_per_row) { ?>
						<div class="clear"></div>
						</div>
							<?php
							$iBrowseCol = 1;
						} else {
							$iBrowseCol ++;
						}
					}
					?>
				</div>
			</div>
	</div>
	
	<div id="tabs-2" class="tab_content">
			<div id="product_list2">
					<?php // Category and Columns Counter
					$counterr = 0;
					$counter = 0;
					$iBrowseCol = 1;
					$iBrowseProduct = 1;
					
					// Calculating Products Per Row
					$BrowseProducts_per_row = 2;
					$Browsecellwidth = ' width'.floor ( 100 / $BrowseProducts_per_row );
					
					// Separator
					$verticalseparator = " vertical-separator";
					?>
					<div class="browse-view">
					
					<?php // Start the Output
					foreach ( $this->products as $product ) {
					
						// Show the horizontal seperator
						if ($iBrowseCol == 1 && $iBrowseProduct > $BrowseProducts_per_row) { ?>
						<div class="horizontal-separator2"></div>
						<?php }
					
						// this is an indicator wether a row needs to be opened or not
						if ($counterr%2) $cls = "first"; else $cls = "second";
						if ($iBrowseCol == 1) { ?>
						<div class="row <?php echo $cls ?>">
						<?php }
					
						// Show the vertical seperator
						if ($counter%2) $cls = "even"; else $cls = "odd";
						if ($iBrowseProduct == $BrowseProducts_per_row or $iBrowseProduct % $BrowseProducts_per_row == 0) {
							$show_vertical_separator = $verticalseparator;
						} else {
							$show_vertical_separator = $verticalseparator;
						}
					
							// Show Products ?>
							<div class="product1 floatleft <?php echo $cls . $show_vertical_separator ?>">
								<div class="spacer">
									<div class="floatleft col-1">
										<div class="browseProductImageContainer"><?php /** @todo make image popup */
												echo $product->images[0]->displayMediaThumb('class="browseProductImage" border="0" title="'.$product->product_name.'" ',true,'class="modal"');
											?>
										</div>
										<?php if (!VmConfig::get('use_as_catalog') and !(VmConfig::get('stockhandle','none')=='none') && (VmConfig::get ( 'display_stock', 1 )) ){?>
											<div class="padding-stock">
												<span class="stock-level"><?php echo JText::_('COM_VIRTUEMART_STOCK_LEVEL_DISPLAY_TITLE_TIP') ?></span>
												<span class="vmicon vm2-<?php echo $product->stock->stock_level ?>" title="<?php echo $product->stock->stock_tip ?>"></span>
											</div>
										<?php }?>
									</div>
									<div class="floatright col-2">
										<div class="title-indent">
											<h2><?php echo JHTML::link($product->link, $product->product_name); ?></h2>
											<div class="rating">
												<!-- The "Average Customer Rating" Part -->
												<?php
												if ($this->showRating || !empty($product->rating)) {
													$maxrating = VmConfig::get('vm_maximum_rating_scale',5);
													$ratingwidth = ( $product->rating->rating * 100 ) / $maxrating;//I don't use round as percetntage with works perfect, as for me
												?>
													<span class="vote">
														<span title="" class="vmicon ratingbox" style="display:inline-block;">
															<span class="stars-orange" style="width:<?php echo $ratingwidth;?>%">
															</span>
														</span>
													</span>
												<?php
												}
												?>
											</div>
												<div style="display:none;">
												<?php // Product Short Description
												if(!empty($product->product_s_desc)) { ?>
												<?php echo shopFunctionsF::limitStringByWord($product->product_s_desc, 100, '...') ?>
												<?php } ?>
												</div>
												<?php if (!VmConfig::get('use_as_catalog') and !(VmConfig::get('stockhandle','none')=='none') && (VmConfig::get ( 'display_stock', 1 )) ){?>
												<div class="padding-stock" style="display:none;">
													<span class="vmicon vm2-<?php echo $product->stock->stock_level ?>" title="<?php echo $product->stock->stock_tip ?>"></span>
													<span class="stock-level"><?php echo JText::_('COM_VIRTUEMART_STOCK_LEVEL_DISPLAY_TITLE_TIP') ?></span>
												</div>
												<?php }?>
						
											<div class="detal" style="display:none;">
											<?php // Product Details Button
											echo JHTML::link($product->link, JText::_('COM_VIRTUEMART_PRODUCT_DETAILS'), array('title' => $product->product_name,'class' => 'product-details'));
											?>
											</div>
										</div>
										<div class="product-price marginbottom12" id="productPrice<?php echo $product->virtuemart_product_id ?>">
											<?php
											if ($this->show_prices == '1') {
												if( $product->product_unit && VmConfig::get('vm_price_show_packaging_pricelabel')) {
													echo "<strong>". JText::_('COM_VIRTUEMART_CART_PRICE_PER_UNIT').' ('.$product->product_unit."):</strong>";
												}
												echo $this->currency->createPriceDiv('salesPrice','',$product->prices);
												//print_r($product->prices);
												if ($product->prices[discountAmount] > 0) {
													echo $this->currency->createPriceDiv('priceWithoutTax','',$product->prices);
												}
												//echo $this->currency->createPriceDiv('discountAmount','COM_VIRTUEMART_PRODUCT_DISCOUNT_AMOUNT',$product->prices);
												
												//echo $this->currency->createPriceDiv('salesPrice','COM_VIRTUEMART_PRODUCT_SALESPRICE',$product->prices);
												//echo $this->currency->createPriceDiv('priceWithoutTax','COM_VIRTUEMART_PRODUCT_SALESPRICE_WITHOUT_TAX',$product->prices);
												
												//echo $this->currency->createPriceDiv('variantModification','COM_VIRTUEMART_PRODUCT_VARIANT_MOD',$product->prices);
												//echo $this->currency->createPriceDiv('basePriceWithTax','COM_VIRTUEMART_PRODUCT_BASEPRICE_WITHTAX',$product->prices);
												//echo $this->currency->createPriceDiv('discountedPriceWithoutTax','COM_VIRTUEMART_PRODUCT_DISCOUNTED_PRICE',$product->prices);
												//echo $this->currency->createPriceDiv('salesPriceWithDiscount','COM_VIRTUEMART_PRODUCT_SALESPRICE_WITH_DISCOUNT',$product->prices);
												//echo $this->currency->createPriceDiv('taxAmount','COM_VIRTUEMART_PRODUCT_TAX_AMOUNT',$product->prices);
											} ?>
										</div>
									<?php if (!VmConfig::get('use_as_catalog')) {?>
									<div class="addtocart-area">
										<form method="post" class="product" action="index.php" id="addtocartproduct<?php echo $product->virtuemart_product_id ?>">
										<div class="addtocart-bar">
										<label for="quantity<?php echo $this->product->virtuemart_product_id;?>" class="quantity_box"><?php echo JText::_('COM_VIRTUEMART_CART_QUANTITY'); ?>: </label>
										<span class="quantity-box">
											<input type="text" class="quantity-input" name="quantity[]" value="1" />
											</span>
											<span class="quantity-controls">
											<input type="button" class="quantity-controls quantity-plus" />
											<input type="button" class="quantity-controls quantity-minus" />
											</span>
											<?php // Add the button
											$button_lbl = JText::_('COM_VIRTUEMART_CART_ADD_TO');
											$button_cls = 'addtocart-button cart-click'; //$button_cls = 'addtocart_button';
											if ($product->product_in_stock < 1) {
												$button_lbl = JText::_('COM_VIRTUEMART_CART_NOTIFY');
												$button_cls = 'addtocart-button Notify';
											} ?>
											<?php // Display the add to cart button ?>
											<div class="clear"></div>
											<span class="addtocart-button">
												<input type="submit" name="<?php echo $button_name ?>"  class="<?php echo $button_cls ?>" value="<?php echo $button_lbl ?>" title="<?php echo $button_lbl ?>" />
											</span>
										</div>
										<input type="hidden" class="pname" value="<?php echo $product->product_name ?>"/>
										<input type="hidden" name="option" value="com_virtuemart" />
										<input type="hidden" name="view" value="cart" />
										<noscript><input type="hidden" name="task" value="add" /></noscript>
										<input type="hidden" name="virtuemart_product_id[]" value="<?php echo $product->virtuemart_product_id ?>" />
										<input type="hidden" name="virtuemart_category_id[]" value="<?php echo $product->virtuemart_category_id ?>" />
									</form>
									</div>
							<?php }?>
									</div>
								<div class="clear"></div>
								</div>
							</div>
						<?php
						$iBrowseProduct ++;
						$counterr++;
					
						// Do we need to close the current row now?
						if ($iBrowseCol == $BrowseProducts_per_row) { ?>
						<div class="clear"></div>
						</div>
							<?php
							$iBrowseCol = 1;
						} else {
							$iBrowseCol ++;
							$counter++;
						}
					}
					// Do we need a final closing row tag?
					if ($iBrowseCol != 1) { ?>
						<div class="clear"></div>
						</div>
					<?php
					}
					?>
				</div>
			</div>
	</div>
	
	<div id="tabs-3" class="tab_content">
			<div id="product_list3">
					<?php // Category and Columns Counter
					$counterr = 0;
					$counter = 0;
					$iBrowseCol = 1;
					$iBrowseProduct = 1;
					
					// Calculating Products Per Row
					$BrowseProducts_per_row = 3;
					$Browsecellwidth = ' width'.floor ( 100 / $BrowseProducts_per_row );
					
					// Separator
					$verticalseparator = " vertical-separator";
					?>
					<div class="browse-view">
					
					<?php // Start the Output
					foreach ( $this->products as $product ) {
					
						// Show the horizontal seperator
						if ($iBrowseCol == 1 && $iBrowseProduct > $BrowseProducts_per_row) { ?>
						<div class="horizontal-separator2"></div>
						<?php }
					
						// this is an indicator wether a row needs to be opened or not
						if ($counterr%2) $cls = "first"; else $cls = "second";
						if ($counter%2) $cls = "even"; else $cls = "odd";
						if ($iBrowseCol == 1) { ?>
						
						<div class="row <?php echo $cls ?>">
						<?php }
					
						// Show the vertical seperator
						if ($counter%2) $cls = "even"; else $cls = "odd";
						if ($iBrowseProduct == $BrowseProducts_per_row or $iBrowseProduct % $BrowseProducts_per_row == 0) {
							$show_vertical_separator = $verticalseparator;;
						} else {
							$show_vertical_separator = $verticalseparator;
						}
					
							// Show Products ?>
							<div class="product1 floatleft <?php echo $cls . $show_vertical_separator ?>">
								<div class="spacer">
									<div class="col-1">
										<div class="browseProductImageContainer"><?php /** @todo make image popup */
												echo $product->images[0]->displayMediaThumb('class="browseProductImage" border="0" title="'.$product->product_name.'" ',true,'class="modal"');
											?>
										</div>
											<div class="rating">
												<!-- The "Average Customer Rating" Part -->
												<?php
												if ($this->showRating || !empty($product->rating)) {
													$maxrating = VmConfig::get('vm_maximum_rating_scale',5);
													$ratingwidth = ( $product->rating->rating * 100 ) / $maxrating;//I don't use round as percetntage with works perfect, as for me
												?>
													<span class="vote">
														<span title="" class="vmicon ratingbox" style="display:inline-block;">
															<span class="stars-orange" style="width:<?php echo $ratingwidth;?>%">
															</span>
														</span>
													</span>
												<?php
												}
												?>
											</div>
									</div>
									<div class="col-2">
										<div class="title-indent">
											<h2><?php echo JHTML::link($product->link, $product->product_name); ?></h2>
												<div style="display:none;">
												<?php // Product Short Description
												if(!empty($product->product_s_desc)) { ?>
												<?php echo shopFunctionsF::limitStringByWord($product->product_s_desc, 100, '...') ?>
												<?php } ?>
												</div>
												<?php if (!VmConfig::get('use_as_catalog') and !(VmConfig::get('stockhandle','none')=='none') && (VmConfig::get ( 'display_stock', 1 )) ){?>
												<div class="padding-stock" style="display:none;">
													<span class="vmicon vm2-<?php echo $product->stock->stock_level ?>" title="<?php echo $product->stock->stock_tip ?>"></span>
													<span class="stock-level"><?php echo JText::_('COM_VIRTUEMART_STOCK_LEVEL_DISPLAY_TITLE_TIP') ?></span>
												</div>
												<?php }?>
						
											<div class="detal" style="display:none;">
											<?php // Product Details Button
											echo JHTML::link($product->link, JText::_('COM_VIRTUEMART_PRODUCT_DETAILS'), array('title' => $product->product_name,'class' => 'product-details'));
											?>
											</div>
										</div>
										<div class="product-price marginbottom12" id="productPrice<?php echo $product->virtuemart_product_id ?>">
											<?php
											if ($this->show_prices == '1') {
												if( $product->product_unit && VmConfig::get('vm_price_show_packaging_pricelabel')) {
													echo "<strong>". JText::_('COM_VIRTUEMART_CART_PRICE_PER_UNIT').' ('.$product->product_unit."):</strong>";
												}
												echo $this->currency->createPriceDiv('salesPrice','',$product->prices);
												//print_r($product->prices);
												if ($product->prices[discountAmount] > 0) {
													echo $this->currency->createPriceDiv('priceWithoutTax','',$product->prices);
												}
												//echo $this->currency->createPriceDiv('discountAmount','COM_VIRTUEMART_PRODUCT_DISCOUNT_AMOUNT',$product->prices);
												
												//echo $this->currency->createPriceDiv('salesPrice','COM_VIRTUEMART_PRODUCT_SALESPRICE',$product->prices);
												//echo $this->currency->createPriceDiv('priceWithoutTax','COM_VIRTUEMART_PRODUCT_SALESPRICE_WITHOUT_TAX',$product->prices);
												
												//echo $this->currency->createPriceDiv('variantModification','COM_VIRTUEMART_PRODUCT_VARIANT_MOD',$product->prices);
												//echo $this->currency->createPriceDiv('basePriceWithTax','COM_VIRTUEMART_PRODUCT_BASEPRICE_WITHTAX',$product->prices);
												//echo $this->currency->createPriceDiv('discountedPriceWithoutTax','COM_VIRTUEMART_PRODUCT_DISCOUNTED_PRICE',$product->prices);
												//echo $this->currency->createPriceDiv('salesPriceWithDiscount','COM_VIRTUEMART_PRODUCT_SALESPRICE_WITH_DISCOUNT',$product->prices);
												//echo $this->currency->createPriceDiv('taxAmount','COM_VIRTUEMART_PRODUCT_TAX_AMOUNT',$product->prices);
											} ?>
										</div>
									
									</div>
								<div class="clear"></div>
								</div>
							</div>
							
						<?php
						$iBrowseProduct ++;
						$counterr++;
					
						// Do we need to close the current row now?
						if ($iBrowseCol == $BrowseProducts_per_row) { ?>
						<div class="clear"></div>
						</div>
							<?php
							$iBrowseCol = 1;
						} else {
							$iBrowseCol ++;
							$counter++;
						}
					}
					// Do we need a final closing row tag?
					if ($iBrowseCol != 1) { ?>
						<div class="clear"></div>
						</div>
					<?php
					}
					?>
					
					
				</div>
			</div>
	</div>
	
	<div id="bottom-pagination"><?php echo $this->vmPagination->getPagesLinks(); ?></div>	
<?php // Show child categories
}
?>
</div>
</div>
</div>
