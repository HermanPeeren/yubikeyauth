<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.totp.tmpl
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$ajaxURL = JURI::current();

$script = <<< JS
window.jQuery('document').load(function(){
	if ((chrome === undefined) || (window.u2f === undefined))
	{
		window.jQuery('#u2f_interface').hide();
		if ((chrome === undefined))
		{
			window.jQuery('#u2f_error_browser').show();
		}
		else
		{
			window.jQuery('#u2f_error_extension').show();
		}
	}
});

var u2fajaxurl = '$ajaxURL';
JS;


JFactory::getDocument()->addScript('chrome-extension://pfboblefjcgdjicmnffhdgionmgcdmne/u2f-api.js');
JFactory::getDocument()->addScriptDeclaration($script);
?>
<div id="u2f_error_browser" class="alert alert-error" style="display: none">
	<h3><?php echo JText::_('PLG_TWOFACTORAUTH_U2F_ERROR_BROWSER_TITLE') ?></h3>
	<p><?php echo JText::_('PLG_TWOFACTORAUTH_U2F_ERROR_BROWSER_TEXT') ?></p>
</div>
<div id="u2f_error_extension" class="alert alert-error" style="display: none">
	<h3><?php echo JText::_('PLG_TWOFACTORAUTH_U2F_ERROR_EXTENSION_TITLE') ?></h3>
	<p><?php echo JText::_('PLG_TWOFACTORAUTH_U2F_ERROR_EXTENSION_TEXT') ?></p>
</div>
<div id="u2f_interface">
	<div class="well">
		<?php echo JText::_('PLG_TWOFACTORAUTH_U2F_INTRO') ?>
	</div>

	<?php if (!$new_totp): ?>
		<fieldset>
			<legend>
				<span class="icon icon-key"></span>&nbsp;
				<?php echo JText::_('PLG_TWOFACTORAUTH_U2F_RESET_HEAD') ?>
			</legend>

			<p>
				<?php echo JText::_('PLG_TWOFACTORAUTH_U2F_RESET_TEXT') ?>
			</p>

			<table class="table-striped">
				<thead>
				<tr>
					<th>
						<?php echo JText::_('PLG_TWOFACTORAUTH_U2F_REMOVE_HEAD_REMOVE') ?>
					</th>
					<th>
						<?php echo JText::_('PLG_TWOFACTORAUTH_U2F_REMOVE_HEAD_KEY') ?>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($u2fKeys as $index => $u2fKey):
					$regDate = JFactory::getDate($u2fKey->dateRegistered);
					?>
					<tr>
						<td>
							<input type="checkbox" name="jform[twofactor][u2f_unregister][]" id="u2f_unregister_<?php echo $u2fKey->keyHandle ?>" value="<?php echo $u2fKey->keyHandle ?>" >
						</td>
						<td>
							<?php echo $regDate->format(JText::_('DATE_FORMAT_LC2')) ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
				<tfoot>
				<tr>
					<td></td>
					<td align="center">
						<a href="javascript:plg_tfa_U2F_showadd();" class="btn btn-success btn-small" id="u2faddbtn">
							<span class="icon icon-plus"></span>
							<?php echo JText::_('PLG_TWOFACTORAUTH_U2F_ADD_BUTTON') ?>
						</a>
					</td>
				</tr>
				</tfoot>
			</table>
		</fieldset>
	<?php endif; ?>

	<fieldset id="u2fadd" style="display: <?php echo $new_totp ? 'block' : 'none' ?>;">
		<legend>
			<span class="icon icon-plus-circle"></span>&nbsp;
			<?php echo JText::_('PLG_TWOFACTORAUTH_U2F_STEP1_HEAD') ?>
		</legend>

		<p id="u2fadd_button_container">
			<a href="javascript:plg_tfa_U2F_register()" class="btn btn-success">
				<span class="icon icon-plus-circle"></span>
				<?php echo JText::_('PLG_TWOFACTORAUTH_U2F_STEP1_HEAD') ?>
			</a>
		</p>

		<div id="u2fadd_prompt_container" class="alert alert-info" style="display: none">
			Please insert your U2F device. If it has a button and it's flashing, please touch it.
		</div>

		<input type="text" name="jform[twofactor][u2f][register_response]" id="u2fsecurityregisterresponse" autocomplete="0">
	</fieldset>


	<script type="text/javascript">
		function plg_tfa_U2F_showadd()
		{
			window.jQuery('#u2faddbtn').hide();
			window.jQuery('#u2fadd').show();
		}

		function plg_tfa_U2F_register()
		{
			window.jQuery('#u2fadd_button_container').hide();
			window.jQuery('#u2fadd_prompt_container').show();

			resp = JSON.parse('<?php echo $regData ?>');
			var req = resp[0];
			var auth = resp[1];
			u2f.register([req], auth, function (data)
			{
				console.debug(data);
				if ((data.errorCode === undefined) || (data.errorCode === 0))
				{
					window.jQuery('#u2fadd_prompt_container').hide();
					window.jQuery('#u2fsecurityregisterresponse').val(JSON.stringify(data));
					Joomla.submitbutton('user.apply');
				}

				if (data.errorCode == 1)
				{
					alert('Other error');
				}

				if (data.errorCode == 2)
				{
					alert('The request cannot be processed');
				}

				if (data.errorCode == 3)
				{
					alert('Client configuration not supported');
				}

				if (data.errorCode == 4)
				{
					alert('The presented device is not eligible for the request. It is either already registered or it does not know the presented key handle.');
				}

				if (data.errorCode == 5)
				{
					alert('Timeout reached before the request could be satisfied');
				}

				// Reload the page
				//window.location = window.location;
			});
		}
	</script>
</div>