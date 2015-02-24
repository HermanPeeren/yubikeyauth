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

**WARNING** You MUST use a domain name which has been registered with a commercial domain name service OR localhost. Your browser will check the IP requesting the U2F authentication against the public DNS records. If these do not match (or if your browser thinks they do not match) it will result in an error message. In short, this means that if you're using custom domain names in your computer's hosts file you will not be able to use U2F. This is a security feature of the U2F standard, not a bug in the plugin.

## Initial setup

Before using this plugin, you need to enable it. Log in to the backend of your site as a Super User. Go to Extensions, Manage Plugins. Find the "Two Factor Authentication - FIDO U2F (Universal 2nd Factor)" plugin and enable it.

Make sure that the server and browser minimum requirements are met. If not, you will get an error message when setting up Two Factor Authentication for a user.

## Enabling U2F for a user

Edit your *existing* user account, in the backend or the frontend of your site. If you're setting up a new user account you need to first save the new user and then edit it again to enable Two Factor Authentication.

If there is no link provided by your site's manager to edit your user account you can visit a URL like `http://www.example.com/index.php?option=com_users&view=profile&layout=edit` where `http://www.example.com` is the URL to your site.

Click on the Two Factor Authentication tab. From the Authentication Method drop-down list select "FIDO U2F".

If you have already added another U2F key device, click on the "Add another U2F Key" button. This will display a new button titled "Add a U2F Key". If it's the first device you are adding you can already see the "Add a U2F Key" button. 

Click on the big, green "Add a U2F Key" button. Google Chrome will ask you if you want this site to identify you using security keys. Click on Allow. If your U2F device has an LED and a button it will start flashing. Touch the button. If it has a button but no LED just touch the button. If it has neither an LED nor a button it will be accepted automatically without further action necessary on your part. Afterwards the user profile page will be saved automatically, adding the U2F device to your user account.

If you wish to remove a key you have already defined check the box next to it under the Remove column. Then click on Save or Save & Close button in the toolbar to apply the change. Key removal is performed without any additional confirmation.

You can set up one or more U2F key devices per user account. Presenting any of these keys during the login is sufficient. This feature allows you to have backup U2F devices in case you lose / damage your main device.

## Logging in

Enter your username and password normally. Leave the Security Key empty. Click on the Log In button. The login area will now display a message that you need to insert your U2F key. Insert your U2F key device in a USB port.

If your U2F device has an LED and a button it will start flashing. Touch the button. If it has a button but no LED just touch the button. If it has neither an LED nor a button it will be accepted automatically without further action necessary on your part. 

As long as you used a U2F device previously added in your user account the log in will proceed normally. If the U2F device was not registered with your user account or if there was a man-in-the-middle attack the login will NOT proceed and result in an error message.  

## What if I am locked out?

Normally, after you have set up Two Factor Authentication, there are up to ten Emergency One Time Passwords generated for you. Note them down and keep them in a safe place. If you inadvertently lock yourself out of your site due to Two Factor Authentication failure you can enter one of these one time passwords in the Secret Key field of the login form. This will log you in to your site, allowing you to disable Two Factor Authentication. The emergency one time password you have used is immediately "burned" and you can no longer use it.

If you have ran out of emergency passwords, or you do not have access to them, you need to perform a manual procedure for disabling Two Factor Authentication on your site. Connect to your site via FTP, SFTP or your hosting account's file manager. Go inside the `plugins` folder of your site. Find the `twofactorauth` folder and go inside it. Find the `u2f` folder and rename it to `u2f.bak`. You can now log in to your site without using Two Factor Authentication. Edit your user account and set the Two Factor Authentication to None. Click on Save & Close. Now rename the `u2f.bak` folder back to `u2f`. You can now try re-enabling the Two Factor Authentication for your user account.

## Advanced setup

The plugin has the following options:

**Site Section** You can choose between Site (front-end), Administrator (back-end) or Both. This determines which section of your site is protected by this Two Factor Authentication plugin.

We strongly recommend using Both. If you use Administrator (back-end) it is possible that an attacker might be able to crack your username and password and log in the front-end of your site. While more limited than the back-end, the front-end still gives the attacker the power to disable Two Factor Authentication therefore making it possible for them to subsequently log in to your site's back-end. On top of that the front-end does allow editing articles, modifying module configuration and possibly other actions which can be detrimental to the correct operation of your site. If your objective is strong security you need to use the `Both` setting for this option. This is how this plugin is configured by default.
