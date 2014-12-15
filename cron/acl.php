<?php
function checkAccess() {
    if (isset($_SERVER['HTTP_HOST'])) {
        if (!isset($_SERVER['PHP_AUTH_USER'])
            || $_SERVER['PHP_AUTH_USER'] != 'titi'
            || $_SERVER['PHP_AUTH_PW'] != 'henry') {
            header('WWW-Authenticate: Basic realm="Ho ho ho"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'ah! bad credentials';
            exit;
        }
    }
}
