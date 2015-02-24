# Two Factor Authentication - YubiKey Plus

If you are not sure what Two Factor Authentication is and why you should use it, [please read this](tfa.md).

This plugin allows you to assign one or more YubiKey secure hardware tokens as Two Factor Authentication devices for your user account. You need to provide a fresh code generated by *any* of these devices along with your username or password to log in to your user account.

## What is YubiKey?

[YubiKey](https://www.yubico.com/products/yubikey-hardware/yubikey-2/) is a secure hardware token device which connects to your computer via a USB port. Some versions of the YubiKey device are also equipped with an NFC chip, allowing Android mobile devices to use it without having to attach it via USB.

YubiKey produces an one time, cryptographically signed password everytime you use it. This password is checked against a remote service to make sure it hasn't been reused and that it's a genuine password produced by a specific YubiKey device. Since this one time password depends on something you *have* it can be securely used as a means of Two Factor Authentication.

## Requirements

The following are required on your server:

* PHP 5.3.10 or later. Recommended: PHP 5.5.0 or later.
* Joomla! 3.2.1 or later. Recommended: the latest released Joomla! 3 version.
* The cURL extension for PHP installed and activated.

You also need the following on the browser side:

* A device equipped with a USB port or NFC. If you're using an iPhone, iPad or iPod you can connect the YubiKey to your device using the [Apple Camera Adapter](http://store.apple.com/us/product/MD821ZM/A/lightning-to-usb-camera-adapter). If you're using an Android device you need a [USB OTG](http://en.wikipedia.org/wiki/USB_On-The-Go) compatible device and cable, or an NFC-equipped Android device and an NFC-equipped YubiKey Neo.

## Initial setup

Before using this plugin, you need to enable it. Log in to the backend of your site as a Super User. Go to Extensions, Manage Plugins. Find the "Two Factor Authentication - YubiKey Plus" plugin and enable it.

## Enabling YubiKey two factor authentication for a user

Edit your *existing* user account, in the backend or the frontend of your site. If you're setting up a new user account you need to first save the new user and then edit it again to enable Two Factor Authentication.

If there is no link provided by your site's manager to edit your user account you can visit a URL like `http://www.example.com/index.php?option=com_users&view=profile&layout=edit` where `http://www.example.com` is the URL to your site.

Click on the Two Factor Authentication tab. From the Authentication Method drop-down list select "YubiKey Plus (multiple devices)".

If you have previously added another YubiKey device, click on the "Add another YubiKey" button. A new section Add A YubiKey will appear below. If you haven't added another YubiKey the Add A YubiKey section is already visible.

You can see the Security Code field under "Add a YubiKey". Click inside it. Insert your YubiKey in a USB slot and tap its button. If you're using an NFC-equipped Android device and YubiKey Neo you need to tap your YubiKey to your device. Click on Save to register the YubiKey with your user account.

If you wish to remove a key you have already defined check the box next to it under the Remove column. Then click on Save or Save & Close button in the toolbar to apply the change. Key removal is performed without any additional confirmation.

You can set up one or more YubiKey devices per user account. Using any of these keys during the login is sufficient. This feature allows you to have backup YubiKey devices in case you lose / damage your main device.

## Logging in

Enter your username and password normally. Click inside the Security Key field. Insert your YubiKey in a USB slot and tap its button. If you're using an NFC-equipped Android device and YubiKey Neo you need to tap your YubiKey to your device. The login proceeds automatically. If nothing happens, click on the Login button.

As long as the username and password combination is correct and the YubiKey-generated Secret Code is a valid, previously unused code generated by your YubiKey the login will proceed normally. If not, you will get an error message.

## What if I am locked out?

Normally, after you have set up Two Factor Authentication, there are up to ten Emergency One Time Passwords generated for you. Note them down and keep them in a safe place. If you inadvertently lock yourself out of your site due to Two Factor Authentication failure you can enter one of these one time passwords in the Secret Key field of the login form. This will log you in to your site, allowing you to disable Two Factor Authentication. The emergency one time password you have used is immediately "burned" and you can no longer use it.

If you have ran out of emergency passwords, or you do not have access to them, you need to perform a manual procedure for disabling Two Factor Authentication on your site. Connect to your site via FTP, SFTP or your hosting account's file manager. Go inside the `plugins` folder of your site. Find the `twofactorauth` folder and go inside it. Find the `yubikeyplus` folder and rename it to `yubikeyplus.bak`. You can now log in to your site without using Two Factor Authentication. Edit your user account and set the Two Factor Authentication to None. Click on Save & Close. Now rename the `yubikeyplus.bak` folder back to `yubikeyplus`. You can now try re-enabling the Two Factor Authentication for your user account.

## Advanced setup

The plugin has the following options:

**Site Section** You can choose between Site (front-end), Administrator (back-end) or Both. This determines which section of your site is protected by this Two Factor Authentication plugin.

We strongly recommend using Both. If you use Administrator (back-end) it is possible that an attacker might be able to crack your username and password and log in the front-end of your site. While more limited than the back-end, the front-end still gives the attacker the power to disable Two Factor Authentication therefore making it possible for them to subsequently log in to your site's back-end. On top of that the front-end does allow editing articles, modifying module configuration and possibly other actions which can be detrimental to the correct operation of your site. If your objective is strong security you need to use the `Both` setting for this option. This is how this plugin is configured by default.