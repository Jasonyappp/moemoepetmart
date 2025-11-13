<?php
// includes/Footer.php - MoeMoePetMart Footer
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<footer>
  <nav>
    <a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Page 1</a>
    <a href="page2.php" class="<?php echo $currentPage === 'page2.php' ? 'active' : ''; ?>">Page 2</a>
  </nav>
</footer>