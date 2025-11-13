<?php
// includes/Base.php - MoeMoePetMart Base Template
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo $pageTitle ?? 'MoeMoePetMart'; ?></title>
  <link rel="stylesheet" href="assets/style.css" />
</head>
<body>
  <?php include 'Header.php'; ?>

  <main>
    <?php echo $content ?? ''; ?>
  </main>

  <?php include 'Footer.php'; ?>
</body>
</html>