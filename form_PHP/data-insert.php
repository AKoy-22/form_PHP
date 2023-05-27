<?php
//Akiko Koyama 
// 2023-04-23 
require 'helpers.php';
session_start();

display_html_header("Assignment3");
if($_SERVER['REQUEST_METHOD']=='POST'){
    if($_POST['submit']=='Submit'){
        $errors=validate_form();
        if($errors){
            display_form($errors);
        }else{
            confirm_form();
        }
    }if($_POST['submit']==='Confirm'){
        process_form();  
    }if($_POST['submit']==='Edit'){
        display_form();
    }
}else{
    display_form();
    session_unset();
}
display_html_footer();




?>