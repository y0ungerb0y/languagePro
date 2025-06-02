<?php
include('routes.php');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


if (array_key_exists($uri, $routes)) {
    include_once($routes[$uri]);
} else {

    http_response_code(404); 
    include('app/views/404.php');
}

?>