<?php
/*======================================================================*\
|| #################################################################### ||
|| # Copyright ©2006-2009 Youjoomla LLC. All Rights Reserved.           ||
|| # ----------------     JOOMLA TEMPLATES CLUB      ----------- #      ||
|| # @license http://www.gnu.org/copyleft/gpl.html GNU/GPL            # ||
|| #################################################################### ||
\*======================================================================*/
defined('_JEXEC') or die('Restricted access'); 
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'modules/mod_yj_pop_login/css/stylesheet.css');
echo "<!-- http://www.Youjoomla.com YJ Pop Login for Joomla 1.6 starts here --> ";
?>
<?php if($type == 'logout') : ?>
<div id="logins">
	<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form2">
		<input type="submit" name="Submit" class="button" value="Logout" />
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.logout" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
	<span><?php if ($params->get('greeting')) : ?>
	<?php echo JText::_('HINAME') ?> <?php 
	if($params->get('name') == 0 ){
		echo $user->get('username')."!!!";
	}else{
		echo $user->get('name')."!!!";
	}
		?>
	<?php endif; ?></span>
</div>
<?php else : ?>
<?php 
JHTML::_('behavior.mootools'); 
$document->addScript(JURI::base() . 'modules/mod_yj_pop_login/src/yj_login_pop.js');
?>
<script type="text/javascript">
window.addEvent('domready', function() {
		$("login_pop").setStyles({
			right: (window.getScrollLeft() + (window.getWidth() - 290)/2)+'px'
	
		}); 

		$("reg_pop").setStyles({
			left: (window.getScrollLeft() + (window.getWidth() - 445)/2)+'px'

		}); 
});
</script>
<!-- registration and login -->
<div class="poping_links"> <?php echo $params->get('pretext'); ?>
	<?php $usersConfig = &JComponentHelper::getParams( 'com_users' ); if ($usersConfig->get('allowUserRegistration')) : ?>
	<a href="javascript:;" onclick="this.blur();showThem('reg_pop');return false;" id="openReg">&nbsp;criar uma conta</a>&nbsp;&nbsp;ou&nbsp;
	<?php endif; ?>
	<a href="javascript:;" onclick="showThem('login_pop');return false;" id="openLogin">Login</a>
</div>
<!-- login -->
<div id="login_pop" style="display:none;">
	<?php if(JPluginHelper::isEnabled('authentication', 'openid')) : ?>
	<?php JHTML::_('script', 'openid.js'); ?>
	<?php endif; ?>
	<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" name="login" id="form-login" >
		<span><label for="yjpop_username"><?php echo JText::_('USERNAME') ?></label></span>
		<input id="yjpop_username" type="text" name="username" class="inputbox" alt="username" size="18" />
		<span><label for="yjpop_passwd"><?php echo JText::_('PASSWORD') ?></label></span>
		<input id="yjpop_passwd" type="password" name="password" class="inputbox" size="18" alt="password" />
		<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
		<div class="wrapper">
		<label for="yjpop_remember"><?php echo JText::_('REMEMBER') ?></label>
		<input id="yjpop_remember" type="checkbox" name="remember" class="inputbox" value="yes" alt="Remember Me" />
		</div>
		<?php endif; ?>
		<input type="submit" name="Submit" class="button" value="<?php echo JText::_('LOGIN') ?>" />
		<?php echo $params->get('posttext'); ?>
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.login" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
	<ul class="Forgot">
		<li><a href="<?php echo JRoute::_( 'index.php?option=com_users&view=reset' ); ?>"><?php echo JText::_('FORGOT_YOUR_PASSWORD') ?></a></li>
		<li><a href="<?php echo JRoute::_( 'index.php?option=com_users&view=remind' ); ?>"><?php echo JText::_('FORGOT_YOUR_USERNAME') ?></a></li>
	</ul>
	<a href="javascript:;" onclick="this.blur();showThem('login_pop');return true;" id="closeLogin">&nbsp;</a> </div>
<!-- registration  -->
<div id="reg_pop"  style="display:none;">
	<script type="text/javascript" src="<?php echo JURI::base() ?>media/system/js/validate.js"></script>
	<form action="<?php echo JRoute::_('index.php?option=com_users&task=registration.register'); ?>" method="post" id="josForm" name="josForm" class="form-validate">
		<div class="popyjreg">
			<div class="popyjreg_ins">
				<label id="namemsg" for="name"> *&nbsp;<?php echo JText::_( 'NAME' ); ?>: </label>
				<input type="text" name="jform[name]" id="name" size="40" value="" class="inputbox required" maxlength="50" />
			</div>
			<div class="popyjreg_ins">
				<label id="usernamemsg" for="username"> *&nbsp;<?php echo JText::_( 'USERNAME' ); ?>: </label>
				<input type="text" id="username" name="jform[username]" size="40" value="" class="inputbox required validate-username" maxlength="25" />
			</div>
			<div class="popyjreg_ins">
				<label id="emailmsg" for="email"> *&nbsp;<?php echo JText::_( 'EMAIL' ); ?>: </label>
				<input type="text" id="email" name="jform[email1]" size="40" value="" class="inputbox required validate-email" maxlength="100" />
			</div>
			<div class="popyjreg_ins">
				<label id="emailmsg2" for="email2"> *&nbsp;<?php echo JText::_( 'VERIFY_EMAIL' ); ?>: </label>
				<input type="text" id="email2" name="jform[email2]" size="40" value="" class="inputbox required validate-email" maxlength="100" />
			</div>
			<div class="popyjreg_ins">
				<label id="pwmsg" for="password"> *&nbsp;<?php echo JText::_( 'PASSWORD' ); ?>: </label>
				<input class="inputbox required validate-password" type="password" id="password" name="jform[password1]" size="40" value="" />
			</div>
			<div class="popyjreg_ins">
				<label id="pw2msg" for="password2"> *&nbsp;<?php echo JText::_( 'VERIFY_PASSWORD' ); ?>: </label>
				<input class="inputbox required validate-passverify" type="password" id="password2" name="jform[password2]" size="40" value="" />
			</div>
		</div>
		<p class="information_td"><?php echo JText::_( 'REGISTER_REQUIRED' ); ?></p>
		<button class="button validate" type="submit"><?php echo JText::_('REGISTER'); ?></button>
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="registration.register" />
		<?php echo JHtml::_('form.token');?>
	</form>
	<a href="javascript:;" onclick="this.blur();showThem('reg_pop');return true;" id="closeReg">&nbsp;</a> </div>
<!-- end registration and login -->
<?php endif; ?>
