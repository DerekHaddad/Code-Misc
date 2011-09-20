<?php
/*Add Featured Image*/
add_theme_support('post-thumbnails');

/*Get all of the images attached to the current post*/
function get_images() {
	global $post;
	$photos = get_children( array('post_parent' => $post->ID, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID') );
	$results = array();
	if ($photos) {
		foreach ($photos as $photo) {
			// get the correct image html for the selected size
			$results[] = wp_get_attachment_image_src($photo->ID, $size);
		}
	}
	return $results;
}

/*Backend Justifications*/
/*********************************************************************************************/

/*Remove Menu Links From Backend*/
function remove_menus () {

    global $menu;
    $restricted = array(__('Dashboard'), __('Media'), __('Links'), __('Appearance'), __('Tools'), __('Settings'), __('Comments'), __('Plugins'));
    end ($menu);
    while (prev($menu)){
        $value = explode(' ',$menu[key($menu)][0]);
        if(in_array($value[0] != NULL?$value[0]:"" , $restricted)){
            unset($menu[key($menu)]);

        }
    }
}
add_action('admin_menu', 'remove_menus');

/*Custom Login Logo*/
function my_custom_login_logo() {
    echo
     '<style type="text/css">
     h1 a { background-image:url('.get_bloginfo('template_directory').'/images/custom-login-logo.gif) !important; }
     </style>';
}
add_action('login_head', 'my_custom_login_logo');

/*Backend Logo*/
function my_custom_logo() {
   echo
    '<style type="text/css">
    #header-logo { background-image: url('.get_bloginfo('template_directory').'/images/custom-logo.png) !important; }
    </style>';
}
add_action('admin_head', 'my_custom_logo');

/*Disable Update*/
if ( !current_user_can( 'edit_users' ) ) {
  add_action( 'init', create_function( '$a', "remove_action( 'init', 'wp_version_check' );" ), 2 );
  add_filter( 'pre_option_update_core', create_function( '$a', "return null;" ) );
}

/*Monitor Server: Will show server status on dashboard with logs*/
function slt_PHPErrorsWidget() {
	$logfile = '/home/path/logs/php-errors.log'; // Enter the server path to your logs file here
	$displayErrorsLimit = 100; // The maximum number of errors to display in the widget
	$errorLengthLimit = 300; // The maximum number of characters to display for each error
	$fileCleared = false;
	$userCanClearLog = current_user_can( 'manage_options' );
	// Clear file?
	if ( $userCanClearLog && isset( $_GET["slt-php-errors"] ) && $_GET["slt-php-errors"]=="clear" ) {
		$handle = fopen( $logfile, "w" );
		fclose( $handle );
		$fileCleared = true;
	}
	// Read file
	if ( file_exists( $logfile ) ) {
		$errors = file( $logfile );
		$errors = array_reverse( $errors );
		if ( $fileCleared ) echo '<p><em>File cleared.</em></p>';
		if ( $errors ) {
			echo '<p>'.count( $errors ).' error';
			if ( $errors != 1 ) echo 's';
			echo '.';
			if ( $userCanClearLog ) echo ' [ <b><a href="'.get_bloginfo("url").'/wp-admin/?slt-php-errors=clear" onclick="return confirm(\'Are you sure?\');">CLEAR LOG FILE</a></b> ]';
			echo '</p>';
			echo '<div id="slt-php-errors" style="height:250px;overflow:scroll;padding:2px;background-color:#faf9f7;border:1px solid #ccc;">';
			echo '<ol style="padding:0;margin:0;">';
			$i = 0;
			foreach ( $errors as $error ) {
				echo '<li style="padding:2px 4px 6px;border-bottom:1px solid #ececec;">';
				$errorOutput = preg_replace( '/\[([^\]]+)\]/', '<b>[$1]</b>', $error, 1 );
				if ( strlen( $errorOutput ) > $errorLengthLimit ) {
					echo substr( $errorOutput, 0, $errorLengthLimit ).' [...]';
				} else {
					echo $errorOutput;
				}
				echo '</li>';
				$i++;
				if ( $i > $displayErrorsLimit ) {
					echo '<li style="padding:2px;border-bottom:2px solid #ccc;"><em>More than '.$displayErrorsLimit.' errors in log...</em></li>';
					break;
				}
			}
			echo '</ol></div>';
		} else {
			echo '<p>No errors currently logged.</p>';
		}
	} else {
		echo '<p><em>There was a problem reading the error log file.</em></p>';
	}
}

// Add widgets
function slt_dashboardWidgets() {
	wp_add_dashboard_widget( 'slt-php-errors', 'PHP errors', 'slt_PHPErrorsWidget' );
}
add_action( 'wp_dashboard_setup', 'slt_dashboardWidgets' );
/*End of monitor Server*/

/*Remove Dashboard Widgets*/
function example_remove_dashboard_widgets() {
	// Globalize the metaboxes array, this holds all the widgets for wp-admin
 	global $wp_meta_boxes;

	// Remove the incomming links widget
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);

	// Remove the incomming Plugins widget
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);

        // Remove the incomming recent drafts widget
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);

	// Remove right now
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
        remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
}

// Hoook into the 'wp_dashboard_setup' action to register our function
add_action('wp_dashboard_setup', 'example_remove_dashboard_widgets' );
/*End of dashboard modifications*/

/*Customize the footer*/
function modify_footer_admin () {
  echo 'Developed by <a href="#">DonDerek Haddad || ArtinCode</a>.';
}

add_filter('admin_footer_text', 'modify_footer_admin');

add_filter(  'gettext',  'change_post_to_article'  );
add_filter(  'ngettext',  'change_post_to_article'  );

/*Change Post Name to Project*/
function change_post_to_article( $translated ) {
   $translated = str_ireplace(  'Post',  'Project',  $translated );
   return $translated;
}

/* Remove wordpress from source code*/
remove_action('wp_head', 'wp_generator');

/*ShortCodes to get Text inside content, Using RegEx you can pull anything out of the post content*/
function fmc($text) {
        global $post;
        if ( '' == $text ) {
                $text = get_the_content('');
                $text = apply_filters('the_content', $text);
                $text = str_replace('\]\]\>', ']]&gt;', $text);
                $text = preg_replace('@<script[^>]*?>.*?</script>@si', '', $text);
                $text = strip_tags($text, '<p><a><ul<li>');
                $excerpt_length = 500;
                $words = explode(' ', $text, $excerpt_length + 1);
                if (count($words)> $excerpt_length) {
                        array_pop($words);
                        array_push($words, '[...]');
                        $text = implode(' ', $words);
                }
        }
        return $text;
}
remove_filter('get_the_excerpt', 'wp_trim_excerpt');
add_filter('get_the_excerpt', 'fmc');
?>