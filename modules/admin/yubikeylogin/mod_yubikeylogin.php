<?php
/**
 * @package     YubikeyAuthPlugins
 * @subpackage  Module.yubikeylogin
 *
 * @copyright   Copyright (C) 2013 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$langs            = ModYubiKeyloginHelper::getLanguageList();
$twofactormethods = ModYubiKeyloginHelper::getTwoFactorMethods();
$return           = ModYubiKeyloginHelper::getReturnURI();

if (version_compare(JVERSION, '3.0', 'lt'))
{
	$layout = 'default_25';
}
else
{
	$layout = 'default';
}

require JModuleHelper::getLayoutPath('mod_yubikeylogin', $layout);
