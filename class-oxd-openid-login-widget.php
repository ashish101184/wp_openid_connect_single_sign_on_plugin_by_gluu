<?php
require GLUU_PLUGIN_PATH.'/oxd-rp/GetAuthorizationUrl.php';
require GLUU_PLUGIN_PATH.'/oxd-rp/GetTokensByCode.php';
require GLUU_PLUGIN_PATH.'/oxd-rp/GetUserInfo.php';
require GLUU_PLUGIN_PATH.'/oxd-rp/Logout.php';

if(gluu_is_oxd_registered()) {
	/*
     * Login Widget
     */
	class gluu_oxd_openid_login_wid extends WP_Widget {

		public function __construct() {
			parent::__construct(
				'gluu_oxd_openid_login_wid',
				'OpenID Connect Single Sign On (SSO) Widget',
				array( 'description' => __( 'Login using Social Apps and Gluu Apps .', 'flw' ), )
			);
		}

		public function widget( $args, $instance ) {
			extract( $args );
			echo $args['before_widget'];
			$this->openidloginForm();
			echo $args['after_widget'];
		}

		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['wid_title'] = strip_tags( $new_instance['wid_title'] );
			return $instance;
		}

		public function openidloginForm(){
			global $post;
			$this->error_message();
			$selected_theme = get_option('gluu_oxd_openid_login_theme');
			$custom_scripts = get_option('gluu_oxd_openid_custom_scripts');
			$appsConfigured = 0;
			foreach($custom_scripts as $custom_script){
				if(get_option('gluu_oxd_openid_'.$custom_script['value'].'_enable') ){
					$appsConfigured = get_option('gluu_oxd_openid_'.$custom_script['value'].'_enable');
				}
			}
			$spacebetweenicons = get_option('gluu_oxd_login_icon_space');
			$customWidth = get_option('gluu_oxd_login_icon_custom_width');
			$customHeight = get_option('gluu_oxd_login_icon_custom_height');
			$customSize = get_option('gluu_oxd_login_icon_custom_size');
			$customBackground = get_option('gluu_oxd_login_icon_custom_color');
			$customTheme = get_option('gluu_oxd_openid_login_custom_theme');
			$customTextofTitle = get_option('gluu_oxd_openid_login_button_customize_text');
			if( ! is_user_logged_in() ) {
				if( $appsConfigured ) {
					$this->oxd_openid_load_login_script();
					?>
					<div class="oxd-openid-app-icons">
						<p><?php   echo get_option('gluu_oxd_openid_login_widget_customize_text'); ?>
						</p>
						<?php
						$custom_scripts = get_option('gluu_oxd_openid_custom_scripts');
						if($customTheme == 'default'){
							foreach($custom_scripts as $custom_script){
								if( get_option('gluu_oxd_openid_'.$custom_script['value'].'_enable') ) {
									if($selected_theme == 'longbutton'){
										?> <a  onClick="oxdOpenIdLogin('<?php echo $custom_script['value'];?>');" style="width:<?php echo $customWidth ?>px !important;padding-top:<?php echo $customHeight-29 ?>px !important;padding-bottom:<?php echo $customHeight-29 ?>px !important;margin-bottom:<?php echo $spacebetweenicons-5 ?>px !important" class="btn btn-block btn-social btn-facebook  btn-custom-size login-button" > <i style="padding-top:<?php echo $customHeight-35 ?>px !important" class="fa fa-facebook"></i><?php
											echo get_option('gluu_oxd_openid_login_button_customize_text'); 	?> <?php echo $custom_script['name'];?></a>
									<?php }
									else{ ?>
										<a title="<?php echo $customTextofTitle .' '. $custom_script['name'];?>" onClick="oxdOpenIdLogin('<?php echo $custom_script['value'];?>');"><img style="width:<?php echo $customSize?>px !important;height:<?php echo $customSize?>px !important;margin-left:<?php echo $spacebetweenicons-4?>px !important" src="<?php echo $custom_script['image'];?>" class="<?php echo $selected_theme; ?> login-button" ></a>
									<?php }
								}
							}
						}
						if($customTheme == 'custom'){
							foreach($custom_scripts as $custom_script){
								if( get_option('gluu_oxd_openid_'.$custom_script['value'].'_enable') ) {
									if($selected_theme == 'longbutton'){
										?> <a  onClick="oxdOpenIdLogin('<?php echo $custom_script['value'];?>');" style="width:<?php echo $customWidth ?>px !important;padding-top:<?php echo $customHeight-29 ?>px !important;padding-bottom:<?php echo $customHeight-29 ?>px !important;margin-bottom:<?php echo $spacebetweenicons-5 ?>px !important;background:<?php echo "#".$customBackground?> !important" class="btn btn-block btn-social btn-facebook  btn-custom-size login-button" > <i style="padding-top:<?php echo $customHeight-35 ?>px !important" class="fa fa-facebook"></i><?php
											echo get_option('gluu_oxd_openid_login_button_customize_text'); 	?> <?php echo $custom_script['name'];?></a>
									<?php }
									else{ ?>
										<a  onClick="oxdOpenIdLogin('<?php echo $custom_script['value'];?>');" title="<?php echo $customTextofTitle .' '. $custom_script['name'];?>"><i style="width:<?php echo $customSize?>px !important;height:<?php echo $customSize?>px !important;margin-left:<?php echo $spacebetweenicons-4?>px !important;background:<?php echo "#".$customBackground?> !important;font-size:<?php echo $customSize-16?>px !important;" class="fa fa-facebook custom-login-button <?php echo $selected_theme; ?>" ></i></a>
									<?php }
								}
							}
						}
						?>
						<br>
					</div>
					<br>
					<?php
				} else {
					?>
					<div>No apps configured. Please contact your administrator.</div>
					<?php
				}
			}else {
				global $current_user;
				get_currentuserinfo();
				$link_with_username = __('Howdy, ', 'flw') . $current_user->display_name;
				?>
				<div id="logged_in_user" class="gluu_oxd_openid_login_wid">
					<li><?php echo $link_with_username;?> | <a href="<?php echo wp_logout_url( site_url() ); ?>" title="<?php _e('Logout','flw');?>"><?php _e('Logout','flw');?></a></li>
				</div>
				<?php
			}
		}

		public function openidloginFormShortCode( $atts ){
			global $post;
			$html = '';
			$this->error_message();
			$selected_theme = isset( $atts['shape'] )? $atts['shape'] : get_option('gluu_oxd_openid_login_theme');
			$custom_scripts = get_option('gluu_oxd_openid_custom_scripts');
			$appsConfigured = 0;
			foreach($custom_scripts as $custom_script){
				if(get_option('gluu_oxd_openid_'.$custom_script['value'].'_enable') ){
					$appsConfigured = get_option('gluu_oxd_openid_'.$custom_script['value'].'_enable');
				}
			}
			$spacebetweenicons = isset( $atts['space'] )? $atts['space'] : get_option('gluu_oxd_login_icon_space');
			$customWidth = isset( $atts['width'] )? $atts['width'] : get_option('gluu_oxd_login_icon_custom_width');
			$customHeight = isset( $atts['height'] )? $atts['height'] : get_option('gluu_oxd_login_icon_custom_height');
			$customSize = isset( $atts['size'] )? $atts['size'] : get_option('gluu_oxd_login_icon_custom_size');
			$customBackground = isset( $atts['background'] )? $atts['background'] : get_option('gluu_oxd_login_icon_custom_color');
			$customTheme = isset( $atts['theme'] )? $atts['theme'] : get_option('gluu_oxd_openid_login_custom_theme');
			$customText = get_option('gluu_oxd_openid_login_widget_customize_text');
			$buttonText = get_option('gluu_oxd_openid_login_button_customize_text');
			$customTextofTitle = get_option('gluu_oxd_openid_login_button_customize_text');
			$logoutUrl = wp_logout_url( site_url() );
			if($selected_theme == 'longbuttonwithtext'){
				$selected_theme = 'longbutton';
			}
			if($customTheme == 'custombackground'){
				$customTheme = 'custom';
			}
			if( ! is_user_logged_in() ) {
				if( $appsConfigured ) {
					$this->oxd_openid_load_login_script();
					$html .= "<div class='oxd-openid-app-icons'><p> $customText</p>";
					if($customTheme == 'default'){
						$custom_scripts = get_option('gluu_oxd_openid_custom_scripts');
						foreach($custom_scripts as $custom_script){
							if( get_option('gluu_oxd_openid_'.$custom_script['value'].'_enable') ) {
								if($selected_theme == 'longbutton'){
									$html .= "<a  style='width: " . $customWidth . "px !important;padding-top:" . ($customHeight-29) . "px !important;padding-bottom:" . ($customHeight-29) . "px !important;margin-bottom: " . ($spacebetweenicons-5)  . "px !important' class='btn btn-block btn-social btn-facebook btn-custom-dec login-button' onClick='oxdOpenIdLogin(" . '"facebook"' . ");'> <i style='padding-top:" . ($customHeight-35) . "px !important' class='fa ".$custom_script['icon_class']."'></i>" . $buttonText.''.$custom_script['name'] . " </a>"; }
								else{
									$html .= "<a title= ' ".$customTextofTitle.' '.$custom_script['name']." ' onClick='oxdOpenIdLogin(" .$custom_script['value']. ");' ><img style='width:" . $customSize ."px !important;height: " . $customSize ."px !important;margin-left: " . ($spacebetweenicons-4) ."px !important' src='" . $custom_script['image'] . "' class='login-button " .$selected_theme . "' ></a>";
								}
							}
						}
					}
					if($customTheme == 'custom'){
						$custom_scripts = get_option('gluu_oxd_openid_custom_scripts');
						foreach($custom_scripts as $custom_script){
							if($selected_theme == 'longbutton'){
								$html .= "<a   onClick='oxdOpenIdLogin(" . $custom_script['value'] . ");' style='width:" . ($customWidth) . "px !important;padding-top:" . ($customHeight-29) . "px !important;padding-bottom:" . ($customHeight-29) . "px !important;margin-bottom:" . ($spacebetweenicons-5) . "px !important; background:#" . $customBackground . "!important;' class='btn btn-block btn-social btn-customtheme btn-custom-dec login-button' > <i style='padding-top:" .($customHeight-35) . "px !important' class='fa ".$custom_script['icon_class']."'></i> " . $buttonText.' '.$custom_script['name'] . " </a>";
							}
							else{
								$html .= "<a title= ' ".$customTextofTitle.' '.$custom_script['name']."' onClick='oxdOpenIdLogin(" .$custom_script['value']. ");' ><i style='width:" . $customSize . "px !important;height:" . $customSize . "px !important;margin-left:" . ($spacebetweenicons-4) . "px !important;background:#" . $customBackground . " !important;font-size: " . ($customSize-16) . "px !important;'  class='fa ".$custom_script['icon_class']." custom-login-button  " . $selected_theme . "' ></i></a>";
							}
						}
					}
					$html .= '</div> <br>';
				} else {
					$html .= '<div>No apps configured. Please contact your administrator.</div>';
				}
			}else {
				global $current_user;
				get_currentuserinfo();
				$link_with_username = __('Howdy, ', 'flw') . $current_user->display_name;
				$flw = __("Logout","flw");
				$html .= '<div id="logged_in_user" class="gluu_oxd_openid_login_wid">	' . $link_with_username . ' | <a href=' . $logoutUrl .' title=" ' . $flw . '"> ' . $flw . '</a></div>';
			}
			return $html;
		}

		private function oxd_openid_load_login_script() {
			?>
			<script type="text/javascript">
				function oxdOpenIdLogin(app_name) {
					<?php
					if(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
						$http = "https://";
					} else {
						$http =  "http://";
					}
					if ( strpos($_SERVER['REQUEST_URI'],'wp-login.php') !== FALSE){
						$redirect_url = site_url() . '/wp-login.php?option=getOxdSocialLogin&app_name=';

					}else{
						$redirect_url = $http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
						if(strpos($redirect_url, '?') !== false) {
							$redirect_url .= '&option=getOxdSocialLogin&app_name=';
						} else {
							$redirect_url .= '?option=getOxdSocialLogin&app_name=';
						}
					}
					?>
					window.location.href = '<?php echo $redirect_url; ?>' + app_name;
				}
			</script>
			<?php
		}

		public function error_message(){
			if(isset($_SESSION['msg']) and $_SESSION['msg']){

				echo '<div class="'.esc_html($_SESSION['msg_class']).'">'.esc_html($_SESSION['msg']).'</div>';
				unset($_SESSION['msg']);
				unset($_SESSION['msg_class']);
			}
		}

	}


	function gluu_oxd_openid_logout_validate()
	{
		if (isset($_REQUEST['option']) and strpos($_REQUEST['option'], 'allLogout') !== false && !isset($_REQUEST['state'])) {

			echo '<script>
						var delete_cookie = function(name) {
							document.cookie = name + \'=;expires=Thu, 01 Jan 1970 00:00:01 GMT;\';
						};
						delete_cookie(\'user_oxd_access_token\');
						delete_cookie(\'user_oxd_id_token\');
						delete_cookie(\'session_states\');
						delete_cookie(\'states\');
					</script>';
			wp_destroy_current_session();
			wp_clear_auth_cookie();
			wp_logout();
		}
	}
	function gluu_oxd_openid_login_validate(){
		if( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'getOxdSocialLogin' ) !== false ) {
			$http = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? "https://" : "http://";
			$parts = parse_url($http . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
			parse_str($parts['query'], $query);
			$conf = get_option('gluu_oxd_config');
			if(get_option('gluu_oxd_id')){
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
					if(!exec('netstat -aon |find/i "listening" |find "'.$conf['oxd_host_port'].'"')){
						echo "<script>
									alert('Oxd server is not switched on.');location.href='".site_url()."';
								 </script>";
						exit;
					}
				} else {
					if(!exec('netstat -tulpn | grep :'.$conf['oxd_host_port'])){
						echo "<script>
									alert('Oxd server is not switched on.');location.href='".site_url()."';
								 </script>";
						exit;
					}
				}
			}
			$get_authorization_url = new GetAuthorizationUrl();
			$get_authorization_url->setRequestOxdId(get_option('gluu_oxd_id'));
			$get_authorization_url->setRequestAcrValues([$_REQUEST['app_name']]);
			$get_authorization_url->request();
			wp_redirect( $get_authorization_url->getResponseAuthorizationUrl() );
			exit;
		}
		if(isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'oxdOpenId' ) !== false ){
			session_start();
			$http = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? "https://" : "http://";
			$parts = parse_url($http . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
			parse_str($parts['query'], $query);
			$config_option = get_option( 'gluu_oxd_config' );
			$conf = get_option('gluu_oxd_config');
			$get_tokens_by_code = new GetTokensByCode();
			$get_tokens_by_code->setRequestOxdId(get_option('gluu_oxd_id'));
			$get_tokens_by_code->setRequestCode($_REQUEST['code']);
			$get_tokens_by_code->setRequestState($_REQUEST['state']);
			$get_tokens_by_code->setRequestScopes($config_option["scope"]);
			$get_tokens_by_code->request();
			$get_tokens_by_code_array = $get_tokens_by_code->getResponseObject()->data->id_token_claims;
			$_SESSION['user_oxd_id_token']= $get_tokens_by_code->getResponseIdToken();
			$_SESSION['user_oxd_access_token']= $get_tokens_by_code->getResponseAccessToken();
			$_SESSION['session_states']= $_REQUEST['session_state'];
			$_SESSION['states']= $_REQUEST['state'];
			setcookie( 'user_oxd_id_token', $get_tokens_by_code->getResponseIdToken(), time()+3600*24*100, COOKIEPATH, COOKIE_DOMAIN, false);
			setcookie( 'user_oxd_access_token', $get_tokens_by_code->getResponseAccessToken(), time()+3600*24*100, COOKIEPATH, COOKIE_DOMAIN, false);
			setcookie( 'session_states', $_REQUEST['session_state'], time()+3600*24*100, COOKIEPATH, COOKIE_DOMAIN, false);
			setcookie( 'states', $_REQUEST['state'], time()+3600*24*100, COOKIEPATH, COOKIE_DOMAIN, false);
			$get_user_info = new GetUserInfo();
			$get_user_info->setRequestOxdId(get_option('gluu_oxd_id'));
			$get_user_info->setRequestAccessToken($_SESSION['user_oxd_access_token']);
			$get_user_info->request();
			$get_user_info_array = $get_user_info->getResponseObject()->data->claims;

			$reg_first_name = '';
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
			if($get_user_info->getResponsePreferredUsername()){
				$username = $get_user_info->getResponsePreferredUsername();
			}
			else {
				$email_split = explode("@", $reg_email);
				$username = $email_split[0];
			}
			if( $reg_email ) {
				if( email_exists( $reg_email ) ) {
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
				} else if( username_exists( $username ) ) {
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
				} else {
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
		if(strpos($redirect_url,'?') !== FALSE) {
			$redirect_url .= get_option('gluu_oxd_openid_auto_register_enable') ? '' : '&autoregister=false';
		} else{
			$redirect_url .= get_option('gluu_oxd_openid_auto_register_enable') ? '' : '?autoregister=false';
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
	if(get_option('gluu_oxd_openid_logout_redirection_enable') == 1){
		add_filter( 'logout_url', 'gluu_oxd_openid_redirect_after_logout',0,1);
	}
	add_action( 'widgets_init', create_function( '', 'register_widget( "gluu_oxd_openid_login_wid" );' ) );
	add_action( 'init', 'gluu_oxd_openid_login_validate' );
	add_action( 'init', 'gluu_oxd_openid_logout_validate' );
}

?>