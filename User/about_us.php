<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us · VeggieFit · Vegetarian Weight Loss</title>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="../Header/header.css">
    <link rel="stylesheet" href="../Footer/footer.css">
    <link rel="stylesheet" href="about_us.css">
</head>
<body>
    <!-- Header -->
    <div id="header-placeholder"></div>
    <script>
        fetch('../Header/header.php')
            .then(res => res.text())
            .then(data => document.getElementById('header-placeholder').innerHTML = data);
    </script>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-bg-shape"></div>
            <div class="container">
                <div class="hero-grid">
                    <div class="hero-content">
                        <span class="hero-tag">🌱 Vegetarian Weight Loss</span>
                        <h1 class="hero-title">
                            Your <span class="orange-highlight">weight loss</span><br>
                            journey starts here
                        </h1>
                        <p class="hero-text">
                            Science-based vegetarian meal plans tailored to your body. Lose weight naturally with personalized nutrition.
                        </p>
                        <div class="hero-buttons">
                            <a href="../Login/register.php" class="primary-btn">Start Your Journey</a>
                        </div>
                    </div>
                    <div class="hero-visual">
                        <div class="hero-image-wrap orange-hover">
                            <!-- <img src="https://images.unsplash.com/photo-1511690656952-34342bb7c2f2?auto=format&fit=crop&w=1470&q=80" alt="Vegan salad bowl" class="hero-image"> -->
                            <img src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80" alt="Fresh vegetarian salad for weight loss" class="hero-image">
                            <div class="hero-image-overlay"></div>
                        </div>
                        <div class="floating-card orange-border">
                            <div class="floating-card-content">
                                <div class="floating-icon orange-icon">⚖️</div>
                                <div class="floating-text">
                                    <h4>Personalized Plans</h4>
                                    <p>Based on your BMI & goals</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="scroll-hint">
                <span>Scroll to explore</span>
                <div class="scroll-line"></div>
            </div>
        </section>

        <!-- Story Section -->
        <section class="story" id="story">
            <div class="container">
                <div class="story-grid">
                    <div class="story-image-group reveal-left">
                        <div class="story-image-main orange-hover">
                          <img src="https://images.unsplash.com/photo-1498837167922-ddd27525d352?auto=format&fit=crop&w=1470&q=80" alt="Vegan buddha bowl">
                        </div>
                        <div class="story-image-accent orange-hover">
                            <img src="https://images.unsplash.com/photo-1543339308-43e59d6b73a6?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80" alt="Weight loss friendly Buddha bowl">
                        </div>
                    </div>
                    <div class="story-content reveal-right">
                        <span class="section-eyebrow">Our Story</span>
                        <h2 class="section-title">Plan Smart. <span class="orange-highlight">Eat Better.</span> Live Healthier.</h2>
                        <div class="story-text">
                            <p>VeggieFit helps you take control of your health with personalized meal plans, nutrition tracking, and insights that fit your lifestyle.</p>
                            <p>Using BMI, BMR, and TDEE calculations, we create meal plans that create a healthy calorie deficit while ensuring you get all the nutrients you need.</p>
                        </div>
                        <div class="story-features">
                            <div class="feature-item">
                                <div class="feature-icon">📉</div>
                                <span class="feature-text">Sustainable Weight Loss</span>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">🥗</div>
                                <span class="feature-text">100% Vegetarian</span>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">🧮</div>
                                <span class="feature-text">BMI & BMR Based</span>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">📊</div>
                                <span class="feature-text">Track Progress</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="how-it-works">
            <div class="container">
                <div class="section-header">
                    <span class="section-eyebrow">Your Journey</span>
                    <h2 class="section-title">How <span class="orange-highlight">weight loss</span> works</h2>
                    <p class="section-description">Simple steps to achieve your goals with VeggieFit</p>
                </div>
                <div class="steps-grid">
                    <div class="step-card reveal">
                        <div class="step-number">01</div>
                        <div class="step-icon">📝</div>
                        <h3>Calculate Your Metrics</h3>
                        <p>Enter your details to get your BMI, BMR, and daily calorie needs</p>
                    </div>
                    <div class="step-card reveal" data-delay="100">
                        <div class="step-number">02</div>
                        <div class="step-icon">🥗</div>
                        <h3>Get Personalized Plan</h3>
                        <p>Receive a custom vegetarian meal plan designed for your weight loss goals</p>
                    </div>
                    <div class="step-card reveal" data-delay="200">
                        <div class="step-number">03</div>
                        <div class="step-icon">📊</div>
                        <h3>Track Your Progress</h3>
                        <p>Log your daily weight and calories to see your journey unfold</p>
                    </div>
                    <div class="step-card reveal" data-delay="300">
                        <div class="step-number">04</div>
                        <div class="step-icon">🎉</div>
                        <h3>Achieve Your Goal</h3>
                        <p>Reach your target weight with consistent, sustainable habits</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Values Section -->
        <section class="values">
            <div class="container">
                <div class="section-header">
                    <span class="section-eyebrow">Why Choose Us</span>
                    <h2 class="section-title">What makes <span class="orange-highlight">VeggieFit</span> different</h2>
                    <p class="section-description">Science-based, vegetarian-focused weight loss that works</p>
                </div>
                <div class="values-grid">
                    <div class="value-card reveal">
                        <div class="value-number">01</div>
                        <div class="value-icon">🧮</div>
                        <h3>Science-Based</h3>
                        <p>Weight loss plans based on your BMI, BMR, and TDEE for accurate calorie targets</p>
                    </div>
                    <div class="value-card reveal" data-delay="100">
                        <div class="value-number">02</div>
                        <div class="value-icon">🥬</div>
                        <h3>Vegetarian Focused</h3>
                        <p>100% plant-based meals that are nutritious, filling, and support weight loss</p>
                    </div>
                    <div class="value-card reveal" data-delay="200">
                        <div class="value-number">03</div>
                        <div class="value-icon">📈</div>
                        <h3>Track Progress</h3>
                        <p>Monitor your weight loss journey with detailed progress tracking</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Meal Gallery -->
        <section class="meal-gallery">
            <div class="container">
                <div class="section-header">
                    <span class="section-eyebrow">Weight Loss Meals</span>
                    <h2 class="section-title">Delicious <span class="orange-highlight">vegetarian</span> dishes</h2>
                    <p class="section-description">Healthy, filling, and designed for weight loss</p>
                </div>
                <div class="gallery-grid">
                    <div class="gallery-item reveal">
                        <div class="gallery-image-wrap orange-hover">
                            <img src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80" alt="Low calorie salad bowl">
                            <div class="gallery-overlay">
                                <span class="gallery-tag">280 kcal</span>
                            </div>
                        </div>
                    </div>
                    <div class="gallery-item reveal" data-delay="50">
                        <div class="gallery-image-wrap orange-hover">
                            <img src="https://images.unsplash.com/photo-1543339308-43e59d6b73a6?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80" alt="High protein Buddha bowl">
                            <div class="gallery-overlay">
                                <span class="gallery-tag">350 kcal</span>
                            </div>
                        </div>
                    </div>
                    <div class="gallery-item reveal" data-delay="100">
                        <div class="gallery-image-wrap orange-hover">
                               <img src="https://images.unsplash.com/photo-1511690656952-34342bb7c2f2?auto=format&fit=crop&w=1470&q=80" alt="Vegan salad bowl" class="hero-image"> 
                            <div class="gallery-overlay">
                                <span class="gallery-tag">220 kcal</span>
                            </div>
                        </div>
                    </div>
                    <div class="gallery-item reveal" data-delay="150">
                        <div class="gallery-image-wrap orange-hover">
                            <img src="https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg" alt="Vegetable salad">

                            <div class="gallery-overlay">
                                <span class="gallery-tag">310 kcal</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <div id="footer-placeholder"></div>
    <script>
        fetch('../Footer/footer.php')
            .then(res => res.text())
            .then(data => document.getElementById('footer-placeholder').innerHTML = data);
    </script>

    <!-- External JavaScript -->
    <script src="about_us.js"></script>
</body>
</html>