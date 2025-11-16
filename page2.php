<?php
// page2.php
$headerTitle = "MoemoePetMart";
$pageTitle = "Page 2 - Goodbye World";
ob_start();
?>
<h2>Goodbye World</h2>
<?php
$content = ob_get_clean();
include 'includes/Base.php';
?>

// testcjw