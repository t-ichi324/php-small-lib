<?php 
$f = Form::formRequest();
$f->rule("user_id", "ユーザID")->required();
$f->rule("password", "パスワード")->required();

$ret = ActionHandler::getInstance()->default(function() use ($f){

    //デフォルト処理
    $f->set("password", "");


})->isPost(function() use ($f){
    //ログイン処理
    if($f->validate()){
        if( $f->get("user_id") === "test" &&  $f->get("password") === "test" ){

            Auth::login("test-user");
            Response::redirect("home");
        }else{
            $f->addError('password', 'ユーザーIDまたはパスワードが正しくありません');
        }
    }

})->run();


Render::setTitle("ログイン");
Render::setForm($f, true);

Response::view("login");
