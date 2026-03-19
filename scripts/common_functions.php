<?php 

function getPageTitle($title = null) {
    if (!empty($title)) {
        return "RevTrack - ". $title;
    }

    $pageAllData = $_SERVER['SCRIPT_NAME'];
    $pageData = basename($pageAllData, '.php');
    $pageTitle = ucfirst($pageData);

    return 'RevTrack -  ' . $pageTitle;
}

?>