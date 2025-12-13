<?php
require '_base.php';
// logout.php (update to save cart before destroy)
save_cart_to_db();  // NEW: Save cart to DB
session_destroy();
temp('info', 'Logged out successfully! Come back soon ♡');
redirect('/login.php');