<?php 

function create_session_atempts($action) : array {
    if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
    }

    if (!isset($_SESSION['bad_actions'])) {
        $_SESSION['bad_actions'] = [];
    }

    if (isset($_SESSION['bad_actions'][$action])) {
        $_SESSION['bad_actions'][$action]++;
    } else {
        $_SESSION['bad_actions'][$action] = 1;
    }

    return $_SESSION['bad_actions'];
}

function check_session_attempts($action) : int {
    if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
    }

    if (!isset($_SESSION['bad_actions'])) {
        $_SESSION['bad_actions'] = [];
    }

    if (isset($_SESSION['bad_actions'][$action])) {
        return $_SESSION['bad_actions'][$action];
    } else {
        return 0;
    }
}

?>