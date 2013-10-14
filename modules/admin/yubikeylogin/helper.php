<?php
/**
 * @package     YubikeyAuthPlugins
 * @subpackage  Module.yubikeylogin
 *
 * @copyright   Copyright (C) 2013 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_login
 *
 * @package     YubikeyAuthPlugins
 * @subpackage  Module.yubikeylogin
 * @since       1.1
 */
abstract class ModYubikeyloginHelper
{
	/**
	 * Get an HTML select list of the available languages.
	 *
	 * @return  string
	 */
	public static function getLanguageList()
	{
		$languages = JLanguageHelper::createLanguageList(null, JPATH_ADMINISTRATOR, false, true);

		if (count($languages) <= 1)
		{
			return '';
		}

		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			array_unshift($languages, JHtml::_('select.option', '', JText::_('JDEFAULT')));
		}
		else
		{
			array_unshift($languages, JHtml::_('select.option', '', JText::_('JDEFAULTLANGUAGE')));
		}

		return JHtml::_('select.genericlist', $languages, 'lang', ' class="inputbox advancedSelect"', 'value', 'text', null);
	}

	/**
	 * Get the redirect URI after login.
	 *
	 * @return  string
	 */
	public static function getReturnURI()
	{
		$uri    = JUri::getInstance();
		$return = 'index.php' . $uri->toString(array('query'));

		if ($return != 'index.php?option=com_login')
		{
			return base64_encode($return);
		}
		else
		{
			return base64_encode('index.php');
		}
	}

	public static function getTwoFactorMethods()
	{
		$version = explode('.', JVERSION, 4);
		$version = $version[0] . '.' . $version[1] . '.' . $version[2];

		if (version_compare($version, '3.2.0', 'lt'))
		{
			return array();
		}
		else
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';
			return UsersHelper::getTwoFactorMethods();
		}
	}
}
