<?php
/**
 * @package     YubikeyAuthPlugins
 * @subpackage  Module.yubikeylogin
 *
 * @copyright   Copyright (C) 2013 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JFactory::getDocument()->addStyleSheet(JURI::base() . '/modules/mod_yubikeylogin/css/joomla25.css');
?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="yubikey-form-login">
	<fieldset class="loginform">

		<div id="mod-yubikeylogin-username-field" class="mod-yubikeylogin-row" style="display: none">
			<label id="mod-yubikeylogin-username-lbl" for="mod-yubikeylogin-username">
				<?php echo JText::_('JGLOBAL_USERNAME'); ?>
			</label>
			<input name="username" id="mod-yubikeylogin-username" type="text" class="inputbox" size="15" placeholder="<?php echo JText::_('JGLOBAL_USERNAME'); ?>" />
		</div>

		<div id="mod-yubikeylogin-password-field" class="mod-yubikeylogin-row">
			<label id="mod-yubikeylogin-password-lbl" for="mod-yubikeylogin-password">
				<?php echo JText::_('MOD_YUBIKEYLOGIN_YUBIKEY'); ?>
			</label>
			<label id="mod-yubikeylogin-password-lbl-alt" for="mod-yubikeylogin-password" style="display: none;">
				<?php echo JText::_('JGLOBAL_PASSWORD'); ?>
			</label>
			<img src="<?php echo JUri::base() ?>modules/mod_yubikeylogin/icon/yubikey.png" border="0" id="mod-yubikeylogin-password-addon-default" />
			<input name="passwd" id="mod-yubikeylogin-password" type="text" class="inputbox" size="15" placeholder="<?php echo JText::_('MOD_YUBIKEYLOGIN_TOUCHYUBIKEY'); ?>" />
		</div>

		<div id="mod-yubikeylogin-lang-field" class="mod-yubikeylogin-row">
			<label id="mod-yubikeylogin-language-lbl" for="lang"><?php echo JText::_('MOD_YUBIKEYLOGIN_LANGUAGE'); ?></label>
			<?php echo $langs; ?>
		</div>

		<div class="button-holder">
			<div class="button1">
				<div class="next">
					<a href="#" onclick="document.getElementById('form-login').submit();">
						<?php echo JText::_('MOD_YUBIKEYLOGIN_LOGIN'); ?></a>
				</div>
			</div>
		</div>
		<div class="button-holder" id="mod-yubikeylogin-switchtopassword">
			<a href="#" onclick="modYubikeyLoginSwitchToPassword(); return false;">
				<?php echo JText::_('MOD_YUBIKEYLOGIN_SWITCHTOPASSWORD'); ?></a>
		</div>

		<div class="clr"></div>
		<input type="submit" class="hidebtn" value="<?php echo JText::_( 'MOD_YUBIKEYLOGIN_LOGIN' ); ?>" />
		<input type="hidden" name="option" value="com_login" />
		<input type="hidden" name="task" value="login" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
</form>

<script lang="javascript">
	window.addEvent('domready', function () {
		document.getElementById('form-login').getParent().dispose();
		document.getElementById('mod-yubikeylogin-password').select();
		document.getElementById('mod-yubikeylogin-password').focus();
	});

	function modYubikeyLoginSwitchToPassword()
	{
		$('mod-yubikeylogin-switchtopassword').setStyle('display', 'none');
		$('mod-yubikeylogin-username-field').setStyle('display', 'block');
		$('mod-yubikeylogin-password-lbl').setStyle('display', 'none');
		$('mod-yubikeylogin-password-lbl-alt').setStyle('display', 'block');
		$('mod-yubikeylogin-password-addon-default').setStyle('display', 'none');
		$('mod-yubikeylogin-password').setProperty('type', 'password');
		$('mod-yubikeylogin-password').setProperty('placeholder', '<?php echo JText::_('JGLOBAL_PASSWORD') ?>');
		document.getElementById('mod-yubikeylogin-username').select();
		document.getElementById('mod-yubikeylogin-username').focus();
	}
</script>