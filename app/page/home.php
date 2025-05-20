<?php 
Auth::requireLogin();

$r = ActionHandler::getInstance(function(){
    //デフォルト処理

})->has("save", function(){
    //保存処理

})->has("search", function(){
    //検索

})->hasPost("remove", function(){
    //削除処理

})->run();

Render::setTitle("ホーム");
Response::view("home");

