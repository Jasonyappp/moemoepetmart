<?php
require '_base.php';
$_title = 'Welcome to Moe Moe Pet Mart ♡';
include '_head.php';
?>

<div class="hero">
    <div class="hero-content">
        <h1 class="hero-title">Moe Moe Pet Mart</h1>
        <p class="hero-subtitle">Your one-stop shop for the cutest pets & premium pet supplies ♡</p>
        <div class="hero-buttons">
            <a href="/pets.php" class="btn btn-primary">Adopt a Pet</a>
            <a href="/products.php" class="btn btn-secondary">Shop Now</a>
        </div>
    </div>
    <div class="hero-paw">🐾</div>
</div>

<section class="features">
    <div class="container">
        <div class="feature-card">
            <div class="icon">🐕</div>
            <h3>Happy Puppies</h3>
            <p>Healthy, vaccinated & super playful fur babies waiting for you!</p>
        </div>
        <div class="feature-card">
            <div class="icon">🐱</div>
            <h3>Adorable Kittens</h3>
            <p>Fluffy, cuddly kittens ready to melt your heart~</p>
        </div>
        <div class="feature-card">
            <div class="icon">🛍️</div>
            <h3>Premium Supplies</h3>
            <p>Toys, food, beds, and accessories — everything your pet deserves!</p>
        </div>
    </div>
</section>

<section id="products" class="about">
    <div class="container">
        <h2 class="section-title">Why Choose Moe Moe Pet Mart? ♡</h2>
        <div class="about-grid">
            <div class="about-text">
                <p>We believe every pet deserves a loving home and the very best care. Our little angels are raised with love, properly vaccinated, dewormed, and come with a health certificate.</p>
                <p>Visit us today and let one of our cute babies steal your heart! 💕</p>
                <a href="/insert.php" class="btn btn-primary mt-20">See Available Pets</a>
            </div>
        </div>
    </div>
</section>

<?php include '_foot.php'; ?>