<?php
if(!defined("ABSPATH")) { define("ABSPATH", __DIR__); }

include_once "app/include.php";
include_once "app/functions.php";

if(Auth::check()){
    $menu = json_decode(file_get_contents(Path::app( "menu-user.json" )), true);
}else{
    $menu = json_decode(file_get_contents(Path::app( "menu.json" )), true);
}
Render::setMenu($menu);

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE );
header('Pragma:no-cache');


$route = new FileInfo( Path::page( Request::route() . ".php") );


if(! $route->exists()){
    Response::error(404, "404 Not Found");
    exit;
}

require $route->fullName();
