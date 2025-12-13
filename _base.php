<?php

// ============================================================================
// PHP Setups
// ============================================================================

date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

// ============================================================================
// General Page Functions
// ============================================================================

// Is GET request?
function is_get() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

// Is POST request?
function is_post() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

// Obtain GET parameter
function get($key, $value = null) {
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain POST parameter
function post($key, $value = null) {
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain REQUEST (GET and POST) parameter
function req($key, $value = null) {
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Redirect to URL
function redirect($url = null) {
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

// Set or get temporary session variable
function temp($key, $value = null) {
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    }
    else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
}

// ============================================================================
// Authentication Functions
// ============================================================================

// Check if user is logged in
function is_login() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

// Get current user ID
function user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Get current username
function username() {
    return $_SESSION['user'] ?? null;
}

// Get current user role
function user_role() {
    return $_SESSION['role'] ?? null;
}

// Get current user data
function current_user() {
    global $_db;
    if (!is_login()) return null;
    
    $stm = $_db->prepare("SELECT * FROM users WHERE username = ?");
    $stm->execute([username()]);
    return $stm->fetch();
}

// Login user
function login($username, $role) {
    $_SESSION['user'] = $username;
    $_SESSION['role'] = $role;
}

// Logout user
function logout() {
    unset($_SESSION['user'], $_SESSION['role']);
    session_destroy();
}

// Require login - redirect to login if not logged in
function require_login() {
    if (!is_login()) {
        temp('info', 'Please login first~ ♡');
        redirect('/login.php');
    }
}

// Require admin - redirect if not admin
function require_admin() {
    require_login();
    if (user_role() !== 'admin') {
        temp('error', 'Admin access required!');
        redirect('/');
    }
}

// ============================================================================
// HTML Helpers
// ============================================================================

// Encode HTML special characters
function encode($value) {
    return htmlentities($value);
}

// Generate <input type='text'>
function html_text($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='radio'> list
function html_radios($key, $items, $br = false) {
    $value = encode($GLOBALS[$key] ?? '');
    echo '<div>';
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'checked' : '';
        echo "<label><input type='radio' id='{$key}_$id' name='$key' value='$id' $state>$text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

// Generate <select>
function html_select($key, $items, $default = '- Select One -', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}

// ============================================================================
// Error Handlings
// ============================================================================

// Global error array
$_err = [];

// Generate <span class='err'>
function err($key) {
    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='err'>$_err[$key]</span>";
    }
    else {
        echo '<span></span>';
    }
}

// ============================================================================
// Database Setups and Functions
// ============================================================================

// Global PDO object
$_db = new PDO('mysql:host=localhost;dbname=moemoe_petmart;charset=utf8mb4', 'root', '', [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);

// Is unique?
function is_unique($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() == 0;
}

// Is exists?
function is_exists($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}
// Obtain uploaded file --> cast to object
function get_file($key) {
    $f = $_FILES[$key] ?? null;
    
    if ($f && $f['error'] == 0) {
        return (object)$f;
    }

    return null;
}

// Crop, resize and save photo
function save_photo($f, $folder, $width = 800, $height = 800) {
   

    $photo = uniqid('img_') . '.jpg';

    require_once 'lib/SimpleImage.php';
    $img = new SimpleImage();
    $img->fromFile($f->tmp_name)
        ->bestFit($width, $height)
        ->toFile("$folder/$photo", 'image/jpeg', 85);

    return $photo;  // 返回如：img_6759a1b2c3d4e.jpg
}


 function html_textarea($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<textarea id='$key' name='$key' $attr>$value</textarea>";
    }
    

    function html_number($key, $min = '', $max = '', $step = '', $attr = '') {
        $value = encode($GLOBALS[$key] ?? '');
        echo "<input type='number' id='$key' name='$key' value='$value'
                    min='$min' max='$max' step='$step' $attr>";
    }

     function html_status_toggle($key, $default = 1) {
    $checked = ($GLOBALS[$key] ?? $default) == 1 ? 'checked' : '';
    echo <<<HTML
    <div style="display:flex; align-items:center; gap:30px; margin:12px 0; font-size:15px; user-select:none;">
        <span style="color:#999;">Unactive</span>
        <label class="toggle-switch">
            <input type="checkbox" name="$key" value="1" $checked>
            <span class="slider round"></span>
        </label>
        <span style="color:#333; font-weight:500;">Active</span>
    </div>
    HTML;
}

function html_file($key, $accept = '', $attr = '') {
    echo "<input type='file' id='$key' name='$key' accept='$accept' $attr>";}

    function is_money($value) {
        return preg_match('/^\-?\d+(\.\d{1,2})?$/', $value);
    }

// ====================== 永久删除旧产品主图（安全、可重复调用）======================
/**
 * 删除产品旧主图（物理文件）
 * @param int $product_id 产品ID
 * @return bool 是否成功（即使没有旧图也返回 true）
 */
function delete_old_product_photo($product_id) {
    global $_db;
    
    try {
        $stmt = $_db->prepare("SELECT photo_name FROM product WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $old_photo_name = $stmt->fetchColumn();

        // 没有旧图 → 直接返回成功
        if (!$old_photo_name) {
            return true;
        }

        // 构建真实路径（和你 add/edit 完全一致！）
        $full_path = '../admin/uploads/products/' . $old_photo_name;

        // 删除文件（如果存在）
        if (is_file($full_path)) {
            if (unlink($full_path)) {
                error_log("成功删除旧产品主图: $full_path");
                return true;
            } else {
                error_log("删除旧产品主图失败（权限问题？）: $full_path");
                return false;
            }
        }

        return true; // 文件本就不存在，也算成功
    } catch (Exception $e) {
        error_log("delete_old_product_photo 错误: " . $e->getMessage());
        return false;
    }
}    


// Add these functions to your _base.php

function load_cart_from_db() {
    if (is_login()) {
        global $_db;
        $user_id = current_user()->id;
        $stm = $_db->prepare("SELECT c.product_id, p.product_name, p.price, c.quantity 
                             FROM cart_item c 
                             JOIN product p ON c.product_id = p.product_id 
                             WHERE c.user_id = ?");
        $stm->execute([$user_id]);
        $items = $stm->fetchAll();

        $_SESSION['cart'] = [];
        foreach ($items as $item) {
            $_SESSION['cart'][$item->product_id] = [
                'product_id' => $item->product_id,
                'name'       => $item->product_name,
                'price'      => $item->price,
                'qty'        => $item->quantity
            ];
        }
    }
}

function save_cart_to_db() {
    if (!is_login()) return;

    global $_db;
    $user_id = current_user()->id;

    // Always clear the DB first (even if cart is empty!)
    $_db->prepare("DELETE FROM cart_item WHERE user_id = ?")->execute([$user_id]);

    // Only insert if there are items
    if (!empty($_SESSION['cart'])) {
        $stm = $_db->prepare("INSERT INTO cart_item (user_id, product_id, quantity) VALUES (?, ?, ?)");
        foreach ($_SESSION['cart'] as $item) {
            $stm->execute([$user_id, $item['product_id'], $item['qty']]);
        }
    }
    // If cart is empty → table stays empty → perfect!
}

function clear_cart() {
    unset($_SESSION['cart']);
    if (is_login()) {
        global $_db;
        $_db->prepare("DELETE FROM cart_item WHERE user_id = ?")->execute([current_user()->id]);
    }
}
