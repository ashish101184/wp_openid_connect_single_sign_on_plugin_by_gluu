<?php

/**
 * Plugin Name: OpenID Connect Single Sign-On (SSO) Plugin By Gluu
 * Plugin URI: https://oxd.gluu.org/docs/plugin/wordpress/
 * Description: Use OpenID Connect to login by leveraging the oxd client service demon.
 * Version: 2.4.4
 * Author: Gluu
 * Author URI: https://github.com/GluuFederation/wp_openid_connect_single_sign_on_plugin_by_gluu
 * License: GPL3
 */

define( 'GLUU_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require GLUU_PLUGIN_PATH.'gluu_openid_oxd_settings_page.php';
require GLUU_PLUGIN_PATH.'/oxd-rp/RegisterSite.php';
require GLUU_PLUGIN_PATH.'/oxd-rp/UpdateSiteRegistration.php';
require GLUU_PLUGIN_PATH.'/oxd-rp/GetAuthorizationUrl.php';
require GLUU_PLUGIN_PATH.'/oxd-rp/GetTokensByCode.php';
require GLUU_PLUGIN_PATH.'/oxd-rp/GetUserInfo.php';
require GLUU_PLUGIN_PATH.'/oxd-rp/Logout.php';

class gluu_OpenID_OXD {

	function __construct() {
		add_action( 'wp_logout', array( $this,'gluu_oxd_openid_end_session') );
		add_action( 'admin_menu', array( $this, 'gluu_openid_menu' ) );
		add_action( 'admin_init',  array( $this, 'gluu_openid_save_settings' ) );
		add_action( 'plugins_loaded',  array( $this, 'gluu_oxd_login_widget_text_domain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'gluu_oxd_openid_plugin_settings_style' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'gluu_oxd_openid_plugin_settings_style' ) ,5);
		register_deactivation_hook(__FILE__, array( $this, 'gluu_oxd_openid_deactivate'));
		add_option('gluu_auth_type', 'default');
		add_option('gluu_custom_url', site_url());
		add_option('gluu_send_user_check', 1);
		register_uninstall_hook( __FILE__, array( $this, 'gluu_oxd_openid_uninstall'));

		//add shortcode
		add_shortcode( 'gluu_login', array($this, 'gluu_oxd_get_output') );
		//custom avatar
		add_filter( 'get_avatar', array( $this, 'gluu_oxd_gluu_login_custom_avatar' ), 10, 5 );
		remove_action( 'admin_notices', array( $this, 'gluu_oxd_openid_success_message') );
		remove_action( 'admin_notices', array( $this, 'gluu_oxd_openid_error_message') );
		//set default values

		add_option('gluu_oxd_openid_scops',array("openid", "profile","email"));
		add_option('gluu_oxd_openid_custom_scripts',array('none'));
	}
	function gluu_oxd_openid_activating() {

		add_action( 'admin_menu', array( $this, 'gluu_openid_menu' ) );
		add_action( 'admin_init',  array( $this, 'gluu_openid_save_settings' ) );
		add_action( 'plugins_loaded',  array( $this, 'gluu_oxd_login_widget_text_domain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'gluu_oxd_openid_plugin_settings_style' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'gluu_oxd_openid_plugin_settings_style' ) ,5);
		add_option('gluu_auth_type', 'default');
		add_option('gluu_send_user_check', 1);
		add_option('gluu_custom_url', site_url());
		register_deactivation_hook(__FILE__, array( $this, 'gluu_oxd_openid_deactivate'));
		register_activation_hook( __FILE__, array( $this, 'gluu_oxd_openid_activate' ) );
		//add shortcode
		add_shortcode( 'gluu_login', array($this, 'gluu_oxd_get_output') );
		$config_option = array(
			"oxd_host_port" =>8099,
			"authorization_redirect_uri" => site_url().'/index.php?option=oxdOpenId',
			"logout_redirect_uri" => site_url().'/index.php?option=allLogout',
			"scope" => [ "openid", "profile","email"],
			"application_type" => "web",
			"redirect_uris" => [ site_url().'/index.php?option=oxdOpenId' ],
			"response_types" => ["code"],
			"grant_types" =>["authorization_code"],
			"acr_values" => []
		);
		add_option( 'gluu_oxd_config', $config_option );
		//custom avatar
		add_filter( 'get_avatar', array( $this, 'gluu_oxd_gluu_login_custom_avatar' ), 10, 5 );
		remove_action( 'admin_notices', array( $this, 'gluu_oxd_openid_success_message') );
		remove_action( 'admin_notices', array( $this, 'gluu_oxd_openid_error_message') );
		//set default values
		add_option('gluu_oxd_openid_scops',array("openid", "profile","email"));

		add_option('gluu_oxd_openid_custom_scripts',array('none'));
	}
	function gluu_oxd_openid_deactivate() {
		delete_option('gluu_oxd_config');
		delete_option('gluu_oxd_id');
		delete_option('gluu_oxd_openid_new_registration');
		delete_option('gluu_oxd_openid_admin_email');
		delete_option('gluu_oxd_openid_message');
		delete_option('gluu_oxd_openid_scops');
		delete_option('gluu_auth_type');
		delete_option('gluu_send_user_check');
		delete_option('gluu_custom_url');
		delete_option('gluu_op_host');
		delete_option('gluu_redirect_url');
		delete_option('gluu_oxd_openid_custom_scripts');
		delete_option('gluu_Oxd_Activated_Plugin');
	}
	function gluu_oxd_openid_activate() {
		add_option('gluu_Oxd_Activated_Plugin','Plugin-Slug');
	}
	function gluu_oxd_custom_login_stylesheet(){
		wp_enqueue_style( 'oxd-wp-style',plugins_url('includes/css/oxd_openid_style.css?version=2.0', __FILE__), false );
	}
	function gluu_oxd_openid_plugin_settings_style() {
		wp_enqueue_style( 'oxd_openid_admin_settings_style', plugins_url('includes/css/oxd_openid_style.css?version=2.0', __FILE__));
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
					$current_user = wp_get_current_user();
					$oxd_host_port = 0;
					if (is_admin() && get_option('gluu_Oxd_Activated_Plugin') == 'Plugin-Slug') {
						delete_option('gluu_Oxd_Activated_Plugin');
						update_option('gluu_oxd_openid_message', 'Go to plugin <b><a href="admin.php?page=oxd_openid_settings&tab=login">settings</a></b> to enable authentication.');
						add_action('admin_notices', array($this, 'gluu_oxd_openid_activation_message'));
					}
					if (isset($_POST['option']) and $_POST['option'] == "oxd_openid_connect_register_site_oxd") {
						if (isset($_POST['cancel']) and $_POST['cancel'] == "cancel") {
							$this->gluu_oxd_openid_deactivate();
							$this->gluu_oxd_openid_activating();
							return;
						}
						if(!isset($_SERVER['HTTPS']) or $_SERVER['HTTPS'] != "on") {
							update_option('gluu_oxd_openid_message', 'OpenID Connect requires https. This plugin will not work if your website uses http only.');
							$this->gluu_oxd_openid_show_error_message();
							return;
						}
						if ($this->gluu_oxd_openid_check_empty_or_null($_POST['oxd_host_port'])) {
							update_option('gluu_oxd_openid_message', 'All the fields are required. Please enter valid entries.');
							$this->gluu_oxd_openid_show_error_message();
							return;
						}
						else if (!$_POST['users_can_register']) {
							update_option('gluu_oxd_openid_message', 'Need to choose "Automatically register new users" checkbox.');
							$this->gluu_oxd_openid_show_error_message();
							return;
						}
						else if (intval($_POST['oxd_host_port']) > 65535 && intval($_POST['oxd_host_port']) < 0) {

							update_option('gluu_oxd_openid_message', 'Enter your oxd host port (Min. number 0, Max. number 65535)');
							$this->gluu_oxd_openid_show_error_message();
							return;
						}
						else if  (!empty($_POST['gluu_server_url'])) {
							if (filter_var(sanitize_text_field($_POST['gluu_server_url']), FILTER_VALIDATE_URL) === false) {
								update_option('gluu_oxd_openid_message', 'Please enter valid URL.');
								$this->gluu_oxd_openid_show_error_message();
								return;
							}
						}
						else if  (!empty($_POST['gluu_custom_url'])) {
							if (filter_var(sanitize_text_field($_POST['gluu_custom_url']), FILTER_VALIDATE_URL) === false) {
								update_option('gluu_oxd_openid_message', 'Please enter valid custom URI.');
								$this->gluu_oxd_openid_show_error_message();
								return;
							}
						}
						if (isset($_POST['gluu_server_url']) and !empty($_POST['gluu_server_url'])) {
							update_option('gluu_server_url', sanitize_text_field($_POST['gluu_server_url']));
						}
						$oxd_host_port = intval($_POST['oxd_host_port']);
						if (empty($_POST['users_can_register']) || !empty($_POST['users_can_register']) && trim($_POST['default_role']) != 1) {
							update_option('gluu_oxd_openid_message', '<strong>ERROR</strong>: Signup has been disabled. Only members of this site can comment.');
						}
						else {
							update_option('users_can_register', sanitize_text_field($_POST['users_can_register']));
						}
						if (empty($_POST['default_role']) || !empty($_POST['default_role']) && trim($_POST['default_role']) == '') {
							update_option('gluu_oxd_openid_message', '<strong>ERROR</strong>: You must include a role.');
						}
						else {
							update_option('default_role', wp_unslash($_POST['default_role']));
						}
						if (isset($_POST['gluu_server_url']) and !empty($_POST['gluu_server_url'])) {
							$json = file_get_contents(sanitize_text_field($_POST['gluu_server_url']).'/.well-known/openid-configuration');
							$obj = json_decode($json);
							if(!empty($obj->userinfo_endpoint)){
								if(empty($obj->registration_endpoint)){
									update_option('gluu_oxd_openid_message', "Please enter your client_id and client_secret.");
									update_option('gluu_redirect_url', site_url()."/index.php?option=oxdOpenId");
									$config_option = get_option('gluu_oxd_config');
									$config_option['oxd_host_port'] = $oxd_host_port;
									update_option('gluu_oxd_config', $config_option);
									update_option('gluu_op_host', sanitize_text_field($_POST['gluu_server_url']));
									if(isset($_POST['gluu_client_id']) and !empty(sanitize_text_field($_POST['gluu_client_id'])) and
										isset($_POST['gluu_client_secret']) and !empty(sanitize_text_field($_POST['gluu_client_secret']))){
										$config_option = array(
											"oxd_host_port" => $oxd_host_port,
											"gluu_client_id" => sanitize_text_field($_POST['gluu_client_id']),
											"gluu_client_secret" => sanitize_text_field($_POST['gluu_client_secret']),
											"authorization_redirect_uri" => site_url() . '/index.php?option=oxdOpenId',
											"logout_redirect_uri" => site_url() . '/index.php?option=allLogout',
											"scope" => ["openid", "profile","email"],
											"application_type" => "web",
											"response_types" => ["code"],
											"grant_types" => ["authorization_code"],
											"acr_values" => []
										);
										update_option('gluu_oxd_config', $config_option);
										update_option('gluu_oxd_openid_scops', $obj->scopes_supported);
										if(!empty($obj->acr_values_supported)){
											update_option('gluu_oxd_openid_custom_scripts', $obj->acr_values_supported);

										}
										$register_site = new RegisterSite();
										$register_site->setRequestOpHost(get_option('gluu_op_host'));
										$register_site->setRequestAcrValues($config_option['acr_values']);
										$register_site->setRequestAuthorizationRedirectUri($config_option['authorization_redirect_uri']);
										$register_site->setRequestGrantTypes($config_option['grant_types']);
										$register_site->setRequestResponseTypes(['code']);
										$register_site->setRequestLogoutRedirectUri($config_option['logout_redirect_uri']);
										$register_site->setRequestContacts([$current_user->user_email]);
										$register_site->setRequestApplicationType('web');
										$register_site->setRequestClientLogoutUri($config_option['logout_redirect_uri']);
										if($obj->scopes_supported){
											update_option('gluu_oxd_openid_scops', $obj->scopes_supported);
											$register_site->setRequestScope($obj->scopes_supported);
										}else{
											$register_site->setRequestScope($config_option['scope']);
										}
										$register_site->setRequestClientId($config_option['gluu_client_id']);
										$register_site->setRequestClientSecret($config_option['gluu_client_secret']);
										$status = $register_site->request();
										if ($status['message'] == 'invalid_op_host') {
											update_option('gluu_oxd_openid_message', 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json');
											$this->gluu_oxd_openid_show_error_message();
											return;
										}
										if (!$status['status']) {
											update_option('gluu_oxd_openid_message', 'Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.');
											$this->gluu_oxd_openid_show_error_message();
											return;
										}
										if ($status['message'] == 'internal_error') {
											update_option('gluu_oxd_openid_message', 'ERROR: '.$status['error_message']);
											$this->gluu_oxd_openid_show_error_message();
											return;
										}
										if ($register_site->getResponseOxdId()) {
											update_option('gluu_oxd_id', $register_site->getResponseOxdId());
											update_option('gluu_op_host', $register_site->getResponseOpHost());
											update_option('gluu_oxd_openid_message', 'Your settings are saved successfully.');
											$_SESSION['openid_success_reg'] = 'success.';
											$this->gluu_oxd_openid_show_success_message();
											return;
										} else {
											update_option('gluu_oxd_openid_message', 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json');
											$this->gluu_oxd_openid_show_error_message();
											return;
										}
									}
									else{
										$_SESSION['openid_error'] = 'Error505.';
										$this->gluu_oxd_openid_show_success_message();
										return;
									}
								}
								else{
									$config_option = array();
									$config_option = array(
										"oxd_host_port" => $oxd_host_port,
										"authorization_redirect_uri" => site_url() . '/index.php?option=oxdOpenId',
										"logout_redirect_uri" => site_url() . '/index.php?option=allLogout',
										"application_type" => "web",
										"scope" => ["openid", "profile","email"],
										"response_types" => ["code"],
										"grant_types" => ["authorization_code"],
										"acr_values" => []
									);

									update_option('gluu_oxd_config', $config_option);
									$obj = null;
									if (!empty(get_option('gluu_op_host'))) {
										$json = file_get_contents(get_option('gluu_op_host') . '/.well-known/openid-configuration');
										$obj = json_decode($json);
										update_option('gluu_oxd_openid_scops', $obj->scopes_supported);
									}
									$register_site = new RegisterSite();
									$register_site->setRequestOpHost(sanitize_text_field($_POST['gluu_server_url']));
									$register_site->setRequestAcrValues($config_option['acr_values']);
									$register_site->setRequestAuthorizationRedirectUri($config_option['authorization_redirect_uri']);
									$register_site->setRequestGrantTypes($config_option['grant_types']);
									$register_site->setRequestResponseTypes(['code']);
									$register_site->setRequestLogoutRedirectUri($config_option['logout_redirect_uri']);
									$register_site->setRequestContacts([$current_user->user_email]);
									$register_site->setRequestApplicationType('web');
									$register_site->setRequestClientLogoutUri($config_option['logout_redirect_uri']);
									if($obj->scopes_supported){
										update_option('gluu_oxd_openid_scops', $obj->scopes_supported);
										$register_site->setRequestScope($obj->scopes_supported);
									}else{
										$register_site->setRequestScope($config_option['scope']);
									}
									$status = $register_site->request();
									if ($status['message'] == 'invalid_op_host') {
										update_option('gluu_oxd_openid_message', 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json');
										$this->gluu_oxd_openid_show_error_message();
										return;
									}
									if (!$status['status']) {
										update_option('gluu_oxd_openid_message', 'Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.');
										$this->gluu_oxd_openid_show_error_message();
										return;
									}
									if ($status['message'] == 'internal_error') {
										update_option('gluu_oxd_openid_message', 'ERROR: '.$status['error_message']);
										$this->gluu_oxd_openid_show_error_message();
										return;
									}
									if ($register_site->getResponseOxdId()) {
										update_option('gluu_oxd_id', $register_site->getResponseOxdId());
										update_option('gluu_op_host', $register_site->getResponseOpHost());

										$json = file_get_contents(get_option('gluu_op_host').'/.well-known/openid-configuration');
										$obj = json_decode($json);
										update_option('gluu_oxd_openid_scops', $obj->scopes_supported);
										if(!empty($obj->acr_values_supported)){
											update_option('gluu_oxd_openid_custom_scripts', $obj->acr_values_supported);

										}
										$config_option = array(
											"oxd_host_port" => $oxd_host_port,
											"authorization_redirect_uri" => site_url() . '/index.php?option=oxdOpenId',
											"logout_redirect_uri" => site_url() . '/index.php?option=allLogout',
											"scope" => ["openid", "profile","email"],
											"application_type" => "web",
											"response_types" => ["code"],
											"grant_types" => ["authorization_code"],
											"acr_values" => []
										);
										update_option('gluu_oxd_config', $config_option);
										update_option('gluu_oxd_openid_message', 'Your settings are saved successfully.');
										$_SESSION['openid_success_reg'] = 'success.';
										$this->gluu_oxd_openid_show_success_message();
									} else {
										update_option('gluu_oxd_openid_message', 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json');
										$this->gluu_oxd_openid_show_error_message();
									}
								}
							}
							else{
								update_option('gluu_oxd_openid_message', 'Please enter correct URI of the OpenID Connect Provider');
								$this->gluu_oxd_openid_show_error_message();
								return;
							}
						}
						else{
							$config_option = array(
								"oxd_host_port" => $oxd_host_port,
								"authorization_redirect_uri" => site_url() . '/index.php?option=oxdOpenId',
								"logout_redirect_uri" => site_url() . '/index.php?option=allLogout',
								"application_type" => "web",
								"response_types" => ["code"],
								"scope" => ["openid", "profile","email"],
								"grant_types" => ["authorization_code"],
								"acr_values" => []
							);
							update_option('gluu_oxd_config', $config_option);

							$register_site = new RegisterSite();
							$register_site->setRequestAcrValues($config_option['acr_values']);
							$register_site->setRequestAuthorizationRedirectUri($config_option['authorization_redirect_uri']);
							$register_site->setRequestGrantTypes($config_option['grant_types']);
							$register_site->setRequestResponseTypes(['code']);
							$register_site->setRequestLogoutRedirectUri($config_option['logout_redirect_uri']);
							$register_site->setRequestContacts([$current_user->user_email]);
							$register_site->setRequestApplicationType('web');
							$register_site->setRequestClientLogoutUri($config_option['logout_redirect_uri']);
							$register_site->setRequestScope($config_option['scope']);
							$status = $register_site->request();

							if ($status['message'] == 'invalid_op_host') {
								update_option('gluu_oxd_openid_message', 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json');
								$this->gluu_oxd_openid_show_error_message();
								return;
							}
							if (!$status['status']) {
								update_option('gluu_oxd_openid_message', 'Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.');
								$this->gluu_oxd_openid_show_error_message();
								return;
							}
							if ($status['message'] == 'internal_error') {
								update_option('gluu_oxd_openid_message', 'ERROR: '.$status['error_message']);
								$this->gluu_oxd_openid_show_error_message();
								return;
							}
							if ($register_site->getResponseOxdId()) {
								update_option('gluu_oxd_id', $register_site->getResponseOxdId());
								update_option('gluu_op_host', $register_site->getResponseOpHost());

								$json = file_get_contents(get_option('gluu_op_host').'/.well-known/openid-configuration');
								$obj = json_decode($json);
								update_option('gluu_oxd_openid_scops', $obj->scopes_supported);
								if(!empty($obj->acr_values_supported)){
									update_option('gluu_oxd_openid_custom_scripts', $obj->acr_values_supported);

								}
								$config_option = array(
									"oxd_host_port" => $oxd_host_port,
									"authorization_redirect_uri" => site_url() . '/index.php?option=oxdOpenId',
									"logout_redirect_uri" => site_url() . '/index.php?option=allLogout',
									"scope" => ["openid", "profile","email"],
									"application_type" => "web",
									"response_types" => ["code"],
									"grant_types" => ["authorization_code"],
									"acr_values" => []
								);
								update_option('gluu_oxd_config', $config_option);
								update_option('gluu_oxd_openid_message', 'Your settings are saved successfully.');
								$_SESSION['openid_success_reg'] = 'success.';
								$this->gluu_oxd_openid_show_success_message();
							} else {
								update_option('gluu_oxd_openid_message', 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json');
								$this->gluu_oxd_openid_show_error_message();
							}
						}
					}
					else if (isset($_POST['option']) and $_POST['option'] == "oxd_openid_edit_config") {
						$config_option['gluu_client_id'] = '';
						$config_option['gluu_client_secret'] = '';
						update_option('gluu_oxd_id', '');
						if(!empty($_POST['gluu_server_url'])){
							update_option('gluu_op_host', sanitize_text_field($_POST['gluu_server_url']));
						}
						else if  (!empty($_POST['gluu_custom_url'])) {
							if (filter_var(sanitize_text_field($_POST['gluu_custom_url']), FILTER_VALIDATE_URL) === false) {
								update_option('gluu_oxd_openid_message', 'Please enter valid custom URI.');
								$this->gluu_oxd_openid_show_error_message();
								return;
							}
						}
						if (isset($_POST['gluu_custom_url']) and !empty($_POST['gluu_custom_url'])) {
							update_option('gluu_custom_url', sanitize_text_field($_POST['gluu_custom_url']));
						}
						update_option('gluu_oxd_openid_scops',array("openid", "profile","email"));
						update_option('gluu_oxd_openid_custom_scripts',array('none'));
						update_option('gluu_oxd_config', $config_option);
						if(!isset($_SERVER['HTTPS']) or $_SERVER['HTTPS'] != "on") {
							update_option('gluu_oxd_openid_message', 'OpenID Connect requires https. This plugin will not work if your website uses http only.');
							$this->gluu_oxd_openid_show_error_message();
							return;
						}
						if ($this->gluu_oxd_openid_check_empty_or_null($_POST['oxd_host_port'])) {
							update_option('gluu_oxd_openid_message', 'All the fields are required. Please enter valid entries.');
							$this->gluu_oxd_openid_show_error_message();
							return;
						}
						else if (!$_POST['users_can_register']) {
							update_option('gluu_oxd_openid_message', 'Need to choose "Automatically register new users" checkbox.');
							$this->gluu_oxd_openid_show_error_message();
							return;
						}
						else if (intval($_POST['oxd_host_port']) > 65535 && intval($_POST['oxd_host_port']) < 0) {

							update_option('gluu_oxd_openid_message', 'Enter your oxd host port (Min. number 0, Max. number 65535)');
							$this->gluu_oxd_openid_show_error_message();
							return;
						}

						$oxd_host_port = intval($_POST['oxd_host_port']);
						$config_option = array(
							"oxd_host_port" => intval($_POST['oxd_host_port']),
							"authorization_redirect_uri" => site_url() . '/index.php?option=oxdOpenId',
							"logout_redirect_uri" => site_url() . '/index.php?option=allLogout',
							"scope" => ["openid", "profile","email"],
							"application_type" => "web",
							"response_types" => ["code"],
							"grant_types" => ["authorization_code"],
							"acr_values" => []
						);
						update_option('gluu_oxd_config', $config_option);
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

						if (!empty(get_option('gluu_op_host'))) {
							$json = file_get_contents(get_option('gluu_op_host').'/.well-known/openid-configuration');
							$obj = json_decode($json);
							if(!empty($obj->userinfo_endpoint)){
								if(empty($obj->registration_endpoint)){
									update_option('gluu_oxd_openid_message', "Please enter your client_id and client_secret.");
									update_option('gluu_redirect_url', site_url()."/index.php?option=oxdOpenId");
									$config_option = get_option('gluu_oxd_config');
									update_option('gluu_op_host', sanitize_text_field(get_option('gluu_op_host')));
									$config_option['oxd_host_port'] = $oxd_host_port;
									update_option('gluu_oxd_config', $config_option);
									if(isset($_POST['gluu_client_id']) and !empty(sanitize_text_field($_POST['gluu_client_id'])) and
										isset($_POST['gluu_client_secret']) and !empty(sanitize_text_field($_POST['gluu_client_secret'])) and !$obj->registration_endpoint){
										$config_option = array(
											"oxd_host_port" => $oxd_host_port,
											"gluu_client_id" => sanitize_text_field($_POST['gluu_client_id']),
											"gluu_client_secret" => sanitize_text_field($_POST['gluu_client_secret']),
											"authorization_redirect_uri" => site_url() . '/index.php?option=oxdOpenId',
											"logout_redirect_uri" => site_url() . '/index.php?option=allLogout',
											"scope" => ["openid", "profile","email"],
											"application_type" => "web",
											"response_types" => ["code"],
											"grant_types" => ["authorization_code"],
											"acr_values" => []
										);
										update_option('gluu_oxd_config', $config_option);
										update_option('gluu_oxd_openid_scops', $obj->scopes_supported);
										if(!empty($obj->acr_values_supported)){
											update_option('gluu_oxd_openid_custom_scripts', $obj->acr_values_supported);

										}
										$register_site = new RegisterSite();
										$register_site->setRequestOpHost(get_option('gluu_op_host'));
										$register_site->setRequestAcrValues($config_option['acr_values']);
										$register_site->setRequestAuthorizationRedirectUri($config_option['authorization_redirect_uri']);
										$register_site->setRequestGrantTypes($config_option['grant_types']);
										$register_site->setRequestResponseTypes(['code']);
										$register_site->setRequestLogoutRedirectUri($config_option['logout_redirect_uri']);
										$register_site->setRequestContacts([$current_user->user_email]);
										$register_site->setRequestApplicationType('web');
										$register_site->setRequestClientLogoutUri($config_option['logout_redirect_uri']);
										if($obj->scopes_supported){
											update_option('gluu_oxd_openid_scops', $obj->scopes_supported);
											$register_site->setRequestScope($obj->scopes_supported);
										}else{
											$register_site->setRequestScope($config_option['scope']);
										}
										$register_site->setRequestClientId($config_option['gluu_client_id']);
										$register_site->setRequestClientSecret($config_option['gluu_client_secret']);
										$status = $register_site->request();
										if ($status['message'] == 'invalid_op_host') {
											update_option('gluu_oxd_openid_message', 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json');
											$this->gluu_oxd_openid_show_error_message();
											return;
										}
										if (!$status['status']) {
											update_option('gluu_oxd_openid_message', 'Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.');
											$this->gluu_oxd_openid_show_error_message();
											return;
										}
										if ($status['message'] == 'internal_error') {
											update_option('gluu_oxd_openid_message', 'ERROR: '.$status['error_message']);
											$this->gluu_oxd_openid_show_error_message();
											return;
										}
										update_option('gluu_oxd_id', $register_site->getResponseOxdId());
										$_SESSION['openid_edit_success'] = 'edit success.';
										update_option('gluu_oxd_openid_message', 'Your settings are saved successfully.');
										$this->gluu_oxd_openid_show_success_message();
										return;
									}
									else{
										$_SESSION['openid_error_edit'] = 'Error506.';
										$this->gluu_oxd_openid_show_success_message();
										return;
									}

								}
							}
							else{
								$_SESSION['openid_error_edit'] = 'Error506.';
								update_option('gluu_oxd_openid_message', 'Please enter correct URI of the OpenID Connect Provider');
								$this->gluu_oxd_openid_show_error_message();
								return;
							}
						}
						$obj = null;
						if (!empty(get_option('gluu_op_host'))) {
							$json = file_get_contents(get_option('gluu_op_host') . '/.well-known/openid-configuration');
							$obj = json_decode($json);
							update_option('gluu_oxd_openid_scops', $obj->scopes_supported);
						}else{
							update_option('gluu_oxd_openid_scops', ["openid", "profile","email"]);
						}
						$config_option = array(
							"oxd_host_port" => intval($_POST['oxd_host_port']),
							"authorization_redirect_uri" => site_url() . '/index.php?option=oxdOpenId',
							"logout_redirect_uri" => site_url() . '/index.php?option=allLogout',
							"application_type" => "web",
							"response_types" => ["code"],
							"scope" => ["openid", "profile","email"],
							"grant_types" => ["authorization_code"],
							"acr_values" => []
						);
						update_option('gluu_oxd_config', $config_option);
						$obj = null;
						if (!empty(get_option('gluu_op_host'))) {
							$json = file_get_contents(get_option('gluu_op_host') . '/.well-known/openid-configuration');
							$obj = json_decode($json);
							update_option('gluu_oxd_openid_scops', $obj->scopes_supported);
						}else{
							update_option('gluu_oxd_openid_scops', ["openid", "profile","email"]);
						}
						$config_option = array(
							"oxd_host_port" => intval($_POST['oxd_host_port']),
							"authorization_redirect_uri" => site_url() . '/index.php?option=oxdOpenId',
							"logout_redirect_uri" => site_url() . '/index.php?option=allLogout',
							"application_type" => "web",
							"response_types" => ["code"],
							"scope" => ["openid", "profile","email"],
							"grant_types" => ["authorization_code"],
							"acr_values" => []
						);
						update_option('gluu_oxd_config', $config_option);

						$register_site = new RegisterSite();
						$register_site->setRequestOpHost(get_option('gluu_op_host'));
						$register_site->setRequestAcrValues($config_option['acr_values']);
						$register_site->setRequestAuthorizationRedirectUri($config_option['authorization_redirect_uri']);
						$register_site->setRequestGrantTypes($config_option['grant_types']);
						$register_site->setRequestResponseTypes(['code']);
						$register_site->setRequestLogoutRedirectUri($config_option['logout_redirect_uri']);
						$register_site->setRequestContacts([$current_user->user_email]);
						$register_site->setRequestApplicationType('web');
						$register_site->setRequestClientLogoutUri($config_option['logout_redirect_uri']);
						if($obj->scopes_supported){
							$register_site->setRequestScope($obj->scopes_supported);
						}else{
							$register_site->setRequestScope($config_option['scope']);
						}
						$status = $register_site->request();
						if ($status['message'] == 'invalid_op_host') {
							update_option('gluu_oxd_openid_message', 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json');
							$this->gluu_oxd_openid_show_error_message();
							return;
						}
						if (!$status['status']) {
							update_option('gluu_oxd_openid_message', 'Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.');
							$this->gluu_oxd_openid_show_error_message();
							return;
						}
						if ($status['message'] == 'internal_error') {
							update_option('gluu_oxd_openid_message', 'ERROR: '.$status['error_message']);
							$this->gluu_oxd_openid_show_error_message();
							return;
						}
						if ($register_site->getResponseOxdId()) {
							update_option('gluu_oxd_id', $register_site->getResponseOxdId());
							update_option('gluu_op_host', $register_site->getResponseOpHost());
							$json = file_get_contents(get_option('gluu_op_host').'/.well-known/openid-configuration');
							$obj = json_decode($json);
							update_option('gluu_oxd_openid_scops', $obj->scopes_supported);
							$config_option = array(
								"oxd_host_port" => $oxd_host_port,
								"authorization_redirect_uri" => site_url() . '/index.php?option=oxdOpenId',
								"logout_redirect_uri" => site_url() . '/index.php?option=allLogout',
								"scope" => ["openid", "profile","email"],
								"application_type" => "web",
								"response_types" => ["code"],
								"grant_types" => ["authorization_code"],
								"acr_values" => []
							);
							update_option('gluu_oxd_config', $config_option);
							if(!empty($obj->acr_values_supported)){
								update_option('gluu_oxd_openid_custom_scripts', $obj->acr_values_supported);

							}
							$_SESSION['openid_edit_success'] = 'edit success.';
							update_option('gluu_oxd_openid_message', 'Your settings are saved successfully.');
							$this->gluu_oxd_openid_show_success_message();
						}
						else {
							update_option('gluu_oxd_openid_message', 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json');
							$this->gluu_oxd_openid_show_error_message();
						}
					}
					else if (isset($_POST['option']) and $_POST['option'] == "oxd_openid_config_info_hidden") {
						if (gluu_is_oxd_registered()) {
							if(sanitize_text_field($_POST['send_user_type'])){
								update_option('gluu_auth_type', sanitize_text_field($_POST['send_user_type']));
							}else{
								update_option('gluu_auth_type', 'default');
							}
							update_option('gluu_send_user_check', sanitize_text_field($_POST['send_user_check']));
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
									if ($custom_script == sanitize_text_field($_POST['delete_value'])) {
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
									if ( isset($_POST['new_custom_script_value_' . $i]) && !empty($_POST['new_custom_script_value_' . $i])) {
										$custom_scripts = get_option('gluu_oxd_openid_custom_scripts');
										foreach ($custom_scripts as $custom_script) {
											if ($custom_script == sanitize_text_field($_POST['new_custom_script_value_' . $i])) {
												$error = false;
												array_push($error_array, $i);
											}
										}
										if ($error) {
											array_push($custom_scripts, sanitize_text_field($_POST['new_custom_script_value_' . $i]));
											update_option('gluu_oxd_openid_custom_scripts', $custom_scripts);
										} else {
											update_option('gluu_oxd_openid_message', 'ACR Value = ' . sanitize_text_field($_POST['new_custom_script_value_' . $i]) . ' is exist.');
											$this->gluu_oxd_openid_show_error_message();
										}
									}
								}
							}
							if (!$error) {
								$error_message = '';
								foreach ($error_array as $error_a) {
									$error_message .= 'ACR Value = ' . sanitize_text_field($_POST['new_custom_script_value_' . $error_a]) . ' is exist.<br/>';
								}
								update_option('gluu_oxd_openid_message', $error_message);
								$this->gluu_oxd_openid_show_error_message();
							} else {
								$config_option = get_option( 'gluu_oxd_config');
								$json = file_get_contents(get_option('gluu_op_host').'/.well-known/openid-configuration');
								$obj = json_decode($json);
								if(!empty($obj->registration_endpoint)) {
									$update_site_registration = new UpdateSiteRegistration();
									$update_site_registration->setRequestOxdId(get_option('gluu_oxd_id'));
									$update_site_registration->setRequestAcrValues($config_option['acr_values']);
									$update_site_registration->setRequestAuthorizationRedirectUri($config_option['authorization_redirect_uri']);
									$update_site_registration->setRequestGrantTypes($config_option['grant_types']);
									$update_site_registration->setRequestResponseTypes(['code']);
									$update_site_registration->setRequestLogoutRedirectUri($config_option['logout_redirect_uri']);
									$update_site_registration->setRequestContacts([$current_user->user_email]);
									$update_site_registration->setRequestApplicationType('web');
									$update_site_registration->setRequestClientLogoutUri($config_option['logout_redirect_uri']);
									$update_site_registration->setRequestScope($config_option['scope']);
									$status = $update_site_registration->request();
									if ($status['message'] == 'invalid_op_host') {
										update_option('gluu_oxd_openid_message', 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json');
										$this->gluu_oxd_openid_show_error_message();
										return;
									}
									if (!$status['status']) {
										update_option('gluu_oxd_openid_message', 'Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.');
										$this->gluu_oxd_openid_show_error_message();
										return;
									}
									if ($status['message'] == 'internal_error') {
										update_option('gluu_oxd_openid_message', 'ERROR: '.$status['error_message']);
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
										update_option( 'gluu_oxd_openid_message', 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json');
										$this->gluu_oxd_openid_show_error_message();
									}
									update_option( 'gluu_oxd_id', $update_site_registration->getResponseOxdId() );
								}
								update_option( 'gluu_oxd_openid_message', 'Your settings are saved successfully.' );
								$_SESSION['openid_success_reg'] = 'success.';
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
				}else{
					update_option('gluu_oxd_openid_message', 'Nonce not verified!');
					$this->gluu_oxd_openid_show_error_message();
				}

			}

		}
	}

	function gluu_openid_menu() {
		$page = add_menu_page( 'Gluu OpenID Settings ' . __( 'Configure OpenID', 'oxd_openid_settings' ), 'OpenID Connect', 'administrator',
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
		delete_option('gluu_oxd_openid_scops');
		delete_option('gluu_auth_type');
		delete_option('gluu_send_user_check');
		delete_option('gluu_custom_url');
		delete_option('gluu_op_host');
		delete_option('gluu_redirect_url');
		delete_option('gluu_oxd_openid_custom_scripts');
		delete_option('gluu_Oxd_Activated_Plugin');
	}
	function gluu_oxd_openid_end_session() {
		session_start();
		$config_option = get_option( 'gluu_oxd_config' );
		$json = file_get_contents(get_option('gluu_op_host').'/.well-known/openid-configuration');
		$obj = json_decode($json);
		if(time()<(int)$_SESSION['session_in_op']){
			if(!empty($obj->end_session_endpoint)){
				if(!empty($_SESSION['user_oxd_id_token'])){
					if(get_option('gluu_oxd_id') && $_SESSION['user_oxd_id_token'] && $_SESSION['session_in_op']){
						$logout = new Logout();
						$logout->setRequestOxdId(get_option('gluu_oxd_id'));
						$logout->setRequestIdToken($_SESSION['user_oxd_id_token']);
						$logout->setRequestPostLogoutRedirectUri($config_option['logout_redirect_uri']);
						$logout->setRequestSessionState($_SESSION['session_states']);
						$logout->setRequestState($_SESSION['states']);
						$logout->request();
						unset($_SESSION['user_oxd_access_token']);
						unset($_SESSION['user_oxd_id_token']);
						unset($_SESSION['session_states']);
						unset($_SESSION['states']);
						unset($_SESSION['session_in_op']);
						wp_redirect( $logout->getResponseObject()->data->uri );
						exit;

					}

				}
			}
		}else{
			unset($_SESSION['user_oxd_access_token']);
			unset($_SESSION['user_oxd_id_token']);
			unset($_SESSION['session_states']);
			unset($_SESSION['states']);
			unset($_SESSION['session_in_op']);
		}

		wp_redirect(site_url());
	}
}
new gluu_OpenID_OXD;

function gluu_oxd_openid_get_redirect_url() {
	$option = get_option( 'gluu_oxd_openid_login_redirect' );
	$redirect_url = site_url();
	if( $option == 'same' ) {
		if(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
			$http = "https://";
		} else {
			$http =  "http://";
		}
		$redirect_url = urldecode(html_entity_decode(esc_url($http . $_SERVER["HTTP_HOST"] . str_replace('option=oxdOpenId','',$_SERVER['REQUEST_URI']))));
		if(html_entity_decode(esc_url(remove_query_arg('ss_message', $redirect_url))) == wp_login_url() || strpos($_SERVER['REQUEST_URI'],'wp-login.php') !== FALSE || strpos($_SERVER['REQUEST_URI'],'wp-admin') !== FALSE){
			$redirect_url = site_url().'/';
		}
	} else if( $option == 'homepage' ) {
		$redirect_url = site_url();
	} else if( $option == 'dashboard' ) {
		$redirect_url = admin_url();
	} else if( $option == 'custom' ) {
		$redirect_url = get_option('gluu_oxd_openid_login_redirect_url');
	}

	return $redirect_url;
}

function gluu_oxd_openid_redirect_after_logout($logout_url) {
	if(get_option('gluu_oxd_openid_logout_redirection_enable')){
		$option = get_option( 'gluu_oxd_openid_logout_redirect' );
		$redirect_url = site_url();
		if( $option == 'homepage' ) {
			$redirect_url = $logout_url . '&redirect_to=' .home_url()  ;
		}
		else if($option == 'currentpage'){
			if(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
				$http = "https://";
			} else {
				$http =  "http://";
			}
			$redirect_url = $logout_url . '&redirect_to=' . $http . $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'];
		}
		else if($option == 'login') {
			$redirect_url = $logout_url . '&redirect_to=' . site_url() . '/wp-admin' ;
		}
		else if($option == 'custom') {
			$redirect_url = $logout_url . '&redirect_to=' . site_url() . (null !== get_option('gluu_oxd_openid_logout_redirect_url')?get_option('gluu_oxd_openid_logout_redirect_url'):'');
		}
		return $redirect_url;
	}else{
		return $logout_url;
	}
}

function gluu_my_login_redirect($prompt = null  ) {
	$config_option = get_option('gluu_oxd_config');
	$get_authorization_url = new GetAuthorizationUrl();
	$get_authorization_url->setRequestOxdId(get_option('gluu_oxd_id'));
	$get_authorization_url->setRequestScope($config_option['scope']);
	if(get_option('gluu_auth_type') != "default"){
		$get_authorization_url->setRequestAcrValues([get_option('gluu_auth_type')]);
	}else{
		$get_authorization_url->setRequestAcrValues(null);
	}


	if(!empty($prompt)){
		$get_authorization_url->setRequestPrompt($prompt);
	}
	$get_authorization_url->request();

	return $get_authorization_url->getResponseAuthorizationUrl();
}
function gluu_oxd_openid_logout_validate()
{
	//var_dump($_REQUEST);exit;
	if (isset($_REQUEST['option']) and strpos($_REQUEST['option'], 'allLogout') !== false && !isset($_REQUEST['state'])) {

		if(is_user_logged_in()){
			wp_destroy_current_session();
			wp_clear_auth_cookie();
			wp_logout();
		}

	}else if (isset($_REQUEST['option']) and strpos($_REQUEST['option'], 'allLogout') !== false ) {

		wp_redirect(get_option('gluu_custom_url'));exit;
	}
}
function gluu_oxd_openid_login_validate(){
	if(isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'oxdOpenId' ) !== false ){
		if(isset( $_REQUEST['error'] ) and strpos( $_REQUEST['error'], 'session_selection_required' ) !== false ){
			wp_redirect( gluu_my_login_redirect('login') );
			exit;
		}
		session_start();
		$http = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? "https://" : "http://";
		$parts = parse_url($http . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
		parse_str($parts['query'], $query);
		$config_option = get_option( 'gluu_oxd_config' );
		$conf = get_option('gluu_oxd_config');
		//var_dump($_REQUEST);exit;
		$get_tokens_by_code = new GetTokensByCode();
		$get_tokens_by_code->setRequestOxdId(get_option('gluu_oxd_id'));
		$get_tokens_by_code->setRequestCode($_REQUEST['code']);
		$get_tokens_by_code->setRequestState($_REQUEST['state']);
		$get_tokens_by_code->request();
		$get_tokens_by_code_array = $get_tokens_by_code->getResponseObject();
		$_SESSION['session_in_op']= $get_tokens_by_code->getResponseIdTokenClaims()->exp[0];
		$_SESSION['user_oxd_id_token']= $get_tokens_by_code->getResponseIdToken();
		$_SESSION['user_oxd_access_token']= $get_tokens_by_code->getResponseAccessToken();
		$_SESSION['session_states']= $_REQUEST['session_state'];
		$_SESSION['states']= $_REQUEST['state'];
		$get_user_info = new GetUserInfo();
		$get_user_info->setRequestOxdId(get_option('gluu_oxd_id'));
		$get_user_info->setRequestAccessToken($_SESSION['user_oxd_access_token']);
		$get_user_info->request();
		$get_user_info_array = $get_user_info->getResponseObject()->data->claims;
		/*echo '<pre>';
		echo($get_user_info_array->user_name[0]);exit;*/
		$reg_first_name = '';
		$reg_user_name = '';
		$reg_last_name = '';
		$reg_email = '';
		$reg_avatar = '';
		$reg_display_name = '';
		$reg_nikname = '';
		$reg_website = '';
		$reg_middle_name = '';
		$reg_country = '';
		$reg_city = '';
		$reg_region = '';
		$reg_gender = '';
		$reg_postal_code = '';
		$reg_fax = '';
		$reg_home_phone_number = '';
		$reg_phone_mobile_number = '';
		$reg_street_address = '';
		$reg_birthdate = '';
		if($get_user_info_array->website[0]){
			$reg_website = $get_user_info_array->website[0];
		}elseif($get_tokens_by_code_array->website[0]){
			$reg_website = $get_tokens_by_code_array->website[0];
		}
		if($get_user_info_array->nickname[0]){
			$reg_nikname = $get_user_info_array->nickname[0];
		}elseif($get_tokens_by_code_array->nickname[0]){
			$reg_nikname = $get_tokens_by_code_array->nickname[0];
		}
		if($get_user_info_array->name[0]){
			$reg_display_name = $get_user_info_array->name[0];
		}elseif($get_tokens_by_code_array->name[0]){
			$reg_display_name = $get_tokens_by_code_array->name[0];
		}
		if($get_user_info_array->given_name[0]){
			$reg_first_name = $get_user_info_array->given_name[0];
		}elseif($get_tokens_by_code_array->given_name[0]){
			$reg_first_name = $get_tokens_by_code_array->given_name[0];
		}
		if($get_user_info_array->family_name[0]){
			$reg_last_name = $get_user_info_array->family_name[0];
		}elseif($get_tokens_by_code_array->family_name[0]){
			$reg_last_name = $get_tokens_by_code_array->family_name[0];
		}
		if($get_user_info_array->middle_name[0]){
			$reg_middle_name = $get_user_info_array->middle_name[0];
		}elseif($get_tokens_by_code_array->middle_name[0]){
			$reg_middle_name = $get_tokens_by_code_array->middle_name[0];
		}
		if($get_user_info_array->email[0]){
			$reg_email = $get_user_info_array->email[0];
		}elseif($get_tokens_by_code_array->email[0]){
			$reg_email = $get_tokens_by_code_array->email[0];
		}
		if($get_user_info_array->country[0]){
			$reg_country = $get_user_info_array->country[0];
		}elseif($get_tokens_by_code_array->country[0]){
			$reg_country = $get_tokens_by_code_array->country[0];
		}
		if($get_user_info_array->gender[0]){
			if($get_user_info_array->gender[0] == 'male'){
				$reg_gender = '1';
			}else{
				$reg_gender = '2';
			}

		}elseif($get_tokens_by_code_array->gender[0]){
			if($get_tokens_by_code_array->gender[0] == 'male'){
				$reg_gender = '1';
			}else{
				$reg_gender = '2';
			}
		}
		if($get_user_info_array->locality[0]){
			$reg_city = $get_user_info_array->locality[0];
		}elseif($get_tokens_by_code_array->locality[0]){
			$reg_city = $get_tokens_by_code_array->locality[0];
		}
		if($get_user_info_array->postal_code[0]){
			$reg_postal_code = $get_user_info_array->postal_code[0];
		}elseif($get_tokens_by_code_array->postal_code[0]){
			$reg_postal_code = $get_tokens_by_code_array->postal_code[0];
		}
		if($get_user_info_array->phone_number[0]){
			$reg_home_phone_number = $get_user_info_array->phone_number[0];
		}elseif($get_tokens_by_code_array->phone_number[0]){
			$reg_home_phone_number = $get_tokens_by_code_array->phone_number[0];
		}
		if($get_user_info_array->phone_mobile_number[0]){
			$reg_phone_mobile_number = $get_user_info_array->phone_mobile_number[0];
		}elseif($get_tokens_by_code_array->phone_mobile_number[0]){
			$reg_phone_mobile_number = $get_tokens_by_code_array->phone_mobile_number[0];
		}
		if($get_user_info_array->picture[0]){
			$reg_avatar = $get_user_info_array->picture[0];
		}elseif($get_tokens_by_code_array->picture[0]){
			$reg_avatar = $get_tokens_by_code_array->picture[0];
		}
		if($get_user_info_array->street_address[0]){
			$reg_street_address = $get_user_info_array->street_address[0];
		}elseif($get_tokens_by_code_array->street_address[0]){
			$reg_street_address = $get_tokens_by_code_array->street_address[0];
		}
		if($get_user_info_array->birthdate[0]){
			$reg_birthdate = $get_user_info_array->birthdate[0];
		}elseif($get_tokens_by_code_array->birthdate[0]){
			$reg_birthdate = $get_tokens_by_code_array->birthdate[0];
		}
		if($get_user_info_array->region[0]){
			$reg_region = $get_user_info_array->region[0];
		}elseif($get_tokens_by_code_array->region[0]){
			$reg_region = $get_tokens_by_code_array->region[0];
		}

		$username = '';
		if($get_user_info_array->user_name[0]){
			$username = $get_user_info_array->user_name[0];
		}
		else {
			$email_split = explode("@", $reg_email);
			$username = $email_split[0];
		}
		if( $reg_email ) {
			if( username_exists( $username ) ) {
				$user 	= get_user_by('login', $username );
				$user_id 	= $user->ID;
				wp_update_user(
					array(
						'ID' => $user_id,
						'user_login'  =>  $username,
						'user_nicename'  =>  $reg_nikname,
						'user_email'    =>  $reg_email,
						'display_name' => $reg_display_name,
						'first_name' => $reg_first_name,
						'last_name' => $reg_last_name,
						'user_url' => $reg_website,
					)
				);
				if(get_option('gluu_oxdOpenId_gluu_login_avatar') && isset($reg_avatar))
					update_user_meta($user_id, 'oxdOpenId_user_avatar', $reg_avatar);
				do_action( 'wp_login', $user->user_login, $user );
				wp_set_auth_cookie( $user_id, true );
			}
			else if( email_exists( $reg_email ) ) {
				$user 	= get_user_by('email', $reg_email );
				$user_id 	= $user->ID;
				wp_update_user(
					array(
						'ID' => $user_id,
						'user_login'  =>  $username,
						'user_nicename'  =>  $reg_nikname,
						'user_email'    =>  $reg_email,
						'display_name' => $reg_display_name,
						'first_name' => $reg_first_name,
						'last_name' => $reg_last_name,
						'user_url' => $reg_website,
					)
				);
				if(get_option('gluu_oxdOpenId_gluu_login_avatar') && isset($reg_avatar))
					update_user_meta($user_id, 'oxdOpenId_user_avatar', $reg_avatar);
				do_action( 'wp_login', $user->user_login, $user );
				wp_set_auth_cookie( $user_id, true );
			}
			else {
				if(get_option('gluu_oxd_openid_auto_register_enable')) {
					$random_password 	= wp_generate_password( 10, false );
					$userdata = array(
						'user_login'  =>  $username,
						'user_nicename'  =>  $reg_nikname,
						'user_email'    =>  $reg_email,
						'user_pass'   =>  $random_password,
						'display_name' => $reg_display_name,
						'first_name' => $reg_first_name,
						'last_name' => $reg_last_name,
						'user_url' => $reg_website,
					);
					$user_id 	= wp_insert_user( $userdata);
					$user	= get_user_by('email', $reg_email );
					if(get_option('gluu_oxdOpenId_gluu_login_avatar') && isset($reg_avatar)){
						update_user_meta($user_id, 'oxdOpenId_user_avatar', $reg_avatar);
					}
					do_action( 'wp_login', $user->user_login, $user );
					wp_set_auth_cookie( $user_id, true );
				}
			}
		}else{
			echo "<script>
					alert('Missing claims : (email or username). Please talk to your organizational system administrator.');
					location.href='".site_url()."';
				 </script>";
			exit;
		}
		$redirect_url = gluu_oxd_openid_get_redirect_url();
		wp_redirect($redirect_url);
		exit;

	}
	if(isset($_REQUEST['autoregister']) and strpos($_REQUEST['autoregister'],'false') !== false) {
		if(!is_user_logged_in()) {
			gluu_oxd_openid_disabled_register_message();
		}
	}
}
function gluu_oxd_openid_disabled_register_message() {
	wp_enqueue_script('thickbox');
	wp_enqueue_style('thickbox');
	wp_enqueue_script( 'oxd-wp-settings-script',plugins_url('includes/js/settings_popup.js', __FILE__), array('jquery'));
	add_thickbox();
	$script = '<script>
							function getAutoRegisterDisabledMessage() {
								var disabledMessage = "' . get_option('gluu_oxd_openid_register_disabled_message') . '";
								return disabledMessage;
							}
						</script>';
	echo $script;
}
if(get_option('gluu_send_user_check')) {
	add_filter('login_url', 'gluu_my_login_redirect', 0, 0);
	add_filter( 'logout_url', 'gluu_oxd_openid_redirect_after_logout',0,1);
}
function redirect_login() {
	wp_redirect( gluu_my_login_redirect() );
}
//add_action( 'login_form_login', 'redirect_login' );

add_action( 'init', 'gluu_oxd_openid_login_validate' );
add_action( 'init', 'gluu_oxd_openid_logout_validate' );


?>