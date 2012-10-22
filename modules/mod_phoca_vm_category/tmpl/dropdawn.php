<?php
defined('_JEXEC') or die('Restricted access');
// All done in recursive function - don't foreach data again and save the server performance.
?>
<script type="text/javascript">
  var $j = jQuery.noConflict();
	$j(document).ready(function(){
		$j('#pvmc-menu li.parent ').hover(
			function() {
				$j(this).find('ul:first').stop(true, true);
				$j(this).find('ul:first').css({display:'none'}).slideDown();
			},
			function() {
				$j(this).find('ul:first').stop(true, true).css({display:'block'}).slideUp(50);
			}
		);
});
 
</script>

<?php
function PhocaVmCategoryTree ($category_id, $active_category_id, $vendorId, $cache, $categoryModel, $p) {
	
	static $level 		= 0;
	static $columns 	= 0;// == $submenu
	static $parentmenu	= 0;
	
	$categories			= $cache->call( array( 'VirtueMartModelCategory', 'getChildCategoryList' ),$vendorId, $category_id );
	$categories 		= $categoryModel->getChildCategoryList($vendorId, $category_id);
	$parentCategories 	= $categoryModel->getCategoryRecurse($active_category_id,0);
	
	if ($p['allcategories'] == 1 && $level == 0) {
		$categories = array();
		$categories[0]->virtuemart_category_id = '0';
		$categories[0]->category_name = JText::_('MOD_PHOCA_VM_CATEGORY_ALL_CATEGORIES');
		$categories[0]->category_description = JText::_('MOD_PHOCA_VM_CATEGORY_ALL_CATEGORIES');
		$categories[0]->metadesc = '';
		$categories[0]->metakey = '';
		$categories[0]->slug = '';
		$categories[0]->virtuemart_media_id = array();
		$parentmenu++;
	}
	
	if (!empty($categories)) {
		
		$ulA 			= '';
		$submenustyle 	= '';
		
		if ($level == 0) {
			$ulA = ' id="pvmc-menu" class="sub-menu"';
		} else if ($level == 1){
			if ($p['submenustyle'] != '') {
				$submenustyle = $p['submenustyle'];
			}
			$ulA = ' class="level'.$level.' child pvmc-submenu" style="'.strip_tags($submenustyle).'"';
		} else {
			$ulA = ' class="level'.$level.' child"';
		}
		
		echo "\n\n";
		echo  '<ul'.$ulA.'>'."\n";
		
		foreach ($categories as $c) {

			$childCats 	= $cache->call( array( 'VirtueMartModelCategory', 'getChildCategoryList' ),$vendorId, $c->virtuemart_category_id );
			$childCats 	= $categoryModel->getChildCategoryList($vendorId, $c->virtuemart_category_id);
			$url 		= JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$c->virtuemart_category_id);
			
			$parent 		= '';
			$drop 			= '';
			$columnstyle 	= '';
			$active 		= '';
				
			if (isset($childCats) && !empty($childCats)) {
				$parent = ' parent';
				$drop	= ' class="drop"';
				// Only design issue - no submenu, no style
				if ((int)$p['countlevels'] == 1) {
					$parent = '';
					$drop	= '';
				}
				
			}
			if ( $active_category_id <> 0 && ($active_category_id == $c->virtuemart_category_id || in_array($c->virtuemart_category_id, $parentCategories)) ) {
				$active = ' active';
			}
			
			if ($p['columnstyle'] != '' && $level == 1) {
				$columnstyle = $p['columnstyle'];
				
				if ($p['countcolumns'] == $columns && $level == 1) {
					$columnstyle = $p['columnstyle'] . 'clear: both;';
					$columns = 0;
				} 
			}
			
			if ($level == 0) {
				$parentmenu++;
				echo  '<li class="level'.$level.''.$parent.''.$active.'"> ';
			} else {
				echo  '<li class="level'.$level.''.$parent.''.$active.'" style="'.strip_tags($columnstyle).'">';
			}
			
			if ($level > 0) {
			if ($p['enablethumbs'] == 1) {
					$categoryModel->addImages($c);
					if (isset($c->images[0]->file_url_thumb) && $c->images[0]->file_url_thumb != '') {
						$img = '<img alt="" src="'.JURI::base(true).'/'.$c->images[0]->file_url_thumb.'" />';
						echo '<div class="pvmc-submenu-img">'.$img.'</div>';
					}
				}
			}
			
			
				echo  '<a  href="'.$url.'" >'.$c->category_name.'<span '.$drop.'></span></a>' . "\n";
			
			
			if ($level == 1) {
			
				$columns++;
				if ($p['enabledesc'] == 1) {
					echo '<div class="pvmc-submenu-desc">'.$c->category_description.'</div>';
				}
			}
			
			if (isset($childCats) && !empty($childCats)) {
				$level++;
				
				if ((int)$p['countlevels'] == (int)$level) {
					$level--;
				} else {
					PhocaVmCategoryTree($c->virtuemart_category_id, $active_category_id, $vendorId, $cache, $categoryModel, $p);
					$level--;
				}
			}
			echo  '</li>'."\n";
			
			
			
			if ((int)$p['countsubmenu'] == (int)$columns && $level == 1) {
				$level = 1;
				break;
			}
			
			if ((int)$p['countparentmenu'] == (int)$parentmenu && $level == 0) {
				echo  '</ul>'."\n\n";
				return false;
			}
		}
		echo  '</ul>'."\n\n";
	}
}

PhocaVmCategoryTree ($category_id, $active_category_id, $vendorId, $cache, $categoryModel, $p);
echo '<div style="clear:both;"> </div>';
?>