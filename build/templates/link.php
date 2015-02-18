<?php
/**
 * Akeeba Linker setup file
 */
$hardlink_files = array();

$symlink_files = array();

$symlink_folders = array(
	# Build files
	'../buildfiles/bin'                                    => 'build/bin',
	'../buildfiles/buildlang'                              => 'build/buildlang',
	'../buildfiles/phingext'                               => 'build/phingext',
	'../buildfiles/tools'                                  => 'build/tools',

	# Component translation
	'translations/plugins/twofactorauth/yubikeyplus/en-GB' => 'plugins/twofactorauth/yubikeyplus/language/en-GB',
	'translations/plugins/twofactorauth/yubikeytotp/en-GB' => 'plugins/twofactorauth/yubikeytotp/language/en-GB',
	'translations/plugins/twofactorauth/u2f/en-GB'         => 'plugins/twofactorauth/u2f/language/en-GB',
);