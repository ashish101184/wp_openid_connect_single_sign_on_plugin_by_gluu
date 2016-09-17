<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

if(is_admin()){
	delete_option('gluu_oxd_config');
	delete_option('gluu_oxd_id');
	delete_option('gluu_oxd_openid_new_registration');
	delete_option('gluu_oxd_openid_admin_email');
	delete_option('gluu_oxd_openid_message');
	delete_option('gluu_oxd_openid_scops');
	delete_option('gluu_auth_type');
	delete_option('gluu_send_user_check');
}

?>