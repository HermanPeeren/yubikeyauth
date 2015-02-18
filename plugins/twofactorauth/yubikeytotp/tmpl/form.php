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
	<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_INTRO') ?>
</div>

<?php if (!$new_totp): ?>
<fieldset>
	<legend>
		<span class="icon icon-key"></span>&nbsp;
		<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_RESET_HEAD') ?>
	</legend>

	<p>
		<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_RESET_TEXT') ?>
	</p>

	<table class="table-striped">
		<thead>
		<tr>
			<th>
				<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_REMOVE_HEAD_KEY') ?>
			</th>
			<th width="278em">
				<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_REMOVE_HEAD_REMOVE') ?>
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
				<a href="javascript:plg_tfa_yubikeytotp_showremove('<?php echo $yubikey?>');" class="btn btn-danger btn-small" id="yubikeytotpremovebtn<?php echo $yubikey?>">
					<span class="icon icon-delete"></span>
					<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_REMOVE_BUTTON') ?>
				</a>
				<span id="yubikeytotpremove<?php echo $yubikey?>" style="display: none;">
					<input type="text" class="input-large" name="jform[twofactor][yubikeytotp][remove][<?php echo $yubikey?>]" autocomplete="0" placeholder="<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_REMOVE_SECRET') ?>">
				</span>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
		<tfoot>
		<tr>
			<td></td>
			<td align="center">
				<a href="javascript:plg_tfa_yubikeytotp_showadd();" class="btn btn-success btn-small" id="yubikeytotpaddbtn">
					<span class="icon icon-plus"></span>
					<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_ADD_BUTTON') ?>
				</a>
			</td>
		</tr>
		</tfoot>
	</table>
</fieldset>

<script type="text/javascript">
	function plg_tfa_yubikeytotp_showremove(key_id)
	{
		window.jQuery('#yubikeytotpremovebtn' + key_id).hide();
		window.jQuery('#yubikeytotpremove' + key_id).show();
		window.jQuery('#yubikeytotpremove' + key_id + '>input').focus();
	}

	function plg_tfa_yubikeytotp_showadd()
	{
		window.jQuery('#yubikeytotpaddbtn').hide();
		window.jQuery('#yubikeytotpadd').show();
	}
</script>
<?php endif; ?>

<fieldset id="yubikeytotpadd" style="display: <?php echo $new_totp ? 'block' : 'none' ?>;">
	<legend>
		<span class="icon icon-plus-circle"></span>&nbsp;
		<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_STEP1_HEAD') ?>
	</legend>

	<p>
		<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_STEP1_TEXT') ?>
	</p>

	<div class="control-group">
		<label class="control-label" for="yubikeytotpsecuritycode">
			<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_SECURITYCODE') ?>
		</label>
		<div class="controls">
			<input type="text" class="input-medium" name="jform[twofactor][yubikeytotp][securitycode]" id="yubikeytotpsecuritycode" autocomplete="0">
		</div>
	</div>
</fieldset>

<p></p>

<fieldset>
	<legend>
		<span class="icon icon-lock"></span>&nbsp;
		<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_TOTP_STEP1_HEAD') ?>
	</legend>

	<input type="hidden" name="jform[twofactor][yubikeytotp][totpkey]" value="<?php echo $secret ?>" />

	<div class="clearfix"></div>

	<?php echo JHtml::_('bootstrap.startTabSet', 'yubikeytotp_totpsetup', array('active' => 'yubikeytotp_totpsetup_text')); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'yubikeytotp_totpsetup', 'yubikeytotp_totpsetup_text', JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_SETUPINFO')); ?>

	<div>
		<p>
			<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_TOTP_STEP2_TEXT') ?>
		</p>
		<table class="table table-striped">
			<tr>
				<td>
					<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_TOTP_STEP2_ACCOUNT') ?>
				</td>
				<td>
					<?php echo $username ?>@<?php echo $hostname ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_TOTP_STEP2_KEY') ?>
				</td>
				<td>
					<?php echo $secret ?>
				</td>
			</tr>
		</table>
	</div>

	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'yubikeytotp_totpsetup', 'yubikeytotp_totpsetup_qr', JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_QR')); ?>

	<div>
		<p>
			<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_TOTP_STEP2_ALTTEXT') ?>
			<br />
			<img src="<?php echo $url ?>" style="float: none;" />
		</p>
	</div>

	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JHtml::_('bootstrap.endTabSet'); ?>

	<?php if ($new_totpkey): ?>
	<p>
		<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_STEP3_TEXT') ?>
	</p>
	<div class="control-group">
		<label class="control-label" for="totpsecuritycode">
			<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_TOTP_STEP3_SECURITYCODE') ?>
		</label>
		<div class="controls">
			<input type="text" class="input-small" name="jform[twofactor][yubikeytotp][totpcode]" id="yubikeytotpsecuritycode" autocomplete="0">
		</div>
	</div>
	<?php else: ?>
		<p>
			<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_ALREADYSETUP_HEAD') ?>
		</p>
	<?php endif; ?>
</fieldset>