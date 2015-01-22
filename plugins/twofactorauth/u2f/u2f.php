<?php
/**
 * @package     YubikeyAuthPlugins
 * @subpackage  Twofactorauth.yubikey
 *
 * @copyright   Copyright (C) 2013-2015 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Two Factor Authentication using FIDO U2F Plugin for Joomla! 3.2 or later.
 *
 * @package     YubikeyAuthPlugins
 * @subpackage  Twofactorauth.u2f
 */
class PlgTwofactorauthU2f extends JPlugin
{
	protected $methodName = 'u2f';

	/** @var  LibU2F\U2F|null  U2F server instance  */
	protected $u2f = null;

	protected $enabled = false;

	// TODO: Turn off debug mode before shipping
	private static $developerModeTurnsOffSecurityAndSanityChecks = true;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		// Check OpenSSL version
		$this->enabled = $this->isOpenSSL10();

		// Load the Joomla! RAD layer
		if (!defined('FOF_INCLUDED'))
		{
			include_once JPATH_LIBRARIES . '/fof/include.php';
		}

		if (!class_exists('LibU2F\\U2F'))
		{
			require_once __DIR__ . '/lib/U2F.php';
		}

		$jURI = JURI::getInstance();
		$appId = $jURI->toString(array('scheme', 'host', 'port'));

		$this->u2f = new LibU2F\U2F($appId);

		// Debug mode: turn off security and sanity checks
		if (self::$developerModeTurnsOffSecurityAndSanityChecks)
		{
			\LibU2F\U2F::$ignoreSecurityForDebugging = true;
		}

		// Load the translation files
		$this->loadLanguage();

