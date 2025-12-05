<?php
// product_set_main.php
require '../_base.php';
require_login();
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id']) && !empty($_POST['photo'])) {
    $id = (int)$_POST['id'];
    $photo = basename($_POST['photo']);

    if (is_file("../admin/uploads/products/$photo")) {
        $_db->prepare("UPDATE product SET photo_name = ? WHERE product_id = ?")
             ->execute([$photo, $id]);
        echo 'OK';
        exit;
    }
}
echo 'FAIL';