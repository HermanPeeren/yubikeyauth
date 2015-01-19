<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.totp.tmpl
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="well">
	<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYPLUS_INTRO') ?>
</div>

<?php if ($new_totp): ?>
<fieldset>
	<legend>
		<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYPLUS_STEP1_HEAD') ?>
	</legend>

	<p>
		<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYPLUS_STEP1_TEXT') ?>
	</p>

	<div class="control-group">
		<label class="control-label" for="yubikeyplussecuritycode">
			<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYPLUS_SECURITYCODE') ?>
		</label>
		<div class="controls">
			<input type="text" class="input-medium" name="jform[twofactor][yubikeyplus][securitycode]" id="yubikeyplussecuritycode" autocomplete="0">
		</div>
	</div>
</fieldset>
<?php else: ?>
<fieldset>
	<legend>
		<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_RESET_HEAD') ?>
	</legend>

	<p>
		<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_RESET_TEXT') ?>
	</p>
</fieldset>
<?php endif; ?>
