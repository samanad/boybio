<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="LinkDooni - Elegant link management and bio link platform">
    <title>LinkDooni - Elegant Link Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary-gradient: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            --accent-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 50%, #4facfe 100%);
            --gold-gradient: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            --text-primary: #1a1a2e;
            --text-secondary: #6b7280;
            --bg-light: #fafafa;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
            --shadow-lg: 0 20px 60px rgba(0,0,0,0.2);
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.7;
            color: var(--text-primary);
            background: var(--primary-gradient);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }
        
        /* Animated background elements */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: drift 20s linear infinite;
            z-index: 0;
        }
        
        @keyframes drift {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
        
        .container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 80px 40px;
            position: relative;
            z-index: 1;
        }
        
        /* Hero Section */
        .hero {
            text-align: center;
            margin-bottom: 100px;
            animation: fadeInUp 0.8s ease-out;
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
        
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 72px;
            font-weight: 700;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
            letter-spacing: -2px;
            position: relative;
            display: inline-block;
        }
        
        .logo::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--gold-gradient);
            border-radius: 2px;
        }
        
        .tagline {
            font-size: 24px;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 15px;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-size: 14px;
        }
        
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 56px;
            font-weight: 700;
            color: white;
            margin-bottom: 25px;
            line-height: 1.2;
        }
        
        .subtitle {
            font-size: 20px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 50px;
            font-weight: 300;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Features Grid */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin: 60px 0;
        }
        
        .feature {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px 30px;
            border-radius: 24px;
            box-shadow: var(--shadow-md);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .feature::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--accent-gradient);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease;
        }
        
        .feature:hover::before {
            transform: scaleX(1);
        }
        
        .feature:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--shadow-lg);
        }
        
        .feature-icon {
            font-size: 48px;
            margin-bottom: 20px;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
            transition: transform 0.3s ease;
        }
        
        .feature:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .feature-title {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--text-primary);
        }
        
        .feature-desc {
            color: var(--text-secondary);
            font-size: 15px;
            line-height: 1.6;
        }
        
        /* Buttons */
        .buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 60px;
        }
        
        .btn {
            padding: 18px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.5px;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
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
            background: var(--accent-gradient);
            color: white;
            box-shadow: 0 8px 24px rgba(245, 87, 108, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(245, 87, 108, 0.5);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-3px);
        }
        
        /* Footer */
        .footer {
            margin-top: 100px;
            padding-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .footer-brand {
            margin-bottom: 25px;
        }
        
        .footer-brand a {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 600;
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .footer-brand a:hover {
            transform: scale(1.05);
        }
        
        .footer-contact {
            margin-bottom: 25px;
        }
        
        .footer-contact a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 16px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .footer-contact a:hover {
            color: white;
            transform: translateY(-2px);
        }
        
        .social-links {
            display: flex;
            gap: 20px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .social-links a {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .social-links a:hover {
            background: var(--accent-gradient);
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 8px 20px rgba(245, 87, 108, 0.4);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 60px 20px;
            }
            
            .logo {
                font-size: 48px;
            }
            
            h1 {
                font-size: 36px;
            }
            
            .subtitle {
                font-size: 18px;
            }
            
            .features {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .feature {
                padding: 30px 20px;
            }
            
            .buttons {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }
        
        /* Loading animation */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        
        .loading {
            animation: pulse 2s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero">
            <div class="tagline">Elegant • Modern • Fast</div>
            <div class="logo">LinkDooni</div>
            <h1>Elevate Your Digital Presence</h1>
            <p class="subtitle">The most elegant link management platform for creators, brands, and businesses. Beautiful bio pages, powerful analytics, and lightning-fast performance.</p>
            
            <div class="features">
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    <div class="feature-title">Short Links</div>
                    <div class="feature-desc">Create memorable, branded short URLs that reflect your style. Track every click with detailed analytics.</div>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div class="feature-title">Bio Links</div>
                    <div class="feature-desc">Design stunning bio pages with our intuitive editor. Showcase your content, products, and personality beautifully.</div>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="feature-title">Analytics</div>
                    <div class="feature-desc">Real-time insights into your link performance. Understand your audience with comprehensive tracking and reports.</div>
                </div>
            </div>
            
            <div class="buttons">
                <a href="https://cloub.io/login" class="btn btn-primary">
                    <span>Get Started</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="https://cloub.io/register" class="btn btn-secondary">
                    <span>Sign Up Free</span>
                    <i class="fas fa-user-plus"></i>
                </a>
                <a href="https://cloub.io/directory" class="btn btn-secondary">
                    <span>Explore Directory</span>
                    <i class="fas fa-compass"></i>
                </a>
            </div>
        </div>
        
        <div class="footer">
            <div class="footer-brand">
                <a href="https://saman.host">Saman.Host</a>
            </div>
            <div class="footer-contact">
                <a href="mailto:info@biolink.dev">
                    <i class="fas fa-envelope"></i>
                    <span>info@biolink.dev</span>
                </a>
            </div>
            <div class="social-links">
                <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" title="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" title="Facebook">
                    <i class="fab fa-facebook"></i>
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
    </div>
</body>
</html>
