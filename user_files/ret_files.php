<?php
    global $wpdb;
                    
    $table_name = $wpdb->prefix . "user_files_link";
    
    if (count($_POST) > 0) {
        if ($_POST['action'] == 'add') {
            $rows_affected = $wpdb->insert(
                    $table_name,
                    array(
                        'user_id'       => $_POST['users'], 
                        'attachment_id' => $_POST['files']
                    )
            );
        }
        
        if ($_POST['action'] == 'delete') {
            $rows_affected = $wpdb->query(
                    "DELETE FROM ". $table_name. " WHERE user_id = ". $_POST['userID']
                  . " AND attachment_id = ". $_POST['fileID']
            );
        }
    }
    
    $media_query = new WP_Query(
            array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => -1,
            )
    );

    $fileList = array();

    foreach ($media_query->posts as $post) {
        $fileList[] = array(
            $post->post_name,
            $post->ID
        );
    }
?>

<div class="wrap">
    <div id="icon-options-general" class="icon32">
        <br />
    </div>
    
    <h2>User Files Settings</h2>
    
    <p>
        To assign a file to a user, select the file from the left dropdown below,
        select the user you want to assign it to from the right dropdown below, and
        click the Assign File to User button.
    </p>
    
    <form method="post" action="">
        <table>
            <tr>
                <td>
                    Files:

                    <?php

                        // display $list in selectbox here;
                        echo '<select name="files">';

                        foreach ($fileList as $file) {
                            echo '<option value="'. $file[1]. '">'.
                            ucwords(strtolower($file[0])). '</option>';
                        }

                        echo '</select>';

                    ?>
                </td>
                <td>
                    Users:

                    <select name="users">
                    <?php
                        // Get the users from the database
                        $userDbArray = $wpdb->get_col(
                                "SELECT ". $wpdb->users. ".ID FROM ". $wpdb->users
                        );

                        $userArray = array();
 
                        // Iterate through the user array and display the user data
                        // in the option
                        foreach ($userDbArray as $userData) {
                            $user        = get_userdata($userData);
                            $userArray[] = $user;

                            echo '<option value="'. $user->ID. '">'
                               . ucwords(strtolower($user->user_nicename)). '</option>';
                        }
                    ?>
                    </select>
                </td>
                <td>
                    <input type="hidden" name="action" value="add" />
                    <input type="submit" class="button-primary" value="Assign File to User" />
                </td>
            </tr>
        </table>
    </form>
    
    <br/>
    
    <h2>Existing User File Links</h2>
    
    <p>
        Below are the files that are already assigned to the users. If you would like
        to remove a file from a user, click the Delete button on the line of the user
        file link you would like to remove.
    </p>
    
    <table class="widefat" style="width: 40%;">
        <thead>
            <tr>
                <th>
                    Users
                </th>
                <th>
                    Files
                </th>
                <th>
                    Delete
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Get the current links we have
            $linkList = $wpdb->get_results("SELECT * FROM ". $table_name);

            // Iterate through existing links
            foreach ($linkList as $record) {
                // Go through the user array to find the user of this link
                foreach ($userArray as $user) {
                    if ($user->ID == $record->user_id) {
                        $username = $user->user_nicename;
                    }
                }
                
                // Go through the file array to find the file
                foreach ($fileList as $file) {
                    if ($file[1] == $record->attachment_id) {
                        $filename = $file[0];
                    }
                }
                
                // Display it in the table
                echo str_replace(
                        array("%user", "%file"),
                        array(ucwords( strtolower($username)), ucwords( strtolower($filename))),
                        '<tr><td>%user</td><td>%file</td><td style="width: 50px;"> '. PHP_EOL
                      . '<form method="post" action=""> '. PHP_EOL
                      . '<input type="hidden" name="action" value="delete"> '. PHP_EOL
                      . '<input type="hidden" name="userID" value="'.$record->user_id.'"> '. PHP_EOL
                      . '<input type="hidden" name="fileID" value="'.$record->attachment_id.'"> '. PHP_EOL
                      . '<input type="submit" value="Delete Record" class="button" /> '. PHP_EOL
                      . '</form></td></tr>'
                );
            }

            ?>
        </tbody>
    </table>
</div>
