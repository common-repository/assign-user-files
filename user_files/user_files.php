<?php
/*
Plugin Name: Users to Files
Description: This plugin retrieves a list of users from wordpress, retrieves media files from wordpress, and assigns the files to the corresponding users so that no users can see any files that are not intended for them.
Version: 1.0
Author: Marc Dalton
*/

/**
 * Add the menu option
 */
function userfiles_plugin_menu()
{
    add_options_page(
            'User Files Options', 'User Files', 10, 'user_files/ret_files.php'
    );
}

/**
 * Installs the plugin.
 * 
 * @global object $wpdb 
 */
function ftu_install () 
{
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    global $wpdb;
    
    $table_name = $wpdb->prefix. "user_files_link";
    
    $sql = "CREATE TABLE " . $table_name . " ( ". PHP_EOL
	 . "user_id mediumint(10) NOT NULL, ". PHP_EOL 
         . "attachment_id mediumint(10) NOT NULL, ". PHP_EOL 
	 . " PRIMARY KEY  id (user_id, attachment_id) ". PHP_EOL 
	 . ");";

    dbDelta($sql);
    add_option("ftu_db_version", "1.0");
}

/**
 * Displays the dashboard widget with the linked files for the logged in user.
 * 
 * @global object $current_user
 * @global object $wpdb 
 */
function add_dashboard_widget_function() 
{
    global $current_user, $wpdb;
    
    $table_name = $wpdb->prefix. "user_files_link";
    
    get_currentuserinfo();
    
    // assign the current user's id to $user_ID
    $user_ID = $current_user->ID;
    
    $linkList = $wpdb->get_results(
            "SELECT * FROM ". $table_name. " WHERE user_id = ". $user_ID
    );
    
    $media_query = new WP_Query(
        array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => -1,
        )
    );

    $fileList = array();
    $counter  = 0;
    
    foreach ($media_query->posts as $post) {
        $fileList[] = array(
            $post->post_name,
            $post->ID,
            $post->post_content,
            $post->guid
        );
    }
  
    echo 'Below are the files assigned to your user account, you can download '
       . 'these files by clicking the links, or by right clicking them and '
       . 'selecting Save link as...<br/><br/> '
       . '<table class="widefat"> ' 
       . '  <thead>'. PHP_EOL
       . '    <tr>'. PHP_EOL
       . '      <th>'. PHP_EOL
       . '        Files'. PHP_EOL
       . '      </th>'. PHP_EOL
       . '    </tr>'. PHP_EOL
       . '  </thead>'. PHP_EOL
       . '  <tbody>';
    
    if (count($linkList) > 0) {
        foreach ($linkList as $record) {
            foreach ($fileList as $file) {
                if ($file[1] == $record->attachment_id) {
                    $filename = $file[0];
                    $fileDesc = $file[2];
                    $fileURL  = $file[3];
                    
                }
            }
            
            echo str_replace(
                    array("%file"),
                    array(ucwords(strtolower($filename))),
                    '<tr><td style="padding: 5px;"><a href="'. $fileURL
                  . '">%file</a><br/>'. $fileDesc. '</td></tr>'
            );
            $counter++;
        }
    } else {
        echo '<tr><td>No files available.</td></tr>';
    }
    
    echo '  </tbody>'. PHP_EOL
       . '</table>';
}

/**
 * Adds the dashboard widget for display.
 * 
 * @global array $wp_meta_boxes 
 */
function add_dashboard_widget() 
{
    global $wp_meta_boxes;
    
    if (isset($wp_meta_boxes['dashboard']['side']) === true) {
        foreach ($wp_meta_boxes['dashboard']['side'] as $key=>$side) {
            unset($wp_meta_boxes['dashboard']['side'][$key]);
        }
    }
    
    if (isset($wp_meta_boxes['dashboard']['normal']) === true) {
        foreach ($wp_meta_boxes['dashboard']['normal'] as $key=>$side) {
            unset($wp_meta_boxes['dashboard']['normal'][$key]);
        }
    }
    
    wp_add_dashboard_widget(
            'userfiles_dashboard', 
            'Available Files', 
            'add_dashboard_widget_function'
    );	
}

// Install the plugin
register_activation_hook(__FILE__, 'ftu_install');

// Add the menu
add_action('admin_menu', 'userfiles_plugin_menu');

// Add the dashboard widget
add_action('wp_dashboard_setup', 'add_dashboard_widget' );