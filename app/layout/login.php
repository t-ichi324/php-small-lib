<?php $f = Render::getForm(); ?>
<form method="post">
    <input type="text" <?php $f->rend("user_id")->name()->value(); ?>>
    <input type="password" <?php $f->rend("password")->name(); ?>>
    <button type="submit">OK</button>
</form>