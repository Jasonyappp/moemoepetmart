<?php
require '_base.php';
$_title = 'Welcome to Moe Moe Pet Mart ♡';
include '_head.php';
?>

<div class="hero">
    <div class="hero-content">
        <h1 class="hero-title">Moe Moe Pet Mart</h1>
        <p class="hero-subtitle">Your one-stop shop for the cutest & premium pet supplies ♡</p>
        <div class="hero-buttons">
            <a href="/member/products.php" class="btn btn-primary">Shop Now ♡</a>
            <a href="/member/products.php#categories" class="btn btn-secondary">Browse Categories</a>
        </div>
    </div>
    <div class="hero-paw">🐾</div>
</div>

<section class="features">
    <div class="container">
        <div class="feature-card">
            <div class="icon">🧸</div>
            <h3>Fun Toys</h3>
            <p>Interactive, squeaky, and plush toys to keep your fur baby entertained all day!</p>
        </div>
        <div class="feature-card">
            <div class="icon">🍖</div>
            <h3>Premium Food & Treats</h3>
            <p>Healthy, nutritious food and yummy treats for happy and energetic pets~</p>
        </div>
        <div class="feature-card">
            <div class="icon">🛏️</div>
            <h3>Cozy Beds & Accessories</h3>
            <p>Super soft beds, stylish collars, leashes, grooming tools, and more!</p>
        </div>
    </div>
</section>

<section id="products" class="about">
    <div class="container">
        <h2 class="section-title">Why Choose Moe Moe Pet Mart? ♡</h2>
        <div class="about-grid">
            <div class="about-text">
                <p>We hand-pick only the highest quality pet supplies to spoil your furry friends with the very best. From adorable kawaii-style accessories to premium nutrition and comfy essentials — everything is chosen with love for your pet's happiness and health.</p>
                <p>Make every day extra special for your pet! 💕</p>
                <a href="/member/products.php" class="btn btn-primary mt-20">Start Shopping Now ♡</a>
            </div>
        </div>
    </div>
</section>

<?php include '_foot.php'; ?>