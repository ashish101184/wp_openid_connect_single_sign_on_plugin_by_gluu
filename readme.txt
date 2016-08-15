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
This plugin enables Login. Allow your visitors to choose from their favourite authenticate login apps to login and optionally auto-register with your website or blog.

One-click login to your WordPress site using authenticate login applications like U2F Fido token, Google+, Duo, OxPush, Basic.

= Easy Integration =
Easy integration with your website with options to add SSO login on login page, registration page and comments section. Add `OpenID Connect By Gluu - OpenID Connect Single Sign On` widget to add authenticate login in widget area. Add shortcode [gluu_login shape="oval" theme="default" space="5" size="40"] to add login in other places.

= Beautiful customizations =
Customize login icons using a range of UI options like shape, size and theme. Choose from the following shapes - square, circle and rounded.

= Single sign-on =
Single Sign-On using login creates a single authentication system for multiple web properties allowing users to navigate websites with a single account.

All other authentication applications are supported through in website https://support.gluu.org.

= Features - =

*	Clean and easy to use WordPress admin UI
*	Support for SHORTCODE for login .
*   Choose where to add the login icons: login page, registration page, comment form or anywhere on your site using our OpenID Connect Single Sign On widget/ shortcode.
*	Select from a range beautiful designs of login buttons/icons.
*	Preview customization of selected login applications in WordPress admin panel.
*	Customize login buttons to match your website's theme.
*	One-click login to your website using any authentication login app.
*	Login to authentication applications - U2F Fido token, Google+, Duo, OxPush, Gluu Basic.
*	Optional automatic user registration after login if the user is not already registered with your site.
*	Assign universal role to users registering through login
*	Variety of troubleshooting topics in plugin.
*	**Support** using website https://support.gluu.org.

= Website =
*   **Gluu server site :** https://www.gluu.org
*   **Oxd server site :** https://oxd.gluu.org
*   **Documentation :** https://oxd.gluu.org/docs/plugin/wordpress/
*   **Support :** https://support.gluu.org

== Installation ==

= From your WordPress dashboard =
1. Visit `Plugins > Add New`.
2. Search for `OpenID Connect Single Sign On (SSO) Plugin By Gluu`. Find and Install `OpenID Connect Single Sign On (SSO) Plugin By Gluu`.
3. Activate the plugin from your Plugins page.

= From WordPress.org =
1. Download OpenID Connect Single Sign On (SSO) Plugin By Gluu.
2. Unzip and upload the `wp_openid_connect_single_sign_on_plugin_by_gluu` directory to your `/wp-content/plugins/` directory.
3. Activate OpenID Connect Single Sign On (SSO) Plugin By Gluu from your Plugins page.

= Once Activated =
Read documentation step by step.
Documentation : https://oxd.gluu.org/docs/plugin/wordpress/

== Frequently Asked Questions ==

= I need login with other SSO apps like U2F Fido token, Google+, Duo, OxPush, Gluu Basic etc. ? =
Please visit to support website https://support.gluu.org.

= I want to add SSO to a custom location in my page. How can I achieve that? =
To add login icons to a custom location, use a Shortcode. For further details refer to Shortcode tab in the plugin.

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