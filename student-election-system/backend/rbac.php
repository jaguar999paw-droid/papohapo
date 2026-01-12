<?php
function require_role($roles){
    session_start();
    if(!isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles)){
        http_response_code(403);
        exit("Access denied");
    }
}
