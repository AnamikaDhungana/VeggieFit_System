<?php
session_start();

if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    echo "<script>alert('You have been logged out successfully');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriPlan - Smart Nutrition, Better Health</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="Header/header.css">
    <link rel="stylesheet" href="Footer/footer.css">
</head>
<body>

<!-- JS to Load Header -->
<div id="header-placeholder"></div>
<script>
    fetch('Header/header.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('header-placeholder').innerHTML = data;
        })
        .catch(error => console.error('Error loading header:', error));
</script>

<main>
    <!-- Hero Section -->
    <section class="hero" aria-label="Welcome">
        <div class="hero-bg">
            <div class="hero-gradient"></div>
            <div class="hero-grid"></div>
            <div class="hero-glow hero-glow-1"></div>
            <div class="hero-glow hero-glow-2"></div>
        </div>
        <div class="hero-inner">
            <div class="hero-content animate-on-scroll">
                <p class="hero-badge">Personalized nutrition platform</p>
                <h1>Plan Smart. <span class="gradient-text">Eat Better.</span> Live Healthier.</h1>
                <p class="hero-desc">NutriPlan helps you take control of your health with personalized meal plans, nutrition tracking, and insights that fit your lifestyle.</p>
                <div class="hero-cta">
                    <a href="/nutrition_system/Login/register.php" class="btn btn-primary">
                        <span>Get Started</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <a href="Login/login.php" class="btn btn-ghost">Sign In</a>
                </div>
            </div>
            <div class="hero-visual animate-on-scroll" data-delay="1">
                <div class="hero-image-wrap">
                    <div class="image-float-orb image-orb-1"></div>
                    <div class="image-float-orb image-orb-2"></div>
                    <div class="image-float-orb image-orb-3"></div>
                    <div class="image-curve image-curve-1"></div>
                    <div class="image-curve image-curve-2"></div>
                    <div class="image-frame">
                        <div class="hero-glow-ring"></div>
                        <div class="image-container hero-image-container">
                            <img src="Web_image/home_page01.jpg" alt="Healthy Meal" loading="eager">
                            <div class="image-overlay"></div>
                            <div class="hero-tech-pattern"></div>
                        </div>
                        <div class="hero-image-shine"></div>
                        <div class="image-corner image-corner-tl"></div>
                        <div class="image-corner image-corner-tr"></div>
                        <div class="image-corner image-corner-bl"></div>
                        <div class="image-corner image-corner-br"></div>
                    </div>
                    <div class="image-badge-wrapper">
                        <span class="image-badge">Fresh & Healthy</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-scroll">
            <span>Scroll to explore</span>
            <div class="scroll-indicator"></div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" aria-label="Features">
        <div class="section-header animate-on-scroll">
            <p class="section-badge">Why NutriPlan</p>
            <h2>What Makes NutriPlan Special?</h2>
            <p class="section-desc">Your health journey is unique. NutriPlan gives you the tools to make it simple, smart, and sustainable.</p>
        </div>
        <div class="feature-grid">
            <article class="feature-card animate-on-scroll" data-delay="0">
                <div class="feature-image-wrapper">
                    <div class="feature-tech-bg feature-tech-1"></div>
                    <div class="feature-image-curve feature-curve-1"></div>
                    <div class="feature-image-curve feature-curve-2"></div>
                    <div class="feature-image-curve feature-curve-3"></div>
                    <div class="feature-image-frame">
                        <div class="feature-glow-ring"></div>
                        <div class="feature-icon-wrap">
                            <img src="Web_image/home_page02.jpg" alt="Tailored Meal Plans">
                            <div class="feature-tech-overlay"></div>
                        </div>
                        <div class="feature-image-shine"></div>
                        <div class="feature-corner feature-corner-1"></div>
                        <div class="feature-corner feature-corner-2"></div>
                    </div>
                    <div class="feature-float-dot feature-dot-1"></div>
                    <div class="feature-float-dot feature-dot-2"></div>
                </div>
                <h3>Tailored Meal Plans</h3>
                <p>Enjoy meal suggestions that perfectly match your fitness goals, dietary needs, and preferences.</p>
            </article>
            <article class="feature-card animate-on-scroll" data-delay="1">
                <div class="feature-image-wrapper">
                    <div class="feature-tech-bg feature-tech-2"></div>
                    <div class="feature-image-curve feature-curve-1"></div>
                    <div class="feature-image-curve feature-curve-2"></div>
                    <div class="feature-image-curve feature-curve-3"></div>
                    <div class="feature-image-frame">
                        <div class="feature-glow-ring"></div>
                        <div class="feature-icon-wrap">
                            <img src="Web_image/home_page03.png" alt="Real-Time Tracking">
                            <div class="feature-tech-overlay"></div>
                        </div>
                        <div class="feature-image-shine"></div>
                        <div class="feature-corner feature-corner-1"></div>
                        <div class="feature-corner feature-corner-2"></div>
                    </div>
                    <div class="feature-float-dot feature-dot-1"></div>
                    <div class="feature-float-dot feature-dot-2"></div>
                </div>
                <h3>Real-Time Tracking</h3>
                <p>Track calories, nutrients, and daily intake effortlessly, and watch your progress evolve.</p>
            </article>
            <article class="feature-card animate-on-scroll" data-delay="2">
                <div class="feature-image-wrapper">
                    <div class="feature-tech-bg feature-tech-3"></div>
                    <div class="feature-image-curve feature-curve-1"></div>
                    <div class="feature-image-curve feature-curve-2"></div>
                    <div class="feature-image-curve feature-curve-3"></div>
                    <div class="feature-image-frame">
                        <div class="feature-glow-ring"></div>
                        <div class="feature-icon-wrap">
                            <img src="Web_image/home_page04.jpg" alt="Food Database">
                            <div class="feature-tech-overlay"></div>
                        </div>
                        <div class="feature-image-shine"></div>
                        <div class="feature-corner feature-corner-1"></div>
                        <div class="feature-corner feature-corner-2"></div>
                    </div>
                    <div class="feature-float-dot feature-dot-1"></div>
                    <div class="feature-float-dot feature-dot-2"></div>
                </div>
                <h3>Powerful Food Database</h3>
                <p>Access detailed nutritional data for thousands of food items and make informed choices.</p>
            </article>
        </div>
    </section>

    <!-- Motivation / CTA Section -->
    <section class="cta-section" aria-label="Call to action">
        <div class="cta-bg">
            <div class="cta-gradient"></div>
            <div class="cta-pattern"></div>
        </div>
        <div class="cta-inner">
            <div class="cta-content animate-on-scroll">
                <h2>Your Health. Your Way.</h2>
                <p>Whether you're managing weight, improving fitness, or simply eating smarter, NutriPlan adapts to you. Small steps lead to lasting change.</p>
            
            </div>
            <div class="cta-visual animate-on-scroll" data-delay="1">
                <div class="cta-image-wrap">
                    <div class="cta-float-orb cta-orb-1"></div>
                    <div class="cta-float-orb cta-orb-2"></div>
                    <div class="cta-image-curve cta-image-curve-1"></div>
                    <div class="cta-image-curve cta-image-curve-2"></div>
                    <div class="cta-image-frame">
                        <div class="cta-glow-ring"></div>
                        <div class="image-container cta-image-container">
                            <img src="Web_image/home_page05.jpg" alt="Healthy Lifestyle" loading="lazy">
                            <div class="image-overlay"></div>
                            <div class="cta-tech-pattern"></div>
                        </div>
                        <div class="cta-image-shine"></div>
                        <div class="cta-corner cta-corner-1"></div>
                        <div class="cta-corner cta-corner-2"></div>
                        <div class="cta-corner cta-corner-3"></div>
                        <div class="cta-corner cta-corner-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- JS to Load Footer -->
<div id="footer-placeholder"></div>
<script>
    fetch('Footer/footer.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('footer-placeholder').innerHTML = data;
        })
        .catch(error => console.error('Error loading footer:', error));
</script>

<script src="home.js"></script>
</body>
</html>
