<?php
// index.php
$headerTitle = "MoemoePetMart";
$pageTitle = "Page 1 - Hello World";
ob_start();
?>
<h2>Hello World</h2>
<?php
$content = ob_get_clean();
include 'includes/Base.php';
?>
