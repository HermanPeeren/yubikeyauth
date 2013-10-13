<?php
/**
 * Akeeba Linker setup file
 */
$hardlink_files = array(
);

$symlink_files = array(
);

$symlink_folders = array(
	# Build files
	'../buildfiles/bin'							=> 'build/bin',
	'../buildfiles/buildlang'					=> 'build/buildlang',
	'../buildfiles/phingext'					=> 'build/phingext',
	'../buildfiles/tools'						=> 'build/tools',

	# Component translation
	'translations/plugins/authentication/yubikey/en-GB'	=> 'plugins/authentication/yubikey/language/en-GB',
	'translations/plugins/twofactorauth/yubikey/en-GB'	=> 'plugins/twofactorauth/yubikey/language/en-GB',
	'translations/plugins/user/yubikey/en-GB'			=> 'plugins/user/yubikey/language/en-GB',
);