<?php
/**
 * @version					$Id: index.php 20196 2011-01-09 02:40:25Z ian $
 * @package					Joomla.Site
 * @copyright				Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license					GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
error_reporting('E_ALL');
$path = $this->baseurl.'/templates/'.$this->template;

JHTML::_('behavior.framework', true);


// get params

$app				= JFactory::getApplication();
$templateparams		= $app->getTemplate(true)->params;
$showLeftColumn = ($this->countModules('left'));
$showRightColumn = ($this->countModules('right'));
$showuser3 = ($this->countModules('user3'));
$showuser4 = ($this->countModules('user4'));
$showuser5 = ($this->countModules('user5'));
$showuser6 = ($this->countModules('user6'));
$showuser8 = ($this->countModules('user8'));
$showuser9 = ($this->countModules('user9'));
$showuser10 = ($this->countModules('user10'));
$showFeatured = ($this->countModules('user2'));
$showNew = ($this->countModules('new'));
$showSpecials = ($this->countModules('specials'));

if (isset($_GET['view'])) {$opt_content = $_GET['view'];} else {$opt_content="no_content";}
if (isset($_GET['layout'])) {$Edit = $_GET['layout'];} else {$Edit="no_edit";}
if (isset($_GET['option'])) {$option = $_GET['option'];}

$menus      = &JSite::getMenu();
$menu      = $menus->getActive();
$pageclass   = "";

if (is_object( $menu )) : 
$params1 =  $menu->params;
$pageclass = $params1->get( 'pageclass_sfx' );
endif; 
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
<jdoc:include type="head" />
<link href='http://fonts.googleapis.com/css?family=Oswald&v1' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Anton' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $path ?>/css/position.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" href="<?php echo $path ?>/css/layout.css" type="text/css" media="screen,projection" />
<link rel="stylesheet" href="<?php echo $path ?>/css/print.css" type="text/css" media="Print" />
<link rel="stylesheet" href="<?php echo $path ?>/css/virtuemart.css" type="text/css"  />
<link rel="stylesheet" href="<?php echo $path ?>/css/products.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $path ?>/css/personal.css" type="text/css" />
<style>
.img-indent  , .slider-bg , .module-category .boxIndent , .module_special .boxIndent , .social , .content-indent , #right .module , .module_address  , .productdetails-view  , .product-neighbours a , .cart-view .login-box, .cart-view .billing-box , #pvmc-menu li a  , .moduletable-category , .pvmc-submenu-img , .module_new  h3 , .module_new  .boxIndent , .footer-box , #pvmc-menu li.parent ul , .module_best  h3  {
 behavior:url(<?php echo $path ?>/PIE.php);
}
</style>
<!--[if lt IE 8]>
    <div style=' clear: both; text-align:center; position: relative; z-index:9999;'>
        <a href="http://www.microsoft.com/windows/internet-explorer/default.aspx?ocid=ie6_countdown_bannercode"><img src="http://www.theie6countdown.com/images/upgrade.jpg" border="0" &nbsp;alt="" /></a>
    </div>
<![endif]-->
<!--[if lt IE 9]>
<script type="text/javascript" src="<?php echo $path ?>/javascript/html5.js"></script>
<![endif]-->
<script type="text/javascript" src="<?php echo $path ?>/javascript/jquery.pikachoose.full.js"></script>
<script type="text/javascript" src="<?php echo $path ?>/javascript/jqtransform.js"></script>
<script type="text/javascript" src="<?php echo $path ?>/javascript/cookie.js"></script>
<script type="text/javascript" src="<?php echo $path ?>/javascript/script.js"></script>
<script type="text/javascript">
var $j = jQuery.noConflict();
		$j(function(){
			 $j('#select-form').jqTransform({imgPath:'<?php echo $path ?>/images/'}).css('display','block');
		});
$j(document).ready(function() {
	var vmcartck = $j('.vmCartModule');
	vmcartck.top = vmcartck.offset().top;
	vmcartck.left = vmcartck.offset().left;
	
	$j('.cart-click').click(function() {
			var el = $j(this);
			var imgtodrag = $j('.product-image:first');
			if (!imgtodrag.length) {
				elparent = el.parent();
				while (!elparent.hasClass('spacer')) {
					elparent = elparent.parent();
				}	
				imgtodrag = elparent.find('img.browseProductImage');
			}
			if (imgtodrag.length) {
				var imgclone = imgtodrag.clone()
					.offset({ top: imgtodrag.offset().top, left: imgtodrag.offset().left })
					.css({'opacity': '0.7', 'position': 'absolute' , 'height':'150px' , 'width': '150px','z-index': '1000'})
					.appendTo($j('body'))
					.animate({
						'top': vmcartck.top+10,
						'left': vmcartck.left+30,
						'width':55,
						'height':55
					},400, 'linear');
				imgclone.animate({
					'width': 0,
					'height': 0
				});
			}
	});							
});							
</script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<?php
$menu = &JSite::getMenu();
if ($menu->getActive() == $menu->getDefault()) {
    $body_class = 'first';
}else{
    $body_class = 'all';
}
?>
<body class="<?php echo $body_class." ".$pageclass;?>">
<div id="header">
			<div class="main">
				<div id="logo">
					<a href="<?php echo $this->baseurl ?>"><img alt="" src="<?php echo $path ?>/images/logo.png" /></a>
				</div>
				<?php if ($showuser3) : ?>
					<div id="topmenu">
						<jdoc:include type="modules" name="user3" style="xhtml" />
					</div>
				<?php endif; ?>
						 <?php if ($showuser4) : ?>
							<div id="search">
								<jdoc:include type="modules" name="user4" style="xhtml" />
							</div>
						<?php endif; ?>
				
                 <?php if ($showuser6) : ?>
                <div class="cart">
                    <jdoc:include type="modules" name="user6" style="xhtml" />
                </div>
                <?php endif; ?>
                 <?php if ($showuser5) : ?>
                <div class="currency">
                    <jdoc:include type="modules" name="user5" style="xhtml" />
                </div>
                <?php endif; ?>
                <jdoc:include type="modules" name="user10" style="xhtml" />
				<jdoc:include type="modules" name="user7" style="xhtml"/>
			</div>
		</div>	
		<!-- END header -->
	<div class="main">
	<div id="content">
		<?php if ($showuser8) : ?>
			<div class="slider-bg">
				<jdoc:include type="modules" name="user8" style="xhtml"/>
			</div> 	
		 <?php endif; ?>     
			<div class="wrapper2">
				<?php if ($showLeftColumn): ?>
				<div id="left">
					<div class="wrapper2">
						<div class="extra-indent">
							<jdoc:include type="modules" name="left" style="left" />
						</div>
					</div>
				</div>
				<?php endif; ?>
				<?php if ($showRightColumn) : ?>
				<div id="right">
					<div class="wrapper">
						<div class="extra-indent">
							<jdoc:include type="modules" name="right" style="user" />
						</div>
					</div>
				</div>
				<?php endif; ?>
				<div class="container">
				<?php if ((($showFeatured ) || ($showNew )) && ($option!="com_search") ) { ?>
					<?php if ($this->getBuffer('message')) : ?>
						<div class="error err-space">
							<jdoc:include type="message" />
						</div>
					<?php endif; ?>
					<jdoc:include type="modules" name="new" style="new" />
					<jdoc:include type="modules" name="user2" style="user" />
				<?php } else { ?>
					<?php if ($this->getBuffer('message')) : ?>
						<div class="error err-space">
							<jdoc:include type="message" />
						</div>
					<?php endif; ?>
					<div class="content-indent">
						<jdoc:include type="component" />
					</div>
				<?php }; ?>
			</div><div class="clear"></div>
		</div>
	</div>
	<div class="clear"></div>
	</div>
	<div id="foot">
		<div class="main">
			<div class="space">
				<div class="footer-box">
					<jdoc:include type="modules" name="user9" style="xhtml"/>
				</div>	
				<div class="wrapper">
					<div class="footerText">
						<jdoc:include type="modules" name="footer" />
						<?php
							if ($menu->getActive() == $menu->getDefault())  { ?>
							More Electronics Store VirtueMart Templates at <a rel="nofollow" href="http://www.templatemonster.com/category/electronics-store-virtuemart-templates/" target="_blank">TemplateMonster.com</a>
							<?php  }
						?>

					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>