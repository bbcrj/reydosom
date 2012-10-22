<?php
/**
 * @version                $Id: component.php 20196 2011-01-09 02:40:25Z ian $
 * @package                Joomla.Site
 * @subpackage        tpl_beez2
 * @copyright        Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license                GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

$path = $this->baseurl.'/templates/'.$this->template;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="head" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $path ?>/css/position.css" type="text/css" media="screen,projection" />
	<link rel="stylesheet" href="<?php echo $path ?>/css/layout.css" type="text/css" media="screen,projection" />
	<link rel="stylesheet" href="<?php echo $path ?>/css/personal.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo $path ?>/css/print.css" type="text/css" />
</head>
<body class="contentpane">
	<div id="all">
		<div id="main">
			<jdoc:include type="message" />
			<jdoc:include type="component" />
		</div>
	</div>
</body>
</html>
