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

	/** @var  u2flib_server\U2F|null  U2F server instance  */
	protected $u2f = null;

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

		if (!class_exists('u2flib_server\\U2F'))
		{
			require_once __DIR__ . '/lib/U2F.php';
		}

		$jURI = JURI::getInstance();
		$appId = $jURI->toString(array('scheme', 'host', 'port'));

		$this->u2f = new u2flib_server\U2F($appId);

		// Load the translation files
		$this->loadLanguage();

		// @TODO Add JS handler code for login forms
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

		if ($otpConfig->method == $this->methodName)
		{
			// This method is already activated.
			$u2fKeys = $otpConfig->config['u2f'];

			if (!is_array($u2fKeys))
			{
				$u2fKeys = array();
			}
		}
		else
		{
			// This methods is not activated yet.
			$u2fKeys = array();
		}

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

		// Get a reference to the input data object
		$input = JFactory::getApplication()->input;

		// Load raw data
		$rawData = $input->get('jform', array(), 'array');
		$data = $rawData['twofactor']['u2f'];

		// Get the existing OTP configuration
		/** @var UsersModelUser $model */
		$model = JModelLegacy::getInstance('User', 'UsersModel');
		$userId = $input->getInt('id', JFactory::getUser());
		$otpConfig = $model->getOtpConfig($userId);

		if (!isset($otpConfig->config['u2f']))
		{
			$otpConfig->config['u2f'] = array();
		}

		if (!is_array($otpConfig->config['u2f']))
		{
			$otpConfig->config['u2f'] = array();
		}

		// Do I have to remove keys?
		if (array_key_exists('remove', $data) && ($otpConfig->method == 'u2f'))
		{
			foreach ($data['remove'] as $key => $code)
			{
				// TODO Remove keys
				$idx = array_search($key, $otpConfig->config['u2f']);
				unset ($otpConfig->config['u2f'][$idx]);
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
			catch (\u2flib_server\Error $err)
			{
				$app = JFactory::getApplication();
				$app->enqueueMessage($err->getMessage(), 'error');

				return false;
			}

			$now                          = new DateTime();
			$registration->dateRegistered = $now->getTimeStamp();

			$otpConfig->config['u2f'][] = $registration;
			$otpConfig->method = $this->methodName;

			if (!isset($otpConfig->otep))
			{
				$otpConfig->otep = array();
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

		// Get the list of valid YubiKeys
		$u2f_valid = $otpConfig->config['u2f'];

		if (!is_array($u2f_valid))
		{
			$u2f_valid = array();
		}

		// Whoops! The user has not configure any YubiKeys yet. We have to let them in.
		if (empty($u2f_valid))
		{
			return false;
		}

		// Check if there is a security code
		// TODO That's now how we validate it, right?
		if (empty($credentials['TODO-FIXME']))
		{
			return false;
		}

		// TODO Perform the actual check, somehow
	}

	public function onAfterRender()
	{
		// TODO AJAX handlers go here
	}
}