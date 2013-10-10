<?php
/**
 * @package     YubikeyAuthPlugins
 * @subpackage  Authentication.yubikey
 *
 * @copyright   Copyright (C) 2013 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * YubiKey Authentication plugin
 *
 * @package     YubikeyAuthPlugins
 * @subpackage  Authentication.yubikey
 * @since       1.0
 */
class PlgAuthenticationYubikey extends JPlugin
{
    /**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   array   $credentials  Array holding the user credentials
	 * @param   array   $options      Array of extra options
	 * @param   object  &$response    Authentication response object
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
    {
        $this->loadLanguage();
        
        $response->type = 'YubiKey';
        
        // A blank password *and* username means that we have no YubiKey OTP
		if (empty($credentials['password']) && empty($credentials['username']))
		{
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('PLG_AUTHENTICATION_YUBIKEY_ERR_BLANKPASSANDUSERNAME');

			return false;
		}

        // Get the YubiKey OTP
        $otp = null;
        $username = null;

        if (!empty($credentials['username']))
        {
            $otp = $credentials['username'];
            
            if (strlen($otp) != 44)
            {
                // This is not a YubiKey OTP
                $otp = null;
            }
        }
        
        if (is_null($otp) && !empty($credentials['password']))
        {
            $otp = $credentials['password'];
            
            if (strlen($otp) != 44)
            {
                // This is not a YubiKey OTP
                $otp = null;
            }
            elseif (!empty($credentials['username']))
            {
                $username = $credentials['username'];
            }
        }
        
        // If no YubiKey signature was found call it quits
        if (is_null($otp))
        {
            $response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('PLG_AUTHENTICATION_YUBIKEY_ERR_NOYUBIKEYOTP');

			return false;
        }
        
        // Extract the YubiKey signature
        $signature = substr($otp, 0, -32);
        
        if (!is_null($username))
        {
            // First check if this is the master key's signature
            $master_signature = $this->params->get('masterkey', '');
            
            if (!empty($master_signature))
            {
                $master_signature = substr($master_signature, 0, -32);
            }
            
            if ($master_signature != $signature)
            {
                // @todo This is not the master signature, check if the username matches the signature
            }
        }
        else
        {
            // @todo Find the username from the signature
        }
        
        // Load the user from the database
        if (!class_exists('JUserHelper', true))
        {
            jimport('joomla.user.helper');
        }
        
        $user_id = JUserHelper::getUserId($username);
        $user = JFactory::getUser($user_id);
        
        // Check the YubiKey OTP for validity
        $validOTP = $this->validateYubikeyOTP($otp);
        
        if ($validOTP)
        {
            $response->email = $user->email;
            $response->fullname = $user->name;
            
            if (JFactory::getApplication()->isAdmin())
            {
                $response->language = $user->getParam('admin_language');
            }
            else
            {
                $response->language = $user->getParam('language');
            }

            $response->status = JAuthentication::STATUS_SUCCESS;
            $response->error_message = '';
        }
        else
        {
            $response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('PLG_AUTHENTICATION_YUBIKEY_ERR_INVALIDOTP');

			return false;
        }
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
        $customURL = $this->params->get('customurl', '');
        $customURL = trim($customURL);
        
        if (!empty($customURL))
        {
            $server_queue = array($customURL);
        }
        else
        {
            $server_queue = array(
                'api.yubico.com', 'api2.yubico.com', 'api3.yubico.com',
                'api4.yubico.com', 'api5.yubico.com'
            );

            shuffle($server_queue);
        }

		$gotResponse = false;
		$check = false;

		$token = JSession::getFormToken();
		$nonce = md5($token . uniqid(rand()));

		while (!$gotResponse && !empty($server_queue))
		{
			$server = array_shift($server_queue);

            if (!empty($customURL))
            {
                $uri = new JUri($server);
            }
            else
            {
                $uri = new JUri('https://' . $server . '/wsapi/2.0/verify');
            }

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
				$response = $this->getHttp($uri->toString());

				if (!empty($response) && ($response !== false))
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
		$lines = explode("\n", $response);
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
    
    private function getHttp($url)
    {
        if(function_exists('curl_exec'))
		{
			// Use cURL
			$curl_options = array(
				CURLOPT_AUTOREFERER		=> true,
				CURLOPT_FAILONERROR		=> true,
				CURLOPT_FOLLOWLOCATION	=> true,
				CURLOPT_HEADER			=> false,
				CURLOPT_RETURNTRANSFER	=> true,
				CURLOPT_SSL_VERIFYPEER	=> true,
				CURLOPT_CONNECTTIMEOUT	=> 10,
				CURLOPT_MAXREDIRS		=> 20
			);
            
			$ch = curl_init($url);
			
            @curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');
			
            foreach($curl_options as $option => $value)
			{
				@curl_setopt($ch, $option, $value);
			}
			
            $data = curl_exec($ch);
		}
		elseif(ini_get('allow_url_fopen'))
		{
			// Use fopen() wrappers
			$options = array(
                'http' => array(
                    'max_redirects' => 20,
                    'timeout'       => 10
                )
            );
            
			$context = stream_context_create($options);
			$data = @file_get_contents($url, false, $context);
		}
		else
		{
			$data = false;
		}
        
        return $data;
    }
}