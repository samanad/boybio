<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="LinkDooni - Revolutionary link management platform with elegant design and powerful features">
    <title>LinkDooni - Where Links Meet Art</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Serif+Display:ital@0;1&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --color-bg-primary: #0a0a0f;
            --color-bg-secondary: #141420;
            --color-bg-tertiary: #1a1a2e;
            --color-accent-primary: #ff006e;
            --color-accent-secondary: #8338ec;
            --color-accent-tertiary: #3a86ff;
            --color-accent-gold: #ffbe0b;
            --color-text-primary: #ffffff;
            --color-text-secondary: #b8b8c8;
            --color-text-muted: #6b6b7a;
            --gradient-primary: linear-gradient(135deg, #ff006e 0%, #8338ec 50%, #3a86ff 100%);
            --gradient-secondary: linear-gradient(135deg, #ff006e 0%, #ffbe0b 100%);
            --gradient-tertiary: linear-gradient(135deg, #8338ec 0%, #3a86ff 100%);
            --gradient-gold: linear-gradient(135deg, #ffbe0b 0%, #ff9500 100%);
            --shadow-glow: 0 0 40px rgba(255, 0, 110, 0.3);
            --shadow-glow-strong: 0 0 60px rgba(131, 56, 236, 0.5);
            --border-radius-sm: 12px;
            --border-radius-md: 20px;
            --border-radius-lg: 32px;
            --border-radius-xl: 48px;
            --transition-fast: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-medium: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        html {
            scroll-behavior: smooth;
            font-size: 16px;
        }
        
        body {
            font-family: 'Space Grotesk', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--color-bg-primary);
            color: var(--color-text-primary);
            line-height: 1.7;
            overflow-x: hidden;
            position: relative;
            min-height: 100vh;
        }
        
        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }
        
        .bg-gradient-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: float 20s ease-in-out infinite;
        }
        
        .bg-gradient-orb:nth-child(1) {
            width: 600px;
            height: 600px;
            background: var(--color-accent-primary);
            top: -200px;
            left: -200px;
            animation-delay: 0s;
        }
        
        .bg-gradient-orb:nth-child(2) {
            width: 500px;
            height: 500px;
            background: var(--color-accent-secondary);
            top: 50%;
            right: -150px;
            animation-delay: -5s;
        }
        
        .bg-gradient-orb:nth-child(3) {
            width: 400px;
            height: 400px;
            background: var(--color-accent-tertiary);
            bottom: -100px;
            left: 20%;
            animation-delay: -10s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            33% {
                transform: translate(50px, -50px) scale(1.1);
            }
            66% {
                transform: translate(-30px, 30px) scale(0.9);
            }
        }
        
        /* Grid Pattern Overlay */
        .grid-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(255, 0, 110, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 0, 110, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: 1;
            pointer-events: none;
            animation: gridMove 20s linear infinite;
        }
        
        @keyframes gridMove {
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(50px, 50px);
            }
        }
        
        /* Main Container */
        .main-container {
            position: relative;
            z-index: 2;
            min-height: 100vh;
        }
        
        /* Navigation */
        .nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 24px 40px;
            backdrop-filter: blur(20px);
            background: rgba(10, 10, 15, 0.7);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: var(--transition-medium);
        }
        
        .nav.scrolled {
            padding: 16px 40px;
            background: rgba(10, 10, 15, 0.95);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .nav-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-logo {
            font-family: 'DM Serif Display', serif;
            font-size: 28px;
            font-weight: 400;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
            letter-spacing: -0.5px;
            transition: var(--transition-fast);
        }
        
        .nav-logo:hover {
            transform: scale(1.05);
        }
        
        .nav-links {
            display: flex;
            gap: 32px;
            align-items: center;
        }
        
        .nav-link {
            color: var(--color-text-secondary);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: var(--transition-fast);
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gradient-primary);
            transition: var(--transition-medium);
        }
        
        .nav-link:hover {
            color: var(--color-text-primary);
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        /* Hero Section */
        .hero {
            padding: 180px 40px 120px;
            max-width: 1400px;
            margin: 0 auto;
            text-align: center;
            position: relative;
        }
        
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            background: rgba(255, 0, 110, 0.1);
            border: 1px solid rgba(255, 0, 110, 0.3);
            border-radius: 100px;
            color: var(--color-accent-primary);
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 32px;
            animation: fadeInUp 0.8s ease-out;
        }
        
        .hero-badge i {
            font-size: 12px;
        }
        
        .hero-title {
            font-family: 'DM Serif Display', serif;
            font-size: clamp(48px, 8vw, 96px);
            font-weight: 400;
            line-height: 1.1;
            margin-bottom: 32px;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: fadeInUp 0.8s ease-out 0.2s both;
            letter-spacing: -2px;
        }
        
        .hero-subtitle {
            font-size: clamp(18px, 2.5vw, 24px);
            color: var(--color-text-secondary);
            max-width: 700px;
            margin: 0 auto 56px;
            line-height: 1.6;
            font-weight: 400;
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }
        
        .hero-cta {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease-out 0.6s both;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 18px 36px;
            border-radius: 100px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: var(--transition-medium);
            position: relative;
            overflow: hidden;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn span {
            position: relative;
            z-index: 1;
        }
        
        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 8px 32px rgba(255, 0, 110, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 48px rgba(255, 0, 110, 0.5);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--color-text-primary);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-4px);
        }
        
        /* Features Section */
        .features-section {
            padding: 120px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 80px;
        }
        
        .section-label {
            display: inline-block;
            padding: 6px 16px;
            background: rgba(131, 56, 236, 0.1);
            border: 1px solid rgba(131, 56, 236, 0.3);
            border-radius: 100px;
            color: var(--color-accent-secondary);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 24px;
        }
        
        .section-title {
            font-family: 'DM Serif Display', serif;
            font-size: clamp(36px, 5vw, 56px);
            font-weight: 400;
            margin-bottom: 20px;
            background: var(--gradient-secondary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .section-description {
            font-size: 18px;
            color: var(--color-text-secondary);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 32px;
            margin-top: 60px;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: var(--border-radius-lg);
            padding: 48px 36px;
            transition: var(--transition-medium);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-primary);
            transform: scaleX(0);
            transform-origin: left;
            transition: var(--transition-medium);
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            border-color: rgba(255, 0, 110, 0.3);
            box-shadow: var(--shadow-glow);
        }
        
        .feature-card:hover::before {
            transform: scaleX(1);
        }
        
        .feature-icon-wrapper {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            font-size: 28px;
            color: white;
            transition: var(--transition-medium);
        }
        
        .feature-card:hover .feature-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
        }
        
        .feature-title {
            font-family: 'DM Serif Display', serif;
            font-size: 28px;
            font-weight: 400;
            margin-bottom: 16px;
            color: var(--color-text-primary);
        }
        
        .feature-description {
            color: var(--color-text-secondary);
            font-size: 16px;
            line-height: 1.7;
        }
        
        /* Stats Section */
        .stats-section {
            padding: 100px 40px;
            background: rgba(255, 255, 255, 0.02);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .stats-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 60px;
            text-align: center;
        }
        
        .stat-item {
            position: relative;
        }
        
        .stat-number {
            font-family: 'JetBrains Mono', monospace;
            font-size: clamp(36px, 4vw, 56px);
            font-weight: 600;
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 12px;
            display: block;
        }
        
        .stat-label {
            color: var(--color-text-secondary);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 500;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 120px 40px;
            max-width: 1400px;
            margin: 0 auto;
            text-align: center;
        }
        
        .cta-content {
            background: var(--gradient-primary);
            border-radius: var(--border-radius-xl);
            padding: 80px 60px;
            position: relative;
            overflow: hidden;
        }
        
        .cta-content::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 40px 40px;
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        .cta-title {
            font-family: 'DM Serif Display', serif;
            font-size: clamp(36px, 5vw, 56px);
            font-weight: 400;
            color: white;
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
        }
        
        .cta-description {
            font-size: 20px;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }
        
        .btn-white {
            background: white;
            color: var(--color-accent-primary);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        .btn-white:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 48px rgba(0, 0, 0, 0.3);
        }
        
        /* Footer */
        .footer {
            padding: 80px 40px 40px;
            max-width: 1400px;
            margin: 0 auto;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 60px;
            margin-bottom: 60px;
        }
        
        .footer-brand h3 {
            font-family: 'DM Serif Display', serif;
            font-size: 32px;
            font-weight: 400;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
        }
        
        .footer-brand p {
            color: var(--color-text-secondary);
            font-size: 15px;
            line-height: 1.7;
        }
        
        .footer-section h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--color-text-primary);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: var(--color-text-secondary);
            text-decoration: none;
            font-size: 15px;
            transition: var(--transition-fast);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .footer-links a:hover {
            color: var(--color-text-primary);
            transform: translateX(4px);
        }
        
        .footer-social {
            display: flex;
            gap: 16px;
            margin-top: 24px;
        }
        
        .footer-social a {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-text-secondary);
            text-decoration: none;
            transition: var(--transition-medium);
        }
        
        .footer-social a:hover {
            background: var(--gradient-primary);
            border-color: transparent;
            color: white;
            transform: translateY(-4px);
            box-shadow: var(--shadow-glow);
        }
        
        .footer-bottom {
            padding-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            text-align: center;
            color: var(--color-text-muted);
            font-size: 14px;
        }
        
        .footer-bottom a {
            color: var(--color-text-secondary);
            text-decoration: none;
            transition: var(--transition-fast);
        }
        
        .footer-bottom a:hover {
            color: var(--color-accent-primary);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav {
                padding: 20px 24px;
            }
            
            .nav-links {
                gap: 20px;
            }
            
            .nav-link {
                font-size: 14px;
            }
            
            .hero {
                padding: 140px 24px 80px;
            }
            
            .features-section,
            .stats-section,
            .cta-section {
                padding: 80px 24px;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            
            .feature-card {
                padding: 36px 24px;
            }
            
            .cta-content {
                padding: 60px 32px;
            }
            
            .footer {
                padding: 60px 24px 32px;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }
        }
        
        /* Scroll Animations */
        @media (prefers-reduced-motion: no-preference) {
            .feature-card,
            .stat-item {
                opacity: 0;
                transform: translateY(30px);
                animation: fadeInUp 0.8s ease-out forwards;
            }
            
            .feature-card:nth-child(1) { animation-delay: 0.1s; }
            .feature-card:nth-child(2) { animation-delay: 0.2s; }
            .feature-card:nth-child(3) { animation-delay: 0.3s; }
            .feature-card:nth-child(4) { animation-delay: 0.4s; }
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="bg-gradient-orb"></div>
        <div class="bg-gradient-orb"></div>
        <div class="bg-gradient-orb"></div>
    </div>
    <div class="grid-overlay"></div>
    
    <nav class="nav" id="nav">
        <div class="nav-content">
            <a href="/" class="nav-logo">LinkDooni</a>
            <div class="nav-links">
                <a href="https://cloub.io/directory" class="nav-link">Directory</a>
                <a href="https://cloub.io/pricing" class="nav-link">Pricing</a>
                <a href="https://cloub.io/login" class="nav-link">Login</a>
                <a href="https://cloub.io/register" class="btn btn-primary">
                    <span>Get Started</span>
                </a>
            </div>
        </div>
    </nav>
    
    <div class="main-container">
        <section class="hero">
            <div class="hero-badge">
                <i class="fas fa-sparkles"></i>
                <span>Revolutionary Link Management</span>
            </div>
            <h1 class="hero-title">Where Links<br>Meet Art</h1>
            <p class="hero-subtitle">
                Transform your digital presence with beautifully crafted bio links, 
                powerful analytics, and lightning-fast performance. Built for creators who demand excellence.
            </p>
            <div class="hero-cta">
                <a href="https://cloub.io/register" class="btn btn-primary">
                    <span>Start Creating</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="https://cloub.io/directory" class="btn btn-secondary">
                    <span>Explore Examples</span>
                    <i class="fas fa-compass"></i>
                </a>
            </div>
        </section>
        
        <section class="features-section">
            <div class="section-header">
                <div class="section-label">Powerful Features</div>
                <h2 class="section-title">Everything You Need to Succeed</h2>
                <p class="section-description">
                    A comprehensive suite of tools designed to elevate your link management 
                    and help you build a stronger online presence.
                </p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <i class="fas fa-link"></i>
                    </div>
                    <h3 class="feature-title">Smart Short Links</h3>
                    <p class="feature-description">
                        Create memorable, branded short URLs that reflect your unique style. 
                        Track every click with real-time analytics and detailed insights.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3 class="feature-title">Stunning Bio Pages</h3>
                    <p class="feature-description">
                        Design beautiful bio pages with our intuitive drag-and-drop editor. 
                        Showcase your content, products, and personality with unlimited customization.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">Advanced Analytics</h3>
                    <p class="feature-description">
                        Gain deep insights into your link performance with comprehensive tracking, 
                        heatmaps, and detailed reports. Understand your audience like never before.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="feature-title">Lightning Fast</h3>
                    <p class="feature-description">
                        Built for speed with cutting-edge technology. Your links load instantly, 
                        providing the best experience for your audience across all devices.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">Secure & Reliable</h3>
                    <p class="feature-description">
                        Enterprise-grade security with 99.9% uptime guarantee. Your data is protected 
                        with encryption and regular backups.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="feature-title">Mobile Optimized</h3>
                    <p class="feature-description">
                        Perfect experience on every device. Responsive design ensures your links 
                        look stunning on phones, tablets, and desktops.
                    </p>
                </div>
            </div>
        </section>
        
        <section class="stats-section">
            <div class="stats-container">
                <div class="stat-item">
                    <span class="stat-number">10M+</span>
                    <div class="stat-label">Links Created</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">500K+</span>
                    <div class="stat-label">Active Users</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">99.9%</span>
                    <div class="stat-label">Uptime</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">24/7</span>
                    <div class="stat-label">Support</div>
                </div>
            </div>
        </section>
        
        <section class="cta-section">
            <div class="cta-content">
                <h2 class="cta-title">Ready to Transform Your Links?</h2>
                <p class="cta-description">
                    Join thousands of creators and businesses who trust LinkDooni for their link management needs.
                </p>
                <div class="cta-buttons">
                    <a href="https://cloub.io/register" class="btn btn-white">
                        <span>Get Started Free</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="https://cloub.io/login" class="btn btn-secondary" style="background: rgba(255,255,255,0.1); color: white; border-color: rgba(255,255,255,0.2);">
                        <span>Sign In</span>
                    </a>
                </div>
            </div>
        </section>
        
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-brand">
                    <h3>LinkDooni</h3>
                    <p>
                        The most elegant link management platform for creators, brands, and businesses. 
                        Beautiful design meets powerful functionality.
                    </p>
                    <div class="footer-social">
                        <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" title="LinkedIn">
                            <i class="fab fa-linkedin"></i>
                        </a>
                        <a href="https://github.com" target="_blank" rel="noopener noreferrer" title="GitHub">
                            <i class="fab fa-github"></i>
                        </a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Product</h4>
                    <ul class="footer-links">
                        <li><a href="https://cloub.io/features">Features</a></li>
                        <li><a href="https://cloub.io/pricing">Pricing</a></li>
                        <li><a href="https://cloub.io/directory">Directory</a></li>
                        <li><a href="https://cloub.io/changelog">Changelog</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Company</h4>
                    <ul class="footer-links">
                        <li><a href="https://saman.host">About Us</a></li>
                        <li><a href="mailto:info@biolink.dev">Contact</a></li>
                        <li><a href="https://cloub.io/blog">Blog</a></li>
                        <li><a href="https://cloub.io/careers">Careers</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Support</h4>
                    <ul class="footer-links">
                        <li><a href="https://cloub.io/help">Help Center</a></li>
                        <li><a href="https://cloub.io/docs">Documentation</a></li>
                        <li><a href="https://cloub.io/api">API</a></li>
                        <li><a href="https://cloub.io/status">Status</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 LinkDooni. All rights reserved. | <a href="https://saman.host">Saman.Host</a></p>
            </div>
        </footer>
    </div>
    
    <script>
        // Nav scroll effect
        const nav = document.getElementById('nav');
        let lastScroll = 0;
        
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
            
            lastScroll = currentScroll;
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
