<?php

/**
 * Plugin Name: OpenID Connect Single Sign On (SSO) Plugin By Gluu
 * Plugin URI: https://oxd.gluu.org
 * Description: Use OpenID Connect to login by leveraging the oxd client service demon.
 * Version: 2.4.4
 * Author: Vlad Karapetyan
 * Author URI: https://github.com/dollar007
 * License: GPL3
 */
define( 'GLUU_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require GLUU_PLUGIN_PATH.'gluu_openid_oxd_settings_page.php';
require GLUU_PLUGIN_PATH.'/class-oxd-openid-login-widget.php';
require GLUU_PLUGIN_PATH.'/oxd-rp/RegisterSite.php';
require GLUU_PLUGIN_PATH.'/oxd-rp/UpdateSiteRegistration.php';

class gluu_OpenID_OXD {

	function __construct() {
		add_action( 'wp_logout', array( $this,'gluu_oxd_openid_end_session') );
		add_action( 'admin_menu', array( $this, 'gluu_openid_menu' ) );
		add_action( 'admin_init',  array( $this, 'gluu_openid_save_settings' ) );
		add_action( 'plugins_loaded',  array( $this, 'gluu_oxd_login_widget_text_domain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'gluu_oxd_openid_plugin_settings_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'gluu_oxd_openid_plugin_settings_script' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'gluu_oxd_openid_plugin_settings_style' ) ,5);
		register_deactivation_hook(__FILE__, array( $this, 'gluu_oxd_openid_deactivate'));
		register_uninstall_hook( __FILE__, array( $this, 'gluu_oxd_openid_uninstall'));

		// add social login icons to default login form
		if(get_option('gluu_oxd_openid_default_login_enable') == 1){
			add_action( 'login_form', array($this, 'gluu_oxd_openid_add_gluu_login') );
			add_action( 'login_enqueue_scripts', array( $this, 'gluu_oxd_custom_login_stylesheet' ) );
		}
		// add social login icons to default registration form
		if(get_option('gluu_oxd_openid_default_register_enable') == 1){

			add_action( 'register_form', array($this, 'gluu_oxd_openid_add_gluu_login') );
		}
		//add shortcode
		add_shortcode( 'gluu_login', array($this, 'gluu_oxd_get_output') );
		// add social login icons to comment form
		if(get_option('gluu_oxd_openid_default_comment_enable') == 1 ){
			add_action('comment_form_must_log_in_after', array($this, 'gluu_oxd_openid_add_gluu_login'));
			add_action('comment_form_top', array($this, 'gluu_oxd_openid_add_gluu_login'));
		}
		//add social login to woocommerce
		if(get_option('gluu_oxd_openid_woocommerce_login_form') == 1){
			add_action( 'woocommerce_login_form', array($this, 'gluu_oxd_openid_add_gluu_login'));
		}
		if(get_option('gluu_oxd_openid_logout_redirection_enable') == 0){
			remove_filter( 'logout_url', 'oxd_openid_redirect_after_logout');
		}
		//custom avatar
		add_filter( 'get_avatar', array( $this, 'gluu_oxd_gluu_login_custom_avatar' ), 10, 5 );
		remove_action( 'admin_notices', array( $this, 'gluu_oxd_openid_success_message') );
		remove_action( 'admin_notices', array( $this, 'gluu_oxd_openid_error_message') );
		//set default values
		add_option('gluu_oxd_openid_login_redirect', 'same' );
		add_option('gluu_oxd_openid_login_theme', 'oval' );
		add_option('gluu_oxd_openid_default_login_enable', '1');
		add_option('gluu_oxd_openid_login_widget_customize_text', 'Connect with:' );
		add_option('gluu_oxd_openid_login_button_customize_text', 'Login with' );
		add_option('gluu_oxd_login_icon_custom_size','40');
		add_option('gluu_oxd_login_icon_space','5');
		add_option('gluu_oxd_login_icon_custom_width','200');
		add_option('gluu_oxd_login_icon_custom_height','40');
		add_option('gluu_oxd_openid_login_custom_theme', 'default' );
		add_option('gluu_oxd_login_icon_custom_color', '2B41FF' );
		add_option('gluu_oxd_openid_logout_redirection_enable', '0' );
		add_option('gluu_oxd_openid_logout_redirect', 'currentpage' );
		add_option('gluu_oxd_openid_auto_register_enable', '1');
		add_option('gluu_oxd_openid_register_disabled_message', 'Registration is disabled for this website. Please contact the administrator for any queries.' );
		add_option('gluu_oxdOpenId_gluu_login_avatar','1');
		add_option('gluu_oxdOpenId_user_attributes','0');
		add_option('gluu_oxd_openid_scops',array("openid", "profile","email","address", "clientinfo", "mobile_phone", "phone"));
		$custom_scripts = array(
				array('name'=>'Google','image'=>plugins_url( 'includes/images/icons/google.png', __FILE__ ),'value'=>'gplus'),
				array('name'=>'Basic','image'=>plugins_url( 'includes/images/icons/basic.png', __FILE__ ),'value'=>'basic'),
				array('name'=>'Duo','image'=>plugins_url( 'includes/images/icons/duo.png', __FILE__ ),'value'=>'duo'),
				array('name'=>'U2F token','image'=>plugins_url( 'includes/images/icons/U2F.png', __FILE__ ),'value'=>'u2f'),
				array('name'=>'OxPush2','image'=>plugins_url( 'includes/images/icons/oxpush2.png', __FILE__ ),'value'=>'oxpush2')
		);
		add_option('gluu_oxd_openid_custom_scripts',$custom_scripts);
	}
	function gluu_oxd_openid_activating() {

		add_action( 'admin_menu', array( $this, 'gluu_openid_menu' ) );
		add_action( 'admin_init',  array( $this, 'gluu_openid_save_settings' ) );
		add_action( 'plugins_loaded',  array( $this, 'gluu_oxd_login_widget_text_domain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'gluu_oxd_openid_plugin_settings_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'gluu_oxd_openid_plugin_settings_script' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'gluu_oxd_openid_plugin_settings_style' ) ,5);
		register_deactivation_hook(__FILE__, array( $this, 'gluu_oxd_openid_deactivate'));
		register_activation_hook( __FILE__, array( $this, 'gluu_oxd_openid_activate' ) );
		// add social login icons to default login form
		if(get_option('gluu_oxd_openid_default_login_enable') == 1){
			add_action( 'login_form', array($this, 'gluu_oxd_openid_add_gluu_login') );
			add_action( 'login_enqueue_scripts', array( $this, 'gluu_oxd_custom_login_stylesheet' ) );
		}
		// add social login icons to default registration form
		if(get_option('gluu_oxd_openid_default_register_enable') == 1){

			add_action( 'register_form', array($this, 'gluu_oxd_openid_add_gluu_login') );
		}

		//add shortcode
		add_shortcode( 'gluu_login', array($this, 'gluu_oxd_get_output') );
		// add social login icons to comment form
		if(get_option('gluu_oxd_openid_default_comment_enable') == 1 ){
			add_action('comment_form_must_log_in_after', array($this, 'gluu_oxd_openid_add_gluu_login'));
			add_action('comment_form_top', array($this, 'gluu_oxd_openid_add_gluu_login'));
		}
		//add social login to woocommerce
		if(get_option('gluu_oxd_openid_woocommerce_login_form') == 1){
			add_action( 'woocommerce_login_form', array($this, 'gluu_oxd_openid_add_gluu_login'));
		}
		if(get_option('gluu_oxd_openid_logout_redirection_enable') == 0){
			remove_filter( 'logout_url', 'oxd_openid_redirect_after_logout');
		}
		$config_option = array(
				"op_host" => '',
				"oxd_host_ip" => '127.0.0.1',
				"oxd_host_port" =>8099,
				"authorization_redirect_uri" => wp_login_url().'?option=oxdOpenId',
				"logout_redirect_uri" => site_url().'/index.php?option=allLogout',
				"scope" => [ "openid", "profile","email","address", "clientinfo", "mobile_phone", "phone"],
				"application_type" => "web",
				"response_types" => ["code"],
				"grant_types" =>["authorization_code"],
				"acr_values" => [],
				"am_host" =>""
		);
		add_option( 'gluu_oxd_config', $config_option );
		//custom avatar
		add_filter( 'get_avatar', array( $this, 'gluu_oxd_gluu_login_custom_avatar' ), 10, 5 );
		remove_action( 'admin_notices', array( $this, 'gluu_oxd_openid_success_message') );
		remove_action( 'admin_notices', array( $this, 'gluu_oxd_openid_error_message') );
		//set default values
		add_option('gluu_oxd_openid_login_redirect', 'same' );
		add_option('gluu_oxd_openid_login_theme', 'oval' );
		add_option('gluu_gluu_oxd_openid_default_login_enable', '1');
		add_option('gluu_oxd_openid_login_widget_customize_text', 'Connect with:' );
		add_option('gluu_oxd_openid_login_button_customize_text', 'Login with' );
		add_option('gluu_oxd_login_icon_custom_size','40');
		add_option('gluu_oxd_login_icon_space','5');
		add_option('gluu_oxd_login_icon_custom_width','200');
		add_option('gluu_oxd_login_icon_custom_height','40');
		add_option('gluu_oxd_openid_login_custom_theme', 'default' );
		add_option('gluu_oxd_login_icon_custom_color', '2B41FF' );
		add_option('gluu_oxd_openid_logout_redirection_enable', '0' );
		add_option('gluu_oxd_openid_logout_redirect', 'currentpage' );
		add_option('gluu_oxd_openid_auto_register_enable', '1');
		add_option('gluu_oxd_openid_register_disabled_message', 'Registration is disabled for this website. Please contact the administrator for any queries.' );
		add_option('gluu_oxdOpenId_gluu_login_avatar','1');
		add_option('gluu_oxdOpenId_user_attributes','0');
		add_option('gluu_oxd_openid_scops',array("openid", "profile","email","address", "clientinfo", "mobile_phone", "phone"));
		$custom_scripts = array(
			array('name'=>'Google','image'=>plugins_url( 'includes/images/icons/google.png', __FILE__ ),'value'=>'gplus'),
			array('name'=>'Basic','image'=>plugins_url( 'includes/images/icons/basic.png', __FILE__ ),'value'=>'basic'),
			array('name'=>'Duo','image'=>plugins_url( 'includes/images/icons/duo.png', __FILE__ ),'value'=>'duo'),
			array('name'=>'U2F token','image'=>plugins_url( 'includes/images/icons/U2F.png', __FILE__ ),'value'=>'u2f'),
			array('name'=>'OxPush2','image'=>plugins_url( 'includes/images/icons/oxpush2.png', __FILE__ ),'value'=>'oxpush2')
		);
		add_option('gluu_oxd_openid_custom_scripts',$custom_scripts);
	}
	function gluu_oxd_openid_deactivate() {
		$conf = get_option('gluu_oxd_config');
		$custom_scripts = get_option('gluu_oxd_openid_custom_scripts');
		foreach($custom_scripts as $custom_script){
			delete_option('gluu_oxd_openid_'.$custom_script['value'].'_enable');
		}
		delete_option('gluu_oxd_openid_new_registration');
		delete_option('gluu_oxd_openid_default_register_enable');
		delete_option('gluu_oxd_openid_default_comment_enable');
		delete_option('gluu_oxd_openid_woocommerce_login_form');
		delete_option('gluu_oxd_openid_login_redirect_url');
		delete_option('gluu_oxd_openid_logout_redirect_url');
		delete_option('gluu_oxd_openid_message');
		delete_option('gluu_oxd_openid_login_redirect');
		delete_option('gluu_oxd_openid_login_theme' );
		delete_option('gluu_gluu_oxd_openid_default_login_enable');
		delete_option('gluu_oxd_openid_login_widget_customize_text' );
		delete_option('gluu_oxd_openid_login_button_customize_text' );
		delete_option('gluu_oxd_login_icon_custom_size');
		delete_option('gluu_oxd_login_icon_space');
		delete_option('gluu_oxd_login_icon_custom_width');
		delete_option('gluu_oxd_login_icon_custom_height');
		delete_option('gluu_oxd_openid_login_custom_theme' );
		delete_option('gluu_oxd_login_icon_custom_color' );
		delete_option('gluu_oxd_openid_logout_redirection_enable');
		delete_option('gluu_oxd_openid_logout_redirect');
		delete_option('gluu_oxd_openid_auto_register_enable');
		delete_option('gluu_oxd_openid_register_disabled_message');
		delete_option('gluu_oxdOpenId_gluu_login_avatar');
		delete_option('gluu_oxdOpenId_user_attributes');
		delete_option('gluu_oxd_openid_scops');
		delete_option('gluu_oxd_openid_custom_scripts');
		delete_option('gluu_Oxd_Activated_Plugin');
		delete_option('gluu_oxd_openid_admin_email');
		delete_option('gluu_oxd_config');
		delete_option('gluu_oxd_id');
		delete_option('gluu_oxd_openid_message');
		delete_option('gluu_widget_oxd_openid_login_wid');

	}

	function gluu_oxd_openid_activate() {
		add_option('gluu_Oxd_Activated_Plugin','Plugin-Slug');


	}

	function gluu_oxd_openid_add_gluu_login(){
		if(!is_user_logged_in() && gluu_is_oxd_registered()){
			$oxd_login_widget = new gluu_oxd_openid_login_wid();
			$oxd_login_widget->openidloginForm();
		}
	}

	function gluu_oxd_custom_login_stylesheet(){
		wp_enqueue_style( 'oxd-wp-style',plugins_url('includes/css/oxd_openid_style.css?version=2.0', __FILE__), false );
		wp_enqueue_style( 'oxd-wp-bootstrap-social',plugins_url('includes/css/bootstrap-social.css', __FILE__), false );
		wp_enqueue_style( 'oxd-wp-bootstrap-main',plugins_url('includes/css/bootstrap.min.css', __FILE__), false );
		wp_enqueue_style( 'oxd-wp-font-awesome',plugins_url('includes/css/font-awesome.min.css', __FILE__), false );
		wp_enqueue_style( 'oxd-wp-font-awesome',plugins_url('includes/css/font-awesome.css', __FILE__), false );
	}

	function gluu_oxd_openid_plugin_settings_style() {
		wp_enqueue_style( 'oxd_openid_admin_settings_style', plugins_url('includes/css/oxd_openid_style.css?version=2.0', __FILE__));
		wp_enqueue_style( 'oxd_openid_admin_settings_phone_style', plugins_url('includes/css/phone.css', __FILE__));
		wp_enqueue_style( 'oxd-wp-bootstrap-social',plugins_url('includes/css/bootstrap-social.css', __FILE__), false );
		wp_enqueue_style( 'oxd-wp-bootstrap-main',plugins_url('includes/css/bootstrap.min-preview.css', __FILE__), false );
		wp_enqueue_style( 'oxd-wp-font-awesome',plugins_url('includes/css/font-awesome.min.css', __FILE__), false );
		wp_enqueue_style( 'oxd-wp-font-awesome',plugins_url('includes/css/font-awesome.css', __FILE__), false );
	}

	function gluu_oxd_openid_plugin_settings_script() {
		wp_enqueue_script( 'oxd_openid_admin_settings_phone_script', plugins_url('includes/js/phone.js', __FILE__ ));
		wp_enqueue_script( 'oxd_openid_admin_settings_color_script', plugins_url('includes/jscolor/jscolor.js', __FILE__ ));
		wp_enqueue_script( 'oxd_openid_admin_settings_script', plugins_url('includes/js/settings.js', __FILE__ ), array('jquery'));
		wp_enqueue_script( 'oxd_openid_admin_settings_phone_script', plugins_url('includes/js/bootstrap.min.js', __FILE__ ));
	}

	function gluu_oxd_openid_success_message() {
		$message = get_option('gluu_oxd_openid_message'); ?>
		<script>

			jQuery(document).ready(function() {
				var message = "<?php echo $message; ?>";
				jQuery('#oxd_openid_msgs').append("<div class='error notice is-dismissible oxd_openid_error_container'> <p class='oxd_openid_msgs'>" + message + "</p></div>");
			});
		</script>
	<?php }

	function gluu_oxd_openid_error_message() {
		$message = get_option('gluu_oxd_openid_message'); ?>
		<script>
			jQuery(document).ready(function() {
				var message = "<?php echo $message; ?>";
				jQuery('#oxd_openid_msgs').append("<div class='updated notice is-dismissible oxd_openid_success_container'> <p class='oxd_openid_msgs'>" + message + "</p></div>");
			});
		</script>
	<?php }

	private function gluu_oxd_openid_show_success_message() {
		remove_action( 'admin_notices', array( $this, 'gluu_oxd_openid_success_message') );
		add_action( 'admin_notices', array( $this, 'gluu_oxd_openid_error_message') );
	}

	private function gluu_oxd_openid_show_error_message() {
		remove_action( 'admin_notices', array( $this, 'gluu_oxd_openid_error_message') );
		add_action( 'admin_notices', array( $this, 'gluu_oxd_openid_success_message') );
	}

	public function gluu_oxd_openid_check_empty_or_null( $value ) {
		if( ! isset( $value ) || empty( $value ) ) {
			return true;
		}
		return false;
	}

	function  gluu_oxd_login_widget_openid_options() {
		global $wpdb;
		gluu_oxd_register_openid();
	}

	function gluu_oxd_openid_activation_message() {
		$class = "updated";
		$message = get_option('gluu_oxd_openid_message');
		echo "<div class='" . $class . "'> <p>" . $message . "</p></div>";
	}

	function gluu_oxd_login_widget_text_domain(){
		load_plugin_textdomain('flw', FALSE, basename( dirname( __FILE__ ) ) .'/languages');
	}

	function gluu_openid_save_settings(){
		if ( current_user_can( 'manage_options' )) {
			if(isset($_POST['custom_nonce'])){
				if(wp_verify_nonce($_POST['custom_nonce'], 'validating-nonce-value')){
					if (is_admin() && get_option('gluu_Oxd_Activated_Plugin') == 'Plugin-Slug') {
						delete_option('gluu_Oxd_Activated_Plugin');
						update_option('gluu_oxd_openid_message', 'Go to plugin <b><a href="admin.php?page=oxd_openid_settings&tab=login">settings</a></b> to enable login by gluu.');
						add_action('admin_notices', array($this, 'gluu_oxd_openid_activation_message'));
					}
					if (isset($_POST['option']) and $_POST['option'] == "oxd_openid_connect_register_site_oxd") {

						if(!isset($_SERVER['HTTPS']) or $_SERVER['HTTPS'] != "on") {
							update_option('gluu_oxd_openid_message', 'OpenID Connect requires https. This plugin will not work if your website uses http only.');
							$this->gluu_oxd_openid_show_error_message();
							return;
						}
						if ($this->gluu_oxd_openid_check_empty_or_null($_POST['email']) || $this->gluu_oxd_openid_check_empty_or_null($_POST['oxd_host_port'])) {
							update_option('gluu_oxd_openid_message', 'All the fields are required. Please enter valid entries.');
							$this->gluu_oxd_openid_show_error_message();
							return;
						}else if ($this->gluu_oxd_openid_check_empty_or_null($_POST['gluu_server_url']) || $this->gluu_oxd_openid_check_empty_or_null($_POST['gluu_server_url'])) {
							update_option('gluu_oxd_openid_message', 'All the fields are required. Please enter Gluu server URL.');
							$this->gluu_oxd_openid_show_error_message();
							return;
						} else if (!$_POST['users_can_register']) {
							update_option('gluu_oxd_openid_message', 'Need to choose anyone can register checkbox.');
							$this->gluu_oxd_openid_show_error_message();
							return;
						} else if (intval($_POST['oxd_host_port']) > 65535 && intval($_POST['oxd_host_port']) < 0) {

							update_option('gluu_oxd_openid_message', 'Enter your oxd host port (Min. number 0, Max. number 65535)');
							$this->gluu_oxd_openid_show_error_message();
							return;
						} else if (intval($_POST['oxd_host_port']) > 65535 && intval($_POST['oxd_host_port']) < 0) {

							update_option('gluu_oxd_openid_message', 'Enter your oxd host port (Min. number 0, Max. number 65535)');
							$this->gluu_oxd_openid_show_error_message();
							return;
						} else if (!is_email($_POST['email'])) {
							update_option('gluu_oxd_openid_message', 'Please match the format of Email. No special characters are allowed.');
							$this->gluu_oxd_openid_show_error_message();
							return;
						} else if (filter_var(sanitize_text_field($_POST['gluu_server_url']), FILTER_VALIDATE_URL) === false) {
							update_option('gluu_oxd_openid_message', 'Please enter valid URL.');
							$this->gluu_oxd_openid_show_error_message();
							return;
						}else {
							$email = sanitize_email($_POST['email']);
							update_option('gluu_oxd_openid_admin_email', $email);
							$oxd_host_port = intval($_POST['oxd_host_port']);
						}
						if (empty($_POST['users_can_register']) || !empty($_POST['users_can_register']) && trim($_POST['default_role']) != 1) {
							update_option('gluu_oxd_openid_message', '<strong>ERROR</strong>: Signup has been disabled. Only members of this site can comment.');
						} else {
							update_option('users_can_register', sanitize_text_field($_POST['users_can_register']));
						}
						if (empty($_POST['default_role']) || !empty($_POST['default_role']) && trim($_POST['default_role']) == '') {
							update_option('gluu_oxd_openid_message', '<strong>ERROR</strong>: You must include a role.');
						} else {
							update_option('default_role', wp_unslash($_POST['default_role']));
						}
						$config_option = array(
							"op_host" => sanitize_text_field($_POST['gluu_server_url']),
							"oxd_host_ip" => '127.0.0.1',
							"oxd_host_port" => $oxd_host_port,
							"authorization_redirect_uri" => wp_login_url() . '?option=oxdOpenId',
							"logout_redirect_uri" => site_url() . '/index.php?option=allLogout',
							"scope" => ["openid", "profile", "email", "address", "clientinfo", "mobile_phone", "phone"],
							"application_type" => "web",
							"response_types" => ["code"],
							"grant_types" => ["authorization_code"],
							"acr_values" => [],
							"am_host" => ""
						);
						update_option('gluu_oxd_config', $config_option);
						$register_site = new RegisterSite();
						$register_site->setRequestOpHost($config_option['op_host']);
						$register_site->setRequestAcrValues($config_option['acr_values']);
						$register_site->setRequestAuthorizationRedirectUri($config_option['authorization_redirect_uri']);
						$register_site->setRequestGrantTypes($config_option['grant_types']);
						$register_site->setRequestResponseTypes(['code']);
						$register_site->setRequestLogoutRedirectUri($config_option['logout_redirect_uri']);
						$register_site->setRequestContacts([$email]);
						$register_site->setRequestApplicationType('web');
						$register_site->setRequestClientLogoutUri($config_option['logout_redirect_uri']);
						$register_site->setRequestScope($config_option['scope']);
						$status = $register_site->request();
						if (!$status['status']) {
							update_option('gluu_oxd_openid_message', $status['message']);
							$this->gluu_oxd_openid_show_error_message();
							return;
						}
						if ($register_site->getResponseOxdId()) {
							if (get_option('gluu_oxd_id')) {
								update_option('gluu_oxd_id', $register_site->getResponseOxdId());
							} else {
								add_option('gluu_oxd_id', $register_site->getResponseOxdId());
							}
							update_option('gluu_oxd_openid_message', 'Your settings are saved successfully.');
							$this->gluu_oxd_openid_show_success_message();
						} else {
							update_option('gluu_oxd_openid_message', 'Gluu server url, oxd ip or oxd host is not a valid.');
							$this->gluu_oxd_openid_show_error_message();
						}
					}
					else if (isset($_POST['option']) and $_POST['option'] == "oxd_openid_config_info_hidden") {
						if (gluu_is_oxd_registered()) {
							$custom_scripts = get_option('gluu_oxd_openid_custom_scripts');
							foreach ($custom_scripts as $custom_script) {
								update_option('gluu_oxd_openid_' . $custom_script['value'] . '_enable', isset($_POST['oxd_openid_' . $custom_script['value'] . '_enable']) ? sanitize_text_field($_POST['oxd_openid_' . $custom_script['value'] . '_enable']) : 0);
							}
							$error = true;
							$error_array = array();
							$oxd_config = !empty(get_option('gluu_oxd_config')) ? get_option('gluu_oxd_config') : array();
							$oxd_config['response_types'] = !empty($_POST['response_types']) && isset($_POST['response_types']) ? sanitize_text_field($_POST['response_types']) : $oxd_config['response_types'];
							$oxd_config['scope'] = !empty($_POST['scope']) && isset($_POST['scope']) ? array_map( 'sanitize_text_field', wp_unslash($_POST['scope'])) : $oxd_config['scope'];
							update_option('gluu_oxd_config', $oxd_config);

							if (!empty($_POST['new_scope']) && isset($_POST['new_scope'])) {
								foreach (array_map( 'sanitize_text_field', wp_unslash($_POST['new_scope'])) as $scope) {
									if ($scope) {
										$get_scopes = get_option('gluu_oxd_openid_scops');
										array_push($get_scopes, $scope);
										update_option('gluu_oxd_openid_scops', $get_scopes);
									}
								}
							}
							if (!empty($_POST['delete_scope']) && isset($_POST['delete_scope'])) {
								$custom_scripts = get_option('gluu_oxd_openid_scops');
								$check = false;
								$up_cust_sc = array();
								foreach ($custom_scripts as $custom_script) {
									if ($custom_script == sanitize_text_field($_POST['delete_scope'])) {
										$check = true;
									} else {
										array_push($up_cust_sc, $custom_script);
									}
								}
								update_option('gluu_oxd_openid_scops', $up_cust_sc);
								if ($check) {
									echo 1;
									exit;
								} else {
									echo 0;
									exit;
								}
							}
							if (!empty($_POST['delete_value']) && isset($_POST['delete_value'])) {
								$custom_scripts = get_option('gluu_oxd_openid_custom_scripts');
								$check = false;
								$up_cust_sc = array();
								foreach ($custom_scripts as $custom_script) {
									if ($custom_script['value'] == sanitize_text_field($_POST['delete_value'])) {
										$check = true;
									} else {
										array_push($up_cust_sc, $custom_script);
									}
								}
								update_option('gluu_oxd_openid_custom_scripts', $up_cust_sc);
								if ($check) {
									echo 1;
									exit;
								} else {
									echo 0;
									exit;
								}
							}
							if (isset($_POST['count_scripts'])) {
								for ($i = 1; $i <= intval($_POST['count_scripts']); $i++) {
									if (isset($_POST['new_custom_script_name_' . $i]) && !empty($_POST['new_custom_script_name_' . $i]) && isset($_POST['new_custom_script_value_' . $i]) && !empty($_POST['new_custom_script_value_' . $i]) && isset($_POST['image_url_' . $i]) && !empty($_POST['image_url_' . $i])) {
										$custom_scripts = get_option('gluu_oxd_openid_custom_scripts');
										foreach ($custom_scripts as $custom_script) {
											if ($custom_script['value'] == sanitize_text_field($_POST['new_custom_script_value_' . $i]) || $custom_script['name'] == sanitize_text_field($_POST['new_custom_script_name_' . $i])) {
												$error = false;
												array_push($error_array, $i);
											}
										}
										if ($error) {
											array_push($custom_scripts, array('name' => sanitize_text_field($_POST['new_custom_script_name_' . $i]), 'image' => sanitize_text_field($_POST['image_url_' . $i]), 'value' => sanitize_text_field($_POST['new_custom_script_value_' . $i])));
											update_option('gluu_oxd_openid_custom_scripts', $custom_scripts);
										} else {
											update_option('gluu_oxd_openid_message', 'Name = ' . sanitize_text_field($_POST['new_custom_script_name_' . $i]) . ' or value = ' . sanitize_text_field($_POST['new_custom_script_value_' . $i]) . ' is exist.');
											$this->gluu_oxd_openid_show_error_message();
										}
									}
								}
							}
							if (!$error) {
								$error_message = '';
								foreach ($error_array as $error_a) {
									$error_message .= 'Name = ' . sanitize_text_field($_POST['new_custom_script_name_' . $error_a]) . ' or value = ' . sanitize_text_field($_POST['new_custom_script_value_' . $error_a]) . ' is exist.<br/>';
								}
								update_option('gluu_oxd_openid_message', $error_message);
								$this->gluu_oxd_openid_show_error_message();
							} else {
								$config_option = get_option( 'gluu_oxd_config');
								$update_site_registration = new UpdateSiteRegistration();
								$update_site_registration->setRequestOxdId(get_option('gluu_oxd_id'));
								$update_site_registration->setRequestAcrValues($config_option['acr_values']);
								$update_site_registration->setRequestAuthorizationRedirectUri($config_option['authorization_redirect_uri']);
								$update_site_registration->setRequestGrantTypes($config_option['grant_types']);
								$update_site_registration->setRequestResponseTypes(['code']);
								$update_site_registration->setRequestLogoutRedirectUri($config_option['logout_redirect_uri']);
								$update_site_registration->setRequestContacts([get_option( 'gluu_oxd_openid_admin_email')]);
								$update_site_registration->setRequestApplicationType('web');
								$update_site_registration->setRequestClientLogoutUri($config_option['logout_redirect_uri']);
								$update_site_registration->setRequestScope($config_option['scope']);
								$status = $update_site_registration->request();
								if(!$status['status']){
									update_option( 'gluu_oxd_openid_message', $status['message']);
									$this->gluu_oxd_openid_show_error_message();
									return;
								}
								if($update_site_registration->getResponseOxdId()){
									if(get_option('gluu_oxd_id')){
										update_option( 'gluu_oxd_id', $update_site_registration->getResponseOxdId() );
									}else{
										add_option( 'gluu_oxd_id', $update_site_registration->getResponseOxdId() );
									}
									$this->gluu_oxd_openid_show_success_message();
								}else{
									update_option( 'gluu_oxd_openid_message', 'Gluu server url, oxd ip or oxd host is not a valid.');
									$this->gluu_oxd_openid_show_error_message();
								}
								update_option( 'gluu_oxd_id', $update_site_registration->getResponseOxdId() );
								update_option( 'gluu_oxd_openid_message', 'Your settings are saved successfully.' );
								$this->gluu_oxd_openid_show_success_message();
							}
						} else {
							update_option('gluu_oxd_openid_message', 'Please register an account before trying to enable any app');
							$this->gluu_oxd_openid_show_error_message();
						}
					}
					else if (isset($_POST['option']) and $_POST['option'] == "oxd_openid_reset_config") {
						$this->gluu_oxd_openid_deactivate();
						$this->gluu_oxd_openid_activating();

					}
					else if (isset($_POST['option']) and $_POST['option'] == "oxd_openid_enable_apps") {
						if (gluu_is_oxd_registered()) {
							$custom_scripts = get_option('gluu_oxd_openid_custom_scripts');
							foreach ($custom_scripts as $custom_script) {
								update_option('gluu_oxd_openid_' . $custom_script['value'] . '_enable', isset($_POST['oxd_openid_' . $custom_script['value'] . '_enable']) ? intval($_POST['oxd_openid_' . $custom_script['value'] . '_enable']) : 0);
							}
							update_option('gluu_gluu_oxd_openid_default_login_enable', isset($_POST['oxd_openid_default_login_enable']) ? intval($_POST['oxd_openid_default_login_enable']) : 0);
							update_option('gluu_oxd_openid_default_register_enable', isset($_POST['oxd_openid_default_register_enable']) ? intval($_POST['oxd_openid_default_register_enable']) : 0);
							update_option('gluu_oxd_openid_default_comment_enable', isset($_POST['oxd_openid_default_comment_enable']) ? intval($_POST['oxd_openid_default_comment_enable']) : 0);
							update_option('gluu_oxd_openid_woocommerce_login_form', isset($_POST['oxd_openid_woocommerce_login_form']) ? intval($_POST['oxd_openid_woocommerce_login_form']) : 0);
							//Redirect URL
							update_option('gluu_oxd_openid_login_redirect', isset($_POST['oxd_openid_login_redirect']) ? intval($_POST['oxd_openid_login_redirect']) : 0);
							update_option('gluu_oxd_openid_login_redirect_url', isset($_POST['oxd_openid_login_redirect_url']) ? intval($_POST['oxd_openid_login_redirect_url']) : 0);
							//Logout Url
							update_option('gluu_oxd_openid_logout_redirection_enable', isset($_POST['oxd_openid_logout_redirection_enable']) ? intval($_POST['oxd_openid_logout_redirection_enable']) : 0);
							update_option('gluu_oxd_openid_logout_redirect', isset($_POST['oxd_openid_logout_redirect']) ? sanitize_text_field($_POST['oxd_openid_logout_redirect']) : 'currentpage');
							update_option('gluu_oxd_openid_logout_redirect_url', isset($_POST['oxd_openid_logout_redirect_url']) ? intval($_POST['oxd_openid_logout_redirect_url']) : 0);
							//auto register
							update_option('gluu_oxd_openid_auto_register_enable', isset($_POST['oxd_openid_auto_register_enable']) ? intval($_POST['oxd_openid_auto_register_enable']) : 0);
							update_option('gluu_oxd_openid_register_disabled_message', isset($_POST['oxd_openid_register_disabled_message']) ? sanitize_text_field($_POST['oxd_openid_register_disabled_message']) : 'Registration is disabled for this website. Please contact the administrator for any queries.');
							update_option('gluu_oxd_openid_login_widget_customize_text', isset($_POST['oxd_openid_login_widget_customize_text']) ? sanitize_text_field($_POST['oxd_openid_login_widget_customize_text']) : 'Connect with:');
							update_option('gluu_oxd_openid_login_button_customize_text', isset($_POST['oxd_openid_login_button_customize_text']) ? sanitize_text_field($_POST['oxd_openid_login_button_customize_text']) : 'Login with');
							update_option('gluu_oxd_openid_login_theme', isset($_POST['oxd_openid_login_theme']) ? sanitize_text_field($_POST['oxd_openid_login_theme']) : 'oval');
							update_option('gluu_oxd_openid_message', 'Your settings are saved successfully.');
							//customization of icons
							update_option('gluu_oxd_login_icon_custom_size', isset($_POST['oxd_login_icon_custom_size']) ? intval($_POST['oxd_login_icon_custom_size']) : 40);
							update_option('gluu_oxd_login_icon_space', isset($_POST['oxd_login_icon_space']) ? intval($_POST['oxd_login_icon_space']) : 5);
							update_option('gluu_oxd_login_icon_custom_width', isset($_POST['oxd_login_icon_custom_width']) ? intval($_POST['oxd_login_icon_custom_width']) : 200);
							update_option('gluu_oxd_login_icon_custom_height', isset($_POST['oxd_login_icon_custom_height']) ? intval($_POST['oxd_login_icon_custom_height']) : 40);
							update_option('gluu_oxd_openid_login_custom_theme', isset($_POST['oxd_openid_login_custom_theme']) ? sanitize_text_field($_POST['oxd_openid_login_custom_theme']) : 'default');
							update_option('gluu_oxd_login_icon_custom_color', isset($_POST['oxd_openid_login_custooxd_login_icon_custom_colorm_theme']) ? sanitize_text_field($_POST['oxd_login_icon_custom_color']) : '2B41FF');
							// avatar
							update_option('gluu_oxdOpenId_gluu_login_avatar', isset($_POST['oxdOpenId_gluu_login_avatar']) ? intval($_POST['oxdOpenId_gluu_login_avatar']) : 0);
							//Attribute collection
							update_option('gluu_oxdOpenId_user_attributes', isset($_POST['oxdOpenId_user_attributes']) ? intval($_POST['oxdOpenId_user_attributes']) : 0);
							$this->gluu_oxd_openid_show_success_message();
						} else {
							update_option('gluu_oxd_openid_message', 'Please register an account before trying to enable any app');
							$this->gluu_oxd_openid_show_error_message();
						}
					}
				}else{
					update_option('gluu_oxd_openid_message', 'Nonce not verified!');
					$this->gluu_oxd_openid_show_error_message();
				}

			}

		}
	}

	function gluu_openid_menu() {
		$page = add_menu_page( 'Gluu OpenID Settings ' . __( 'Configure OpenID', 'oxd_openid_settings' ), 'OpenID Connect Single Sign On (SSO) By Gluu', 'administrator',
				'oxd_openid_settings', array( $this, 'gluu_oxd_login_widget_openid_options' ),plugin_dir_url(__FILE__) . 'includes/images/gluu_icon.png');
	}

	public function gluu_oxd_get_output( $atts ){
		if(gluu_is_oxd_registered()){
			$gluu_widget = new gluu_oxd_openid_login_wid();
			$html = $gluu_widget->openidloginFormShortCode( $atts );
			return $html;
		}
	}

	function gluu_oxd_gluu_login_custom_avatar( $avatar, $mixed, $size, $default, $alt = '' ) {
		$user = false;

		if ( is_numeric( $mixed ) AND $mixed > 0 ) {
			$user_id = $mixed;
		} elseif ( is_string( $mixed ) AND ( $user = get_user_by( 'email', $mixed )) ) {
			$user_id = $user->ID;
		} elseif ( is_object( $mixed ) AND property_exists( $mixed, 'user_id' ) AND is_numeric( $mixed->user_id ) ) {
			$user_id = $mixed->user_id;
		} else {
			$user_id = null;
		}

		if (!empty( $user_id ) ) {
			$override_avatar = true;
			$user_meta_thumbnail = get_user_meta( $user_id, 'oxdOpenId_user_avatar', true );
			$user_meta_name = get_user_meta( $user_id, 'user_name', true );
			$user_picture = (!empty( $user_meta_thumbnail ) ? $user_meta_thumbnail : '');
			if ( $user_picture !== false AND strlen( trim( $user_picture ) ) > 0 ) {
				return '<img alt="' . $user_meta_name . '" src="' . $user_picture . '" class="avatar apsl-avatar-social-login avatar-' . $size . ' photo" height="' . $size . '" width="' . $size . '" />';
			}
		}
		return $avatar;
	}
	// And here goes the uninstallation function:
	function gluu_oxd_openid_uninstall(){
		//delete all stored key-value pairs
		delete_option('gluu_oxd_config');
		delete_option('gluu_oxd_id');
		delete_option('gluu_oxd_openid_new_registration');
		delete_option('gluu_oxd_openid_admin_email');
		delete_option('gluu_oxd_openid_message');
		foreach(get_option('gluu_oxd_openid_custom_scripts') as $custom_script){
			delete_option('gluu_oxd_openid_'.$custom_script['value'].'_enable');
		}
		delete_option('gluu_oxd_openid_default_login_enable');
		delete_option('gluu_oxd_openid_default_register_enable');
		delete_option('gluu_oxd_openid_default_comment_enable');
		delete_option('gluu_oxd_openid_woocommerce_login_form');
		delete_option('gluu_oxd_openid_login_redirect');
		delete_option('gluu_oxd_openid_login_redirect_url');
		delete_option('gluu_oxdOpenId_gluu_login_avatar');
		delete_option('gluu_oxdOpenId_user_attributes');
		delete_option('gluu_oxd_openid_login_theme' );
		delete_option('gluu_oxd_openid_login_button_customize_text');
		delete_option('gluu_oxd_login_icon_custom_size');
		delete_option('gluu_oxd_login_icon_space' );
		delete_option('gluu_oxd_login_icon_custom_width' );
		delete_option('gluu_oxd_login_icon_custom_height' );
		delete_option('gluu_oxd_openid_login_custom_theme' );
		delete_option('gluu_oxd_login_icon_custom_color');
		delete_option('gluu_oxd_openid_message');
		delete_option('gluu_oxd_openid_logout_redirect');
		delete_option('gluu_oxd_openid_logout_redirection_enable');
		delete_option('gluu_oxd_openid_logout_redirect_url');
		delete_option('gluu_oxd_openid_scops');
	}
	function gluu_oxd_openid_end_session() {
		session_start();

		$config_option = get_option( 'gluu_oxd_config' );
		if(!empty($_SESSION['user_oxd_id_token'])){
			if(get_option('gluu_oxd_id') && $_SESSION['user_oxd_id_token']){
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
					if(exec('netstat -aon |find/i "listening" |find "'.$config_option['oxd_host_port'].'"')){
						$logout = new Logout();
						$logout->setRequestOxdId(get_option('gluu_oxd_id'));
						$logout->setRequestIdToken($_COOKIE['user_oxd_id_token']);
						$logout->setRequestPostLogoutRedirectUri($config_option['logout_redirect_uri']);
						$logout->setRequestSessionState($_COOKIE['session_states']);
						$logout->setRequestState($_COOKIE['states']);
						$logout->request();
						echo '<script>
						var delete_cookie = function(name) {
							document.cookie = name + \'=;expires=Thu, 01 Jan 1970 00:00:01 GMT;\';
						};
						delete_cookie(\'user_oxd_access_token\');
						delete_cookie(\'user_oxd_id_token\');
						delete_cookie(\'session_states\');
						delete_cookie(\'states\');
					</script>';
						unset($_SESSION['user_oxd_access_token']);
						unset($_SESSION['user_oxd_id_token']);
						unset($_SESSION['session_states']);
						unset($_SESSION['states']);

						unset($_COOKIE['user_oxd_access_token']);
						unset($_COOKIE['user_oxd_id_token']);
						unset($_COOKIE['session_states']);
						unset($_COOKIE['states']);
						wp_redirect( $logout->getResponseObject()->data->uri );
						exit;
					}
				} else {
					if(exec('netstat -tulpn | grep :'.$config_option['oxd_host_port'])){
						$logout = new Logout();
						$logout->setRequestOxdId(get_option('gluu_oxd_id'));
						$logout->setRequestIdToken($_COOKIE['user_oxd_id_token']);
						$logout->setRequestPostLogoutRedirectUri($config_option['logout_redirect_uri']);
						$logout->setRequestSessionState($_COOKIE['session_states']);
						$logout->setRequestState($_COOKIE['states']);
						$logout->request();
						echo '<script>
						var delete_cookie = function(name) {
							document.cookie = name + \'=;expires=Thu, 01 Jan 1970 00:00:01 GMT;\';
						};
						delete_cookie(\'user_oxd_access_token\');
						delete_cookie(\'user_oxd_id_token\');
						delete_cookie(\'session_states\');
						delete_cookie(\'states\');
					</script>';
						unset($_SESSION['user_oxd_access_token']);
						unset($_SESSION['user_oxd_id_token']);
						unset($_SESSION['session_states']);
						unset($_SESSION['states']);
						unset($_COOKIE['user_oxd_access_token']);
						unset($_COOKIE['user_oxd_id_token']);
						unset($_COOKIE['session_states']);
						unset($_COOKIE['states']);
						wp_redirect( $logout->getResponseObject()->data->uri );
						exit;
					}
				}

			}

		}

	}
}

new gluu_OpenID_OXD;

?>