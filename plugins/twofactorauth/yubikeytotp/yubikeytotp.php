<?php
/**
 * @package     YubikeyAuthPlugins
 * @subpackage  Twofactorauth.yubikey
 *
 * @copyright   Copyright (C) 2013-2016 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Two Factor Authentication using Yubikey Plugin for Joomla! 3.3 or later.
 *
 * This plugin, unlike the plugin shipped with Joomla! 3.2, 3.3 and 3.4, allows you to set up as many YubiKey devices
 * per user account as you please. It also allows you setting a Google Authenticator (TOTP) auxiliary method, in case
 * you are logging in from a device where YubiKey is not supported (old Android phone, public terminal with the USB port
 * disabled, ...)
 *
 * @package     YubikeyAuthPlugins
 * @subpackage  Twofactorauth.yubikeytotp
 */
class PlgTwofactorauthYubikeytotp extends JPlugin
{
	protected $methodName = 'yubikeytotp';

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

		// Load the Joomla! RAD layer
		if (!defined('FOF_INCLUDED'))
		{
			include_once JPATH_LIBRARIES . '/fof/include.php';
		}

		// Load the translation files
		$this->loadLanguage();
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
			'title'		=> JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_METHOD_TITLE'),
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

		$totp = new FOFEncryptTotp(30, 6, 10);

		if ($otpConfig->method == $this->methodName)
		{
			// This method is already activated. Reuse the same Yubikey ID.
			$yubikeys = $otpConfig->config['yubikeytotp'];
			$secret = isset($otpConfig->config['yubikeytotp_code']) ? $otpConfig->config['yubikeytotp_code'] : '';

			if (is_string($yubikeys))
			{
				$yubikeys = array($yubikeys);
			}
		}
		else
		{
			// This methods is not activated yet. We'll need a Yubikey TOTP to setup this Yubikey.
			$yubikeys = array();
		}

		if (empty($secret))
		{
			$secret = $totp->generateSecret();
		}

        // Is this a new TOTP setup? If so, we'll have to show the code
        // validation field.
        $new_totp = ($otpConfig->method != $this->methodName) || empty($yubikeys);
        $new_totpkey = ($otpConfig->method != $this->methodName) || empty($otpConfig->config['yubikeytotp_code']);

		// These are used by Google Authenticator to tell accounts apart
		$username = JFactory::getUser($user_id)->username;
		$hostname = JFactory::getURI()->getHost();

		// This is the URL to the QR code for Google Authenticator
		$url = $totp->getUrl($username, $hostname, $secret);

		// Start output buffering
		@ob_start();

		// Include the form.php from a template override. If none is found use the default.
		$path = FOFPlatform::getInstance()->getTemplateOverridePath('plg_twofactorauth_yubikeytotp', true);

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

		// Get a reference to the input data object
		$input = JFactory::getApplication()->input;

		// Load raw data
		$rawData = $input->get('jform', array(), 'array');
		$data = $rawData['twofactor']['yubikeytotp'];

		// Get the existing OTP configuration
		/** @var UsersModelUser $model */
		$model = JModelLegacy::getInstance('User', 'UsersModel');
		$userId = $input->getInt('id', JFactory::getUser()->id);
		$otpConfig = $model->getOtpConfig($userId);

		if (!isset($otpConfig->config['yubikeytotp']))
		{
			$otpConfig->config['yubikeytotp'] = array();
		}

		if (is_string($otpConfig->config['yubikeytotp']))
		{
			$otpConfig->config['yubikeytotp'] = array($otpConfig->config['yubikeytotp']);
		}

		// Do I have to remove keys?
		if (array_key_exists('remove', $data) && ($otpConfig->method == 'yubikeytotp'))
		{
			foreach ($data['remove'] as $key => $code)
			{
				if (!in_array($key, $otpConfig->config['yubikeytotp']))
				{
					continue;
				}

				if ($this->validateYubikeyOTP($code))
				{
					$idx = array_search($key, $otpConfig->config['yubikeytotp']);
					unset ($otpConfig->config['yubikeytotp'][$idx]);
				}
			}
		}

		// Do I have to add keys?
		if (array_key_exists('securitycode', $data) && !empty($data['securitycode']))
		{
			// Validate the Yubikey OTP
			$check = $this->validateYubikeyOTP($data['securitycode']);

			// If the check failed do not change two factor authentication settings.
			if (!$check)
			{
				try
				{
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::_('PLG_TWOFACTORAUTH_YUBIKEYTOTP_ERR_VALIDATIONFAILED'), 'error');
				}
				catch (Exception $exc)
				{
					// This only happens when we are in a CLI application. We cannot
					// enqueue a message, so just do nothing.
				}

				return false;
			}

			$newCode = substr($data['securitycode'], 0, -32);

			$otpConfig->config['yubikeytotp'][] = $newCode;
			$otpConfig->config['yubikeytotp'] = array_unique($otpConfig->config['yubikeytotp']);
			$otpConfig->method = $this->methodName;