		$this->addJSHandlerForLoginForms();
	}

	/**
	 * This method returns the identification object for this two factor
	 * authentication plugin.
	 *
	 * @return  stdClass  An object with public properties method and title
	 */
	public function onUserTwofactorIdentify()
	{
		if (!defined('FOF_INCLUDED'))
		{
			return false;
		}

		$section = (int)$this->params->get('section', 3);

		$current_section = 0;

		try
		{
			$app = JFactory::getApplication();

			if ($app->isAdmin())
			{
				$current_section = 2;
			}
			elseif ($app->isSite())
			{
				$current_section = 1;
			}
		}
		catch (Exception $exc)
		{
			$current_section = 0;
		}

		if (!($current_section & $section))
		{
			return false;
		}

		return (object)array(
			'method'	=> $this->methodName,
			'title'		=> JText::_('PLG_TWOFACTORAUTH_U2F_METHOD_TITLE'),
		);
	}

	/**
	 * Shows the configuration page for this two factor authentication method.
	 *
	 * @param   object   $otpConfig  The two factor auth configuration object
	 * @param   integer  $user_id    The numeric user ID of the user whose form we'll display
	 *
	 * @see UsersModelUser::getOtpConfig
	 *
	 * @return  boolean|string  False if the method is not ours, the HTML of the configuration page otherwise
	 */
	public function onUserTwofactorShowConfiguration($otpConfig, $user_id = null)
	{
		if (!defined('FOF_INCLUDED'))
		{
			return false;
		}

		$u2fKeys = $this->getKeysFor($user_id);

        // Is this a new TOTP setup? If so, we'll have to show the code
        // validation field.
        $new_totp = ($otpConfig->method != $this->methodName) || empty($u2fKeys);

		// Get a registration request and save it to the session
		$regData = json_encode($this->u2f->getRegisterData($u2fKeys));
		JFactory::getSession()->set('u2f_request', $regData, 'plg_twofactor_u2f');

		// Start output buffering
		@ob_start();

		// Include the form.php from a template override. If none is found use the default.
		$path = FOFPlatform::getInstance()->getTemplateOverridePath('plg_twofactorauth_u2f', true);

		JLoader::import('joomla.filesystem.file');

		if (JFile::exists($path . 'form.php'))
		{
			include_once $path . 'form.php';
		}
		else
		{
			include_once __DIR__ . '/tmpl/form.php';
		}

		// Stop output buffering and get the form contents
		$html = @ob_get_clean();

		// Return the form contents
		return array(
			'method'	=> $this->methodName,
			'form'		=> $html,
		);
	}

	/**
	 * The save handler of the two factor configuration method's configuration
	 * page.
	 *
	 * @param   string  $method  The two factor auth method for which we'll show the config page
	 *
	 * @see UsersModelUser::setOtpConfig
	 *
	 * @return  boolean|stdClass  False if the method doesn't match or we have an error, OTP config object if it succeeds
	 */
	public function onUserTwofactorApplyConfiguration($method)
	{
		if (!defined('FOF_INCLUDED'))
		{
			return false;
		}

		if ($method != $this->methodName)
		{
			return false;
		}

		if (!$this->enabled)
		{
			return false;
		}

		// Get a reference to the input data object
		$input = JFactory::getApplication()->input;

		// Load raw data
		$rawData = $input->get('jform', array(), 'array');
		$data = array();
		$dataUnregister = array();

		if (isset($rawData['twofactor']))
		{
			if (isset($rawData['twofactor']['u2f']))
			{
				$data = $rawData['twofactor']['u2f'];
			}

			if (isset($rawData['twofactor']['u2f_unregister']))
			{
				$dataUnregister = $rawData['twofactor']['u2f_unregister'];
			}
		}


		// Get the existing OTP configuration
		$userId = $input->getInt('id', JFactory::getUser()->id);
		$u2fKeys = $this->getKeysFor($userId);

		/** @var UsersModelUser $model */
		$model = JModelLegacy::getInstance('User', 'UsersModel');
		$otpConfig = $model->getOtpConfig($userId);

		$saveKeys = false;

		// Do I have to remove keys?
		if (!empty($dataUnregister))
		{
			$saveKeys = true;

			foreach ($dataUnregister as $key)
			{
				foreach ($u2fKeys as $idx => $keyData)
				{
					if ($keyData->keyHandle == $key)
					{
						unset($u2fKeys[$idx]);
					}
				}
			}
		}

		// Do I have to add keys?
		if (array_key_exists('register_response', $data) && !empty($data['register_response']))
		{
			$registrationRequest = JFactory::getSession()->get('u2f_request', null, 'plg_twofactor_u2f');
			$registrationRequest = json_decode($registrationRequest);

			if (empty($registrationRequest))
			{
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('PLG_TWOFACTORAUTH_U2F_ERR_NO_REGISTRATION_REQUEST'), 'error');

				return false;
			}

			$registerResponse = json_decode($data['register_response']);

			try
			{
				$registration = $this->u2f->doRegister($registrationRequest[0], $registerResponse);
			}
			catch (\LibU2F\Error $err)
			{
				$app = JFactory::getApplication();
				$app->enqueueMessage($err->getMessage(), 'error');

				return false;
			}

			$now                          = new DateTime();
			$registration->dateRegistered = $now->getTimeStamp();

			$registration = json_encode($registration);
			$registration = json_decode($registration);

			$u2fKeys[] = $registration;

			$otpConfig->method = $this->methodName;

			if (!isset($otpConfig->otep))
			{
				$otpConfig->otep = array();
			}

			$saveKeys = true;
		}

		if ($saveKeys)
		{
			$otpConfig->config['u2f'] = $userId; // I need to look it up when validating

			$this->saveKeysFor($userId, $u2fKeys);
		}

		return $otpConfig;
	}

	/**
	 * This method should handle any two factor authentication and report back
	 * to the subject.
	 *
	 * @param   array   $credentials  Array holding the user credentials
	 * @param   array   $options      Array of extra options
	 *
	 * @return  boolean  True if the user is authorised with this two-factor authentication method
	 *
	 * @since   3.2.0
	 */
	public function onUserTwofactorAuthenticate($credentials, $options)
	{
		if (!defined('FOF_INCLUDED'))
		{
			return false;
		}

		// Get the OTP configuration object
		$otpConfig = $options['otp_config'];

		// Make sure it's an object
		if (empty($otpConfig) || !is_object($otpConfig))
		{
			return false;
		}

		// Check if we have the correct method
		if ($otpConfig->method != $this->methodName)
		{
			return false;
		}

		// Not a valid OpenSSL version? Sorry, I can't process anything.
		if (!$this->enabled)
		{
			return true;
		}

		// Get the list of valid YubiKeys
		$u2f_valid = $this->getKeysFor($otpConfig->config['u2f']);

		if (!is_array($u2f_valid))
		{
			$u2f_valid = array();
		}

		// Whoops! The user has not configure any U2F keys yet. We implicitly accept the request as valid.
		if (empty($u2f_valid))
		{
			return true;
		}

		// Check if there is a security code
		// TODO That's now how we validate it, right?
		if (empty($credentials['TODO-FIXME']))
		{
			return false;
		}

		// TODO Perform the actual check, somehow
	}

	/**
	 * Loads the registered U2F keys for a specific user
	 *
	 * @param   int  $userId  The user ID
	 *
	 * @return  array
	 */
	private function getKeysFor($userId)
	{
		$ret = array();

		$user = JFactory::getUser($userId);

		if (is_string($user->params))
		{
			$user->params = new JRegistry($user->params);
		}

		$registrations = $user->params->get('u2f_registrations', null);

		if (empty($registrations))
		{
			return $ret;
		}

		$key = JFactory::getConfig()->get('secret');
		$aes = new FOFEncryptAes($key, 256);

		// Decrypt the data
		$registrations = $aes->decryptString($registrations);

		// Remove the null padding added during encryption
		$registrations = rtrim($registrations, "\0");

		// json_decode the result
		$ret = json_decode($registrations);

		return $ret;
	}

	/**
	 * Save the registered U2F into a user's parameters
	 *
	 * @param   int    $userId  The user ID
	 * @param   array  $keys    The registered keys array
	 *
	 * @return  void
	 */
	private function saveKeysFor($userId, array $keys)
	{
		$key = JFactory::getConfig()->get('secret');
		$aes = new FOFEncryptAes($key, 256);

		$registrations = json_encode($keys);
		$registrations = $aes->encryptString($registrations);

		$user = JFactory::getUser($userId);

		if (is_string($user->params))
		{
			$user->params = new JRegistry($user->params);
		}

		$user->params->set('u2f_registrations', $registrations);

		// For some reason JUser::save doesn't work for me, so...
		$params = $user->params->toString('JSON');

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->update($db->qn('#__users'))
			->set($db->qn('params') . ' = ' . $db->q($params))
			->where($db->qn('id') . ' = ' . $db->q($userId));
		$db->setQuery($query)->execute();
	}

	/**
	 * Checks if we have OpenSSL 1.0 or later
	 *
	 * @return  bool
	 */
	private function isOpenSSL10()
	{
		// Debug mode: ignore all security and sanity checks.
		if (self::$developerModeTurnsOffSecurityAndSanityChecks)
		{
			return true;
		}

		// No OpenSSL? No joy.
		if (!defined('OPENSSL_VERSION_TEXT'))
		{
			return false;
		}

		$parts = explode(' ', OPENSSL_VERSION_TEXT);

		// Not actually OpenSSL? No joy.
		if (strtoupper($parts[0]) != 'OPENSSL')
		{
			return false;
		}

		// We can't directly use version compare as it doesn't follow PHP version semantics
		$version = $parts[1];
		$parts = explode('.', $version, 4);
		$version = $parts[0] . '.' . $parts[1] . '.' . (int)$parts[2];

		return version_compare($version, '1.0.0', 'ge');
	}

	private function addJSHandlerForLoginForms()
	{
		// If we're not enabled we won't handle U2F logins
		if (!$this->enabled)
		{
			return;
		}

		// If the user is already logged in there's no need to add the JS override
		if (!JFactory::getUser()->guest)
		{
			return;
		}

		$token = JFactory::getSession()->getFormToken();
		$js = <<< JS
setTimeout(u2f_login_form_attach_handler, 500);

function u2f_login_form_attach_handler()
{
	var loginForms = jQuery("input[name='secretkey']").closest('form');

	if (!loginForms.length)
	{
		return;
	}

	jQuery.each(loginForms, function(idx, loginForm)
	{
		jQuery(loginForm).submit(function(event){
			var allowSubmit = jQuery.data(loginForm, 'allowSubmit');

			if (!allowSubmit)
			{
				event.preventDefault();
			}
			else
			{
				return true;
			}

			jQuery.ajax({
				url: window.location,
				dataType: 'text',
				cache: false,
				data: {
					username: jQuery(loginForm).find("input[name='username']").val(),
					password: jQuery(loginForm).find("input[name='password']").val().
					'$token': 1,
					'_u2f_preauth_check': 1
				}
			}).fail(function( jqXHR, textStatus, errorThrown ) {

			}).done(function( data, textStatus, jqXHR ) {

			})
		});
	});

	console.debug(loginForms);
}


JS;
		JFactory::getDocument()->addScriptDeclaration($js);
	}

	public function onAfterRender()
	{
		// TODO AJAX handlers go here
	}
}