<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$col= 1 ;
$pwidth= ' width'.floor ( 100 / $products_per_row );
if ($products_per_row > 1) { $float= "floatleft";}
else {$float="center";}
?>
<?php 
if ($display_style =="div") { ?>

<?php 
$last = count($products)-1;
?>
<div id="mcs_container">
<div class="customScrollBox">
<div class="horWrapper"> <div class="container">  
 <div class="content">
 <?php foreach ($products as $product) : ?>
 <div class="product-box">
  
 <?php if ($show_img) { ?>
    <div class="browseImage">
			<?php
			if (!empty($product->images[0]) )
					$image = $product->images[0]->displayMediaThumb('class="browseProductImage featuredProductImage" border="0"',false) ;
				else $image = '';
					echo JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id),$image,'class="img2"');
			?>
		</div>
		<?php } ?>
		<?php  if ($show_title) { ?>
		<div class="Title">
			<?php echo JHTML::link(JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id), $product->product_name, array('title' => $product->product_name)); ?>
		</div>
	<?php } ?>	
	<?php if ($show_desc) { ?>
				<div class="description">
					<?php echo shopFunctionsF::limitStringByWord($product->product_s_desc, $row, '...') ?>
				</div>
			<?php } ?>	
		<?php if ($show_price) { ?>	
			<div class="Price">
			<?php
					if ($product->prices['salesPrice']>0)
						echo '<span class="sales">' . $currency->createPriceDiv('salesPrice','',$product->prices,true) . '</span>';
					if ($product->prices['priceWithoutTax']>0) 
						echo '<span class="WithoutTax">' . $currency->createPriceDiv('priceWithoutTax','',$product->prices,true) . '</span>';
					if ($product->prices['discountAmount']>0) 
					echo '<span class="discount">' . $currency->createPriceDiv('discountAmount','',$product->prices,true) . '</span>';
			?>			
			</div>
			<?php } ?>
            <div class="wrapper-slide">
			<?php if ($show_addtocart) echo mod_virtuemart_product::addtocart($product);?>
			<?php if ($show_det) { ?>
			<div class="Details">
			<?php echo JHTML::link(JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id), JText::_('TM_DETAILS')); ?><?php ?> 
			</div>
			<?php } ?>
			</div>
    	</div>
		<?php
            if ($col == $products_per_row && $products_per_row && $last) {
                echo "</div><div class='product-box'>";
                $col= 1 ;
            } else {
                $col++;
            }
			$last--;
            endforeach; ?>
	</div>
</div>
<div class="dragger_bg">
		<div class="dragger_container">
			<div class="dragger"></div>
		</div>
	</div>
</div>
		<a href="#" class="scrollUpBtn"></a> <a href="#" class="scrollDownBtn"></a>
	</div>
</div>
<?php
} ?>
	