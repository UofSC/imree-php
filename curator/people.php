<?php

require_once '../../config.php';
$page = new page("", "People");
$string = "";
if(logged_in()) {
   
   if(isset($_POST['action']) AND $_POST['action'] === 'user_create') {
        global $imree_curator_absolute_path;
        $ulogin = new uLogin();
        if(isset($_POST['new_username']) AND $_POST['new_username'] !== "") {
            if(filter_input(INPUT_POST, 'new_username',FILTER_VALIDATE_EMAIL)) {
                $password = random_string(12);
                if($ulogin->CreateUser(filter_input(INPUT_POST, 'new_username'), $password)) {
                    $page->add_message("New User ".filter_input(INPUT_POST, 'new_username')." added.");
                    send_gmail(filter_input(INPUT_POST, "new_username"),
                            "You have a new account on IMREE",
                            "<p>You have been granted access to $imree_curator_absolute_path.</p>
                                You can login using your email address (".filter_input(INPUT_POST, 'new_username').") 
                                and this temporary password: $password</p>");
                    $conn = db_connect();
                    db_exec($conn, build_insert_query($conn, 'people', array(
                        'person_name_first' => $_POST['person_name_first'],
                        'person_name_last' => $_POST['person_name_last'],
                        'person_title' => $_POST['person_title'],
                        'ul_user_id' => $ulogin->Uid($_POST['new_username']),
                    )));
                } else {
                    $page->add_message("Failed to create new user",'error');
                }
            } else {
                $page->add_message("Failed to create new user. Invalid Email supplied.",'error');
            }
        } else if(isset($_POST['new_username']) OR $_POST['new_username'] === "") {
            //create "person" without an account attached
            $conn = db_connect();
            db_exec($conn, build_insert_query($conn, 'people', array(
                'person_name_first' => $_POST['person_name_first'],
                'person_name_last' => $_POST['person_name_last'],
                'person_title' => $_POST['person_title'],
                'ul_user_id' => '',
            )));
        }
    }
    
    $elements = array(
        new f_data_element('First Name','person_name_first','text'),
        new f_data_element('Last Name','person_name_last','text'),
        new f_data_element('Title','person_title','text'),
    );
    
    if(isset($_GET['row_id'])) {
        $conn = db_connect();
        $string .= f_data($elements, $conn, "people", "person_id", $_GET['row_id']);
        $string .= f_data_assignments_one2many($conn, "person_role_assignment", "person_id", "role_id", $_GET['row_id'], "roles", "role_id", "role_title", "role_assignments_form");
    } else {
        $string = f_data_list(db_connect(), "people", "person_id", array('person_name_last','person_name_first'));
    
    
        $string .= "
            <form action='#' method='POST'>
                <fieldset><legend>New User</legend>
                    <input type='hidden' name='action' value='user_create'>
                    <label for='person_name_first'>First Name</label><input type='text' name='person_name_first' id='person_name_first'><br>
                    <label for='person_name_last'>Last Name</label><input type='text' name='person_name_last'  id='person_name_last'><br>
                    <label for='person_title'>Title</label><input type='text' name='person_title'  id='person_title'><br>
                    <label for='new_username'>New Email</label><input type='text' name='new_username'  id='new_username'><br>
                    <button type='submit'>Create new user</button>
                </fieldset>
            </form>  
            ";
    }
    
} else {
    $string = "You need to log in to use this feature.";
}
$page->append_content($string);
echo $page;
