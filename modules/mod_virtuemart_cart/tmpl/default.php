<?php // no direct access
defined('_JEXEC') or die('Restricted access');

// Ajax is displayed in vm_cart_products
// ALL THE DISPLAY IS Done by Ajax using "hiddencontainer" ?>
<script type="text/javascript">
var $j = jQuery.noConflict();
$j(document).ready(function() {

	$j('#vmCartModule').hover(
	   function(){ $j('#cart_list').slideDown('normal') },
	   function(){ $j('#cart_list').slideUp('fast') }
	)
});
function remove_product_cart(elm) {
	var cart_id=elm.getChildren('span').get('text');
	new Request.HTML({
		'url':'index.php?option=com_virtuemart&view=cart&task=delete',
		'method':'post',
		'data':'cart_virtuemart_product_id='+cart_id,
		'onSuccess':function(tree,elms,html,js) {
			//jQuery(".vmCartModule").productUpdate();
			mod=jQuery(".vmCartModule");
			jQuery.getJSON(vmSiteurl+"index.php?option=com_virtuemart&nosef=1&view=cart&task=viewJS&format=json"+vmLang,
				function(datas, textStatus) {
					if (datas.totalProduct >0) {
						mod.find(".vm_cart_products").html("");
						jQuery.each(datas.products, function(key, val) {
							jQuery("#hiddencontainer .container").clone().appendTo(".vmCartModule .vm_cart_products");
							jQuery.each(val, function(key, val) {
								if (jQuery("#hiddencontainer .container ."+key)) mod.find(".vm_cart_products ."+key+":last").html(val) ;
							});
						});
						mod.find(".total").html(datas.billTotal);
						mod.find(".show_cart").html(datas.cart_show);
					} else {
						mod.find(".vm_cart_products").html("");
						mod.find(".total").html(datas.billTotal);
					}
					mod.find(".total_products").html(datas.totalProductTxt);
				}
			);
		}
	}).send();
}

</script>



<!-- Virtuemart 2 Ajax Card -->
<a name="cart"></a>
<div class="vmCartModule" id="vmCartModule">
	<div class="minicart">
    <div class="total_products"><?php echo  $data->totalProductTxt ?></div>
    <div class="total">
	<?php echo  $data->billTotal; ?>
	</div>
	</div>
	<div style="clear:both"></div>
	<div id="hiddencontainer" style="display:none">
		<div class="container">
                    <div class="wrapper marg-bot">
					<!-- Image line -->
					<div class="image">
                    </div>
                    <div class="fleft">
                    <div class="product_row">
						<span class="product_name"></span><div class="clear"></div>
						<span class="quantity"></span><div class="prices" style="display:inline;"></div>
						<a class="vmicon vmicon vm2-remove_from_cart" onclick="remove_product_cart(this);"><span class="product_cart_id" style="display:none;"></span></a>
					</div>
						<div class="product_attributes"></div>
                    </div>
					
                    </div>
                    <div class="wrapper2"> 
                      <div class="none">
                      </div>
                     <div class="xxx">
                      </div>
                    </div>
			</div>
	</div>
    
	<div id="cart_list" class="show_products">
    
		<div class="vm_cart_products" id="vm_cart_products">
			<div class="container">
				<?php
				foreach($data->products as $product) {
					?>
                    <div class="wrapper marg-bot">
					<!-- Image line -->
					<div class="image">
					<?php echo $product["image"]; ?>
                    </div>
                    
                    <div class="fleft">
                    <div class="product_row">
						<span class="product_name"><?php echo $product["product_name"]; ?></span><div class="clear"></div>
						<span class="quantity"><?php echo $product["quantity"]; ?></span><div class="prices" style="display:inline;"><?php echo $product["prices"]; ?></div>
						
						<a class="vmicon vmicon vm2-remove_from_cart" onclick="remove_product_cart(this);"><span class="product_cart_id" style="display:none;"><?php echo $product["product_cart_id"]; ?></span></a>
					</div>
                     <?php
					if(!empty($product["product_attributes"])) {
						?>
						<div class="product_attributes"><?php echo $product["product_attributes"]; ?></div>
						<?php
					}
					?>
                    </div>
					
                    </div>
					<?php
				}
				?>
                <div class="wrapper2"> 
					<?php 
                    $cart_empty = JText::_('TM_VIRTUEMART_CART_EMPTY');
                    if ($data->totalProduct){ ?>
                      <div class="none">
                             <?php echo  $cart_empty ?>
                      </div>
                     <?php }else { ?>
                     
                     <div class="xxx">
                             <?php echo  $cart_empty ?>
                      </div>
                     <?php } ?>
                    </div>
			</div>
		</div>
          <div class="total">
			<?php if ($data->totalProduct) echo  $data->billTotal; ?>
		</div>
		<div class="show_cart">
			<?php if ($data->totalProduct) echo  $data->cart_show; ?>
		</div>

            
	</div>
	<div style="display:none">
		<div class="total_products"></div>
	</div>
	<input type="hidden" id="extra_cart" value="1" />
</div>