			if (!isset($otpConfig->otep))
			{
				$otpConfig->otep = array();
			}
		}

		// Do I have to validate a TOTP code?
		if (array_key_exists('totpcode', $data) && !empty($data['totpcode']))
		{
			// Create a new TOTP class with Google Authenticator compatible settings
			$totp = new FOFEncryptTotp(30, 6, 10);

			// Check the security code entered by the user (exact time slot match)
			$check = $totp->checkCode($data['totpkey'], $data['totpcode']);

			if ($check)
			{
				$otpConfig->config['yubikeytotp_code'] = $data['totpkey'];
			}
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

		if (!isset($otpConfig->config['yubikeytotp']))
		{
			// Whoops! The user has not configure any YubiKeys yet. We have to let them in.
			return false;
		}

		// Get the list of valid YubiKeys
		$yubikey_valid = $otpConfig->config['yubikeytotp'];

		if (is_string($yubikey_valid))
		{
			$yubikey_valid = array($yubikey_valid);
		}

		// Whoops! The user has not configure any YubiKeys yet. We have to let them in.
		if (empty($yubikey_valid))
		{
			return false;
		}

		// Check if there is a security code
		if (empty($credentials['secretkey']))
		{
			return false;
		}

		// Do we have a TOTP?
		$code = trim($credentials['secretkey']);
		$key = isset($otpConfig->config['yubikeytotp_code']) ? $otpConfig->config['yubikeytotp_code'] : null;

		if (!empty($key) && (strlen($code) == 6) && is_numeric($code))
		{
			// Create a new TOTP class with Google Authenticator compatible settings
			$totp = new FOFEncryptTotp(30, 6, 10);
			$check = $totp->checkCode($key, $code);

			// If the check succeeds, return. Otherwise we'll probably be returning false further below.
			if ($check)
			{
				return true;
			}
		}

		// Check if the Yubikey starts with the configured Yubikey user string
		$yubikey = substr($credentials['secretkey'], 0, -32);

		$check = false;

		foreach ($yubikey_valid as $valid_signature)
		{
			if ($yubikey == $valid_signature)
			{
				$check = true;

				break;
			}
		}

		if ($check)
		{
			$check = $this->validateYubikeyOTP($credentials['secretkey']);
		}

		return $check;
	}

	/**
	 * Validates a Yubikey OTP against the Yubikey servers
	 *
	 * @param   string  $otp  The OTP generated by your Yubikey
	 *
	 * @return  boolean  True if it's a valid OTP
	 */
	private function validateYubikeyOTP($otp)
	{
		if (!defined('FOF_INCLUDED'))
		{
			return false;
		}

		$server_queue = array(
			'api.yubico.com', 'api2.yubico.com', 'api3.yubico.com',
			'api4.yubico.com', 'api5.yubico.com'
		);

		shuffle($server_queue);

		$gotResponse = false;
		$check = false;

		$http = JHttpFactory::getHttp();

		$token = JSession::getFormToken();
		$nonce = md5($token . uniqid(rand()));

		while (!$gotResponse && !empty($server_queue))
		{
			$server = array_shift($server_queue);

			$uri = new JUri('https://' . $server . '/wsapi/2.0/verify');

			// I don't see where this ID is used?
			$uri->setVar('id', 1);

			// The OTP we read from the user
			$uri->setVar('otp', $otp);

			// This prevents a REPLAYED_OTP status of the token doesn't change
			// after a user submits an invalid OTP
			$uri->setVar('nonce', $nonce);

			// Minimum service level required: 50% (at least 50% of the YubiCloud
			// servers must reply positively for the OTP to validate)
			$uri->setVar('sl', 50);

			// Timeou waiting for YubiCloud servers to reply: 5 seconds.
			$uri->setVar('timeout', 5);

			try
			{
				$response = $http->get($uri->toString(), null, 6);

				if (!empty($response))
				{
					$gotResponse = true;
				}
				else
				{
					continue;
				}
			}
			catch (Exception $exc)
			{
				// No response, continue with the next server
				continue;
			}
		}

		// No server replied; we can't validate this OTP
		if (!$gotResponse)
		{
			return false;
		}

		// Parse response
		$lines = explode("\n", $response->body);
		$data = array();

		foreach ($lines as $line)
		{
			$line = trim($line);

			$parts = explode('=', $line, 2);

			if (count($parts) < 2)
			{
				continue;
			}

			$data[$parts[0]] = $parts[1];
		}

		// Validate the response - We need an OK message reply
		if ($data['status'] != 'OK')
		{
			return false;
		}

		// Validate the response - We need a confidence level over 50%
		if ($data['sl'] < 50)
		{
			return false;
		}

		// Validate the response - The OTP must match
		if ($data['otp'] != $otp)
		{
			return false;
		}

		// Validate the response - The token must match
		if ($data['nonce'] != $nonce)
		{
			return false;
		}

		return true;
	}
}
