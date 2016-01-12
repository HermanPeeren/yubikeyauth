<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.totp.tmpl
 *
 * @copyright   Copyright (C) 2005-2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="well">
	<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYPLUS_INTRO') ?>
</div>

<?php if (!$new_totp): ?>
<fieldset>
	<legend>
		<span class="icon icon-key"></span>&nbsp;
		<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYPLUS_RESET_HEAD') ?>
	</legend>

	<p>
		<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYPLUS_RESET_TEXT') ?>
	</p>

	<table class="table-striped">
		<thead>
		<tr>
			<th>
				<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYPLUS_REMOVE_HEAD_KEY') ?>
			</th>
			<th width="278em">
				<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYPLUS_REMOVE_HEAD_REMOVE') ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($yubikeys as $yubikey): ?>
		<tr>
			<td>
				<code>&nbsp;&nbsp;<?php echo $yubikey?>&nbsp;&nbsp;</code>
			</td>
			<td align="center">
				<a href="javascript:plg_tfa_yubikeyplus_showremove('<?php echo $yubikey?>');" class="btn btn-danger btn-small" id="yubikeyplusremovebtn<?php echo $yubikey?>">
					<span class="icon icon-delete"></span>
					<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYPLUS_REMOVE_BUTTON') ?>
				</a>
				<span id="yubikeyplusremove<?php echo $yubikey?>" style="display: none;">
					<input type="text" class="input-large" name="jform[twofactor][yubikeyplus][remove][<?php echo $yubikey?>]" autocomplete="0" placeholder="<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYPLUS_REMOVE_SECRET') ?>">
				</span>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
		<tfoot>
		<tr>
			<td></td>
			<td align="center">
				<a href="javascript:plg_tfa_yubikeyplus_showadd();" class="btn btn-success btn-small" id="yubikeyplusaddbtn">
					<span class="icon icon-plus"></span>
					<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYPLUS_ADD_BUTTON') ?>
				</a>
			</td>
		</tr>
		</tfoot>
	</table>
</fieldset>

<script type="text/javascript">
	function plg_tfa_yubikeyplus_showremove(key_id)
	{
		window.jQuery('#yubikeyplusremovebtn' + key_id).hide();
		window.jQuery('#yubikeyplusremove' + key_id).show();
		window.jQuery('#yubikeyplusremove' + key_id + '>input').focus();
	}

	function plg_tfa_yubikeyplus_showadd()
	{
		window.jQuery('#yubikeyplusaddbtn').hide();
		window.jQuery('#yubikeyplusadd').show();
	}
</script>
<?php endif; ?>

<fieldset id="yubikeyplusadd" style="display: <?php echo $new_totp ? 'block' : 'none' ?>;">
	<legend>
		<span class="icon icon-plus-circle"></span>&nbsp;
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