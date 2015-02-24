# Two Factor Authentication - FIDO U2F (Universal 2nd Factor)

If you are not sure what Two Factor Authentication is and why you should use it, [please read this](tfa.md).

## What is U2F?

[FIDO U2F](http://fidoalliance.org/adoption/video/yubico-fido-alliance-universal-2nd-factor-u2f-demonstration)
is a new and very secure second factor authentication method created by an alliance of Internet companies with a srong background and interest in Internet security. It uses secure hardware tokens to provide strong authentication of users when logging in to Internet sites. The security scheme is strong enough to allow your users to use weak passwords, even four digit PINs, without compromising their account's security. 

When you enable U2F two factor authentication on a site a secure cryptographic certificate is generated, linking your user account on that specific site with the U2F hardware token. When logging in you need to plug in your U2F token to a USB port of the computer you're logging in. After you enter your username and password the light on the U2F device blinks and you touch its button. It will then respond with a cryptographic challenge which positively affirms that this specific U2F token is present and the site can proceed with logging you in.

## Requirements

The following are required on your server:

* PHP 5.3.10 or later. Recommended: PHP 5.5.0 or later.
* Joomla! 3.2.1 or later. Recommended: the latest released Joomla! 3 version.
* OpenSSL 1.0.1 or later. U2F will **not** work on servers with OpenSSL 0.9.8 or 1.0.0.

You also need the following on the browser side:

* Google Chrome 38 or later. Support for U2F is planned for other browsers but not implemented at the time of this writing (Feburary 2015).
* The [FIDO U2F extension for Google Chrome](https://chrome.google.com/webstore/detail/fido-u2f-universal-2nd-fa/pfboblefjcgdjicmnffhdgionmgcdmne). This is required for sites outside the google.com domain to be able to use U2F.

**EXTREMELY IMPORTANT**: U2F authentication currently only works with desktops, laptops and Chromebooks. It does not work when logging in from smartphones, tablets and other devices which cannot run the desktop version of the Google Chrome browser, or with browsers other than Google Chrome. *If you do not use Google Chrome on a desktop, laptop or Chromebook with the FIDO U2F extension and have enabled the U2F two factor authentication method you will be UNABLE to log in to your site!* This is NOT a bug in our code. It's the result of the current state of U2F support.

## Initial setup

Before using this plugin, you need to enable it. Log in to the backend of your site as a Super User. Go to Extensions, Manage Plugins. Find the "Two Factor Authentication - FIDO U2F (Universal 2nd Factor)" plugin and enable it.

Make sure that the server and browser minimum requirements are met. If not, you will get an error message when setting up Two Factor Authentication for a user.

## Enabling U2F for a user

Edit your *existing* user account, in the backend or the frontend of your site. If you're setting up a new user account you need to first save the new user and then edit it again to enable Two Factor Authentication.

If there is no link provided by your site's manager to edit your user account you can visit a URL like `http://www.example.com/index.php?option=com_users&view=profile&layout=edit` where `http://www.example.com` is the URL to your site.

Click on the Two Factor Authentication tab. From the Authentication Method drop-down list select "FIDO U2F".

@TODO

## Logging in

@TODO

## Advanced setup

The plugin has the following options:

**Site Section** You can choose between Site (front-end), Administrator (back-end) or Both. This determines which section of your site is protected by this Two Factor Authentication plugin.

We strongly recommend using Both. If you use Administrator (back-end) it is possible that an attacker might be able to crack your username and password and log in the front-end of your site. While more limited than the back-end, the front-end still gives the attacker the power to disable Two Factor Authentication therefore making it possible for them to subsequently log in to your site's back-end. On top of that the front-end does allow editing articles, modifying module configuration and possibly other actions which can be detrimental to the correct operation of your site. If your objective is strong security you need to use the `Both` setting for this option. This is how this plugin is configured by default.
