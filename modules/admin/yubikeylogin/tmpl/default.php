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
JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen');

?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="yubikey-form-login" class="form-inline">
	<fieldset class="loginform">
		<div class="control-group" id="mod-yubikeylogin-username-field" style="display: none;">
			<div class="controls">
				<div class="input-prepend input-append">
					<span class="add-on">
						<i class="icon-user hasTooltip" title="<?php echo JText::_('JGLOBAL_USERNAME'); ?>"></i>
						<label for="mod-yubikeylogin-username" class="element-invisible">
							<?php echo JText::_('JGLOBAL_USERNAME'); ?>
						</label>
					</span>
					<input name="username" tabindex="1" id="mod-yubikeylogin-username" type="text" class="input-medium" placeholder="<?php echo JText::_('JGLOBAL_USERNAME'); ?>" size="15"/>
					<a href="<?php echo JUri::root(); ?>index.php?option=com_users&view=remind" class="btn width-auto hasTooltip" title="<?php echo JText::_('MOD_LOGIN_REMIND'); ?>">
						<i class="icon-help"></i>
					</a>
				</div>
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<div class="input-prepend input-append">
					<span class="add-on">
						<img src="<?php echo JUri::base() ?>/modules/mod_yubikeylogin/icon/yubikey.png" border="0" class="hasTooltip" title="<?php echo JText::_('MOD_YUBIKEYLOGIN_YUBIKEY'); ?>" id="mod-yubikeylogin-password-addon-default" />
						<i class="icon-lock hasTooltip" title="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>" id="mod-yubikeylogin-password-addon-password" style="display: none;"></i>
						<label for="mod-yubikeylogin-password" class="element-invisible">
							<?php echo JText::_('MOD_YUBIKEYLOGIN_TOUCHYUBIKEY'); ?>
						</label>
					</span>
					<input name="passwd" tabindex="2" id="mod-yubikeylogin-password" type="text" class="input-medium" placeholder="<?php echo JText::_('MOD_YUBIKEYLOGIN_TOUCHYUBIKEY'); ?>" size="15"/>
					<span class="btn width-auto hasTooltip" title="<?php echo JText::_('MOD_YUBIKEYLOGIN_TOUCHYUBIKEY_HELP'); ?>" id="mod-yubikeylogin-password-help">
						<i class="icon-help"></i>
					</span>
					<a href="<?php echo JUri::root(); ?>index.php?option=com_users&view=reset" class="btn width-auto hasTooltip" title="<?php echo JText::_('MOD_LOGIN_RESET'); ?>" id="mod-yubikeylogin-password-help-alt" style="display: none">
						<i class="icon-help"></i>
					</a>
				</div>
			</div>
		</div>

		<?php if (count($twofactormethods) > 1): ?>
		<div class="control-group" id="mod-yubikeylogin-secretkey-field" style="display: none">
			<div class="controls">
				<div class="input-prepend input-append">
					<span class="add-on">
						<i class="icon-star hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>"></i>
						<label for="mod-yubikeylogin-secretkey" class="element-invisible">
							<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>
						</label>
					</span>
					<input name="secretkey" tabindex="3" id="mod-yubikeylogin-secretkey" type="text" class="input-medium" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>" size="15"/>
					<span class="btn width-auto hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY_HELP'); ?>">
						<i class="icon-help"></i>
					</span>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if (!empty($langs)) : ?>
			<div class="control-group">
				<div class="controls">
					<div class="input-prepend">
						<span class="add-on">
							<i class="icon-comment hasTooltip" title="<?php echo JHtml::tooltipText('MOD_LOGIN_LANGUAGE'); ?>"></i>
							<label for="lang" class="element-invisible">
								<?php echo JText::_('MOD_LOGIN_LANGUAGE'); ?>
							</label>
						</span>
						<?php echo $langs; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div class="control-group">
			<div class="controls">
				<div class="btn-group pull-left">
					<button tabindex="5" class="btn btn-primary btn-large">
						<i class="icon-lock icon-white"></i> <?php echo JText::_('MOD_LOGIN_LOGIN'); ?>
					</button>
				</div>
			</div>
		</div>

		<input type="hidden" name="option" value="com_login"/>
		<input type="hidden" name="task" value="login"/>
		<input type="hidden" name="return" value="<?php echo $return; ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
</form>

<script lang="javascript">
	jQuery(document).ready(function(){
		// Hide the Joomla! login form (it's always shown, even if unpublished!)
		jQuery('#form-login').remove();
		jQuery('#mod-yubikeylogin-password').trigger( "focus" );

		jQuery('#mod-yubikeylogin-password-help').click(function(e){
			jQuery('#mod-yubikeylogin-username-field').show('fast');
			jQuery('#mod-yubikeylogin-username').focus();
			jQuery('#mod-yubikeylogin-password').attr('type', 'password');
			jQuery('#mod-yubikeylogin-password').attr('placeholder', '<?php echo JText::_('JGLOBAL_PASSWORD') ?>')
			jQuery('#mod-yubikeylogin-password-addon-default').hide('fast');
			jQuery('#mod-yubikeylogin-password-addon-password').show('fast');
			jQuery('#mod-yubikeylogin-password-help').hide('fast');
			jQuery('#mod-yubikeylogin-password-help-alt').show('fast');
			jQuery('#mod-yubikeylogin-secretkey-field').show('fast');
		});
	});

</script>