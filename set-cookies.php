<?php
/*
Plugin Name: Set Custom Cookies
Plugin URI: https://github.com/roma-i/set-cookies.git
Description: Adding custom Cookies to website.
Version: 1.0
Author: Roman I.
*/

require_once (dirname(__FILE__) . '/set-cookies.php');

/* 
	Admin enqueue styles and scripts 
*/

function cookie_styling() {
    wp_enqueue_style( 'cookies_css', plugin_dir_url( __FILE__ ) . 'assets/css/cookies.css' );
    wp_enqueue_media();
    wp_deregister_script( 'jquery-core' );
    wp_register_script( 'jquery-core', '//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js');
    wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'cookie_styling' );

class set_custom_cookies {

	//Adding of the settings and the fields
    public function __construct() {
    	add_action( 'admin_menu', array( $this, 'cookies_settings_page' ) );
    	add_action( 'admin_init', array( $this, 'cookies_settings_sections' ) );
    	add_action( 'admin_init', array( $this, 'cookies_settings_fields' ) );
    }

    //Menu item and page content
    public function cookies_settings_page() {
    	$page_title = __('Custom Cookies Settings Page', 'set-cookies');
    	$menu_title = __('Custom Cookies', 'set-cookies');
    	$capability = 'manage_options';
    	$slug = 'custom_cookies';
    	$callback = array( $this, 'cookies_settings_page_content' );
    	$icon = plugin_dir_url( __FILE__ ) . 'assets/images/cookie.png';
    	$position = 100;
    	add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
    }
    public function cookies_settings_page_content() { ?>
    	<h1><?php _e('Yummy Cookie!', 'set-cookies'); ?></h1>
        <p><?php _e('Inform the visitors that the website is using cookies.', 'set-cookies'); ?></p>
    		<?php
        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ){
              $this->admin_notice();
        } ?>
		<form method="POST" action="options.php">
            <?php
                settings_fields( 'cookies_fields' );
                do_settings_sections( 'cookies_fields' );
                submit_button();
            ?>
		</form>
    <?php }
    
    /*
		Add admin message that settings are saved
    */
    public function admin_notice() { ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Cookies settings are saved'); ?></p>
        </div><?php
    }

    /*
		Add sections for different fields
    */
	public function cookies_settings_sections() {
	    add_settings_section( 'message_section', __('Message', 'set-cookies'), false, 'cookies_fields' );
	    add_settings_section( 'color_section', __('Styling', 'set-cookies'), false, 'cookies_fields' );
	    add_settings_section( 'geolocation_section', __('Geolocation', 'set-cookies'), false, 'cookies_fields' );
	    register_setting( 'cookies_fields');
	}

    /*
		Add fields to sections
    */
    public function cookies_settings_fields() {
        $fields = array(
        	array(
        		'id' => 'message_field',
        		'label' => __('Cookies message','set-cookies'),
        		'section' => 'message_section',
        		'type' => 'wysiwig',
        	),
        	array(
        		'id' => 'cookies_link',
        		'label' => __('Terms and conditions link', 'set-cookies'),
        		'section' => 'message_section',
        		'type' => 'text'
        	),
        	array(
        		'id' => 'banner_color_field',
        		'label' => __('Banner color (hex)', 'set-cookies'),
        		'section' => 'color_section',
        		'type' => 'text'
        	),
        	array(
        		'id' => 'accept_color_field',
        		'label' => __('“Accept” button colour (hex)', 'set-cookies'),
        		'section' => 'color_section',
        		'type' => 'text'
        	),
        	array(
        		'id' => 'geo_asia',
        		'label' => __('Content for Japan', 'set-cookies'),
        		'section' => 'geolocation_section',
        		'type' => 'textarea'
        	),
        	array(
        		'id' => 'geo_sa',
        		'label' => __('Content for Brazil', 'set-cookies'),
        		'section' => 'geolocation_section',
        		'type' => 'textarea'
        	),
        	array(
        		'id' => 'geo_arabic',
        		'label' => __('Content for UAE', 'set-cookies'),
        		'section' => 'geolocation_section',
        		'type' => 'textarea'
        	)
        );
    	foreach( $fields as $field ){
        	add_settings_field( $field['id'], $field['label'], array( $this, 'cookies_field_callback' ), 'cookies_fields', $field['section'], $field );
            register_setting( 'cookies_fields', $field['id'] );
    	}
    }

    public function cookies_field_callback( $arguments ) {
        $value = get_option( $arguments['id'] );
        if( ! $value ) {
            $value = $arguments['default'];
        }
        switch( $arguments['type'] ){

            case 'wysiwig':
            		wp_editor($value, $arguments['id'], array(
						'wpautop'       => 1,
						'media_buttons' => 1,
						'textarea_name' => $arguments['id'], 
						'textarea_rows' => 10,
						'tabindex'      => null,
						'editor_css'    => '',
						'editor_class'  => '',
						'teeny'         => 0,
						'dfw'           => 0,
						'tinymce'       => 1,
						'quicktags'     => 1,
						'drag_drop_upload' => false
					) );
            	break;

            case 'text': 
            	printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['id'], $arguments['type'], $arguments['placeholder'], $value );
                break;

            case 'textarea':
                printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="100">%3$s</textarea>', $arguments['id'], $arguments['placeholder'], $value );
                break;
        }
    }
}
new set_custom_cookies();


