<?php

require_once __DIR__ . "/vendor/autoload.php";

switch (@parse_url($_SERVER['REQUEST_URI'])['path']) {
    case '/':
        require 'start.php';
        break;
    case '/start':
        require 'start.php';
        break;
    case '/test':
        require 'test.php';
        break;
    default:
        http_response_code(404);
        exit('Not Found');
}

?>