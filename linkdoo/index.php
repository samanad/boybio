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
            --color-bg-primary: #f5f7f6;
            --color-bg-secondary: #e8ecea;
            --color-bg-tertiary: #dde4e1;
            --color-accent-primary: #6b8e7f;
            --color-accent-secondary: #5a7a6b;
            --color-accent-tertiary: #4a6b5a;
            --color-accent-green: #7fa892;
            --color-text-primary: #2d3a35;
            --color-text-secondary: #5a6b63;
            --color-text-muted: #8a9a92;
            --gradient-primary: linear-gradient(135deg, #6b8e7f 0%, #5a7a6b 100%);
            --gradient-secondary: linear-gradient(135deg, #7fa892 0%, #6b8e7f 100%);
            --border-radius-sm: 8px;
            --border-radius-md: 12px;
            --border-radius-lg: 16px;
            --transition-fast: 0.2s ease;
            --transition-medium: 0.3s ease;
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
            background: rgba(245, 247, 246, 0.95);
            border-bottom: 1px solid rgba(107, 142, 127, 0.1);
            transition: var(--transition-medium);
        }
        
        .nav.scrolled {
            padding: 16px 40px;
            background: rgba(245, 247, 246, 0.98);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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
            color: var(--color-accent-primary);
            text-decoration: none;
            letter-spacing: -0.5px;
            transition: var(--transition-fast);
        }
        
        .nav-logo:hover {
            color: var(--color-accent-secondary);
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
        }
        
        .nav-link:hover {
            color: var(--color-accent-primary);
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
            background: rgba(107, 142, 127, 0.1);
            border: 1px solid rgba(107, 142, 127, 0.2);
            border-radius: 100px;
            color: var(--color-accent-primary);
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 32px;
        }
        
        .hero-title {
            font-family: 'DM Serif Display', serif;
            font-size: clamp(48px, 8vw, 96px);
            font-weight: 400;
            line-height: 1.1;
            margin-bottom: 32px;
            color: var(--color-accent-primary);
            letter-spacing: -2px;
        }
        
        .hero-subtitle {
            font-size: clamp(18px, 2.5vw, 24px);
            color: var(--color-text-secondary);
            max-width: 700px;
            margin: 0 auto 56px;
            line-height: 1.6;
            font-weight: 400;
        }
        
        .hero-cta {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
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
            border: none;
            cursor: pointer;
            font-family: inherit;
        }
        
        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 12px rgba(107, 142, 127, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(107, 142, 127, 0.4);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--color-accent-primary);
            border: 2px solid var(--color-accent-primary);
        }
        
        .btn-secondary:hover {
            background: var(--color-accent-primary);
            color: white;
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
            background: rgba(107, 142, 127, 0.1);
            border: 1px solid rgba(107, 142, 127, 0.2);
            border-radius: 100px;
            color: var(--color-accent-primary);
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
            color: var(--color-accent-primary);
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
            background: white;
            border: 1px solid rgba(107, 142, 127, 0.1);
            border-radius: var(--border-radius-lg);
            padding: 48px 36px;
            transition: var(--transition-medium);
        }
        
        .feature-card:hover {
            transform: translateY(-4px);
            border-color: var(--color-accent-primary);
            box-shadow: 0 8px 24px rgba(107, 142, 127, 0.15);
        }
        
        .feature-icon-wrapper {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            font-size: 28px;
            color: white;
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
            background: var(--color-bg-secondary);
            border-top: 1px solid rgba(107, 142, 127, 0.1);
            border-bottom: 1px solid rgba(107, 142, 127, 0.1);
        }
        
        .stats-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 60px;
            text-align: center;
        }
        
        .stat-number {
            font-family: 'JetBrains Mono', monospace;
            font-size: clamp(36px, 4vw, 56px);
            font-weight: 600;
            color: var(--color-accent-primary);
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
            border-radius: var(--border-radius-lg);
            padding: 80px 60px;
        }
        
        .cta-title {
            font-family: 'DM Serif Display', serif;
            font-size: clamp(36px, 5vw, 56px);
            font-weight: 400;
            color: white;
            margin-bottom: 24px;
        }
        
        .cta-description {
            font-size: 20px;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 40px;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-white {
            background: white;
            color: var(--color-accent-primary);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .btn-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        /* Footer */
        .footer {
            padding: 80px 40px 40px;
            max-width: 1400px;
            margin: 0 auto;
            border-top: 1px solid rgba(107, 142, 127, 0.1);
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
            color: var(--color-accent-primary);
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
            background: rgba(107, 142, 127, 0.1);
            border: 1px solid rgba(107, 142, 127, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-text-secondary);
            text-decoration: none;
            transition: var(--transition-medium);
        }
        
        .footer-social a:hover {
            background: var(--color-accent-primary);
            border-color: var(--color-accent-primary);
            color: white;
            transform: translateY(-2px);
        }
        
        .footer-bottom {
            padding-top: 40px;
            border-top: 1px solid rgba(107, 142, 127, 0.1);
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
        
    </style>
</head>
<body>
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
                    <a href="https://cloub.io/login" class="btn btn-secondary" style="background: rgba(255,255,255,0.15); color: white; border-color: rgba(255,255,255,0.3);">
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
