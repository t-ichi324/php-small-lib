<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name='robots' content='noindex, nofollow' />
<?php Render::showTitleTag() ?>
<link rel="stylesheet" href="<?= Url::public("style.css") . "?v=" . time(); ?>"/>
</head>
<body>
<header>
    <a class="title" href="<?= Url::root(); ?>"><h1><?= h(SysConf::SITE_NAME); ?></h1></a>
    <?php Render::showMenu(); ?>
</header>
<main>
<?php Render::showMessage(); ?>

