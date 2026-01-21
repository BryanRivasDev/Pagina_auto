<?php
// Function to check if user is logged in
function checkLoggedIn() {
    if(!isset($_SESSION)) { 
        session_start(); 
    } 
    
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        header("location: login.php");
        exit;
    }
}
?>
