<?php

function gluu_is_oxd_registered() {
    $email 			= get_option('gluu_oxd_openid_admin_email');
    $oxd_id 	= get_option('gluu_oxd_id');
    if( ! $email || ! $oxd_id ) {
        return 0;
    } else {
        return 1;
    }
}
function gluu_oxd_register_openid() {
    $custom_nonce = wp_create_nonce('validating-nonce-value');
    if( isset( $_GET[ 'tab' ]) && $_GET[ 'tab' ] !== 'register' ) {
        $active_tab = $_GET[ 'tab' ];
    } else {
        $active_tab = 'register';
    }
    ?>
    <div id="tab">
        <h2 class="nav-tab-wrapper">
            <a class="nav-tab <?php echo $active_tab == 'register' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'register'), $_SERVER['REQUEST_URI'] ); ?>">General</a>
            <a class="nav-tab <?php echo $active_tab == 'login_config' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'login_config'), $_SERVER['REQUEST_URI'] ); ?>">OpenID Connect Configuration</a>
            <a class="nav-tab <?php echo $active_tab == 'login' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'login'), $_SERVER['REQUEST_URI'] ); ?>">Wordpress Configuration</a>
            <a class="nav-tab <?php echo $active_tab == 'help' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'help'), $_SERVER['REQUEST_URI'] ); ?>">Help & Troubleshooting</a>
        </h2>
    </div>
    <div id="oxd_openid_settings">
        <div class="oxd_container">
            <div id="oxd_openid_msgs"></div>
            <table style="width:100%;">
                <tr>
                    <td style="vertical-align:top;width:65%;">
                        <?php
                        if ( $active_tab == 'register') {
                            if ( !gluu_is_oxd_registered()) {
                                gluu_oxd_openid_show_new_registration_page($custom_nonce);
                            }else{
                                gluu_oxd_openid_show_new_registration__restet_page($custom_nonce);
                            }
                        }else if($active_tab == 'login_config') {
                            gluu_oxd_openid_login_config_info($custom_nonce);
                        } else if($active_tab == 'login'){
                            gluu_oxd_openid_apps_config($custom_nonce);
                        }else if($active_tab == 'help') {
                            gluu_oxd_openid_troubleshoot_info();
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php
}
function gluu_oxd_openid_show_new_registration_page($custom_nonce) {
    update_option ( 'oxd_openid_new_registration', 'true' );
    global $current_user;
    get_currentuserinfo();
    ?>
    <form name="f" method="post" action="" id="register-form">
        <input type="hidden" name="option" value="oxd_openid_connect_register_site_oxd" />
        <input type="hidden" name="custom_nonce" value="<?php echo $custom_nonce; ?>" />
        <div class="oxd_openid_table_layout">
            <?php if(!gluu_is_oxd_registered()) { ?>
                <div class="mess_red">
                    Please enter the details of your OpenID Connect Provider.
                </div>
            <?php } ?>
            <br/>
            <div><h3>Register your site with an OpenID Connect Provider</h3></div>
            <hr>
            <div class="mess_red">If you do not have an OpenID Connect provider, you may want to look at the
                Gluu Server (<a target="_blank" href="http://www.gluu.org/docs">. Like Wordpress, there is a free open
                    source Community Edition. For more information about Gluu Server support please visit <a target="_blank"
                                                                                                             href="http://www.gluu.org/">our website</a>.
            </div>
            <div class="mess_red">
                <h3>Instructions to Install oxd server</h3>
                <br><b>NOTE:</b> The oxd server should be installed on the same server as your Wordpress site.
                It is recommended that the oxd server listen only on the localhost interface, so only your
                local applications can reach its API's.
                <ol style="list-style:decimal !important; margin: 30px">
                    <li>Extract and copy in your DMZ Server.</li>
                    <li>Download the latest oxd-server package for Centos or Ubuntu. See
                        <a href="http://gluu.org/docs-oxd">oxd docs</a> for more info.
                    <li>If you are installing an .rpm or .deb, make sure you have Java in your server.
                    <li>Edit <b>oxd-conf.json</b> in the <b>conf</b> directory to specify the port on which
                        it will run, and specify the hostname of the OpenID Connect provider.</li>
                    <li>Open the command line and navigate to the extracted folder in the <b>bin</b> directory.</li>
                    <li>For Linux environment, run <b>sh oxd-start.sh &</b></li>
                    <li>For Windows environment, run <b>oxd-start.bat</b></li>
                    <li>After the server starts, set the port number and your email in this page and click Next.</li>
                </ol>
            </div>
            <hr>
            <table class="oxd_openid_settings_table">
                <tr>
                    <td><b><font color="#FF0000">*</font>Membership:</b></td>
                    <td>
                        <fieldset><legend class="screen-reader-text"><span><b><font color="#FF0000">*</font>Membership:</b></span></legend><label for="users_can_register">
                                <input name="users_can_register" type="checkbox" id="users_can_register" <?php if(get_option('users_can_register')){ echo "checked";} ?> value="1">
                                <b>Anyone can register:</b> </label>
                        </fieldset>
                        <br/>
                    </td>
                </tr>
                <tr>
                    <td><label for="default_role"><b><font color="#FF0000">*</font>New User Default Role:</b></label></td>
                    <td>
                        <select name="default_role" id="default_role"><?php wp_dropdown_roles( get_option('default_role') ); ?></select>
                        <br/><br/>
                    </td>
                </tr>
                <tr>
                    <td><b><font color="#FF0000">*</font>Admin Email:</b></td>
                    <td><input class="oxd_openid_table_textbox" type="email" name="email"
                               required placeholder="person@example.com"
                               value="<?php echo $current_user->user_email;?>" /></td>
                </tr>
                <tr>
                    <td><b><font color="#FF0000">*</font>Oxd port:</b></td>
                    <td>
                        <br/>
                        <input class="oxd_openid_table_textbox" required type="number" name="oxd_host_port" value="8099" placeholder="Please enter free port (for example 8099). (Min. number 0, Max. number 65535)" />
                        <p style="color:red" class="description">It must be free port in your server.</p>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><br /><input type="submit" name="submit" value="Next" style="width:100px;"
                                     class="button button-primary button-large" /></td>
                </tr>
            </table>
        </div>
    </form>
    <?php
}
function gluu_oxd_openid_show_new_registration__restet_page($custom_nonce) {
    update_option ( 'oxd_openid_new_registration', 'true' );
    global $current_user;
    get_currentuserinfo();
    ?>
    <form name="f" method="post" action="" id="register-form">
        <input type="hidden" name="option" value="oxd_openid_reset_config" />
        <input type="hidden" name="custom_nonce" value="<?php echo $custom_nonce; ?>" />
        <div class="oxd_openid_table_layout">
            <fieldset style="border: 2px solid #53cc6b;">
                <legend><div class="about">
                        <img style=" height: 45px" src="<?php echo plugins_url('includes/images/logo.png', __FILE__)?>" />server config
                    </div></legend>
                <table class="form-table" style="margin-left: 30px">
                    <tbody>
                    <tr>
                        <th scope="row">
                            Oxd id
                        </th>
                        <td>
                            <input <?php echo 'disabled'?> type="text" name="oxd_id" value="<?php echo get_option('gluu_oxd_id'); ?>" size="100%" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <input type="submit" name="submit" value="Reset configurations" style="float:left; margin-right:2%; margin-top: -3px;width:200px; " <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> class="button button-denger button-large" />
                        </th>
                    </tr>
                    </tbody>
                </table>
            </fieldset>
        </div>
    </form>
    <?php
}
function gluu_oxd_openid_apps_config($custom_nonce) {
    ?>
    <form id="form-apps" name="form-apps" method="post" action="">
        <input type="hidden" name="option" value="oxd_openid_enable_apps" />
        <input type="hidden" name="custom_nonce" value="<?php echo $custom_nonce; ?>" />
        <div class="oxd_openid_table_layout">
            <?php if(!gluu_is_oxd_registered()) { ?>
                <div class="mess_red">
                    Please enter OXD configuration to continue.
                </div>
            <?php } ?>
            <table>
                <tr>
                    <td colspan="2">
                        <h4 style="margin-bottom:0 !important">Current Shortcode</h4>
                        <?php if(get_option('gluu_oxd_openid_login_theme') != 'longbutton'){?>
                            <code>[gluu_login  shape="<?php echo get_option('gluu_oxd_openid_login_theme');?>" theme="<?php echo get_option('gluu_oxd_openid_login_custom_theme');?>" space="<?php echo get_option('gluu_oxd_login_icon_space')?>" size="<?php echo get_option('gluu_oxd_login_icon_custom_size')?>"]</code><br>
                        <?php }else{?>
                            <code>[gluu_login  shape="<?php echo get_option('gluu_oxd_openid_login_theme');?>" theme="<?php echo get_option('gluu_oxd_openid_login_custom_theme');?>" space="<?php echo get_option('gluu_oxd_login_icon_space')?>" width="<?php echo get_option('gluu_oxd_login_icon_custom_width')?>" height="<?php echo get_option('gluu_oxd_login_icon_custom_height')?>"]</code><br>
                        <?php }?>
                        <h3>Gluu login config
                            <input type="submit" name="submit" value="Save" style="float:right; margin-right:2%; margin-top: -3px;width:100px;" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> class="button button-primary button-large" />
                        </h3>
                        <b>Customize your login icons using a range of shapes and sizes. You can choose different places to display these icons and also customize redirect url after login.</b>
                    </td>
                </tr>
            </table>
            <table>
                <tr style="display: none">
                    <td>
                        <table style="width:100%">
                            <tr>
                                <?php
                                $custom_scripts = get_option('gluu_oxd_openid_custom_scripts');
                                foreach($custom_scripts as $custom_script){
                                    ?>
                                    <td>
                                        <input type="checkbox" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> id="<?php echo $custom_script['value'];?>_enable" class="app_enable" name="<?php echo 'oxd_openid_'.$custom_script['value'].'_enable';?>" value="1" onchange="previewLoginIcons();"
                                            <?php checked( get_option('gluu_oxd_openid_'.$custom_script['value'].'_enable') == 1 );?> /><strong><?php echo $custom_script['name'];?></strong>
                                    </td>
                                    <?php
                                }
                                ?>
                            </tr>
                        </table>
                    </td>

                </tr>
                <tr>
                    <td>
                        <br>
                        <hr>
                        <h3>Customize Login Icons</h3>
                        <p>Customize shape, theme and size of the login icons</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Shape</b>
                        <b style="margin-left:130px; display: none">Theme</b>
                        <b style="margin-left:130px;">Space between Icons</b>
                        <b style="margin-left:86px;">Size of Icons</b>
                    </td>
                </tr>
                <tr>
                    <td class="oxd_openid_table_td_checkbox">
                        <input type="radio" name="oxd_openid_login_theme" value="circle" onclick="checkLoginButton();oxdLoginPreview(document.getElementById('oxd_login_icon_size').value ,'circle',setLoginCustomTheme(),document.getElementById('oxd_login_icon_custom_color').value,document.getElementById('oxd_login_icon_space').value)"
                            <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>
                            <?php checked( get_option('gluu_oxd_openid_login_theme') == 'circle' );?> />Round
                        <span style="margin-left:106px; display: none">
                            <input type="radio" id="oxd_openid_login_default_radio"  name="oxd_openid_login_custom_theme" value="default" onclick="checkLoginButton();oxdLoginPreview(setSizeOfIcons(), setLoginTheme(),'default',document.getElementById('oxd_login_icon_custom_color').value,document.getElementById('oxd_login_icon_space').value,document.getElementById('oxd_login_icon_height').value)"
                                <?php checked( get_option('gluu_oxd_openid_login_custom_theme') == 'default' );?> <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>/>Default

                        </span>
                        <span  style="margin-left:111px;">
                                <input style="width:50px" onkeyup="oxdLoginSpaceValidate(this)" id="oxd_login_icon_space" name="oxd_login_icon_space" type="text" value="<?php echo get_option('gluu_oxd_login_icon_space')?>" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>/>
                                <input id="oxd_login_space_plus" type="button" value="+" onmouseup="oxdLoginPreview(setSizeOfIcons() ,setLoginTheme(),setLoginCustomTheme(),document.getElementById('oxd_login_icon_custom_color').value,document.getElementById('oxd_login_icon_space').value)" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>/>
                                <input id="oxd_login_space_minus" type="button" value="-" onmouseup="oxdLoginPreview(setSizeOfIcons()  ,setLoginTheme(),setLoginCustomTheme(),document.getElementById('oxd_login_icon_custom_color').value,document.getElementById('oxd_login_icon_space').value)" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>/>
                        </span>
                        <span id="commontheme" style="margin-left:115px">
                        <input style="width:50px" id="oxd_login_icon_size" onkeyup="oxdLoginSizeValidate(this)" name="oxd_login_icon_custom_size" type="text" value="<?php echo get_option('gluu_oxd_login_icon_custom_size')?>" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>>
                        <input id="oxd_login_size_plus" type="button" value="+" onmouseup="oxdLoginPreview(document.getElementById('oxd_login_icon_size').value ,setLoginTheme(),setLoginCustomTheme(),document.getElementById('oxd_login_icon_custom_color').value,document.getElementById('oxd_login_icon_space').value)" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>>
                        <input id="oxd_login_size_minus" type="button" value="-" onmouseup="oxdLoginPreview(document.getElementById('oxd_login_icon_size').value ,setLoginTheme(),setLoginCustomTheme(),document.getElementById('oxd_login_icon_custom_color').value,document.getElementById('oxd_login_icon_space').value)" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>>

                        </span>
                        <span style="margin-left:115px" class="longbuttontheme">Width:&nbsp;
                        <input style="width:50px" id="oxd_login_icon_width" onkeyup="oxdLoginWidthValidate(this)" name="oxd_login_icon_custom_width" type="text" value="<?php echo get_option('gluu_oxd_login_icon_custom_width')?>" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>>
                        <input id="oxd_login_width_plus" type="button" value="+" onmouseup="oxdLoginPreview(document.getElementById('oxd_login_icon_width').value ,setLoginTheme(),setLoginCustomTheme(),document.getElementById('oxd_login_icon_custom_color').value,document.getElementById('oxd_login_icon_space').value,document.getElementById('oxd_login_icon_height').value)" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>>
                        <input id="oxd_login_width_minus" type="button" value="-" onmouseup="oxdLoginPreview(document.getElementById('oxd_login_icon_width').value ,setLoginTheme(),setLoginCustomTheme(),document.getElementById('oxd_login_icon_custom_color').value,document.getElementById('oxd_login_icon_space').value,document.getElementById('oxd_login_icon_height').value)" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>>

                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="oxd_openid_table_td_checkbox">
                        <input type="radio"   name="oxd_openid_login_theme"  value="oval" onclick="checkLoginButton();oxdLoginPreview(document.getElementById('oxd_login_icon_size').value,'oval',setLoginCustomTheme(),document.getElementById('oxd_login_icon_custom_color').value,document.getElementById('oxd_login_icon_space').value,document.getElementById('oxd_login_icon_size').value )"
                            <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>
                            <?php checked( get_option('gluu_oxd_openid_login_theme') == 'oval' );?> />Rounded Edges
                        <span style="margin-left:50px; display: none">
                            <input type="radio" id="oxd_openid_login_custom_radio"  name="oxd_openid_login_custom_theme" value="custom" onclick="checkLoginButton();oxdLoginPreview(setSizeOfIcons(), setLoginTheme(),'custom',document.getElementById('oxd_login_icon_custom_color').value,document.getElementById('oxd_login_icon_space').value,document.getElementById('oxd_login_icon_height').value)" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>
                                <?php checked( get_option('gluu_oxd_openid_login_custom_theme') == 'custom' );?> />Custom Background*

                            </span>
                        <span style="margin-left:249px" class="longbuttontheme" >Height:
                    <input style="width:50px" id="oxd_login_icon_height" onkeyup="oxdLoginHeightValidate(this)" name="oxd_login_icon_custom_height" type="text" value="<?php echo get_option('gluu_oxd_login_icon_custom_height')?>" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>>
                    <input id="oxd_login_height_plus" type="button" value="+" onmouseup="oxdLoginPreview(document.getElementById('oxd_login_icon_width').value,setLoginTheme(),setLoginCustomTheme(),document.getElementById('oxd_login_icon_custom_color').value,document.getElementById('oxd_login_icon_space').value,document.getElementById('oxd_login_icon_height').value)" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>>
                    <input id="oxd_login_height_minus" type="button" value="-" onmouseup="oxdLoginPreview(document.getElementById('oxd_login_icon_width').value,setLoginTheme(),setLoginCustomTheme(),document.getElementById('oxd_login_icon_custom_color').value,document.getElementById('oxd_login_icon_space').value,document.getElementById('oxd_login_icon_height').value)" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>>

                    </span>
                    </td>
                </tr>
                <tr>
                    <td class="oxd_openid_table_td_checkbox">
                        <input type="radio"   name="oxd_openid_login_theme" value="square" onclick="checkLoginButton();oxdLoginPreview(document.getElementById('oxd_login_icon_size').value ,'square',setLoginCustomTheme(),document.getElementById('oxd_login_icon_custom_color').value,document.getElementById('oxd_login_icon_space').value,document.getElementById('oxd_login_icon_size').value )"
                            <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>
                            <?php checked( get_option('gluu_oxd_openid_login_theme') == 'square' );?> />Square
                        <span style="margin-left:113px; display: none">
                            <input id="oxd_login_icon_custom_color" style="width:135px;" name="oxd_login_icon_custom_color"  class="color" value="<?php echo get_option('gluu_oxd_login_icon_custom_color')?>" onchange="oxdLoginPreview(setSizeOfIcons(), setLoginTheme(),'custom',document.getElementById('oxd_login_icon_custom_color').value,document.getElementById('oxd_login_icon_space').value)" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>>
                        </span>
                    </td>
                </tr>
                <tr style="display: none">
                    <td class="oxd_openid_table_td_checkbox">
                        <input type="radio" id="iconwithtext"   name="oxd_openid_login_theme" value="longbutton" onclick="checkLoginButton();oxdLoginPreview(document.getElementById('oxd_login_icon_width').value ,'longbutton',setLoginCustomTheme(),document.getElementById('oxd_login_icon_custom_color').value,document.getElementById('oxd_login_icon_space').value,document.getElementById('oxd_login_icon_height').value)"
                            <?php if(!gluu_is_oxd_registered()) echo 'disabled';  checked( get_option('gluu_oxd_openid_login_theme') == 'longbutton' );?> />Long Button with Text
                    </td>
                </tr>
                <tr>
                    <td>
                        <br><b>Preview : </b>
                        <br/><span hidden id="no_apps_text">No apps selected</span>
                        <div>
                            <?php
                            $custom_scripts = get_option('gluu_oxd_openid_custom_scripts');
                            foreach($custom_scripts as $custom_script){
                                ?>
                                <img class="oxd_login_icon_preview" id="oxd_login_icon_preview_<?php echo $custom_script['value'];?>" src="<?php echo $custom_script['image'];?>" />
                                <?php
                            }
                            ?>
                        </div>
                        <div>
                            <?php
                            foreach($custom_scripts as $custom_script){
                                ?>
                                <a id="oxd_login_button_preview_<?php echo $custom_script['value'];?>" class="btn btn-block btn-defaulttheme btn-social btn-facebook btn-custom-size"> <i class="fa <?php echo $custom_script['icon_class'];?>"></i><?php
                                    echo get_option('gluu_oxd_openid_login_button_customize_text'); 	?> <?php echo $custom_script['name'];?></a>
                                <?php
                            }
                            ?>

                        </div>
                        <div>
                            <?php
                            foreach($custom_scripts as $custom_script){
                                ?>
                                <i class="oxd_custom_login_icon_preview fa <?php echo $custom_script['icon_class'];?>" id="oxd_custom_login_icon_preview_<?php echo $custom_script['value'];?>"  style="color:#ffffff;text-align:center;margin-top:5px;"></i>
                                <?php
                            }
                            ?>
                        </div>
                        <div>
                            <?php
                            foreach($custom_scripts as $custom_script){
                                ?>
                                <a id="oxd_custom_login_button_preview_<?php echo $custom_script['value'];?>" class="btn btn-block btn-customtheme btn-social  btn-custom-size"> <i class="fa <?php echo $custom_script['icon_class'];?>"></i><?php
                                    echo get_option('gluu_oxd_openid_login_button_customize_text'); 	?> <?php echo $custom_script['name'];?></a>
                                <?php
                            }
                            ?>
                        </div>
                    </td>
                </tr>
                <tr style="display: none">
                    <td>
                        <br><strong>*NOTE:</strong><br/>Custom background: This will change the background color of
                        the login icons.
                    </td>
                </tr>
                <tr>
                    <td>
                        <br>
                        <hr>
                        <h3>Display Options</h3>
                        <b>Select the options where you want to display the social login icons</b>
                    </td>
                </tr>
                <tr>
                    <td class="oxd_openid_table_td_checkbox">
                        <input type="checkbox" id="default_login_enable" name="oxd_openid_default_login_enable" value="1"
                            <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>	<?php checked( get_option('gluu_oxd_openid_default_login_enable') == 1 );?> />Default Login Form
                    </td>
                </tr>
                <tr>
                    <td class="oxd_openid_table_td_checkbox">
                        <input type="checkbox" id="default_register_enable" name="oxd_openid_default_register_enable" value="1"
                            <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>	<?php checked( get_option('gluu_oxd_openid_default_register_enable') == 1 );?> />Default Registration Form
                    </td>
                </tr>
                <tr>
                    <td class="oxd_openid_table_td_checkbox">
                        <input type="checkbox" id="default_comment_enable" name="oxd_openid_default_comment_enable" value="1"
                            <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>	<?php checked( get_option('gluu_oxd_openid_default_comment_enable') == 1 );?> />Comment Form
                    </td>
                </tr>
                <tr>
                    <td class="oxd_openid_table_td_checkbox">
                        <input type="checkbox" id="woocommerce_login_form" name="oxd_openid_woocommerce_login_form" value="1"
                            <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>	<?php checked( get_option('gluu_oxd_openid_woocommerce_login_form') == 1 );?> />WooCommerce Login Form
                    </td>
                </tr>


                <?php
                $script_option = array(
                    'oxd_login_icon_custom_size'=>get_option('gluu_oxd_login_icon_custom_size'),
                    'oxd_openid_login_theme'=>get_option('gluu_oxd_openid_login_theme'),
                    'oxd_openid_login_custom_theme'=>get_option('gluu_oxd_openid_login_custom_theme'),
                    'oxd_login_icon_custom_color'=>get_option('gluu_oxd_login_icon_custom_color'),
                    'oxd_login_icon_space'=>get_option('gluu_oxd_login_icon_space'),
                    'oxd_openid_custom_scripts'=>get_option('gluu_oxd_openid_custom_scripts'),
                    'oxd_login_icon_custom_height'=>get_option('gluu_oxd_login_icon_custom_height')
                );
                wp_enqueue_script( 'gluu_oxd_scripts',plugins_url('includes/js/gluu_oxd_scripts.js', __FILE__), array('jquery'));
                wp_localize_script( 'gluu_oxd_scripts', 'option', $script_option );
                ?>

            </table>
            <table class="oxd_openid_display_table">
                <tr>
                    <td><b>Enter text to show above login widget:</b></td>
                    <td><input class="oxd_openid_table_textbox" type="text" name="oxd_openid_login_widget_customize_text" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> value="<?php echo get_option('gluu_oxd_openid_login_widget_customize_text'); ?>" /></td>
                </tr>
                <tr style="display: none">
                    <td><b>Enter text to show on your login buttons (If you have selected shape 4 from 'Customize Login Icons' section):</b></td>
                    <td><input class="oxd_openid_table_textbox" type="text" name="oxd_openid_login_button_customize_text" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> value="<?php echo get_option('gluu_oxd_openid_login_button_customize_text'); ?>"  /></td>
                </tr>
                <tr>
                    <td>
                        <br />
                        <input type="submit" name="submit" value="Save" style="width:100px;" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> class="button button-primary button-large" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <hr>
                        <p>
                        <h3>Add Login Icons</h3>
                        You can add login icons in the following areas from <strong>Display Options</strong>. For
                        other areas (widget areas), use Login Widget.
                        <ol>
                            <li>Default Login Form: Login icons will appear below the default login form on wp-login.</li>
                            <li>Default Registration Form: Login icons will appear below the default registration form.</li>
                            <li>Comment Form: Login icons will appear above the comment section of all your posts.</li>
                        </ol>
                        <h3>Add Login Icons as Widget</h3>
                        <ol>
                            <li>Go to Appearance->Widgets. Among the available widgets you
                                will find the OpenID Connect Single Sign On (SSO) Plugin By Gluu Widget, drag it to the widget area where
                                you want it to appear.</li>
                            <li>Now logout and go to your site. You should see the new login icons.</li>
                            <li>Click that icon and login with your existing account to Wordpress.</li>
                        </ol>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </form>
    <div style="display: none">
        <table>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>
                    <b>Redirect URL after login:</b>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="radio" id="login_redirect_same_page" name="oxd_openid_login_redirect" value="same"
                        <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> <?php checked( get_option('gluu_oxd_openid_login_redirect') == 'same' );?> />Same page where user logged in
                </td>
            </tr>
            <tr>
                <td>
                    <input type="radio" id="login_redirect_homepage" name="oxd_openid_login_redirect" value="homepage"
                        <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> <?php checked( get_option('gluu_oxd_openid_login_redirect') == 'homepage' );?> />Homepage
                </td>
            </tr>
            <tr>
                <td>
                    <input type="radio" id="login_redirect_dashboard" name="oxd_openid_login_redirect" value="dashboard"
                        <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> <?php checked( get_option('gluu_oxd_openid_login_redirect') == 'dashboard' );?> />Account dashboard
                </td>
            </tr>
            <tr>
                <td>
                    <input type="radio" id="login_redirect_customurl" name="oxd_openid_login_redirect" value="custom"
                        <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> <?php checked( get_option('gluu_oxd_openid_login_redirect') == 'custom' );?> />Custom URL
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="url" id="login_redirect_url" style="width:50%" name="oxd_openid_login_redirect_url" value="<?php echo get_option('gluu_oxd_openid_login_redirect_url')?>" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>/>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" id="logout_redirection_enable" name="oxd_openid_logout_redirection_enable" value="1"
                        <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>	<?php checked( get_option('gluu_oxd_openid_logout_redirection_enable') == 1 );?> />
                    <b>Enable Logout Redirection</b>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="radio" id="logout_redirect_home" name="oxd_openid_logout_redirect" value="homepage"
                        <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> <?php checked( get_option('gluu_oxd_openid_logout_redirect') == 'homepage' );?> />Home Page
                </td>
            </tr>
            <tr>
                <td>
                    <input type="radio" id="logout_redirect_current" name="oxd_openid_logout_redirect" value="currentpage"
                        <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> <?php checked( get_option('gluu_oxd_openid_logout_redirect') == 'currentpage' );?> />Current Page
                </td>
            </tr>
            <tr>
                <td>
                    <input type="radio" id="logout_redirect_login" name="oxd_openid_logout_redirect" value="login"
                        <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> <?php checked( get_option('gluu_oxd_openid_logout_redirect') == 'login' );?> />Login Page
                </td>
            </tr>
            <tr>
                <td>
                    <input type="radio" id="logout_redirect_customurl" name="oxd_openid_logout_redirect" value="custom"
                        <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> <?php checked( get_option('gluu_oxd_openid_logout_redirect') == 'custom' );?> />Relative URL
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <?php echo site_url();?>
                    <input type="text" id="logout_redirect_url" style="width:50%" name="oxd_openid_logout_redirect_url" value="<?php echo get_option('gluu_oxd_openid_logout_redirect_url')?>" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>/>
                </td>
            </tr>
            <tr>
                <td>
                    <br>
                    <hr>
                    <h3>Registration Options</h3>
                </td>
            </tr>
            <tr>
                <td>
                    If "Auto-Register Users" is checked, when a new user is authenticated via
                    OpenID Connect, the plugin will automatically create a corresponding local
                    user in the Wordpress database, using the information about the person
                    retrieved from the user_info endpoint of the Openid Connect Provider.
                    If it's unchecked, only users who already have an account in the local
                    Wordpress database will be able to login. Note: this feature will not interfere
                    with people who try to register through the regular WordPress registration form.
                    <br/><br/>
                    <input type="checkbox" id="auto_register_enable" name="oxd_openid_auto_register_enable" value="1"
                        <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>	<?php checked( get_option('gluu_oxd_openid_auto_register_enable') == 1 );?> /><b>Auto-register users</b>
                    <br/><br/>
                    <b>Registration disabled message: </b>
                    <textarea id="auto_register_disabled_message" style="width:80%" name="oxd_openid_register_disabled_message" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>><?php echo get_option('gluu_oxd_openid_register_disabled_message')?></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <br/>
                    <hr>
                    <h3>Advanced Settings</h3>
                </td>
            </tr>
            <tr>
                <td><input type="checkbox" id="oxdOpenId_gluu_login_avatar" name="oxdOpenId_gluu_login_avatar" value="1" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> <?php checked( get_option('gluu_oxdOpenId_gluu_login_avatar') == 1 );?> /><b>Set Display Picture for User</b>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td><input type="checkbox" id="oxdOpenId_user_attributes" name="oxdOpenId_user_attributes" value="1" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> <?php checked( get_option('gluu_oxdOpenId_user_attributes') == 1 );?> /><b>Collect User Attributes</b>
                </td>
            </tr>
            <tr>
                <td>
                    <br>
                    <hr>
                    <h3>Customize Text For Login Icons</h3>
                </td>
            </tr>
        </table>

    </div>
    <?php
}
function gluu_oxd_openid_login_config_info($custom_nonce){
    wp_enqueue_script('jquery');
    wp_enqueue_media();
    wp_enqueue_script( 'oxd_scope_custom_script',plugins_url('includes/js/oxd_scope_custom_script.js', __FILE__), array('jquery'));
    ?>
    <div class="oxd_openid_table_layout">
        <?php
        $options = get_option('gluu_oxd_config');
        if(!gluu_is_oxd_registered()) {
            ?>
            <div class="mess_red">
                Please enter OXD configuration to continue.
            </div>
        <?php } ?>
        <div>
            <form action="" method="post">
                <input type="hidden" name="option" value="oxd_openid_config_info_hidden" />
                <input type="hidden" name="custom_nonce" value="<?php echo $custom_nonce; ?>" />
                <br/>
                <fieldset style="border: 2px solid #53cc6b;">
                    <legend><div class="about">
                            <img style=" height: 45px" src="<?php echo plugins_url('includes/images/gl.png', __FILE__)?>" />server config
                        </div></legend>
                    <table style="margin-left: 30px" class="form-table">
                        <tbody>
                        <tr>
                            <th scope="col" >
                                Scopes
                            </th>
                            <?php $get_scopes = get_option('gluu_oxd_openid_scops');
                            ?>
                            <td>
                                <div >
                                    <div>
                                        <?php foreach($get_scopes as $scop) :?>
                                            <?php if ($scop == 'openid'){?>
                                                <input <?php if (!gluu_is_oxd_registered()) echo ' disabled ' ?>  type="hidden"  name="scope[]"  <?php if ($options && in_array($scop, $options['scope'])) {
                                                    echo " checked "; } ?> value="<?php echo $scop; ?>" />
                                            <?php } ?>
                                            <input <?php if($options && in_array($scop, $options['scope'])){ echo "checked";} ?> type="checkbox" name="scope[]"  id="<?php echo $scop;?>" value="<?php echo $scop;?>" <?php if (!gluu_is_oxd_registered() || $scop=='openid') echo ' disabled '; ?> />
                                            <label  for="<?php echo $scop;?>"><?php echo $scop;?></label>
                                        <?php endforeach;?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr >
                            <th scope="row">
                                Add scopes
                            </th>
                            <td>
                                <div >
                                    <div id="p_scents">
                                        <input <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> type="button" id="add_new_scope" value="Add scope">
                                        <p>
                                            <input <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> type="text" name="new_scope[]" placeholder="Input scope name" />
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <td>
                                <div style="">
                                    <button type="button" style="width:95%;font-size: 20px; color: black; font-weight: bold; cursor: pointer; text-align: center;" id="show_scope_table">Click here to delete scopes</button>
                                </div>
                                <br/>
                                <table id="custom_scope_table" class="form-table" style="width:95%;display: none; text-align: center">
                                    <tr>
                                        <th> <h3>N</h3> </th>
                                        <th><h3>Scope name</h3></th>
                                        <th><h3>Delete</h3></th>
                                    </tr>
                                    <?php
                                    $custom_scripts = get_option('gluu_oxd_openid_scops');
                                    $n = 0;
                                    foreach($custom_scripts as $custom_script){
                                        $n++;
                                        ?>
                                        <tr>
                                            <td><?php echo $n;?></td>
                                            <td><?php echo $custom_script;?></td>
                                            <td>
                                                <?php if($custom_script!='openid'){?>
                                                    <input type="button" onclick="delete_scopes('<?php echo $custom_script;?>','<?php echo $custom_nonce;?>')" class="button button-primary button-large" <?php if(!gluu_is_oxd_registered() || $custom_script=='openid') echo 'disabled'?> value="Delete" name="set_oxd_config" />
                                                <?php }?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </table>
                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <th scope="col" >
                                <h3>Custom scripts.</h3>
                                <h4><i>Select applications to enable login for your users.</i></h4>
                            </th>
                            <?php $get_scopes = get_option('gluu_oxd_openid_custom_scripts');
                            ?>
                            <td>
                                <div style="margin-right: 30px">
                                    <h3>Manage Authentication</h3>

                                        <span>An OpenID Connect Provider (OP) like the Gluu Server may provide many different work flows for
                                            authentication. For example, an OP may offer password authentication, token authentication, social
                                            authentication, biometric authentication, and other different mechanisms. Offering a lot of different
                                            types of authentication enables an OP to offer the most convenient, secure, and affordable option to
                                            identify a person, depending on the need to mitigate risk, and the sensors and inputs available on the
                                            device that the person is using.
                                        <br/>
                                            The OP enables a client (like a Wordpress site), to signal which type of authentication should be
                                            used. The client can register a
                                            <a href="http://openid.net/specs/openid-connect-registration-1_0.html#ClientMetadata">default_acr_value</a>
                                            or during the authentication process, a client may request a specific type of authentication using the
                                            <a href="http://openid.net/specs/openid-connect-core-1_0.html#AuthRequest">acr_values</a> parameter.
                                            This is the mechanism that the OpenID Connect Single Sign On (SSO) Plugin By Gluu Plugin uses: each login icon corresponds to a different acr value.
                                            For example, and acr may tell the OpenID Connect to use Facebook, Google or even plain old password
                                            authentication. The nice thing about this approach is that your applications (like Wordpress) don't have
                                            to implement the business logic for social login--it's handled by the OpenID Connect Provider.
                                            <br/>
                                            If you are using the Gluu Server as your OP, you'll notice that in the Manage Custom Scripts
                                            tab of oxTrust (the Gluu Server admin interface), each authentication script has a name.
                                            This name corresponds to the acr value.  The default acr for password authentication is set in
                                            the
                                            <a href="https://www.gluu.org/docs/admin-guide/configuration/#manage-authentication">LDAP Authentication</a>,
                                            section--look for the "Name" field. Likewise, each custom script has a "Name", for example
                                            see the
                                            <a href="https://www.gluu.org/docs/admin-guide/configuration/#manage-custom-scripts">Manage Custom
                                                Scripts</a> section.
                                        </span>
                                    <table>
                                        <tr >

                                            <td >

                                                <table style="width:100%">
                                                    <tr>
                                                        <?php
                                                        $custom_scripts = get_option('gluu_oxd_openid_custom_scripts');
                                                        foreach($custom_scripts as $custom_script){
                                                            ?>
                                                            <td>
                                                                <input type="checkbox" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> id="<?php echo $custom_script['value'];?>_enable" class="app_enable" name="<?php echo 'oxd_openid_'.$custom_script['value'].'_enable';?>" value="1" onchange="previewLoginIcons();"
                                                                    <?php checked( get_option('gluu_oxd_openid_'.$custom_script['value'].'_enable') == 1 );?> /><strong><?php echo $custom_script['name'];?></strong>
                                                            </td>
                                                            <?php
                                                        }
                                                        ?>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <p>Add custom scripts</p>
                                <p style="color:red" class="description">Both fields are required</p>
                            </th>
                            <td>
                                <div >

                                    <input <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> type="button" id="add_new_suctom_script"  value="Add acr"/>
                                    <input type="hidden" name="count_scripts" value="1" id="count_scripts">
                                    <div id="p_scents_script">
                                        <p>
                                            <input <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> type="text" style="margin-right: 5px " name="new_custom_script_name_1" size="30" placeholder="Display name (example Google+)" />
                                            <input <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> type="text" style="margin-right: 5px " name="new_custom_script_value_1" size="40" placeholder="ACR Value (script name in the Gluu Server)" />
                                            <input type="hidden" name="image_url_1" id="image_url_1" class="regular-text">
                                            <input <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> type="button" name="upload-btn" id="upload-btn_1" onclick="upload_this(1)" class="button-secondary" value="Upload app image (120x120) ">
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                    <table style="margin-left: 30px" class="form-table">
                        <tbody>
                        <tr>
                            <th></th>
                            <td>
                                <div style="">
                                    <button type="button"  style="width:95%;font-size: 20px; color: black; font-weight: bold; cursor: pointer; text-align: center;" id="show_script_table">Click here to delete ACRs</button>
                                </div>
                                <br/>
                                <table id="custom_script_table" class="form-table" style="width:95%;display: none; text-align: center">
                                    <tr>
                                        <th> <h3>N</h3> </th>
                                        <th><h3>Display Name</h3></th>
                                        <th><h3>ACR Value</h3></th>
                                        <th><h3>Image</h3></th>
                                        <th><h3>Delete</h3></th>
                                    </tr>
                                    <?php
                                    $custom_scripts = get_option('gluu_oxd_openid_custom_scripts');
                                    $n = 0;
                                    foreach($custom_scripts as $custom_script){
                                        $n++;
                                        ?>
                                        <tr>
                                            <td><?php echo $n;?></td>
                                            <td><?php echo $custom_script['name'];?></td>
                                            <td><?php echo $custom_script['value'];?></td>
                                            <td><img src="<?php echo $custom_script['image'];?>" width="40px" height="40px"/></td>
                                            <td><input type="button" onclick="delete_custom_script('<?php echo $custom_script['value'];?>', '<?php echo $custom_nonce;?>')" class="button button-primary button-large" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> value="Delete" name="set_oxd_config" /></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>

                                </table>
                                <br/>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </fieldset>
                <br/><br/><br/>
                <input style="width: 100px" type="submit" class="button button-primary button-large" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> value="Save" name="set_oxd_config" />
            </form>
        </div>
    </div>
    <?php
}
function gluu_oxd_openid_shortcode_info(){
    ?>
    <div class="oxd_openid_table_layout">
        <?php if(!gluu_is_oxd_registered()) { ?>
            <div class="mess_red">
                Please enter gluu configuration to continue.
            </div>
        <?php } ?>
        <table>

        </table>
    </div>
    <?php
}
function gluu_oxd_openid_troubleshoot_info(){ ?>
    <div class="oxd_openid_table_layout">
        <?php if(!gluu_is_oxd_registered()) { ?>
            <div class="mess_red">
                Please enter OXD configuration to continue.
            </div>
        <?php } ?>
        <table width="100%">
            <tbody>
            <tr>
                <td>
                    <h1><a id="Wordpress_GLUU_SSO_plugin_0"></a>WordPress OpenID Connect Single Sign On (SSO) Plugin By Gluu</h1>
                    <p><img src="https://raw.githubusercontent.com/GluuFederation/gluu-wordpress-oxd-login-plugin/master/plugin.jpg" alt="image"></p>
                    <p>WordPress OpenID Connect Single Sign On (SSO) Plugin By Gluu gives access for login to your site, with the help of Gluu server.</p>
                    <p>In details how to use plugin step by step.</p>
                    <p>Plugin will not be working if your host does not have https://.</p>
                    <h2><a id="Step_1_Install_Gluuserver_13"></a>Step 1. Install Gluu-server</h2>
                    <p>(version 2.4.3)</p>
                    <p>If you want to use external gluu server, You can not do this step.</p>
                    <p><a target="_blank" href="https://www.gluu.org/docs/deployment/">Gluu-server installation gide</a>.</p>
                    <h2><a id="Step_2_Download_oxDserver_21"></a>Step 2. Download oxd-server</h2>
                    <p><a target="_blank" href="https://ox.gluu.org/maven/org/xdi/oxd-server/2.4.3.Final/oxd-server-2.4.3.Final-distribution.zip">Download oxd-server-2.4.3</a>.</p>
                    <h2><a id="Step_3_Unzip_and_run_oXDserver_31"></a>Step 3. Unzip and run oxd-server</h2>
                    <ol>
                        <li>Unzip your oxD-server.</li>
                        <li>Open the command line and navigate to the extracted folder in the conf directory.</li>
                        <li>Open oxd-conf.json file.</li>
                        <li>If your server is using 8099 port, please change “port” number to free port, which is not used.</li>
                        <li>Set parameter “op_host”:“Your gluu-server-url (internal or external)”</li>
                        <li>Open the command line and navigate to the extracted folder in the bin directory.</li>
                        <li>For Linux environment, run sh <a href="http://oxd-start.sh">oxd-start.sh</a>&amp;.</li>
                        <li>For Windows environment, run oxd-start.bat.</li>
                        <li>After the server starts, go to Step 4.</li>
                    </ol>
                    <p><a target="_blank" href="https://oxd.gluu.org/docs/oxdserver/install/">Oxd-server installation gide</a>.</p>
                    <h2><a id="Step_6_General_73"></a>Step 4. General</h2>
                    <p><img src="<?php echo plugins_url('docu/1.png', __FILE__)?>" alt="General"></p>
                    <ol>
                        <li>Admin Email: please add your or admin email address for registrating site in Gluu server.</li>
                        <li>Port number: choose that port which is using oxd-server (see in oxd-server/conf/oxd-conf.json file).</li>
                        <li>Click <code>Next</code> to continue.</li>
                    </ol>
                    <p>If You are successfully registered in gluu server, you will see bottom page.</p>
                    <p><img src="<?php echo plugins_url('docu/2.png', __FILE__)?>" alt="oxD_id"></p>
                    <p>For making sure go to your gluu server / OpenID Connect / Clients and search for your oxd ID</p>
                    <p>If you want to reset configurations click on Reset configurations button.</p>
                    <h2><a id="Step_8_OpenID_Connect_Configuration_89"></a>Step 5. OpenID Connect Configuration</h2>
                    <h3><a id="Scopes_93"></a>Scopes.</h3>
                    <p>You can look all scopes in your gluu server / OpenID Connect / Scopes and understand the meaning of  every scope.
                        Scopes are need for getting loged in users information from gluu server.
                        Pay attention to that, which scopes you are using that are switched on in your gluu server.</p>
                    <p>You can add, enable, disable and delete scope.
                        <img src="<?php echo plugins_url('docu/4.png', __FILE__)?>" alt="Scopes1"></p>
                    <h3><a id="Custom_scripts_104"></a>Custom scripts.</h3>
                    <p><img src="<?php echo plugins_url('docu/5.png', __FILE__)?>" alt="Customscripts"></p>
                    <p>You can look all custom scripts in your gluu server / Configuration / Manage Custom Scripts / and enable login type, which type you want.
                        Custom Script represent itself the type of login, at this moment gluu server supports (U2F, Duo, Google +, Basic) types.</p>
                    <h3><a id="Pay_attention_to_that_111"></a>Pay attention to that.</h3>
                    <ol>
                        <li>Which custom script you enable in your Wordpress site in order it must be switched on in gluu server too.</li>
                        <li>Which custom script you will be enable in OpenID Connect Configuration page, after saving that will be showed in Wordpress Configuration page too.</li>
                        <li>When you create new custom script, both fields are required.</li>
                    </ol>
                    <h2><a id="Step_9_Wordpress_Configuration_117"></a>Step 6. WordPress Configuration</h2>
                    <h3><a id="Customize_Login_Icons_1194"></a>Customize Login Icons</h3>
                    <p>Pay attention to that, if custom scripts are not enabled, nothing will be showed.
                        Customize shape, space between icons and size of the login icons.</p>
                    <h3><a id="Customize_Login_Icons_119"></a>Display Options</h3>
                    <ul>
                        <ol> If you enable Default Login Form,than login icons will be showed in wordpress Default Login page .</ol>
                        <ol> If you enable Default Registration Form,than login icons will be showed in wordpress Default Registration page .</ol>
                        <ol> If you enable Comment Form,than login icons will be showed near wordpress Comment Form.</ol>
                        <ol> If you enable WooCommerce Login Form,than login icons will be showed in wordpress WooCommerce Login page.</ol>
                    </ul>
                    <p><img src="<?php echo plugins_url('docu/6.png', __FILE__)?>" alt="WordpressConfiguration"></p>
                    <h2><a id="Step_10_Show_icons_in_frontend_1288"></a>Step 7.Widget OpenID Connect Single Sign On (SSO) </h2>
                    <p>
                        You can use plugin also as widget.
                        In your widget page find OpenID Connect Single Sign On (SSO) Widget and use.
                    </p>
                    <h2><a id="Step_10_Show_icons_in_frontend_126"></a>Step 8. Show icons in frontend</h2>
                    <p><img src="<?php echo plugins_url('docu/7.png', __FILE__)?>" alt="frontend"></p>
                </td>
            </tr>
            <tr>
                <td>
                    <h3><a  id="openid_question_login" class="oxd_openid_title_panel" >OpenID Connect Single Sign On (SSO) Plugin By Gluu</a></h3>
                    <div class="oxd_openid_help_desc" hidden="" id="openid_question_login_desc">
                        <h4><a  id="openid_question2"  >How to add login icons to frontend login page?</a></h4>
                        <div id="openid_question2_desc">
                            You can add social and gluu login icons to frontend login page using our shortcode [gluu_login].
                            Refer to 'Shortcode' tab to add customizations to Shortcode.
                        </div>
                        <hr>
                        <h4><a  id="openid_question4"  >How can I put OpenID Connect Single Sign On (SSO) icons on a page without using widgets?</a></h4>
                        <div  id="openid_question4_desc">
                            You can add social and gluu login icons to any page or custom login page using 'OpenID Connect Single Sign On (SSO) shortcode' [gluu_login].
                            Refer to 'Shortcode' tab to add customizations to Shortcode.
                        </div>

                        <h4 style="display: none"><a  id="openid_question12" >OpenID Connect Single Sign On (SSO) icons are not added to login/registration form.</a></h4>
                        <div  id="openid_question12_desc" style="display: none">
                            Your login/registration form may not be wordpress's default login/registration form.
                            In this case you can add social and gluu login icons to custom login/registration form using 'OpenID Connect Single Sign On (SSO) shortcode' [gluu_login].
                            Refer to 'Shortcode' tab to add customizations to Shortcode.
                        </div>
                        <h4 style="display: none"><a  id="openid_question3"  >How can I redirect to my blog page after login?</a></h4>
                        <div style="display: none" id="openid_question3_desc">
                            You can select one of the options from <b>Redirect URL after login</b> of <b>Display Option</b> section under <b>Wordpress Config</b> tab. <br>
                            1. Same page where user logged in <br>
                            2. Homepage <br>
                            3. Account Dsahboard <br>
                            4. Custom URL - Example: https://www.example.com <br>
                        </div>
                    </div>
                    <hr>
                </td>
            </tr>
            <tr>
                <td>
                    <h3><a id="openid_login_shortcode_title"  aria-expanded="false" >OpenID Connect Single Sign On (SSO) Shortcode</a></h3>
                    <div class="oxd_openid_help_desc" hidden="" id="openid_login_shortcode" style="font-size:13px !important">
                        Use the shortcode in the content of the required page/post where you want to display login icons.<br>
                        <b>Example:</b> <code>[gluu_login]</code>
                        <h4 style="margin-bottom:0 !important">For Icons</h4>
                        You can use request attributes to customize the icons. All attributes are optional.<br>
                        <b>Example:</b> <code>[gluu_login  shape="<?php echo get_option('gluu_oxd_openid_login_theme');?>" theme="<?php echo get_option('gluu_oxd_openid_login_custom_theme');?>" space="<?php echo get_option('gluu_oxd_login_icon_space')?>" size="<?php echo get_option('gluu_oxd_login_icon_custom_size')?>"]</code><br>

                        <br/>
                        You can use a shortcode in a PHP file like this: &nbsp;&nbsp;
                        &nbsp;
                        <code>&lt;&#63;php echo do_shortcode(‘SHORTCODE’) /&#63;&gt;</code>
                        <br>
                        Replace SHORTCODE in the above code with the required shortcode like [gluu_login theme="default"], so the final code looks like following :
                        <br>
                        <code>&lt;&#63;php echo do_shortcode('[gluu_login theme="default"]') &#63;&gt;</code>
                    </div>
                    <hr>
                </td>
            </tr>
            <tr style="display: none">
                <td>
                    <h3>
                        <a  id="openid_question_logout" class="oxd_openid_title_panel" >Logout Redirection</a>
                    </h3>
                    <div class="oxd_openid_help_desc" hidden="" id="openid_question_logout_desc">
                        <h4><a  id="openid_question11"  >After logout I am redirected to blank page</a></h4>
                        <div  id="openid_question11_desc">
                            Your theme and OpenID Connect Single Sign On (SSO) Plugin By Gluu may conflict during logout. To resolve it you need to uncheck <b>Enable Logout Redirection</b> checkbox under <b>Display Option</b> of <b>Social Login</b> tab.
                        </div>
                    </div>
                    <hr>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <?php
}



