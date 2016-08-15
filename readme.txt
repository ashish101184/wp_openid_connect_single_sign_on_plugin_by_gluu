=== OpenID Connect Single Sign On (SSO) Plugin By Gluu ===
Contributors: vladkarapetyan1988, dollar007, nynymike, willow9886
Donate link: https://www.gluu.org/deploy/
Tags: shortcodes, widgets, google plus login, u2f token, fido login, gluu basic login, gluu, duo, oauth, oxpush, auto user registration, auto-login, autologin, openid connect, single sign-on, social authentication,social sign-in, SSO technology
Requires at least: 2.0.2
Tested up to: 4.5
Stable tag: 2.4.4
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin will enable you to authenticate users against any standard OpenID Connect Provider.

== Description ==
= OpenID Connect Single Sign On (SSO) Plugin By Gluu =
This plugin will enable you to authenticate users against any standard OpenID Connect Provider. You’ll need to also run a local oxd OpenID Connect client service. The oxd middleware service is easy to install, and makes it easier to keep up-to-date with the latest security fixes for OAuth2. There are oxd plugins, modules and extennsions for many popular platforms and frameworks like: Wordpress, Magento, OpenCart, SugarCRM, SuiteCRM, Drupal, Roundcube, Spring Framework, Play Framework, Ruby on Rails and Python Flask. Using this plugin, you’ll be able to request a certain type of authentication using the OpenID Connect “acr” parameter. You may want to use strong two-factor authentication (2FA), or social login to Google, Facebook or other popular sites. Also supported is Super Gluu--a free mobile two factor authentication app. If you are also looking for a modern access management platform, you should consider the Gluu Server Community Edition. The Gluu Server includes an OpenID Connect Provider that will enable you to create local accounts for people in your domain, and to manage single sign-on (SSO) across your websites.

= Login =
Allow visitors to choose from a list of available authentication mechanisms and optionally enable just-in-time registration.  If you are using the Gluu Server as your OpenID Provider, you can use easily enable social login or two factor authentication.

= Easy Integration =
You can enable authentication from the login page, registration page or comments section by adding our widget, or use a shortcode such as [gluu_login shape="oval" theme="default" space="5" size="40"] to add a login button in other places.

= Beautiful customizations =
Customize login icons using a range of UI options like shape, size and theme. Choose from the following shapes - square, circle and rounded.

= Single sign-on =
There are Gluu oxd plugins for many popular applications and Web frameworks, like Magento, OpenCart, Drupal, SugarCRM, SuiteCRM, Ruby on Rails, Java Spring, and the Java Play Framework. If you have a custom application written in Php, Python, Java, Node, Ruby or C#, you can also use one of the easy oxd libraries. 

= Features - =

*	Super easy to use WordPress admin UI
*	SHORTCODE support.
*       SSO with other applications using a centralized OpenID Connect provider like the Gluu Server.
*	Use social login or two factor authentication (2FA) with the Gluu Server OpenID Connect Provider. Out of the box support for Yubikey Fido U2F tokens, Google, Duo, or SuperGluu
*	Optional dynamic user registration after login—creates account if the user is not already registered with your site.
*	Assign universal role to users registering through login
*       Choose where to add the login icons: login page, registration page, comment form or anywhere on your site using our OpenID Connect Single Sign On widget / shortcode.
*	Select from a range of beautiful designs of login buttons / icons.
*	Preview customization of selected login applications in WordPress admin panel.
*	Customize login buttons to match your website's theme.
*	One-click login to your website using any authentication login app.
*	Variety of troubleshooting topics in plugin.

= Website =
*   **Gluu server site :** https://www.gluu.org
*   **Oxd server site :** https://oxd.gluu.org
*   **Documentation :** https://oxd.gluu.org/docs/plugin/wordpress/
*   **Support :** https://support.gluu.org

== Installation ==

= From your WordPress dashboard =
1. Visit `Plugins > Add New`.
2. Search for and install `OpenID Connect Single Sign On (SSO) Plugin By Gluu`.
3. Activate the plugin from your Plugins page.

= From WordPress.org =
1. Download OpenID Connect Single Sign On (SSO) Plugin By Gluu.
2. Unzip and upload the `wp_openid_connect_single_sign_on_plugin_by_gluu` directory to your `/wp-content/plugins/` directory.
3. Activate OpenID Connect Single Sign On (SSO) Plugin By Gluu from your Plugins page.

= Once Activated =
Read documentation step by step.

== Frequently Asked Questions ==

= I need SSO across several websites. How do I do it? 
You’ll need two things: (1) a central OpenID Connect Provider that holds the passwords and user information; (2) websites that use the OpenID Connect protocol to authenticate users. An easy way to accomplish the first-- install and configure the free open source Gluu Server using the Linux packages for Centos, Ubuntu, Debian or Red Hat. The second is accomplished by installing the oxd service on each web server that needs SSO. This provides easy to use local API’s that can be called by your web applications, and enables you to use a number of plugins for popular open source software packages. 

= Can I use this plugin for social login? 
Currently the Gluu Server supports Google authentication. In the next release, we’ll be supporting a new social login module called Passportjs. This will enable you to use over 300 social login sites, including Facebook or Twitter. Stay tuned! 

= Can I use this plugin for two factor authentication? 
In this plugin you can specify a value for “acr,” which provides the OpenID Connect provider with a hint about what kind of authentication to use. The Gluu Server ships with several built in two factor authentication mechanisms. Two that are very easy to use are FIDO U2F tokens (like Yubikey) and Duo Security. Gluu also has published a free mobile two factor authentication app for iOS and Android called Super Gluu. If you’re a geek, you can write your own custom authentication script in the Gluu Server, and implement support for any kind of strong authentication technology. 

= Can I use Google or Microsoft Azure Active Directory as my OpenID Connect Provider?
Probably, but Google and Microsoft do not support dynamic client registration. If you are successful with this, please let us know! It should work.

= Is this plugin free? 
The plugin is free, but the oxd software is commercially licensed, and has both free and paid features. In a nutshell, if you are authenticating less than two people per second, you can use the free version. If you have a high volume website, and need more throughput, please purchase a reasonably priced commercial license on http://oxd.gluu.org

= Can I purchase support for the Gluu Server or oxd? 
Yes, for information on paid support, visit our website http://gluu.org

== Screenshots ==

1. General.
2. Oxd id.
3. OpenID Connect Configuration (Scopes).
4. OpenID Connect Configuration (Custom scripts).
5. Wordpress Configuration.
6. Show icons in frontend.


== Changelog ==

= 2.4.4 =
* Added gluu server url section (op_host).
* Removed redirect_uris and added prompt => login
* Stable version, supported by Gluu Inc.
* Working with gluu and oxd servers version 2.4.4

= 2.4.3 =
* Added update site registration after saving openid configuration.
* Not stable, not supported.
* Working with gluu and oxd servers version 2.4.3

= 2.4.2 =
* First version of OpenID Connect Single Sign On (SSO) Plugin By Gluu.
* Not stable, not supported.
* Working with gluu and oxd servers version 2.4.2

== Upgrade Notice ==
= 2.4.4 =
* Added gluu server url section (op_host).
* Removed redirect_uris and added prompt => login
* Stable version, supported by Gluu Inc.
* Working with gluu and oxd servers version 2.4.4

= 2.4.3 =
* Added update site registration after saving openid configuration.
* Not stable, not supported.
* Working with gluu and oxd servers version 2.4.3

= 2.4.2 =
* First version of OpenID Connect Single Sign On (SSO) Plugin By Gluu.
* Not stable, not supported.
* Working with gluu and oxd servers version 2.4.2