/* 
	Add cookies message to footer
*/

if (isset($_GET['accept-cookies'])) {
	setcookie('accept-cookies', 'true', time() + 31556925);
	header('Location: ./');
}    


function footer_cookies() {
if (!isset($_COOKIE['accept-cookies'])) {

    /*
        Get fields data
    */
    $link = get_option('cookies_link');
    $banner_color = get_option('banner_color_field');
    $accept_color = get_option('accept_color_field');
    
    /*
        Get continent area for showing different cookies messages
    */
    $ip = $_SERVER['REMOTE_ADDR']; // This will contain the ip of the request
    $dataArray = json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));
    $country = $dataArray->geoplugin_countryCode;
    $japan_m = get_option('geo_asia');
    $brazil_m = get_option('geo_sa');
    $uae_m = get_option('geo_arabic');

    /*
        Detect different messages for different countries
    */
    if($country = 'JP' && (!empty($japan_m))) {
        $message = $japan_m;
    } elseif($country = "BR" && (!empty($brazil_m))) {
        $message = $brazil_m;
    } elseif($country = "AE" && (!empty($uae_m))) {
        $message = $uae_m;
    } else {
        $message = get_option('message_field');
    }


    ?>
	    <div class="cookies-section"
            <?php if(isset($banner_color)) { ?> style="background: <?php esc_html_e($banner_color); ?>;" <?php } ?>
        >
	    	<div class="items-wrap">
	    		<div class="section-img"><img src="<?php echo(plugin_dir_url( __FILE__ ) . 'assets/images/cookies-img.png'); ?>" alt=""></div>
	    		<div class="section-message">
                    <?php if(!empty($message) && isset($message)) {
                        esc_html_e($message);
                    } else { 
                    _e('We are using cookies on website. We use cookies to ensure that we give you the best experience on our website.'); } ?> 
                    <?php if(isset($link) && !empty($link)) { ?>
                        <a href="<?php esc_html_e($link); ?>" target='_blank'><?php _e('Learn More.'); ?></a>
                    <?php } else { 
                        _e('Please, add link to terms and conditions page in plugin settings.'); 
                    } ?>
                </div>
	    		<div class="section-button">
                    <a 
                        <?php if(isset($accept_color)) { ?> style="background: <?php esc_html_e($accept_color); ?>;" <?php } ?>
                        href="?accept-cookies" class="accept-button" type="submit"><?php _e('Accept'); ?>
                    </a>
                </div>
	    		<img src="<?php echo(plugin_dir_url( __FILE__ ) . 'assets/images/close-img.png'); ?>" alt="close" class="close-button">
	    	</div>
	    </div>
        <script src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/js/jquery.cookie.js' ?>"></script>
        <script src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/js/cookies.js' ?>"></script>
    <?php };
}

add_action( 'wp_footer', 'footer_cookies', 10, 1 ); 
