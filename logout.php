<?php
require '_base.php';
session_destroy();
temp('info', 'Logged out successfully! Come back soon ♡');
redirect('/login.php');