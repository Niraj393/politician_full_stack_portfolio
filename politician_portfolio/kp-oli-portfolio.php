<?php
// kp-oli-portfolio.php - COMPLETE FILE WITH SETTINGS
session_start();
require_once 'backend/database/connection.php';

// Load settings functions
if (!function_exists('getAllSettings')) {
    require_once 'backend/includes/settings-functions.php';
}

// Get all settings
$settings = getAllSettings();

// Helper function to get setting with fallback
function get_setting($key, $default = '') {
    global $settings;
    return isset($settings[$key]) ? $settings[$key] : $default;
}

// Helper function to get image path with fallback - CHECK IF EXISTS FIRST
if (!function_exists('get_image_path')) {
    function get_image_path($setting_key, $default = '') {
        $image_path = get_setting($setting_key);
        
        if (!$image_path || empty(trim($image_path))) {
            return $default;
        }
        
        // If it's already a full URL, return it
        if (strpos($image_path, 'http://') === 0 || strpos($image_path, 'https://') === 0) {
            return $image_path;
        }
        
        // Ensure relative path starts with '/' for absolute path from root
        $relative_path = '/' . ltrim($image_path, '/');
        
        // Optional: Check if file exists (uncomment if needed for debugging)
        // if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $relative_path)) {
        //     return $default; // Fallback if file missing
        // }
        
        return $relative_path;
    }
}

// Get current language
$currentLang = isset($_COOKIE['portfolio_lang']) ? $_COOKIE['portfolio_lang'] : 'en';
?>

<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(get_setting('site_title', 'KP Sharma Oli - Official Portfolio | NetaKnown')); ?></title>
    <link rel="icon" href="logo.jpeg" type="image/jpeg">
    <meta name="description" content="<?php echo htmlspecialchars(get_setting('site_description', 'Official portfolio of KP Sharma Oli - Former Prime Minister of Nepal, Chairman of CPN (UML)')); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_setting('site_keywords', 'KP Sharma Oli, Nepal Prime Minister, CPN UML, Nepali politician, political portfolio')); ?>">
    <meta name="author" content="<?php echo htmlspecialchars(get_setting('site_author', 'NetaKnown')); ?>">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary: #0f2a44;
    // Local video playback removed (YouTube-only experience)
            --primary-dark: #5a67d8;
            --secondary: #475569;
            --dark: #1a202c;
            --light: #f7fafc;
            --gray: #718096;
            --light-gray: #e2e8f0;
            --success: #48bb78;
            --success-light: #9ae6b4;
            --danger: #f56565;
            --danger-light: #fc8181;
            --warning: #ed8936;
            --warning-light: #f6ad55;
            --shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 30px 90px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --category-political: linear-gradient(135deg, #ef4444, #dc2626);
            --category-development: linear-gradient(135deg, #10b981, #059669);
            --category-international: linear-gradient(135deg, #3b82f6, #1d4ed8);
            --category-social: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            overflow-x: hidden;
        }

        body[lang="np"] {
            font-family: 'Noto Sans Devanagari', 'Inter', sans-serif;
            text-align: justify;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Language Toggle */
        .language-toggle {
            position: fixed;
            top: 88px;
            right: 5px;
            z-index: 2000;
            background: white;
            border-radius: 50px;
            padding: 10px;
            box-shadow: var(--shadow);
            display: flex;
            gap: 5px;
        }

        .lang-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 50px;
            background: transparent;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .lang-btn.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        /* Header */
        .portfolio-header {
            position: relative;
            height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.4));
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            overflow: hidden;
        }

        .header-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            align-items: center;
            padding: 0 5%;
        }

        .politician-main {
            display: flex;
            align-items: center;
            gap: 60px;
            width: 100%;
        }

        .politician-image {
            flex-shrink: 0;
            position: relative;
        }

        .main-photo {
            width: 400px;
            height: 500px;
            border-radius: 30px;
            object-fit: cover;
            box-shadow: var(--shadow-lg);
            border: 8px solid rgba(255, 255, 255, 0.92);
            transform: perspective(1000px) rotateY(-10deg);
            transition: var(--transition);
        }

        .main-photo:hover {
            transform: perspective(1000px) rotateY(0deg);
        }

        .party-badge {
            position: absolute;
            bottom: 20px;
            right: -20px;
            background: linear-gradient(135deg, rgba(229, 57, 53, 0.95), rgba(198, 40, 40, 0.95));
            color: white;
            padding: 8px 25px 8px 8px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 1.2rem;
            box-shadow: var(--shadow);
            transform: rotate(-5deg);
            display: flex;
            align-items: center;
            gap: 12px;
            border: 3px solid white;
            z-index: 10;
        }

        .party-flag-container {
            width: 50px;
            height: 40px;
            background: white;
            border-radius: 8px;
            padding: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .party-flag-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 5px;
        }

        .politician-info {
            flex: 1;
        }

        .politician-name {
            font-family: "Arizonia", cursive;
            font-size: 4.5rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff 0%, rgba(255, 255, 255, 0.9) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        body[lang="np"] .politician-name {
            font-family: 'Noto Sans Devanagari', 'Playfair Display', serif;
        }

        .politician-title {
            font-size: 1.8rem;
            font-weight: 500;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .header-stats {
            display: flex;
            gap: 40px;
            margin-top: 40px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, rgba(255, 255, 255, 0.8) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Navigation */
        .portfolio-nav {
            position: sticky;
            top: 0;
            z-index: 1000;
            background:var(--primary);
            backdrop-filter: blur(10px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
        }

        .nav-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-menu {
            display: flex;
            gap: 40px;
        }

        .nav-link {
            color:#e5e7eb;
            text-decoration: none;
            font-weight: 600;
            position: relative;
            padding: 10px 14px;
            transition: var(--transition);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(135deg, #21f6f9ff, #4384deff);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }

        .nav-link:hover {
            color:#e5e7eb;
    border-bottom: 2px solid #ffffff;
        }

        /* Sections */
        .section {
            padding: 20px 0;
        }

        .section-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
        }

        /* About Section */
        .about-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
            align-items: start;
        }

        .about-text {
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .about-text p {
            margin-bottom: 25px;
        }

        .quick-facts {
            background: white;
            padding: 9px;
            border-radius: 9px;
            box-shadow: var(--shadow);
        }

        .quick-facts h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .fact-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--light-gray);
        }

        .fact-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .fact-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        /* Preset category gradients for fact icons */
        .fact-icon.category-political { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .fact-icon.category-development { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .fact-icon.category-international { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
        .fact-icon.category-social { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
        .fact-icon.category-economy { background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%); }
        .fact-icon.category-health { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
        .fact-icon.category-education { background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); }
        .fact-icon.category-infrastructure { background: linear-gradient(135deg, #64748b 0%, #334155 100%); }
        .fact-icon.category-environment { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); }
        .fact-icon.category-culture { background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); }
        .fact-icon.category-default { background: linear-gradient(135deg, var(--primary), var(--secondary)); }

        .fact-content h4 {
            font-size: 1rem;
            color: var(--gray);
            margin-bottom: 5px;
        }

        .fact-content p {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
        }

        /* Gallery Section */
        .gallery-filter {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 12px 30px;
            background: white;
            border: 2px solid var(--light-gray);
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .filter-btn.active,
        .filter-btn:hover {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-color: transparent;
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .gallery-item {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            height: 240px;
            cursor: pointer;
        }

        .gallery-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover .gallery-img {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            padding: 30px;
            color: white;
            transform: translateY(100%);
            transition: var(--transition);
        }

        .gallery-item:hover .gallery-overlay {
            transform: translateY(0);
        }

        /* Timeline Section */
        .timeline-controls {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-bottom: 10px;
        }

        .timeline-nav-btn {
            margin: 0 5px;
            width: 35px;
            height: 35px;
            border-radius: 40%;
            border: none;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .timeline-nav-btn:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .timeline-nav-btn:disabled {
            opacity: 0.2;
            cursor: not-allowed;
        }

        .horizontal-timeline-container {
            position: relative;
            width: 100%;
            overflow-x: auto;
            padding: 20px 0 40px;
            scrollbar-width: thin;
            scrollbar-color: var(--primary) var(--light-gray);
        }

        .horizontal-timeline-container::-webkit-scrollbar {
            height: 100px;
        }

        .horizontal-timeline-container::-webkit-scrollbar-track {
            background: var(--light-gray);
            border-radius: 10px;
        }

        .horizontal-timeline-container::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
        }

        .timeline-track {
            display: flex;
            gap: 20px;
            padding: 0 15px;
            min-width: min-content;
            position: relative;
        }

        .timeline-track::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 15px;
            right: 15px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            z-index: 1;
        }

        .timeline-item {
            position: relative;
            min-width: 280px;
            max-width: 280px;
            flex-shrink: 0;
            z-index: 2;
            cursor: pointer;
            transition: var(--transition);
        }

        .timeline-item:hover {
            transform: translateY(-10px);
        }

        .timeline-dot {
            position: absolute;
            top: 14px;
            left: 0;
            width: 16px;
            height: 16px;
            background: white;
            border: 3px solid var(--primary);
            border-radius: 50%;
            z-index: 3;
            transition: var(--transition);
        }

        .timeline-item:hover .timeline-dot {
            transform: scale(1.2);
            background: var(--primary);
            box-shadow: 0 0 0 8px rgba(102, 126, 234, 0.2);
        }

        .timeline-content {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            margin-top: 30px;
            position: relative;
            transition: var(--transition);
            min-height: 200px;
            display: flex;
            flex-direction: column;
        }

        .timeline-content::before {
            content: '';
            position: absolute;
            top: -8px;
            left: 15px;
            width: 0;
            height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-bottom: 8px solid white;
        }

        .timeline-item:hover .timeline-content {
            box-shadow: var(--shadow-lg);
        }

        .timeline-year {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 6px 15px;
            border-radius: 50px;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .timeline-content h3 {
            font-size: 1.1rem;
            margin-bottom: 8px;
            color: var(--dark);
            line-height: 1.2;
        }

        .timeline-content p {
            font-size: 0.85rem;
            line-height: 1.5;
            color: var(--gray);
            flex-grow: 1;
        }

        .timeline-progress {
            margin-top: 30px;
            padding: 0 15px;
        }

        .progress-track {
            height: 4px;
            background: var(--light-gray);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            width: 10%;
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: var(--gray);
        }

        #currentYear {
            font-weight: 600;
            color: var(--primary);
        }

        /* Achievements Section */
        .achievements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 18px;
        }

        .achievement-card {
            background: white;
            padding: 18px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            font-size: 0.95rem;
        }

        .achievement-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }

        .achievement-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .achievement-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            color: white;
            font-size: 1.1rem;
        }

        /* Category Color Variations - apply only to the icon element */
        .achievement-icon.category-political { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .achievement-icon.category-development { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .achievement-icon.category-international { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
        .achievement-icon.category-social { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }

        /* Video Section */
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 18px;
        }

        .video-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            font-size: 0.95rem;
        }

        .video-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .video-thumbnail {
            position: relative;
            height: 140px;
            overflow: hidden;
            background: #000;
        }

        .video-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .video-card:hover .video-thumbnail img {
            transform: scale(1.1);
        }

        .video-buttons {
            position: absolute;
            bottom: 15px;
            left: 15px;
            right: 15px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .video-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .yt-btn {
            background: #ff0000;
            color: white;
            padding: 5px 4px;
            font-size: 1rem;
            border-radius: 12px;
            min-width: 160px;
            justify-content: center;
            margin-bottom: 0%;
        }

        .yt-btn:hover {
            background: #cc0000;
            transform: translateY(-3px);
        }

        .local-btn {
            background: var(--primary);
            color: white;
        }

        .local-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
        }

        .video-info {
            padding: 16px;
        }

        .video-info h3 {
            font-size: 1rem;
            margin-bottom: 6px;
            color: var(--dark);
        }

        .video-info p {
            color: var(--gray);
            margin-bottom: 10px;
            font-size: 0.85rem;
            line-height: 1.4;
        }

        /* Video Modal */
        .video-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .video-modal.show {
            display: flex;
            animation: fadeIn 0.3s ease;
        }

        .video-modal-content {
            width: 90%;
            max-width: 900px;
            background: transparent;
            position: relative;
        }

        .video-modal-close {
            position: absolute;
            top: -40px;
            right: 0;
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark);
            border: none;
            transition: var(--transition);
        }

        .video-modal-close:hover {
            background: var(--danger);
            color: white;
        }

        /* Local video player removed */

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Contact Section */
        .contact-section {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 40px 0;
            margin-bottom: 20px;
        }

        .contact-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            align-items: start;
        }

        .contact-column {
            display: flex;
            flex-direction: column;
        }

        /* Contact Form */
        .contact-form-card {
            background:linear-gradient(135deg, #0bc6f5ff 0%, #9f542cff 100%); ;
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            height: 392px;
            display: flex;
            flex-direction: column;
        }

        .contact-form-card h3 {
            font-size: 1rem;
            margin-bottom: 10px;
            color: var(--dark);
            text-align: center;
        }

        .form-group {
            margin-bottom: 7px;
        }

        .form-label {
            display: block;
            margin-bottom: 0%;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.80rem;
        }

        .form-input,
        .form-textarea {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            font-family: 'Inter', sans-serif;
            font-size: 0.8rem;
            transition: var(--transition);
            background: #f8fafc;
        }

        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
        }

        .form-textarea {
            min-height: 20px;
            resize: vertical;
        }

        .submit-btn {
            padding: 8px 12px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
            width: 100%;
            margin-bottom: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* Donation Card */
        .donation-card {
            background: linear-gradient(135deg, #080cddff 0%, #e2e8f0 100%);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            height: 100%;
            border: 1px solid var(--light-gray);
        }

        .donation-header {
            background: linear-gradient(135deg, #1a237e, #8f99eaff);
            color: white;
            padding: 12px;
            text-align: center;
        }

        .donation-icon {
            width: 30px;
            height: 30px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-size: 0.9rem;
        }

        .donation-header h3 {
            font-size: 1rem;
            margin: 0;
        }

        .donation-body {
            padding: 15px;
        }

        .donation-body p {
            text-align: center;
            color: var(--gray);
            margin-bottom: 12px;
            line-height: 1.4;
            font-size: 0.85rem;
        }

        .qr-container {
            background: #f8fafc;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid var(--light-gray);
        }

        .qr-code {
            width: 60px;
            height: 60px;
            flex-shrink: 0;
        }

        .qr-code img {
            width: 110%;
            height: 110%;
            border-radius: 6px;
            object-fit: cover;
        }

        .qr-info h4 {
            font-size: 0.85rem;
            margin-bottom: 4px;
            color: var(--dark);
        }

        .qr-info p {
            font-size: 0.75rem;
            color: var(--gray);
            margin: 0;
        }

        .donation-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .amount-section,
        .upload-section {
            flex: 1;
        }

        .form-label-small {
            display: block;
            margin-bottom: 4px;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.8rem;
        }

        .amount-input-small {
            position: relative;
            display: flex;
            align-items: center;
        }

        .total-amount-input {
            width: 100%;
            padding: 8px 10px 8px 25px;
            border: 2px solid var(--light-gray);
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: var(--transition);
            background: white;
        }

        .currency-small {
            position: absolute;
            left: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--dark);
        }

        .upload-area-small {
            border: 2px dashed var(--light-gray);
            border-radius: 6px;
            padding: 8px 10px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            height: 36px;
        }

        .upload-area-small:hover {
            border-color: var(--primary);
            background: rgba(102, 126, 234, 0.05);
        }

        .upload-area-small i {
            font-size: 0.9rem;
            color: var(--gray);
        }

        .upload-text {
            font-size: 0.8rem;
            color: var(--gray);
            font-weight: 600;
        }

        .file-preview {
            background: #f0f9ff;
            border: 1px solid #b3e0ff;
            border-radius: 6px;
            padding: 8px 10px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .file-name {
            font-size: 0.8rem;
            color: var(--dark);
            word-break: break-all;
            max-width: 80%;
        }

        .remove-file {
            background: none;
            border: none;
            color: #ff6b6b;
            cursor: pointer;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.8rem;
            transition: var(--transition);
        }

        .remove-file:hover {
            background: rgba(255, 107, 107, 0.1);
        }

        .donate-btn-primary {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #ff5722, #ff7043);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-bottom: 10px;
        }

        .donate-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 87, 34, 0.3);
        }

        .donation-security {
            text-align: center;
            font-size: 0.75rem;
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        /* Secretary Card */
        .secretary-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            height: 100%;
            border: 1px solid var(--light-gray);
        }

        .secretary-header {
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            align-items: center;
            gap: 15px;
            min-height: 100px;
        }

        .secretary-image {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.3);
            flex-shrink: 0;
        }

        .secretary-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .secretary-info {
            flex: 1;
            min-width: 0;
        }

        .secretary-info h3 {
            font-size: 0.8rem;
            margin-bottom: 5px;
            opacity: 0.9;
            line-height: 1.2;
        }

        .secretary-info h4 {
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .secretary-details {
            padding: 15px;
        }

        .contact-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--light-gray);
        }

        .contact-detail:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .contact-icon {
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        .contact-content {
            flex: 1;
            min-width: 0;
        }

        .contact-label {
            display: block;
            font-size: 0.75rem;
            color: var(--gray);
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .contact-value {
            display: block;
            font-weight: 600;
            color: var(--dark);
            text-decoration: none;
            transition: var(--transition);
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .contact-value:hover {
            color: var(--primary);
        }

        .secretary-actions {
            padding: 0 15px 15px;
        }

        .call-btn {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .call-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* Footer */
        .portfolio-footer {
            background: var(--dark);
            color: white;
            padding: 14px 18px;
            margin-top: auto;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 88px;
            align-items: center;
            padding: 5px 8px;
            max-width: 1200px;
            margin: 0 auto;
            height: 100%;
        }

        /* Tighten secretary / contact box spacing */
        .single__box-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .single__box-image {
            width: 72px;
            height: 72px;
            flex-shrink: 0;
            overflow: hidden;
            border-radius: 8px;
        }

        .single__box-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Ensure footer secretary image displays correctly */
        .secretary-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.06);
        }

        .single__box-info {
            padding: 0;
            line-height: 1.1;
        }

        .single__box-name,
        .secretary-info h3 {
            margin: 0;
            font-size: 0.92rem;
            font-weight: 700;
        }

        .secretary-info h4 {
            margin: 2px 0 6px 0;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .contact-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            padding-bottom: 6px;
            border-bottom: 1px solid rgba(255,255,255,0.03);
        }

        .contact-value {
            font-size: 0.85rem;
        }

        .footer-social {
            display: flex;
            gap: 12px;
            margin-top: 5px;
            margin-bottom: 5px;
            
        }

        .footer-logo {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0%;
            background: linear-gradient(135deg, #fff 0%, #c3cfe2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 1px;
        }

        .footer-social {
            display: flex;
            gap: 25px;
            margin-top: 20px;
            margin-left: auto;
            margin-right: 0px;
        }

        .social-link {
            width: 10px;
            height: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: var(--transition);
            margin-top: 1%;
            margin-left: 0px;
            font-size: 1.2rem;
        }

        .social-link:hover {
            background: var(--primary);
            transform: translateY(-5px);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal.show {
            display: flex;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            max-width: 900px;
            width: 100%;
            background: white;
            border-radius: 30px;
            overflow: hidden;
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 10;
            box-shadow: var(--shadow);
        }

        /* More Videos Link */
        .more-videos-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            background: transparent;
            border: 2px solid #FF0000;
            border-radius: 50px;
            text-decoration: none;
            color: #FF0000;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .more-videos-link:hover {
            background: #FF0000;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 0, 0, 0.2);
        }

        .more-videos-link::before {
            content: '▶';
            font-size: 0.9rem;
        }

        .name {
            color: rgb(118, 205, 227);
            font-family: 'Arizonia', cursive;
            font-size: 36px;
        }

        /* Single Box Wrapper */
        .single__box-wrapper {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 18px 20px;
            background:none;
            border-radius: 10px;
            max-width: 520px;
            font-family: "Noto Sans Devanagari", Arial, sans-serif;
        }

        .single__box-image {
            flex-shrink: 0;
            width: 100px;
            height: 100px;
        }

        .single__box-image img {
            width: 100%;
            height: 100%;
            border-radius: 100%;
            object-fit: center;
            border: 0px solid #ffffff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        .single__box-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .single__box-name {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            color: #ffffffff;
        }

        .team__phone,
        .team__mail {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #ffffffff;
            text-decoration: none;
            margin-top: 4px;
        }

        .team__mail svg {
            width: 16px;
            height: 16px;
            color: #6796d7ff;
        }

        /* Carousel Styles */
        .carousel-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow);
            z-index: 10;
        }

        .carousel-nav-btn:hover:not(:disabled) {
            transform: translateY(-50%) scale(1.1);
            box-shadow: var(--shadow-lg);
        }

        .carousel-nav-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .carousel-nav-btn.prev {
            left: 0;
        }

        .carousel-nav-btn.next {
            right: 0;
        }

        .carousel-indicators {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .carousel-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--light-gray);
            cursor: pointer;
            transition: var(--transition);
        }

        .carousel-dot.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            width: 30px;
            border-radius: 6px;
        }

        .gallery-carousel-wrapper,
        .achievements-carousel-wrapper,
        .video-carousel-wrapper {
            position: relative;
            overflow: hidden;
            padding: 0 60px;
        }

        .gallery-carousel {
            display: flex;
            gap: 30px;
            transition: transform 0.5s ease-in-out;
            
        }
        .video-carousel {
            display: flex;
            gap: 30px;
            transition: transform 0.5s ease-in-out;
            width:55%;
        }
        .achievements-carousel {
            display: flex;
            gap: 30px;
            transition: transform 0.5s ease-in-out;
            width: 85%;
            padding: 0 40px;
            height: auto;
        }

        .gallery-carousel .gallery-item,
        .achievements-carousel .achievement-card,
        .video-carousel .video-card {
            min-width: 30%;
            flex-shrink: 0;
        }

        /* Fallback Image Styles */
        .fallback-image {
            opacity: 0.8;
            border: 2px dashed rgba(255, 255, 255, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .politician-main {
                flex-direction: column;
                text-align: center;
                gap: 40px;
            }

            .main-photo {
                width: 300px;
                height: 400px;
            }

            .contact-container {
                grid-template-columns: 1fr;
                gap: 15px;
                max-width: 400px;
                margin: 0 auto;
            }
        }

        @media (max-width: 768px) {
            .politician-name {
                font-size: 3rem;
            }

            .section-title {
                font-size: 2.5rem;
            }

            .about-content,
            .contact-container {
                grid-template-columns: 1fr;
            }

            .timeline-item {
                min-width: 240px;
                max-width: 240px;
            }

            .timeline-content {
                min-height: 180px;
                padding: 15px;
            }

            .timeline-track {
                gap: 15px;
            }

            .language-toggle {
                top: 15px;
                right: 15px;
            }

            .video-buttons {
                flex-direction: column;
                align-items: stretch;
            }

            .video-btn {
                justify-content: center;
            }

            /* Local video player removed */

            .donation-row {
                flex-direction: column;
                gap: 8px;
            }

            .qr-container {
                flex-direction: column;
                text-align: center;
                gap: 8px;
            }

            .contact-form-card,
            .donation-body,
            .secretary-details {
                padding: 15px;
            }

            .donation-header,
            .secretary-header {
                padding: 15px 12px;
            }

            .gallery-carousel-wrapper,
            .achievements-carousel-wrapper,
            .video-carousel-wrapper {
                padding: 0 40px;
            }
            
            .carousel-nav-btn {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .politician-name {
                font-size: 2.5rem;
            }

            .header-stats {
                flex-direction: column;
                gap: 20px;
            }

            .nav-menu {
                gap: 20px;
                font-size: 0.9rem;
            }

            .party-badge {
                right: -10px;
                bottom: 10px;
                padding: 8px 15px;
                font-size: 1rem;
            }

            /* Local video player removed */

            .contact-container {
                gap: 12px;
            }

            .form-input,
            .form-textarea {
                padding: 6px 8px;
                font-size: 0.85rem;
            }

            .submit-btn,
            .donate-btn-primary,
            .call-btn {
                padding: 8px;
                font-size: 0.85rem;
            }

            .secretary-image {
                width: 45px;
                height: 45px;
            }

            .more-videos-link {
                padding: 10px 25px;
                font-size: 1rem;
            }
        }

        /* ==================== UPDATED ACTIVITIES SECTION ==================== */
        #activities {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 40px 0;
            min-height: 550px;
        }

        /* Activities Container */
        .activities-container {
            position: relative;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Small Stats Container */
        .activities-stats-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            padding: 0 20px;
        }

        .stat-card-small {
            flex: 1;
            min-width: 150px;
            max-width: 200px;
            background: white;
            border-radius: 12px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .stat-card-small:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-icon-small {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .stat-card-small:nth-child(2) .stat-icon-small {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .stat-card-small:nth-child(3) .stat-icon-small {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .stat-card-small:nth-child(4) .stat-icon-small {
            background: linear-gradient(135deg, #6b7280, #4b5563);
        }

        .stat-content-small h3 {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1e293b;
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-content-small p {
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Activities Carousel Container */
        .activities-carousel-container {
            position: relative;
            width: 100%;
            overflow: hidden;
            margin: 20px auto 40px;
            padding: 0 60px;
        }

        .activities-carousel {
            display: flex;
            gap: 20px;
            transition: transform 0.5s ease-in-out;
            padding: 10px 5px;
        }

        .activities-carousel .activity-card-compact {
            flex: 0 0 auto;
            width: 300px;
            min-height: 320px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .activities-carousel .activity-card-compact:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }

        /* Compact Activity Card */
        .activity-image-compact {
            height: 150px;
            position: relative;
            overflow: hidden;
        }

        .activity-image-compact img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .activity-card-compact:hover .activity-image-compact img {
            transform: scale(1.05);
        }

        .activity-status-compact {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 700;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            backdrop-filter: blur(10px);
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .activity-date-compact {
            position: absolute;
            top: 10px;
            left: 10px;
            background: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 700;
            color: #1e293b;
            font-size: 0.75rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .activity-content-compact {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .activity-category-compact {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: #f1f5f9;
            border-radius: 20px;
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 10px;
            align-self: flex-start;
        }

        .activity-title-compact {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            line-height: 1.3;
            flex-grow: 1;
        }

        .activity-description-compact {
            color: #64748b;
            font-size: 0.8rem;
            line-height: 1.4;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .activity-meta-compact {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 15px;
            padding-top: 10px;
            border-top: 1px solid #f1f5f9;
        }

        .meta-item-compact {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            font-size: 0.75rem;
        }

        .meta-item-compact i {
            width: 16px;
            color: #3b82f6;
            font-size: 0.8rem;
        }

        .activity-footer-compact {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        .activity-actions-compact {
            display: flex;
            gap: 8px;
        }

        .action-btn-compact {
            padding: 6px 12px;
            border-radius: 20px;
            border: none;
            font-weight: 600;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .btn-details-compact {
            background: #3b82f6;
            color: white;
        }

        .btn-details-compact:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }

        .priority-indicator-compact {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-left: 5px;
        }

        .priority-high { background: #ef4444; }
        .priority-medium { background: #f59e0b; }
        .priority-low { background: #10b981; }

        /* Filter Tabs Compact */
        .activities-filter-container {
            margin-bottom: 25px;
        }

        .filter-tabs-compact {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .filter-tab-compact {
            padding: 8px 16px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 30px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
        }

        .filter-tab-compact:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.1);
        }

        .filter-tab-compact.active {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-color: transparent;
            color: white;
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
        }

        .filter-tab-compact i {
            font-size: 0.9rem;
        }

        .filter-dropdowns-compact {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .category-filter-compact,
        .date-filter-compact {
            padding: 8px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 30px;
            background: white;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            color: #334155;
            min-width: 150px;
            cursor: pointer;
            transition: all 0.3s ease;
            outline: none;
            font-size: 0.85rem;
        }

        .category-filter-compact:focus,
        .date-filter-compact:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Loading State */
        .loading-spinner {
            text-align: center;
            padding: 40px;
            width: 100%;
        }

        .loading-spinner i {
            font-size: 1.5rem;
            color: #3b82f6;
            margin-bottom: 10px;
        }

        .loading-spinner p {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* No Activities */
        .no-activities {
            text-align: center;
            padding: 40px 20px;
            width: 100%;
        }

        .no-activities i {
            font-size: 2rem;
            color: #cbd5e1;
            margin-bottom: 15px;
        }

        .no-activities h3 {
            font-size: 1.2rem;
            color: #64748b;
            margin-bottom: 8px;
        }

        .no-activities p {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        /* View All Button */
        .view-all-activities {
            text-align: center;
            margin-top: 30px;
        }

        .view-all-btn {
            padding: 10px 25px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
        }

        .view-all-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        }

        /* Carousel Indicators */
        .activities-indicators {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
        }

        .activities-indicators .indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #e2e8f0;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .activities-indicators .indicator.active {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            width: 25px;
            border-radius: 5px;
        }

        /* Responsive for Activities */
        @media (max-width: 768px) {
            #activities {
                padding: 30px 0;
                min-height: 500px;
            }
            
            .activities-stats-container {
                gap: 10px;
            }
            
            .stat-card-small {
                min-width: 120px;
                padding: 12px;
            }
            
            .stat-content-small h3 {
                font-size: 1.5rem;
            }
            
            .stat-content-small p {
                font-size: 0.7rem;
            }
            
            .activities-carousel-container {
                padding: 0 50px;
            }
            
            .activities-carousel .activity-card-compact {
                width: 280px;
            }
            
            .filter-tabs-compact {
                gap: 8px;
            }
            
            .filter-tab-compact {
                padding: 6px 12px;
                font-size: 0.8rem;
            }
            
            .category-filter-compact,
            .date-filter-compact {
                min-width: 130px;
                padding: 6px 12px;
            }
        }

        @media (max-width: 480px) {
            .activities-stats-container {
                gap: 8px;
            }
            
            .stat-card-small {
                min-width: 100px;
                padding: 10px;
            }
            
            .stat-content-small h3 {
                font-size: 1.3rem;
            }
            
            .activities-carousel-container {
                padding: 0 40px;
            }
            
            .activities-carousel .activity-card-compact {
                width: 250px;
            }
            
            .activity-title-compact {
                font-size: 0.9rem;
            }
            
            .activity-description-compact {
                font-size: 0.75rem;
            }
        }
    </style>
</head>

<body lang="<?php echo $currentLang; ?>">
    <!-- Language Toggle -->
    <div class="language-toggle" id="languageToggle">
        <button class="lang-btn <?php echo $currentLang === 'en' ? 'active' : ''; ?>" data-lang="en">English</button>
        <button class="lang-btn <?php echo $currentLang === 'np' ? 'active' : ''; ?>" data-lang="np">नेपाली</button>
    </div>

   <!-- ==================== HERO SECTION ==================== -->
<header class="portfolio-header">
    <?php
    // Use `get_image_path()` from includes/settings-functions.php (no duplicate)

    // Get hero background image
    $heroBgImage = get_image_path('hero_bg_image', 'https://imgeng.jagran.com/images/2025/09/09/article/image/KP-Sharma-oli-ouster-1757414398029.webp');
    ?>
    
    <div class="header-content" style="background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.4)), url('<?php echo htmlspecialchars($heroBgImage); ?>') no-repeat center center/cover;">
        <div class="container">
            <div class="politician-main" data-aos="fade-up" data-aos-duration="1000">
                <!-- Politician Image -->
                <div class="politician-image">
                    <?php
                    $mainPhoto = get_image_path('hero_main_image', 'https://upload.wikimedia.org/wikipedia/commons/d/dd/The_Prime_Minister_of_Nepal%2C_Shri_KP_Sharma_Oli_at_Bangkok%2C_in_Thailand_on_April_04%2C_2025_%28cropped%29.jpg');
                    ?>
                    <img src="<?php echo htmlspecialchars($mainPhoto); ?>" 
                         alt="<?php echo htmlspecialchars(get_setting('hero_politician_name_en', 'KP Sharma Oli')); ?>" 
                         class="main-photo"
                         onerror="this.src='https://via.placeholder.com/400x500?text=Image+Not+Found'; this.onerror=null;">
                    
                    <!-- Party Badge -->
                    <div class="party-badge">
                        <div class="party-flag-container">
                            <?php
                            $partyLogo = get_image_path('hero_party_logo', 'https://play-lh.googleusercontent.com/0sLxEus620mEaNx72asMxDxWZBqeFfsa1fiuDe3wpV4NvTzJbDwCxLfeUhe2P7HjybA');
                            ?>
                            <img src="<?php echo htmlspecialchars($partyLogo); ?>" 
                                 alt="<?php echo htmlspecialchars(get_setting('hero_party_name_en', 'CPN (UML)')); ?> Flag" 
                                 class="party-flag-img"
                                 onerror="this.src='https://via.placeholder.com/50x40?text=No+Logo'; this.onerror=null;">
                        </div>
                        <span data-en="<?php echo htmlspecialchars(get_setting('hero_party_name_en', 'CPN (UML)')); ?>" 
                              data-np="<?php echo htmlspecialchars(get_setting('hero_party_name_np', 'नेकपा (एमाले)')); ?>">
                            <?php echo $currentLang === 'en' ? get_setting('hero_party_name_en', 'CPN (UML)') : get_setting('hero_party_name_np', 'नेकपा (एमाले)'); ?>
                        </span>
                    </div>
                </div>

                <!-- Politician Information -->
                <div class="politician-info">
                    <!-- Name -->
                    <h1 class="politician-name" 
                        data-en="<?php echo htmlspecialchars(get_setting('hero_politician_name_en', 'Khadga Prasad Sharma Oli')); ?>" 
                        data-np="<?php echo htmlspecialchars(get_setting('hero_politician_name_np', 'खड्गप्रसाद शर्मा ओली')); ?>">
                        <?php echo $currentLang === 'en' ? 
                            get_setting('hero_politician_name_en', 'Khadga Prasad Sharma Oli') : 
                            get_setting('hero_politician_name_np', 'खड्गप्रसाद शर्मा ओली'); ?>
                    </h1>

                    <!-- Title -->
                    <p class="politician-title" 
                       data-en="<?php echo htmlspecialchars(get_setting('hero_politician_title_en', 'Former Prime Minister of Nepal · Chairman of CPN (UML)')); ?>" 
                       data-np="<?php echo htmlspecialchars(get_setting('hero_politician_title_np', 'नेपालका पूर्व प्रधानमन्त्री · नेकपा (एमाले) का अध्यक्ष')); ?>">
                        <?php echo $currentLang === 'en' ? 
                            get_setting('hero_politician_title_en', 'Former Prime Minister of Nepal · Chairman of CPN (UML)') : 
                            get_setting('hero_politician_title_np', 'नेपालका पूर्व प्रधानमन्त्री · नेकपा (एमाले) का अध्यक्ष'); ?>
                    </p>

                    <!-- Statistics -->
                    <div class="header-stats">
                        <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                            <span class="stat-number"><?php echo get_setting('hero_years_in_politics', '42'); ?></span>
                            <span class="stat-label" data-en="Years in Politics" data-np="राजनीतिमा वर्ष">
                                <?php echo $currentLang === 'en' ? 'Years in Politics' : 'राजनीतिमा वर्ष'; ?>
                            </span>
                        </div>
                        <div class="stat-item" data-aos="fade-up" data-aos-delay="400">
                            <span class="stat-number"><?php echo get_setting('hero_terms_as_pm', '4'); ?></span>
                            <span class="stat-label" data-en="Terms as PM" data-np="प्रधानमन्त्री पदमा कार्यकाल">
                                <?php echo $currentLang === 'en' ? 'Terms as PM' : 'प्रधानमन्त्री पदमा कार्यकाल'; ?>
                            </span>
                        </div>
                        <div class="stat-item" data-aos="fade-up" data-aos-delay="600">
                            <span class="stat-number"><?php echo get_setting('hero_approval_rating', '75'); ?>%</span>
                            <span class="stat-label" data-en="Approval Rating" data-np="स्वीकृति दर">
                                <?php echo $currentLang === 'en' ? 'Approval Rating' : 'स्वीकृति दर'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

    <!-- Navigation -->
<nav class="portfolio-nav">
    <div class="container nav-container">
        <a href="#" class="nav-logo">
            <div class="party-flag-container">
                <?php
                $navPartyLogo = get_image_path('footer_party_logo', get_image_path('hero_party_logo', 'https://play-lh.googleusercontent.com/0sLxEus620mEaNx72asMxDxWZBqeFfsa1fiuDe3wpV4NvTzJbDwCxLfeUhe2P7HjybA'));
                ?>
                <img src="<?php echo htmlspecialchars($navPartyLogo); ?>" 
                     alt="<?php echo htmlspecialchars(get_setting('hero_party_name_en', 'CPN (UML)')); ?> Flag" 
                     class="party-flag-img">
            </div>
            <span class="name" data-en="<?php echo htmlspecialchars(get_setting('footer_politician_name_en', 'KP Oli')); ?>" 
                  data-np="<?php echo htmlspecialchars(get_setting('footer_politician_name_np', 'केपी ओली')); ?>">
                <?php echo $currentLang === 'en' ? get_setting('footer_politician_name_en', 'KP Oli') : get_setting('footer_politician_name_np', 'केपी ओली'); ?>
            </span>
        </a>
        
        <div class="nav-menu">
            <a href="#about" class="nav-link active" data-en="About" data-np="बारेमा">
                <?php echo $currentLang === 'en' ? 'About' : 'बारेमा'; ?>
            </a>
            <a href="#gallery" class="nav-link" data-en="Gallery" data-np="ग्यालरी">
                <?php echo $currentLang === 'en' ? 'Gallery' : 'ग्यालरी'; ?>
            </a>
            <a href="#timeline" class="nav-link" data-en="Timeline" data-np="समयरेखा">
                <?php echo $currentLang === 'en' ? 'Timeline' : 'समयरेखा'; ?>
            </a>
            <a href="#activities" class="nav-link" data-en="Activities" data-np="गतिविधिहरू">
                <?php echo $currentLang === 'en' ? 'Activities' : 'गतिविधिहरू'; ?>
            </a>
            <a href="#achievements" class="nav-link" data-en="Achievements" data-np="उपलब्धिहरू">
                <?php echo $currentLang === 'en' ? 'Achievements' : 'उपलब्धिहरू'; ?>
            </a>
            <a href="#videos" class="nav-link" data-en="Videos" data-np="भिडियोहरू">
                <?php echo $currentLang === 'en' ? 'Videos' : 'भिडियोहरू'; ?>
            </a>
            <a href="#contact" class="nav-link" data-en="Contact" data-np="सम्पर्क">
                <?php echo $currentLang === 'en' ? 'Contact' : 'सम्पर्क'; ?>
            </a>
        </div>
    </div>
</nav>

    <!-- ==================== ABOUT SECTION ==================== -->
<section id="about" class="section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2 class="section-title" 
                data-en="<?php echo htmlspecialchars(get_setting('about_title_en', 'About')); ?>" 
                data-np="<?php echo htmlspecialchars(get_setting('about_title_np', 'बारेमा')); ?>">
                <?php echo $currentLang === 'en' ? get_setting('about_title_en', 'About') : get_setting('about_title_np', 'बारेमा'); ?>
            </h2>
            
            <p class="section-subtitle" 
               data-en="<?php echo htmlspecialchars(get_setting('about_subtitle_en', 'A visionary leader dedicated to Nepal\'s development and prosperity')); ?>" 
               data-np="<?php echo htmlspecialchars(get_setting('about_subtitle_np', 'नेपालको विकास र समृद्धिका लागि समर्पित दूरदर्शी नेता')); ?>">
                <?php echo $currentLang === 'en' ? 
                    get_setting('about_subtitle_en', 'A visionary leader dedicated to Nepal\'s development and prosperity') : 
                    get_setting('about_subtitle_np', 'नेपालको विकास र समृद्धिका लागि समर्पित दूरदर्शी नेता'); ?>
            </p>
        </div>

        <div class="about-content" data-aos="fade-up" data-aos-delay="200">
            <div class="about-text">
                <?php
                $aboutContent = $currentLang === 'en' ? 
                    get_setting('about_content_en', 'Khadga Prasad Sharma Oli is a prominent Nepalese politician and the Chairman of the Communist Party of Nepal (Unified Marxist–Leninist). Born on February 22, 1952, in Terhathum District, Nepal, Oli has dedicated over four decades to public service and political leadership.') : 
                    get_setting('about_content_np', 'खड्गप्रसाद शर्मा ओली एक प्रमुख नेपाली राजनीतिज्ञ तथा नेपाल कम्युनिस्ट पार्टी (एकीकृत मार्क्सवादी-लेनिनवादी) का अध्यक्ष हुन्। सन् १९५२ फेब्रुअरी २२ मा तेह्रथुम जिल्लामा जन्मिएका ओलीले चार दशकभन्दा बढी समय जनसेवा र राजनीतिक नेतृत्वमा बिताएका छन्।');
                
                $paragraphs = preg_split('/\n\s*\n/', $aboutContent);
                
                foreach ($paragraphs as $paragraph) {
                    if (trim($paragraph) !== '') {
                        echo '<p>' . nl2br(htmlspecialchars(trim($paragraph))) . '</p>';
                    }
                }
                
                if (empty($aboutContent) || trim($aboutContent) === '') {
                    echo '<p>' . ($currentLang === 'en' ? 
                        'Khadga Prasad Sharma Oli is a prominent Nepalese politician and the Chairman of the Communist Party of Nepal (Unified Marxist–Leninist). Born on February 22, 1952, in Terhathum District, Nepal, Oli has dedicated over four decades to public service and political leadership.' : 
                        'खड्गप्रसाद शर्मा ओली एक प्रमुख नेपाली राजनीतिज्ञ तथा नेपाल कम्युनिस्ट पार्टी (एकीकृत मार्क्सवादी-लेनिनवादी) का अध्यक्ष हुन्। सन् १९५२ फेब्रुअरी २२ मा तेह्रथुम जिल्लामा जन्मिएका ओलीले चार दशकभन्दा बढी समय जनसेवा र राजनीतिक नेतृत्वमा बिताएका छन्।') . '</p>';
                    
                    echo '<p>' . ($currentLang === 'en' ? 
                        'He served as the Prime Minister of Nepal multiple times, most notably from 2015 to 2016 and again from 2018 to 2021. His tenure was marked by significant constitutional reforms, economic development initiatives, and strengthening of Nepal\'s international relations.' : 
                        'उनी नेपालका प्रधानमन्त्रीको रूपमा धेरै पटक सेवा गरिसकेका छन्, विशेष गरी सन् २०१५ देखि २०१६ सम्म र पुनः २०१८ देखि २०२१ सम्म। उनको कार्यकालमा महत्वपूर्ण संवैधानिक सुधार, आर्थिक विकासका पहलहरू र नेपालको अन्तर्राष्ट्रिय सम्बन्धहरू सुदृढीकरण गर्ने कार्यहरू सम्पन्न भए।') . '</p>';
                    
                    echo '<p>' . ($currentLang === 'en' ? 
                        'Oli is known for his unwavering commitment to communist ideologies and national development. He played a pivotal role in the unification of communist parties in Nepal and was instrumental in the peace process and constitutional development following the 2006 revolution.' : 
                        'ओली कम्युनिस्ट विचारधारा र राष्ट्रिय विकासप्रति उनको अडिग प्रतिबद्धताका लागि परिचित छन्। उनले नेपालमा कम्युनिस्ट पार्टीहरूको एकीकरणमा महत्वपूर्ण भूमिका खेलेका थिए र २००६ को क्रान्तिपछि शान्ति प्रक्रिया र संवैधानिक विकासमा निर्णायक भूमिका निर्वाह गरे।') . '</p>';
                }
                ?>
            </div>

            <div class="quick-facts" data-aos="fade-left">
                <h3 data-en="Quick Facts" data-np="मुख्य तथ्यहरू">
                    <?php echo $currentLang === 'en' ? 'Quick Facts' : 'मुख्य तथ्यहरू'; ?>
                </h3>

                <div class="fact-item">
                    <div class="fact-icon category-social">
                        <i class="fas fa-birthday-cake"></i>
                    </div>
                    <div class="fact-content">
                        <h4 data-en="Date of Birth" data-np="जन्म मिति">
                            <?php echo $currentLang === 'en' ? 'Date of Birth' : 'जन्म मिति'; ?>
                        </h4>
                        <p>
                            <?php echo $currentLang === 'en' ? 
                                get_setting('about_birth_date_en', 'February 22, 1952') : 
                                get_setting('about_birth_date_np', '२००८ फागुन १०'); ?>
                        </p>
                    </div>
                </div>

                <div class="fact-item">
                    <div class="fact-icon category-development">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="fact-content">
                        <h4 data-en="Education" data-np="शिक्षा">
                            <?php echo $currentLang === 'en' ? 'Education' : 'शिक्षा'; ?>
                        </h4>
                        <p>
                            <?php echo $currentLang === 'en' ? 
                                get_setting('about_education_en', 'Tribhuvan University') : 
                                get_setting('about_education_np', 'त्रिभुवन विश्वविद्यालय'); ?>
                        </p>
                    </div>
                </div>

                <div class="fact-item">
                    <div class="fact-icon category-international">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="fact-content">
                        <h4 data-en="Constituency" data-np="निर्वाचन क्षेत्र">
                            <?php echo $currentLang === 'en' ? 'Constituency' : 'निर्वाचन क्षेत्र'; ?>
                        </h4>
                        <p>
                            <?php echo $currentLang === 'en' ? 
                                get_setting('about_constituency_en', 'Jhapa-5') : 
                                get_setting('about_constituency_np', 'झापा-५'); ?>
                        </p>
                    </div>
                </div>

                <div class="fact-item">
                    <div class="fact-icon category-political">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="fact-content">
                        <h4 data-en="Political Career" data-np="राजनीतिक करियर">
                            <?php echo $currentLang === 'en' ? 'Political Career' : 'राजनीतिक करियर'; ?>
                        </h4>
                        <p>
                            <?php echo $currentLang === 'en' ? 
                                get_setting('about_career_start_en', 'Since 1970') : 
                                get_setting('about_career_start_np', 'सन् १९७० देखि'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- ==================== GALLERY SECTION ==================== -->
    <section id="gallery" class="section" style="background: #f8fafc;">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title" data-en="Gallery" data-np="ग्यालरी">Gallery</h2>
                <p class="section-subtitle" data-en="Moments from political journey and public life"
                    data-np="राजनीतिक यात्रा र सार्वजनिक जीवनका क्षणहरू">
                    Moments from political journey and public life
                </p>
            </div>

            <div class="gallery-filter" data-aos="fade-up" data-aos-delay="100">
                <button class="filter-btn active" data-filter="all" data-en="All" data-np="सबै">All</button>
                <button class="filter-btn" data-filter="speeches" data-en="Speeches" data-np="भाषणहरू">Speeches</button>
                <button class="filter-btn" data-filter="meetings" data-en="Meetings" data-np="बैठकहरू">Meetings</button>
                <button class="filter-btn" data-filter="public" data-en="Public Events"
                    data-np="सार्वजनिक कार्यक्रम">Public Events</button>
            </div>

            <div class="gallery-carousel-wrapper" data-aos="fade-up" data-aos-delay="200">
                <button class="carousel-nav-btn prev" id="galleryPrev" style="position: absolute; top: 50%; left: 0; transform: translateY(-50%); width: 40px; height: 40px; border-radius: 50%; background: white; border: 2px solid #2c5282; color: #2c5282; font-size: 16px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="gallery-carousel" id="galleryGrid">
                    <div class="gallery-loading" style="text-align: center; padding: 50px; min-width: 100%;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary);"></i>
                        <p>Loading gallery...</p>
                    </div>
                </div>
                <button class="carousel-nav-btn next" id="galleryNext" style="position: absolute; top: 50%; right: 0; transform: translateY(-50%); width: 40px; height: 40px; border-radius: 50%; background: white; border: 2px solid #2c5282; color: #2c5282; font-size: 16px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="carousel-indicators" id="galleryIndicators"></div>
        </div>
    </section>

    <!-- ==================== TIMELINE SECTION ==================== -->
    <section id="timeline" class="section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title" data-en="Career Timeline" data-np="कार्यकाल समयरेखा">Career Timeline</h2>
                <p class="section-subtitle"
                    data-en="Four decades of political journey and achievements - Scroll horizontally to explore"
                    data-np="चार दशकको राजनीतिक यात्रा र उपलब्धिहरू - अन्वेषण गर्न तेर्सो स्क्रोल गर्नुहोस्">
                    Four decades of political journey and achievements - Scroll horizontally to explore
                </p>
            </div>

            <div class="timeline-carousel-wrapper" data-aos="fade-up" data-aos-delay="200" style="position: relative; width: 100%; overflow: hidden; padding: 0 40px;">
                <button class="carousel-nav-btn prev" id="timelinePrev" style="position: absolute; top: 50%; left: 0; transform: translateY(-50%); width: 40px; height: 40px; border-radius: 50%; background: white; border: 2px solid #2c5282; color: #2c5282; font-size: 16px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <div class="horizontal-timeline-container" id="horizontalTimeline" style="overflow-x: auto; padding: 20px 0 40px; scrollbar-width: thin; scrollbar-color: #2c5282 #e2e8f0;">
                    <div class="timeline-track" id="timelineTrack" style="display: flex; gap: 20px; padding: 0 15px; min-width: min-content; position: relative;">
                        <!-- Timeline items will be loaded here -->
                    </div>
                </div>
                
                <button class="carousel-nav-btn next" id="timelineNext" style="position: absolute; top: 50%; right: 0; transform: translateY(-50%); width: 40px; height: 40px; border-radius: 50%; background: white; border: 2px solid #2c5282; color: #2c5282; font-size: 16px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="carousel-indicators" id="timelineIndicators" style="display: flex; justify-content: center; gap: 8px; margin-top: 5px; padding: 5px 0;"></div>

            <div class="timeline-progress" data-aos="fade-up" data-aos-delay="300" style="margin-top: 0%;padding: 0px 0;">
                <!-- <div class="progress-track">
                    <div class="progress-bar" id="timelineProgress"></div>
                </div>
                <div class="progress-label">
                    <span id="currentYear" data-en="1970s" data-np="१९७० को दशक">1970s</span>
                    <span id="totalYears" data-en="of 5+ Decades" data-np="५+ दशकमध्ये">of 5+ Decades</span>
                </div> -->
            </div>
        </div>
    </section>

    <!-- ==================== UPDATED ACTIVITIES SECTION ==================== -->
    <section id="activities" class="section">
        <div class="container activities-container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title" data-en="Political Activities" data-np="राजनीतिक गतिविधिहरू">Political Activities</h2>
                <p class="section-subtitle" data-en="Schedule of events, meetings, and public engagements"
                    data-np="कार्यक्रमहरू, बैठकहरू, र सार्वजनिक संलग्नताको समयतालिका">
                    Schedule of events, meetings, and public engagements
                </p>
            </div>

            <!-- Activity Filters -->
            <div class="activities-filter-container" data-aos="fade-up" data-aos-delay="100">
                <div class="filter-tabs-compact">
                    <button class="filter-tab-compact active" data-filter="all">
                        <i class="fas fa-calendar-alt"></i>
                        <span data-en="All Activities" data-np="सबै गतिविधिहरू">All Activities</span>
                    </button>
                    <button class="filter-tab-compact" data-filter="upcoming">
                        <i class="fas fa-clock"></i>
                        <span data-en="Upcoming" data-np="आगामी">Upcoming</span>
                    </button>
                    <button class="filter-tab-compact" data-filter="ongoing">
                        <i class="fas fa-spinner"></i>
                        <span data-en="Ongoing" data-np="चलिरहेको">Ongoing</span>
                    </button>
                    <button class="filter-tab-compact" data-filter="completed">
                        <i class="fas fa-check-circle"></i>
                        <span data-en="Completed" data-np="सम्पन्न">Completed</span>
                    </button>
                </div>
                
                <!-- <div class="filter-dropdowns-compact">
                    <select class="category-filter-compact" id="categoryFilter">
                        <option value="all" data-en="All Categories" data-np="सबै श्रेणीहरू">All Categories</option>
                        <option value="public_event" data-en="Public Events" data-np="सार्वजनिक कार्यक्रम">Public Events</option>
                        <option value="meeting" data-en="Meetings" data-np="बैठकहरू">Meetings</option>
                        <option value="conference" data-en="Conferences" data-np="सम्मेलनहरू">Conferences</option>
                        <option value="inauguration" data-en="Inaugurations" data-np="उद्घाटनहरू">Inaugurations</option>
                        <option value="health_camp" data-en="Health Camps" data-np="स्वास्थ्य शिविरहरू">Health Camps</option>
                        <option value="party_meeting" data-en="Party Meetings" data-np="पार्टी बैठकहरू">Party Meetings</option>
                        <option value="development" data-en="Development" data-np="विकास">Development</option>
                        <option value="media" data-en="Media" data-np="मिडिया">Media</option>
                    </select>
                    
                    <select class="date-filter-compact" id="dateFilter">
                        <option value="all" data-en="All Dates" data-np="सबै मितिहरू">All Dates</option>
                        <option value="today" data-en="Today" data-np="आज">Today</option>
                        <option value="week" data-en="This Week" data-np="यो हप्ता">This Week</option>
                        <option value="month" data-en="This Month" data-np="यो महिना">This Month</option>
                        <option value="future" data-en="Future" data-np="भविष्य">Future</option>
                    </select>
                </div> -->
            </div>

            

            <!-- Activities Carousel with Navigation -->
            <div class="activities-carousel-container" data-aos="fade-up" data-aos-delay="200">
                <button class="carousel-nav-btn prev" id="activitiesPrev" style="position: absolute; top: 50%; left: 0; transform: translateY(-50%); width: 40px; height: 40px; border-radius: 50%; background: white; border: 2px solid #2c5282; color: #2c5282; font-size: 16px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <div class="activities-carousel" id="activitiesCarousel">
                    <!-- Activities will be loaded here -->
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p data-en="Loading activities..." data-np="गतिविधिहरू लोड गर्दै...">Loading activities...</p>
                    </div>
                </div>
                
                <button class="carousel-nav-btn next" id="activitiesNext" style="position: absolute; top: 50%; right: 0; transform: translateY(-50%); width: 40px; height: 40px; border-radius: 50%; background: white; border: 2px solid #2c5282; color: #2c5282; font-size: 16px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            
            <!-- Carousel Indicators -->
            <div class="activities-indicators" id="activitiesIndicators" style="display: flex; justify-content: center; gap: 8px; margin-top: 0%; padding: 5px 0;"></div>
                <!-- Small Statistics Cards -->
            <div class="activities-stats-container" data-aos="fade-up" data-aos-delay="150">
                <div class="stat-card-small">
                    <div class="stat-icon-small">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content-small">
                        <h3 id="totalActivities">0</h3>
                        <p data-en="Total Activities" data-np="जम्मा गतिविधिहरू">Total Activities</p>
                    </div>
                </div>
                <div class="stat-card-small">
                    <div class="stat-icon-small">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content-small">
                        <h3 id="upcomingActivities">0</h3>
                        <p data-en="Upcoming" data-np="आगामी">Upcoming</p>
                    </div>
                </div>
                <div class="stat-card-small">
                    <div class="stat-icon-small">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div class="stat-content-small">
                        <h3 id="ongoingActivities">0</h3>
                        <p data-en="Ongoing" data-np="चलिरहेको">Ongoing</p>
                    </div>
                </div>
                <div class="stat-card-small">
                    <div class="stat-icon-small">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content-small">
                        <h3 id="completedActivities">0</h3>
                        <p data-en="Completed" data-np="सम्पन्न">Completed</p>
                    </div>
                </div>
            </div>
            <!-- View All Activities Button -->
            <!-- <div class="view-all-activities" data-aos="fade-up" data-aos-delay="250">
                <button class="view-all-btn" id="viewAllActivities">
                    <i class="fas fa-list"></i>
                    <span data-en="View All Activities" data-np="सबै गतिविधिहरू हेर्नुहोस्">View All Activities</span>
                </button>
            </div> -->
        </div>
    </section>

    <!-- ==================== ACHIEVEMENTS SECTION ==================== -->
    <section id="achievements" class="section" style="background: #f8fafc;">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title" data-en="Major Achievements" data-np="प्रमुख उपलब्धिहरू">Major Achievements</h2>
                <p class="section-subtitle" data-en="Significant contributions to Nepal's development"
                    data-np="नेपालको विकासमा महत्वपूर्ण योगदान">
                    Significant contributions to Nepal's development
                </p>
            </div>

            <div class="achievements-carousel-wrapper" data-aos="fade-up" data-aos-delay="200" style="position: relative; width: 100%; overflow: hidden; padding: 0 40px;">
                <button class="carousel-nav-btn prev" id="achievementsPrev" style="position: absolute; top: 50%; left: 0; transform: translateY(-50%); width: 40px; height: 40px; border-radius: 50%; background: white; border: 2px solid #2c5282; color: #2c5282; font-size: 16px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <div class="achievements-grid achievements-carousel" id="achievementsGrid" style="display: flex; flex-wrap: nowrap; gap: 30px; transition: transform 0.5s ease; padding: 20px 0;">
                    <!-- Achievement cards will be loaded here -->
                </div>
                
                <button class="carousel-nav-btn next" id="achievementsNext" style="position: absolute; top: 50%; right: 0; transform: translateY(-50%); width: 40px; height: 40px; border-radius: 50%; background: white; border: 2px solid #2c5282; color: #2c5282; font-size: 16px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="carousel-indicators" id="achievementsIndicators" style="display: flex; justify-content: center; gap: 12px; margin-top: 30px; padding: 10px 0;"></div>
        </div>
    </section>

    <!-- ==================== VIDEO SECTION ==================== -->
    <section id="videos" class="section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title" data-en="Video Showcase" data-np="भिडियो प्रदर्शनी">Video Showcase</h2>
                <p class="section-subtitle" data-en="Important speeches and interviews"
                    data-np="महत्वपूर्ण भाषणहरू र अन्तर्वार्ताहरू">
                    Important speeches and interviews
                </p>
            </div>

            <div class="video-carousel-wrapper" data-aos="fade-up" data-aos-delay="200">
                <button class="carousel-nav-btn prev" id="videosPrev" style="position: absolute; top: 50%; left: 0; transform: translateY(-50%); width: 40px; height: 40px; border-radius: 50%; background: white; border: 2px solid #2c5282; color: #2c5282; font-size: 16px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="video-grid video-carousel">
                    <!-- Video cards will be loaded here -->
                </div>
                <button class="carousel-nav-btn next" id="videosNext" style="position: absolute; top: 50%; right: 0; transform: translateY(-50%); width: 40px; height: 40px; border-radius: 50%; background: white; border: 2px solid #2c5282; color: #2c5282; font-size: 16px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="carousel-indicators" id="videosIndicators" style="display: flex; justify-content: center; gap: 12px; margin-top: 30px; padding: 10px 0;"></div>

           <!-- Simple More Videos Link Section -->
<section id="more-videos" class="section" style="padding: 20px 0;">
    <div class="container" style="text-align: center;">
        <div style="margin: 20px 0;" data-aos="fade-up">
            <a href="<?php echo htmlspecialchars(get_setting('more_videos_link', 'https://www.youtube.com/results?search_query=kp+sharma+oli+speeches')); ?>" 
               target="_blank"
               class="more-videos-link" 
               data-en="<?php echo htmlspecialchars(get_setting('more_videos_button_text_en', 'More Video Links')); ?>" 
               data-np="<?php echo htmlspecialchars(get_setting('more_videos_button_text_np', 'थप भिडियो लिङ्कहरू')); ?>">
                <?php echo $currentLang === 'en' ? 
                    get_setting('more_videos_button_text_en', 'More Video Links') : 
                    get_setting('more_videos_button_text_np', 'थप भिडियो लिङ्कहरू'); ?>
            </a>
        </div>
    </div>
</section>
        </div>
    </section>

    <!-- ==================== CONTACT SECTION ==================== -->
    <section id="contact" class="section contact-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title" data-en="Contact" data-np="सम्पर्क">Contact</h2>
                <p class="section-subtitle" data-en="Get in touch for meetings, inquiries, or support"
                    data-np="बैठकहरू, जानकारी, वा सहयोगको लागि सम्पर्क गर्नुहोस्">
                    Get in touch for meetings, inquiries, or support
                </p>
            </div>

            <div class="contact-container" data-aos="fade-up" data-aos-delay="200">
                <!-- Message Form -->
                <div class="contact-column" style="padding-right:20px;">
                    <div class="contact-form-card">
                        <h3 data-en="Send a Message" data-np="सन्देश पठाउनुहोस्">Send a Message</h3>
                        <form id="contactForm">
                            <div class="form-group">
                                <label class="form-label" data-en="Full Name" data-np="पुरा नाम">Full Name</label>
                                <input type="text" class="form-input" placeholder data-en="Enter your name"
                                    data-np="आफ्नो नाम लेख्नुहोस्" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" data-en="Email Address" data-np="ईमेल ठेगाना">Email
                                    Address</label>
                                <input type="email" class="form-input" placeholder data-en="Enter your email"
                                    data-np="आफ्नो ईमेल लेख्नुहोस्" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" data-en="Subject" data-np="विषय">Subject</label>
                                <input type="text" class="form-input" placeholder data-en="Enter subject"
                                    data-np="विषय लेख्नुहोस्" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" data-en="Message" data-np="सन्देश">Message</label>
                                <textarea class="form-textarea" rows="3" placeholder data-en="Type your message here..."
                                    data-np="आफ्नो सन्देश यहाँ लेख्नुहोस्..." required></textarea>
                            </div>

                            <button type="submit" class="submit-btn">
                                <i class="fas fa-paper-plane"></i>
                                <span data-en="Send Message" data-np="सन्देश पठाउनुहोस्">Send Message</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Appointment Form -->
                <div class="contact-column">
                    <div class="info-card" data-aos="fade-left"
                        style="background:linear-gradient(rgba(244, 56, 56, 0.78),rgba(229, 152, 10, 0.78)),
                        url('') no-repeat center;
                        background-size:cover;
                        border-radius:15px;
                        padding:8px 40px;
                        box-shadow:0 20px 60px rgba(0,0,0,0.15);
                        color:white;
                        height:100%; ">

                        <h3 style="text-align:center;font-weight:700;font-size:1.1rem;"
                            data-en="Request Appointment"
                            data-np="भेटघाट अनुरोध">
                            Request Appointment
                        </h3>

                        <p style="text-align:center;font-weight:600;margin-bottom:10px;"
                            data-en="Prime Minister Office"
                            data-np="प्रधानमन्त्री कार्यालय">
                            Prime Minister Office
                        </p>

                        <form id="appointmentForm" style="height:100%" width="50%; border-radius:8px;   padding:12px;box-shadow:0 10px 30px rgba(0,0,0,0.1);background:white;">
                            <input type="text" name="full_name" required
                                placeholder data-en="Full Name *" data-np="पूरा नाम *"
                                style="width:100%;padding:6px 12px;border-radius:8px;border:none;margin-bottom:12px;font-size:0.85rem;">

                            <input type="tel" name="mobile_number" required
                                placeholder data-en="Mobile Number *" data-np="मोबाइल नम्बर *"
                                style="width:100%;padding:6px 12px;border-radius:8px;border:none;margin-bottom:12px;font-size:0.85rem;">

                            <input type="text" name="address"
                                placeholder data-en="Address / District" data-np="ठेगाना / जिल्ला"
                                style="width:100%;padding:6px 12px;border-radius:8px;border:none;margin-bottom:12px;font-size:0.85rem;">

                            <select name="purpose" required
                                style="width:100%;padding:6px 12px;border-radius:8px;border:none;margin-bottom:12px;font-size:0.85rem;">
                                <option value="" data-en="Purpose of Meeting *" data-np="भेटघाटको उद्देश्य *">Purpose of Meeting *</option>
                                <option value="Personal Issue" data-en="Personal Issue" data-np="व्यक्तिगत समस्या">Personal Issue</option>
                                <option value="Community Problem" data-en="Community Problem" data-np="सामुदायिक समस्या">Community Problem</option>
                                <option value="Development Project" data-en="Development Project" data-np="विकास परियोजना">Development Project</option>
                                <option value="Party Work" data-en="Party Work" data-np="पार्टी कार्य">Party Work</option>
                                <option value="Other" data-en="Other" data-np="अन्य">Other</option>
                            </select>

                            <input type="date" name="preferred_date"
                                style="width:100%;padding:6px 12px;border-radius:8px;border:none;margin-bottom:12px;font-size:0.85rem;">

                            <textarea rows="3" name="message" required
                                placeholder data-en="Short Description / Message" data-np="छोटो विवरण / सन्देश"
                                style="width:100%;padding:6px 12px;border-radius:8px;border:none;margin-bottom:10px;font-size:0.85rem;resize:none;"></textarea>

                            <button type="submit"
                                data-en="Submit Appointment Request" data-np="भेटघाट अनुरोध पेश गर्नुहोस्"
                                style="width:100%;padding:8px;background:linear-gradient(135deg,#667eea,#764ba2);border:none;border-radius:40px;color:white;font-weight:600;font-size:0.9rem;cursor:pointer;">
                                Submit Appointment Request
                            </button>
                        </form>
                    </div>
                </div>

               

<!-- Donation Info Card -->
<div class="contact-column" style="height: 392px; min-height: auto; margin-bottom: 0px;">
    <div class="info-card donation-card" data-aos="fade-left" 
         style="background: linear-gradient(135deg, rgba(24, 35, 193, 0.8), rgba(23, 213, 203, 0.95)); border-radius: 15px; padding: 0px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08); height: 100%; text-align: center; position: relative; overflow: hidden; color: white; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; min-height: auto;">
        
       <!-- QR Code Section -->
<div class="qr-section" style="margin: 20px 0;">
    <div>
        <?php
        $qrCode = get_image_path('donation_qr_code', 'Screenshot_2025-12-17-12-19-45-501_com.f1soft.nepalmobilebanking.jpg');
        ?>
        <img src="<?php echo htmlspecialchars($qrCode); ?>" 
             alt="Donation QR Code" style="width: 120px; height: 120px; border-radius: 5px; object-fit: cover; margin-right: 20px;"
             onerror="this.src='https://via.placeholder.com/120x120?text=No+QR+Code'; this.onerror=null;">
        <p class="qr-label" style="color: white; margin-top: 8px; font-size: 0.9rem;">Scan QR Code to Donate</p>
    </div>
</div>
        
        <!-- Donation Form -->
        <form id="donationForm" method="POST" action="backend/api/donation-submit.php" enctype="multipart/form-data" style="width: 90%; margin: 0px 0;">
            <!-- Amount Input -->
            <div style="margin-bottom: 10px; display: flex; align-items: center; background: none; padding: 1px; border-radius: 8px; margin-top: 0px;">
                <i class="fas fa-money-bill-wave" style="color: white; margin-right: 10px; font-size: 1.2rem; min-width: 25px;"></i>
                <strong style="color: white; opacity: 0.9; font-size: 0.95rem; margin-right: 10px; min-width: 65px;"
                       data-en="Amount:" data-np="रकम:">Amount:</strong>
                <div class="amount-input" style="position: relative; display: flex; align-items: center; flex: 1;">
                    <input type="number" 
                name="amount" 
                class="total-amount-input" 
                id="totalAmount" 
                placeholder="Enter amount" 
                min="<?php echo get_setting('donation_min_amount', '100'); ?>" 
                value="<?php echo get_setting('donation_default_amount', '1000'); ?>" 
                required style="width: 100%; padding: 8px 10px 8px 25px; border: 2px solid rgba(255, 255, 255, 0.3); border-radius: 6px; background: rgba(255, 255, 255, 0.1); color: white; font-size: 0.9rem;">
                            <span class="currency" style="position: absolute; left: 8px; font-size: 0.9rem; font-weight: 600; color: white;">रु</span>
                </div>
            </div>
            
            <!-- Screenshot Upload -->
            <div style="margin-bottom: 0px; display: flex; align-items: center; background: none; padding: 1px; border-radius: 8px; margin-top: 10px;">
                <i class="fas fa-file-upload" style="color: white; margin-right: 10px; font-size: 1.2rem; min-width: 15px;"></i>
                <strong style="color: white; opacity: 0.9; font-size: 0.95rem; margin-right: 10px; min-width: 65px;"
                       data-en="Proof:" data-np="प्रमाण:">Proof:</strong>
                <div class="upload-area" id="uploadArea"
                     style="border: 1px dashed rgba(255, 255, 255, 0.3); border-radius: 6px; padding: 8px; text-align: center; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: rgba(255, 255, 255, 0.1); display: flex; align-items: center; justify-content: space-between; gap: 10px; flex: 1; height: 35px; position: relative; margin-left:10px;"
                     onmouseover="this.style.borderColor='white'; this.style.background='rgba(255, 255, 255, 0.2)'"
                     onmouseout="this.style.borderColor='rgba(255, 255, 255, 0.3)'; this.style.background='rgba(255, 255, 255, 0.1)'">
                    
                    <div id="uploadDefault" style="display: flex; align-items: center; gap: 8px; flex: 1; justify-content: center;">
                        <i class="fas fa-cloud-upload-alt" style="color: white; font-size: 1rem;"></i>
                        <span class="upload-text" style="color: white; font-size: 0.9rem; font-weight: 500;"
                              data-en="Upload Screenshot" data-np="स्क्रिनसट अपलोड">Upload Screenshot</span>
                    </div>
                    
                    <div id="uploadPreview" style="display: none; align-items: center; justify-content: space-between; gap: 10px; flex: 1;">
                        <span id="fileName" class="file-name" style="font-size: 0.9rem; color: white; word-break: break-all; max-width: 80%; text-align: left; flex: 1;"></span>
                        <button type="button" class="remove-file" id="removeFile"
                                style="background: none; border: none; color: rgba(255, 255, 255, 0.7); cursor: pointer; padding: 3px 8px; border-radius: 4px; font-size: 0.9rem; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);"
                                onmouseover="this.style.color='white'; this.style.background='rgba(255, 255, 255, 0.1)'"
                                onmouseout="this.style.color='rgba(255, 255, 255, 0.7)'; this.style.background='none'">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <input type="file" name="screenshot" id="screenshotUpload" accept="image/*" hidden>
            </div>
            
            
            <!-- Hidden inputs -->
            <input type="hidden" name="donor_name" id="donorName" value="">
            <input type="hidden" name="donor_email" id="donorEmail" value="">
            <input type="hidden" name="donor_phone" id="donorPhone" value="">
            
            
            <button type="submit" class="donate-btn" id="contactDonateBtn"
                    style="width: auto; padding: 5px 30px; background: linear-gradient(135deg, #ff5722, #ff7043); border: none; border-radius: 30px; color: white; font-weight: 600; font-size: 1.1rem; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; justify-content: center; gap: 10px; margin: 35px auto 10px;"
                    onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 20px rgba(255, 87, 34, 0.3)'"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                <i class="fas fa-hand-holding-heart"></i>
                <span data-en="Donate Now" data-np="अहिले दान गर्नुहोस्">Donate Now</span>
            </button>
        </form>
        
        <p class="security-note" style="font-size: 0.85rem; color: rgba(255, 255, 255, 0.8); display: flex; align-items: center; justify-content: center; gap: 6px; margin: 0 0 10px 0;">
            <i class="fas fa-shield-alt"></i>
            <span data-en="Secure & Verified Payment" data-np="सुरक्षित र प्रमाणित भुक्तानी">Secure & Verified Payment</span>
        </p>
    </div>
</div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="portfolio-footer">
    <div class="container">
        <div class="footer-content">
            <div>
                <div class="footer-logo">
                    <div class="party-flag-container">
                        <?php
                        $footerPartyLogo = get_image_path('footer_party_logo', get_image_path('hero_party_logo', 'https://play-lh.googleusercontent.com/0sLxEus620mEaNx72asMxDxWZBqeFfsa1fiuDe3wpV4NvTzJbDwCxLfeUhe2P7HjybA'));
                        ?>
                        <img src="<?php echo htmlspecialchars($footerPartyLogo); ?>" 
                             alt="Party Flag" class="party-flag-img">
                    </div>
                    <span data-en="<?php echo get_setting('footer_politician_name_en', 'KP Oli'); ?>" 
                          data-np="<?php echo get_setting('footer_politician_name_np', 'केपी ओली'); ?>">
                        <?php echo $currentLang === 'en' ? get_setting('footer_politician_name_en', 'KP Oli') : get_setting('footer_politician_name_np', 'केपी ओली'); ?>
                    </span>
                </div>
                <p data-en="<?php echo get_setting('footer_description_en', 'Official portfolio description...'); ?>" 
                   data-np="<?php echo get_setting('footer_description_np', 'नेपाली विवरण...'); ?>">
                    <?php echo $currentLang === 'en' ? get_setting('footer_description_en', 'Official portfolio description...') : get_setting('footer_description_np', 'नेपाली विवरण...'); ?>
                </p>
            </div>
            
            <!-- Secretary Information -->
        <div class="contact-details">
                <div class="single__box-wrapper">
                <div class="single__box-image">
                <?php
                    $secretaryPhoto = get_image_path(
                        'secretary_photo',
                        'https://giwmscdnone.gov.np/media/albums/hemraj%20aryal_dftnrTL0BH_n17kigl.jpg'
                    );
                ?>
                <img 
                    src="<?php echo htmlspecialchars($secretaryPhoto); ?>" 
                    alt="<?php echo htmlspecialchars(get_setting('secretary_name_en', 'Secretary')); ?>" 
                    class="secretary-photo"
                    onerror="this.src='https://via.placeholder.com/100x100?text=No+Photo'; this.onerror=null;"
                >
            </div>

                    <div class="single__box-info">
                        <h5 class="single__box-name">
                            <?php echo $currentLang === 'en' ? 
                                get_setting('secretary_name_en', 'Rajesh Sharma') : 
                                get_setting('secretary_name_np', 'राजेश शर्मा'); ?>
                        </h5>
                        <h3>
                            <?php echo $currentLang === 'en' ? 
                                get_setting('secretary_title_en', 'Personal Secretary') : 
                                get_setting('secretary_title_np', 'व्यक्तिगत सचिव'); ?>
                        </h3>
                        <a href="tel:<?php echo get_setting('secretary_phone', '+977-9864499368'); ?>">
                            <i class="fas fa-phone"></i><?php echo get_setting('secretary_phone', '+977-9864499368'); ?>
                        </a>
                        <a href="mailto:<?php echo get_setting('secretary_email', 'kpolisecretary@opmcm.gov.np'); ?>" class="team__mail">
                            <i class="far fa-envelope"></i><?php echo get_setting('secretary_email', 'kpolisecretary@opmcm.gov.np'); ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 data-en="Contact Information" data-np="सम्पर्क जानकारी">Contact Information</h3>
                <p><i class="fas fa-map-marker-alt"></i> 
                    <span data-en="<?php echo get_setting('footer_address_en', 'Kathmandu, Nepal'); ?>" 
                          data-np="<?php echo get_setting('footer_address_np', 'काठमाडौं, नेपाल'); ?>">
                        <?php echo $currentLang === 'en' ? get_setting('footer_address_en', 'Kathmandu, Nepal') : get_setting('footer_address_np', 'काठमाडौं, नेपाल'); ?>
                    </span>
                </p>
                <p><i class="fas fa-phone"></i> <?php echo get_setting('footer_phone', '+977-1-2345678'); ?></p>
                <p><i class="fas fa-envelope"></i> <?php echo get_setting('footer_email', 'contact@kpoli-portfolio.com'); ?></p>
                
                <div class="footer-social">
                    <?php if (get_setting('social_facebook')): ?>
                    <a href="<?php echo get_setting('social_facebook'); ?>" class="social-link" target="_blank">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (get_setting('social_twitter')): ?>
                    <a href="<?php echo get_setting('social_twitter'); ?>" class="social-link" target="_blank">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (get_setting('social_instagram')): ?>
                    <a href="<?php echo get_setting('social_instagram'); ?>" class="social-link" target="_blank">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (get_setting('social_youtube')): ?>
                    <a href="<?php echo get_setting('social_youtube'); ?>" class="social-link" target="_blank">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; padding: 1px 0; border-top: 2px solid rgba(255, 255, 255, 0.1);height: 18px; display: flex; align-items: center; justify-content: center; margin-top:1px;">
            <p data-en="<?php echo get_setting('footer_copyright_en', '© 2024 Portfolio. All rights reserved.'); ?>" 
               data-np="<?php echo get_setting('footer_copyright_np', '© २०२४ पोर्टफोलियो। सर्वाधिकार सुरक्षित।'); ?>">
                <?php echo $currentLang === 'en' ? 
                    get_setting('footer_copyright_en', '© 2024 Portfolio. All rights reserved.') : 
                    get_setting('footer_copyright_np', '© २०२४ पोर्टफोलियो। सर्वाधिकार सुरक्षित।'); ?>
            </p>
        </div>
    </div>
</footer>

    <!-- Gallery Modal -->
    <div class="modal" id="galleryModal">
        <div class="modal-content">
            <div class="modal-close">
                <i class="fas fa-times"></i>
            </div>
            <img id="modalImage" src="" alt="" style="width: 100%; height: auto;">
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

 <script>
    // Initialize AOS animations
    AOS.init({
        duration: 1000,
        once: true,
        offset: 100
    });

    // Language Management
    let currentLang = localStorage.getItem('portfolioLang') || 'en';

    function updateLanguage(lang) {
        currentLang = lang;
        localStorage.setItem('portfolioLang', lang);
        // Persist language selection to server-side via cookie
        document.cookie = 'portfolio_lang=' + lang + '; path=/; max-age=' + (60*60*24*365) + ';';

        document.documentElement.setAttribute('lang', lang);
        document.body.setAttribute('lang', lang);

        // Update all elements with data-en and data-np attributes
        document.querySelectorAll('[data-en]').forEach(element => {
            if (lang === 'en') {
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    element.placeholder = element.getAttribute('data-en') || '';
                } else {
                    element.textContent = element.getAttribute('data-en') || '';
                }
            } else {
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    element.placeholder = element.getAttribute('data-np') || '';
                } else {
                    element.textContent = element.getAttribute('data-np') || '';
                }
            }
        });

        // Update About Section content
        updateAboutContent(lang);
        
        // Reload dynamic content with new language
        loadAchievements();
        loadVideos();
        
        // Update activities
        if (window.activitiesManager) {
            window.activitiesManager.loadActivities();
        }

        document.querySelectorAll('.lang-btn').forEach(btn => {
            if (btn.getAttribute('data-lang') === lang) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateLanguage(currentLang);
        
        // Load dynamic content
        loadGallery();
        loadTimeline();
        loadAboutContent(currentLang);
        loadVideos();
        loadAchievements();
        // Initialize activities
        window.activitiesManager = new ActivitiesManager();
    });

    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const lang = this.getAttribute('data-lang');
            updateLanguage(lang);
        });
    });

    // Smooth scrolling for navigation
    document.querySelectorAll('.nav-link, .footer-link').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));

            if (this.classList.contains('nav-link')) {
                this.classList.add('active');
            }

            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);

            if (targetSection) {
                window.scrollTo({
                    top: targetSection.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Update active nav link on scroll
    window.addEventListener('scroll', function () {
        const sections = document.querySelectorAll('section');
        const navLinks = document.querySelectorAll('.nav-link');

        let current = '';

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;

            if (scrollY >= (sectionTop - 200)) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });

    // Gallery filter functionality
    const filterButtons = document.querySelectorAll('.filter-btn');
    let galleryItems = document.querySelectorAll('.gallery-item');

    function initGalleryFilters() {
        filterButtons.forEach(button => {
            button.addEventListener('click', function () {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                const filterValue = this.getAttribute('data-filter');

                galleryItems.forEach(item => {
                    if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                        item.style.display = 'block';
                        setTimeout(() => {
                            item.style.opacity = '1';
                            item.style.transform = 'scale(1)';
                        }, 100);
                    } else {
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.8)';
                        setTimeout(() => {
                            item.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });
    }

    // Gallery modal functionality
    const galleryModal = document.getElementById('galleryModal');
    const modalImage = document.getElementById('modalImage');

    function initGalleryModal() {
        const galleryClose = galleryModal.querySelector('.modal-close');

        galleryItems.forEach(item => {
            item.addEventListener('click', function () {
                const imgSrc = this.querySelector('.gallery-img').src;
                modalImage.src = imgSrc;
                galleryModal.classList.add('show');
                document.body.style.overflow = 'hidden';
            });
        });

        galleryClose.addEventListener('click', function () {
            galleryModal.classList.remove('show');
            document.body.style.overflow = 'auto';
        });

        galleryModal.addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.remove('show');
                document.body.style.overflow = 'auto';
            }
        });
    }

    // Local playback removed — site now uses YouTube links only

    // ========================================
    // TIMELINE FUNCTIONALITY (MERGED WITH AUTO-SCROLL)
    // ========================================

    const horizontalTimeline = document.getElementById('horizontalTimeline');
    const timelineTrack = document.getElementById('timelineTrack');
    const timelinePrev = document.getElementById('timelinePrev');
    const timelineNext = document.getElementById('timelineNext');
    const timelineProgress = document.getElementById('timelineProgress');
    const currentYear = document.getElementById('currentYear');
    const timelineItems = document.querySelectorAll('.timeline-item');

    // Auto-scroll variables
    let timelineAutoScrollTimer = null;
    const timelineAutoScrollInterval = 8000; // 8 seconds
    let isTimelineHovered = false;

    function updateNavButtons() {
        if (!horizontalTimeline || !timelinePrev || !timelineNext) return;

        const scrollLeft = horizontalTimeline.scrollLeft;
        const maxScroll = timelineTrack.scrollWidth - horizontalTimeline.clientWidth;

        const prevDisabled = scrollLeft <= 0;
        const nextDisabled = scrollLeft >= maxScroll - 1;
        
        timelinePrev.disabled = prevDisabled;
        timelinePrev.style.opacity = prevDisabled ? '0.5' : '1';
        timelinePrev.style.cursor = prevDisabled ? 'not-allowed' : 'pointer';
        
        timelineNext.disabled = nextDisabled;
        timelineNext.style.opacity = nextDisabled ? '0.5' : '1';
        timelineNext.style.cursor = nextDisabled ? 'not-allowed' : 'pointer';
    }

    function updateProgress() {
        if (!horizontalTimeline || !timelineProgress || !currentYear) return;

        const scrollLeft = horizontalTimeline.scrollLeft;
        const maxScroll = timelineTrack.scrollWidth - horizontalTimeline.clientWidth;
        const progress = maxScroll > 0 ? (scrollLeft / maxScroll) * 100 : 0;

        timelineProgress.style.width = `${progress}%`;

        const visibleItem = Array.from(timelineItems).find(item => {
            const rect = item.getBoundingClientRect();
            return rect.left >= 0 && rect.left <= window.innerWidth;
        });

        if (visibleItem) {
            const year = visibleItem.getAttribute('data-year');
            const currentLang = localStorage.getItem('portfolioLang') || 'en';
            const yearText = currentLang === 'en' ? year : getNepaliYear(year);
            currentYear.textContent = yearText;
            currentYear.setAttribute('data-en', year);
            currentYear.setAttribute('data-np', getNepaliYear(year));
        }
    }

    function getNepaliYear(year) {
        const yearMap = {
            '1970s': '१९७० को दशक',
            '1991': '१९९१',
            '1994': '१९९४',
            '2006': '२००६',
            '2014': '२०१४',
            '2015': '२०१५',
            '2018': '२०१८',
            '2021': '२०२१',
            '2023': '२०२३',
            'Present': 'वर्तमान'
        };
        return yearMap[year] || year;
    }

    function scrollTimeline(direction) {
        if (!horizontalTimeline) return;

        const scrollAmount = 400;
        const newScrollLeft = horizontalTimeline.scrollLeft + (scrollAmount * direction);
        const maxScroll = timelineTrack.scrollWidth - horizontalTimeline.clientWidth;
        
        // Clamp the scroll position
        const clampedScroll = Math.max(0, Math.min(newScrollLeft, maxScroll));
        
        horizontalTimeline.scrollTo({
            left: clampedScroll,
            behavior: 'smooth'
        });
        
        // Reset auto-scroll timer
        resetTimelineAutoScroll();
    }

    function initTimeline() {
        if (!horizontalTimeline) return;

        updateNavButtons();
        updateProgress();

        setTimeout(() => {
            horizontalTimeline.scrollLeft = 0;
            updateProgress();
        }, 100);
        
        // Initialize auto-scroll
        startTimelineAutoScroll();
    }

    // Auto-scroll functions
    function startTimelineAutoScroll() {
        if (timelineAutoScrollTimer) return;
        
        timelineAutoScrollTimer = setInterval(() => {
            if (!isTimelineHovered && horizontalTimeline) {
                const maxScroll = timelineTrack.scrollWidth - horizontalTimeline.clientWidth;
                const currentScroll = horizontalTimeline.scrollLeft;
                
                if (currentScroll >= maxScroll) {
                    // If at the end, scroll back to start
                    horizontalTimeline.scrollTo({
                        left: 0,
                        behavior: 'smooth'
                    });
                } else {
                    // Scroll to next position
                    scrollTimeline(1);
                }
                
                updateProgress();
            }
        }, timelineAutoScrollInterval);
    }

    function stopTimelineAutoScroll() {
        if (timelineAutoScrollTimer) {
            clearInterval(timelineAutoScrollTimer);
            timelineAutoScrollTimer = null;
        }
    }

    function resetTimelineAutoScroll() {
        stopTimelineAutoScroll();
        startTimelineAutoScroll();
    }

    // Event Listeners
    if (horizontalTimeline) {
        horizontalTimeline.addEventListener('scroll', () => {
            updateNavButtons();
            updateProgress();
        });
        
        // Pause auto-scroll on hover
        horizontalTimeline.addEventListener('mouseenter', () => {
            isTimelineHovered = true;
            stopTimelineAutoScroll();
        });
        
        horizontalTimeline.addEventListener('mouseleave', () => {
            isTimelineHovered = false;
            startTimelineAutoScroll();
        });
    }

    if (timelinePrev) {
        timelinePrev.addEventListener('click', () => scrollTimeline(-1));
    }

    if (timelineNext) {
        timelineNext.addEventListener('click', () => scrollTimeline(1));
    }

    if (timelineItems.length > 0) {
        timelineItems.forEach(item => {
            item.addEventListener('click', function () {
                const itemLeft = this.offsetLeft;
                const containerWidth = horizontalTimeline.clientWidth;
                const itemWidth = this.offsetWidth;
                const scrollTo = itemLeft - (containerWidth / 2) + (itemWidth / 2);
                const maxScroll = timelineTrack.scrollWidth - horizontalTimeline.clientWidth;
                const clampedScroll = Math.max(0, Math.min(scrollTo, maxScroll));

                horizontalTimeline.scrollTo({
                    left: clampedScroll,
                    behavior: 'smooth'
                });
                
                resetTimelineAutoScroll();
            });
        });
    }

    document.addEventListener('keydown', (e) => {
        const timelineSection = document.querySelector('#timeline');
        if (!timelineSection) return;

        const rect = timelineSection.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            if (e.key === 'ArrowLeft') {
                scrollTimeline(-1);
            } else if (e.key === 'ArrowRight') {
                scrollTimeline(1);
            }
        }
    });

    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initTimeline, 500);
    });

    // Stop auto-scroll when page is not visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopTimelineAutoScroll();
        } else {
            startTimelineAutoScroll();
        }
    });

    // Dynamic Gallery Loading
    async function loadGallery() {
        const galleryGrid = document.getElementById('galleryGrid');
        if (!galleryGrid) return;

        try {
            const response = await fetch('backend/api/get-gallery.php');
            const images = await response.json();

            if (images.error) {
                galleryGrid.innerHTML = '<div style="text-align: center; padding: 50px; grid-column: 1 / -1;"><p>Error loading gallery.</p></div>';
                return;
            }

            // Clear loading state
            galleryGrid.innerHTML = '';

            // Populate gallery
            images.forEach((image, index) => {
                const item = document.createElement('div');
                item.className = 'gallery-item';
                item.setAttribute('data-category', image.category);
                item.setAttribute('data-aos', 'zoom-in');
                item.setAttribute('data-aos-delay', (index * 100).toString());

                const title = currentLang === 'en' ? (image.title || 'Untitled') : (image.title_np || image.title || 'Untitled');
                const description = currentLang === 'en' ? (image.description || '') : (image.description_np || image.description || '');

                item.innerHTML = `
                    <img src="backend/uploads/gallery/${image.image_url}" alt="${title}" class="gallery-img">
                    <div class="gallery-overlay">
                        <h3>${title}</h3>
                        <p>${description}</p>
                    </div>
                `;

                galleryGrid.appendChild(item);
            });

            // Update gallery items reference
            galleryItems = document.querySelectorAll('.gallery-item');
            
            // Reinitialize gallery functionality
            initGalleryModal();
            initGalleryFilters();

        } catch (error) {
            console.error('Error loading gallery:', error);
            galleryGrid.innerHTML = '<div style="text-align: center; padding: 50px; grid-column: 1 / -1;"><p>Failed to load gallery.</p></div>';
        }
    }

    // Dynamic Timeline Loading
    async function loadTimeline() {
        const timelineTrack = document.getElementById('timelineTrack');
        if (!timelineTrack) return;

        try {
            const response = await fetch('backend/api/get-timeline.php');
            if (!response.ok) throw new Error('Network response was not ok');
            
            const entries = await response.json();

            if (entries.error) {
                console.error('Error loading timeline:', entries.error);
                return;
            }

            // Get existing static items to avoid duplicates
            const existingYears = Array.from(document.querySelectorAll('.timeline-item')).map(item => item.getAttribute('data-year'));

            // Filter and sort dynamic entries
            const newEntries = entries
                .filter(entry => !existingYears.includes(entry.year))
                .sort((a, b) => parseInt(b.year) - parseInt(a.year));

            // Append new dynamic items
            newEntries.forEach((entry, index) => {
                const item = document.createElement('div');
                item.className = 'timeline-item';
                item.setAttribute('data-year', entry.year);
                item.setAttribute('data-aos', 'fade-up');
                item.setAttribute('data-aos-delay', ((existingYears.length + index) * 100).toString());

                const title = currentLang === 'en' ? (entry.title_en || 'Untitled') : (entry.title_np || entry.title_en || 'Untitled');
                const content = currentLang === 'en' ? (entry.content_en || '') : (entry.content_np || entry.content_en || '');

                item.innerHTML = `
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <span class="timeline-year">${entry.year}</span>
                        <h3 data-en="${entry.title_en}" data-np="${entry.title_np}">${title}</h3>
                        <p data-en="${entry.content_en}" data-np="${entry.content_np}">${content}</p>
                    </div>
                `;

                timelineTrack.appendChild(item);
            });

            // Refresh AOS for new elements
            AOS.refresh();
            initTimeline();

        } catch (error) {
            console.error('Error loading timeline:', error);
        }
    }

    // Dynamic About Loading
    async function updateAboutContent(lang) {
        const aboutText = document.querySelector('.about-text');
        const quickFacts = document.querySelector('.quick-facts');
        
        if (!aboutText || !quickFacts) return;

        try {
            const response = await fetch('backend/api/get-about.php');
            if (!response.ok) throw new Error('Network response was not ok');

            const aboutData = await response.json();

            if (aboutData.error) {
                console.error('Error loading about:', aboutData.error);
                return;
            }

            // Update about content
            if (aboutData.content) {
                let content;
                if (lang === 'np' && aboutData.content_np) {
                    content = aboutData.content_np;
                } else {
                    content = aboutData.content_en || aboutData.content || '';
                }
                
                aboutText.innerHTML = content;
            }

            // Update quick facts
            const facts = aboutData.facts ? JSON.parse(aboutData.facts) : {};
            if (facts.birth_date || facts.education || facts.constituency || facts.political_career) {
                const factItems = quickFacts.querySelectorAll('.fact-item');
                
                // Update birth date
                if (facts.birth_date && factItems[0]) {
                    const factContent = factItems[0].querySelector('.fact-content p');
                    if (factContent) {
                        factContent.textContent = facts.birth_date;
                    }
                }
                
                // Update education
                if (facts.education && factItems[1]) {
                    const factContent = factItems[1].querySelector('.fact-content p');
                    if (factContent) {
                        factContent.textContent = facts.education;
                    }
                }
                
                // Update constituency
                if (facts.constituency && factItems[2]) {
                    const factContent = factItems[2].querySelector('.fact-content p');
                    if (factContent) {
                        factContent.textContent = facts.constituency;
                    }
                }
                
                // Update political career
                if (facts.political_career && factItems[3]) {
                    const factContent = factItems[3].querySelector('.fact-content p');
                    if (factContent) {
                        factContent.textContent = facts.political_career;
                    }
                }
            }

        } catch (error) {
            console.error('Error loading about:', error);
        }
    }

    // Load About content initially
    async function loadAboutContent(lang) {
        await updateAboutContent(lang);
    }

    // Load Videos
    async function loadVideos() {
        const videoGrid = document.querySelector('.video-carousel');
        if (!videoGrid) return;

        try {
            const lang = currentLang;
            const response = await fetch(`backend/api/get-videos.php?lang=${lang}`);
            const result = await response.json();

            if (result.success && result.videos) {
                // Clear existing content
                videoGrid.innerHTML = '';
                
                // Populate videos
                result.videos.forEach((video, index) => {
                    const videoCard = createVideoCard(video, index);
                    videoGrid.appendChild(videoCard);
                });
                
                // Reinitialize carousel
                if (window.appCarousels && window.appCarousels.videos) {
                    window.appCarousels.videos.refresh();
                }
            }
        } catch (error) {
            console.error('Error loading videos:', error);
        }
    }

    function createVideoCard(video, index) {
        const card = document.createElement('div');
        card.className = 'video-card';
        card.setAttribute('data-aos', 'fade-up');
        card.setAttribute('data-aos-delay', (index * 100).toString());
        
        // Use language-specific titles and descriptions
        const title = currentLang === 'np' ? (video.title_np || video.title_en || video.title) : (video.title_en || video.title);
        const description = currentLang === 'np' ? (video.description_np || video.description_en || video.description) : (video.description_en || video.description);
        
        // Handle thumbnail - check for different possible sources
        let thumbnail = '';
        if (video.thumbnail_url) {
            thumbnail = video.thumbnail_url; // API now returns usable path for local thumbnails
        } else if (video.youtube_url) {
            thumbnail = `https://img.youtube.com/vi/${video.youtube_url}/maxresdefault.jpg`;
        }

        // Category (ensure a sane default)
        const category = (video.category || 'speeches').toLowerCase();
        const categoryLabel = category.charAt(0).toUpperCase() + category.slice(1);
        
        card.innerHTML = `
            <div class="video-thumbnail">
                <img src="${thumbnail}" alt="${title}" onerror="this.src='https://via.placeholder.com/300x200?text=No+Thumbnail'">
                <div style="position:absolute; top:10px; left:10px; z-index:5;">
                    <span class="category-badge category-${category}" style="background: rgba(0,0,0,0.6); color:#fff; padding:6px 10px; border-radius:12px; font-size:0.8rem; text-transform:capitalize;">${categoryLabel}</span>
                </div>
                <div class="video-buttons">
                    ${video.youtube_url ? `
                        <a href="https://youtube.com/watch?v=${video.youtube_url}" target="_blank"
                            class="video-btn yt-btn">
                            <i class="fab fa-youtube"></i>
                            <span data-en="Watch on YouTube" data-np="युट्युबमा हेर्नुहोस्">${currentLang === 'en' ? 'Watch on YouTube' : 'युट्युबमा हेर्नुहोस्'}</span>
                        </a>
                    ` : ''}
                    <!-- Local play removed: only YouTube links are available -->
                </div>
            </div>
            <div class="video-info">
                <h3 data-en="${video.title_en || video.title}" data-np="${video.title_np || video.title}">${title}</h3>
                <p data-en="${video.description_en || video.description}" data-np="${video.description_np || video.description}">${description}</p>
            </div>
        `;
        
        return card;
    }

    // Load Achievements
    async function loadAchievements() {
        const achievementsGrid = document.getElementById('achievementsGrid');
        if (!achievementsGrid) return;

        try {
            const lang = currentLang;
            const response = await fetch(`backend/api/get-achievements.php?lang=${lang}`);
            const result = await response.json();

            if (result.success && result.achievements) {
                // Clear existing content
                achievementsGrid.innerHTML = '';
                
                // Populate achievements
                result.achievements.forEach((achievement, index) => {
                    const achievementCard = createAchievementCard(achievement, index);
                    achievementsGrid.appendChild(achievementCard);
                });
                
                // Reinitialize carousel
                if (window.appCarousels && window.appCarousels.achievements) {
                    window.appCarousels.achievements.refresh();
                }
            }
        } catch (error) {
            console.error('Error loading achievements:', error);
        }
    }

    function createAchievementCard(achievement, index) {
        const card = document.createElement('div');
        card.className = 'achievement-card';
        card.setAttribute('data-aos', 'flip-left');
        card.setAttribute('data-aos-delay', (index * 100).toString());
        
        // Use language-specific titles and descriptions
        const title = currentLang === 'np' ? (achievement.title_np || achievement.title_en || achievement.title) : (achievement.title_en || achievement.title);
        const description = currentLang === 'np' ? (achievement.description_np || achievement.description_en || achievement.description) : (achievement.description_en || achievement.description);
        
        const category = (achievement.category || 'political').toLowerCase();
        const iconClass = `achievement-icon category-${category}`;

        card.innerHTML = `
            <div class="${iconClass}">
                <i class="${achievement.icon || 'fas fa-trophy'}"></i>
            </div>
            <h3 data-en="${achievement.title_en || achievement.title}" data-np="${achievement.title_np || achievement.title}">${title}</h3>
            <p data-en="${achievement.description_en || achievement.description}" data-np="${achievement.description_np || achievement.description}">${description}</p>
        `;
        
        return card;
    }

    // ========================================
    //  UPDATED ACTIVITIES MANAGER
    // ========================================

    class ActivitiesManager {
        constructor() {
            this.activities = [];
            this.filteredActivities = [];
            this.currentFilter = 'all';
            this.currentCategory = 'all';
            this.currentDateFilter = 'all';
            this.loading = false;
            
            this.init();
        }
        
        async init() {
            console.log('Initializing ActivitiesManager...');
            await this.loadActivities();
            this.setupEventListeners();
            this.renderActivities();
            this.updateStatistics();
            
            // Initialize carousel after content is loaded
            setTimeout(() => {
                if (window.appCarousels && window.appCarousels.activities) {
                    window.appCarousels.activities.refresh();
                }
            }, 500);
        }
        
        async loadActivities() {
            try {
                this.showLoading(true);
                
                const lang = currentLang;
                console.log('Loading activities with language:', lang);
                const response = await fetch(`backend/api/get-activities.php?lang=${lang}&status=${this.currentFilter}&category=${this.currentCategory}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('API Response:', data);
                
                if (data.success) {
                    this.activities = data.activities;
                    console.log(`Loaded ${this.activities.length} activities`);
                    this.filteredActivities = [...this.activities];
                    this.applyDateFilter();
                    this.updateStatistics();
                    this.renderActivities();
                } else {
                    throw new Error(data.message || 'Failed to load activities');
                }
            } catch (error) {
                console.error('Error loading activities:', error);
                this.showError(error.message);
            } finally {
                this.showLoading(false);
            }
        }
        
        setupEventListeners() {
            // Filter tabs
            document.querySelectorAll('.filter-tab-compact').forEach(tab => {
                tab.addEventListener('click', () => {
                    document.querySelectorAll('.filter-tab-compact').forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    this.currentFilter = tab.dataset.filter;
                    console.log('Filter changed to:', this.currentFilter);
                    this.applyFilters();
                });
            });
            
            // View All button
            const viewAllBtn = document.getElementById('viewAllActivities');
            if (viewAllBtn) {
                viewAllBtn.addEventListener('click', () => {
                    this.showAllActivities();
                });
            }
        }
        
        applyFilters() {
            this.filteredActivities = this.activities.filter(activity => {
                // Status filter
                if (this.currentFilter !== 'all' && activity.status !== this.currentFilter) {
                    return false;
                }
                
                // Category filter
                if (this.currentCategory !== 'all' && activity.category !== this.currentCategory) {
                    return false;
                }
                
                return true;
            });
            
            this.applyDateFilter();
            this.renderActivities();
            this.updateStatistics();
        }
        
        applyDateFilter() {
            const today = new Date();
            const startOfWeek = new Date(today);
            startOfWeek.setDate(today.getDate() - today.getDay());
            const endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(startOfWeek.getDate() + 6);
            
            const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            
            this.filteredActivities = this.filteredActivities.filter(activity => {
                const activityDate = new Date(activity.date);
                
                switch (this.currentDateFilter) {
                    case 'today':
                        return activityDate.toDateString() === today.toDateString();
                    case 'week':
                        return activityDate >= startOfWeek && activityDate <= endOfWeek;
                    case 'month':
                        return activityDate >= startOfMonth && activityDate <= endOfMonth;
                    case 'future':
                        return activityDate >= today;
                    case 'all':
                    default:
                        return true;
                }
            });
        }
        
        renderActivities() {
            const container = document.getElementById('activitiesCarousel');
            if (!container) {
                console.error('activitiesCarousel container not found!');
                return;
            }
            
            if (this.filteredActivities.length === 0) {
                container.innerHTML = `
                    <div class="no-activities">
                        <i class="fas fa-calendar-times"></i>
                        <h3 data-en="No activities found" data-np="कुनै गतिविधिहरू फेला परेन">
                            ${currentLang === 'en' ? 'No activities found' : 'कुनै गतिविधिहरू फेला परेन'}
                        </h3>
                        <p data-en="Try changing your filters" data-np="फिल्टरहरू परिवर्तन गरेर प्रयास गर्नुहोस्">
                            ${currentLang === 'en' ? 'Try changing your filters' : 'फिल्टरहरू परिवर्तन गरेर प्रयास गर्नुहोस्'}
                        </p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = this.filteredActivities.map((activity, index) => {
                // Use language-specific content
                const title = currentLang === 'np' ? 
                    (activity.title_np || activity.title_en || activity.title) : 
                    (activity.title_en || activity.title);
                const description = currentLang === 'np' ? 
                    (activity.description_np || activity.description_en || activity.description) : 
                    (activity.description_en || activity.description);
                const statusLabel = currentLang === 'np' ? 
                    (activity.status_label_np || activity.status_label_en || activity.status) : 
                    (activity.status_label_en || activity.status);
                const location = currentLang === 'np' ? 
                    (activity.location_np || activity.location_en || activity.location) : 
                    (activity.location_en || activity.location);
                
                // Get category name
                const categoryNames = {
                    'public_event': currentLang === 'en' ? 'Public Event' : 'सार्वजनिक कार्यक्रम',
                    'meeting': currentLang === 'en' ? 'Meeting' : 'बैठक',
                    'conference': currentLang === 'en' ? 'Conference' : 'सम्मेलन',
                    'inauguration': currentLang === 'en' ? 'Inauguration' : 'उद्घाटन',
                    'health_camp': currentLang === 'en' ? 'Health Camp' : 'स्वास्थ्य शिविर',
                    'party_meeting': currentLang === 'en' ? 'Party Meeting' : 'पार्टी बैठक',
                    'development': currentLang === 'en' ? 'Development' : 'विकास',
                    'media': currentLang === 'en' ? 'Media' : 'मिडिया',
                    'general': currentLang === 'en' ? 'General' : 'सामान्य'
                };
                
                const categoryName = categoryNames[activity.category] || activity.category;
                
                return `
                    <div class="activity-card-compact" data-aos="fade-up" data-aos-delay="${index * 100}">
                        <div class="activity-image-compact">
                            <img src="${activity.image_url}" alt="${title}" 
                                 onerror="this.src='https://via.placeholder.com/300x150?text=Activity+Image'">
                            
                            <div class="activity-status-compact" style="background-color: ${activity.status_color}">
                                <i class="fas ${activity.status_icon}"></i>
                                ${statusLabel}
                            </div>
                            
                            <div class="activity-date-compact">
                                <i class="far fa-calendar"></i>
                                ${activity.formatted_date}
                            </div>
                            
                            <span class="priority-indicator-compact priority-${activity.priority <= 2 ? 'low' : activity.priority === 3 ? 'medium' : 'high'}"></span>
                        </div>
                        
                        <div class="activity-content-compact">
                            <div class="activity-category-compact">
                                <i class="fas ${activity.category_icon}"></i>
                                ${categoryName}
                            </div>
                            
                            <h3 class="activity-title-compact">${title}</h3>
                            
                            <p class="activity-description-compact">
                                ${description && description.length > 120 ? description.substring(0, 120) + '...' : (description || (currentLang === 'en' ? 'No description available' : 'कुनै विवरण उपलब्ध छैन'))}
                            </p>
                            
                            <div class="activity-meta-compact">
                                ${activity.start_time ? `
                                    <div class="meta-item-compact">
                                        <i class="fas fa-clock"></i>
                                        <span>${activity.start_time} ${activity.end_time ? `- ${activity.end_time}` : ''}</span>
                                    </div>
                                ` : ''}
                                
                                ${location ? `
                                    <div class="meta-item-compact">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>${location}</span>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            // Refresh carousel after content is loaded
            setTimeout(() => {
                if (window.appCarousels && window.appCarousels.activities) {
                    window.appCarousels.activities.refresh();
                    AOS.refresh();
                }
            }, 300);
        }
        
        showAllActivities() {
            // Show all activities in a modal or separate page
            alert(currentLang === 'en' ? 'This would open a full view of all activities.' : 'यसले सबै गतिविधिहरूको पूर्ण दृश्य खोल्नेछ।');
        }
        
        updateStatistics() {
            const stats = {
                total: this.activities.length,
                upcoming: this.activities.filter(a => a.status === 'upcoming').length,
                ongoing: this.activities.filter(a => a.status === 'ongoing').length,
                completed: this.activities.filter(a => a.status === 'completed').length
            };
            
            // Update UI with animation
            const animateCount = (element, target) => {
                if (!element) return;
                element.textContent = '0';
                let current = 0;
                const increment = target / 50;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        element.textContent = target;
                        clearInterval(timer);
                    } else {
                        element.textContent = Math.floor(current);
                    }
                }, 30);
            };
            
            const totalEl = document.getElementById('totalActivities');
            const upcomingEl = document.getElementById('upcomingActivities');
            const ongoingEl = document.getElementById('ongoingActivities');
            const completedEl = document.getElementById('completedActivities');
            
            if (totalEl) animateCount(totalEl, stats.total);
            if (upcomingEl) animateCount(upcomingEl, stats.upcoming);
            if (ongoingEl) animateCount(ongoingEl, stats.ongoing);
            if (completedEl) animateCount(completedEl, stats.completed);
            
            console.log('Statistics updated:', stats);
        }
        
        showLoading(show) {
            const container = document.getElementById('activitiesCarousel');
            if (!container) return;
            
            if (show) {
                container.innerHTML = `
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p data-en="Loading activities..." data-np="गतिविधिहरू लोड गर्दै...">
                            ${currentLang === 'en' ? 'Loading activities...' : 'गतिविधिहरू लोड गर्दै...'}
                        </p>
                    </div>
                `;
            }
        }
        
        showError(message) {
            const container = document.getElementById('activitiesCarousel');
            if (!container) return;
            
            container.innerHTML = `
                <div class="no-activities">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3 data-en="Failed to load activities" data-np="गतिविधिहरू लोड गर्न असफल">
                        ${currentLang === 'en' ? 'Failed to load activities' : 'गतिविधिहरू लोड गर्न असफल'}
                    </h3>
                    <p>${message}</p>
                    <button onclick="window.activitiesManager.loadActivities()" class="btn btn-primary" style="margin-top: 20px; padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 20px; cursor: pointer;">
                        <i class="fas fa-redo"></i> ${currentLang === 'en' ? 'Retry' : 'पुन: प्रयास गर्नुहोस्'}
                    </button>
                </div>
            `;
        }
    }

    // Initialize activities when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        console.log('DOM Content Loaded, initializing ActivitiesManager...');
        // Wait a bit for other scripts to load
        setTimeout(() => {
            if (!window.activitiesManager) {
                window.activitiesManager = new ActivitiesManager();
                console.log('ActivitiesManager initialized');
            }
        }, 500);
    });

    // ========================================
    //  CAROUSEL FUNCTIONALITY
    // ========================================

    class ImprovedCarousel {
        constructor(wrapperSelector, carouselSelector, options = {}) {
            this.wrapper = document.querySelector(wrapperSelector);
            if (!this.wrapper) {
                console.warn(`Carousel wrapper not found: ${wrapperSelector}`);
                return;
            }
            
            this.carousel = this.wrapper.querySelector(carouselSelector);
            if (!this.carousel) {
                console.warn(`Carousel element not found: ${carouselSelector}`);
                return;
            }
            
            this.carouselId = carouselSelector.replace('.', '') || 'carousel';
            this.prevBtn = this.wrapper.querySelector('.carousel-nav-btn.prev');
            this.nextBtn = this.wrapper.querySelector('.carousel-nav-btn.next');
            this.indicatorsContainer = options.indicators || this.wrapper.nextElementSibling?.classList.contains('carousel-indicators') ? this.wrapper.nextElementSibling : null;
            
            // Enhanced options with defaults
            this.config = {
                autoScroll: options.autoScroll || false,
                interval: options.interval || 5000,
                itemsPerView: options.itemsPerView || { mobile: 1, tablet: 2, desktop: 3 },
                showIndicators: options.showIndicators !== false,
                showNavButtons: options.showNavButtons !== false,
                loop: options.loop || false,
                transitionSpeed: options.transitionSpeed || 500,
                touchEnabled: options.touchEnabled !== false
            };
            
            this.currentIndex = 0;
            this.totalItems = 0;
            this.itemsPerView = this.config.itemsPerView.desktop;
            this.isDragging = false;
            this.startX = 0;
            this.currentTranslate = 0;
            this.prevTranslate = 0;
            this.animationID = null;
            this.timer = null;
            this.isTransitioning = false;
            
            this.init();
        }
        
        init() {
            // Setup carousel items
            this.setupCarouselItems();
            
            // Calculate initial state
            this.calculateView();
            
            // Setup navigation
            this.setupNavigation();
            
            // Setup indicators
            if (this.config.showIndicators && this.indicatorsContainer) {
                this.createIndicators();
            }
            
            // Setup touch/swipe
            if (this.config.touchEnabled) {
                this.setupTouchEvents();
            }
            
            // Setup auto-scroll
            if (this.config.autoScroll) {
                this.startAutoScroll();
                this.wrapper.addEventListener('mouseenter', () => this.stopAutoScroll());
                this.wrapper.addEventListener('mouseleave', () => this.startAutoScroll());
            }
            
            // Setup responsive behavior
            this.setupResponsive();
            
            // Initial update
            this.updateCarousel();
            
            // Add CSS classes for styling
            this.addCSSClasses();
        }
        
        setupCarouselItems() {
            this.items = Array.from(this.carousel.children);
            this.totalItems = this.items.length;
            
            // Add data attributes for styling
            this.items.forEach((item, index) => {
                item.setAttribute('data-carousel-item', index);
                item.style.flex = `0 0 ${100 / this.itemsPerView}%`;
            });
        }
        
        calculateView() {
            const width = window.innerWidth;
            
            if (width < 768) {
                this.itemsPerView = this.config.itemsPerView.mobile;
            } else if (width < 1024) {
                this.itemsPerView = this.config.itemsPerView.tablet;
            } else {
                this.itemsPerView = this.config.itemsPerView.desktop;
            }
            
            // Update item widths
            if (this.items) {
                this.items.forEach(item => {
                    item.style.flex = `0 0 ${100 / this.itemsPerView}%`;
                });
            }
            
            // Clamp current index
            const maxIndex = Math.max(0, this.totalItems - this.itemsPerView);
            if (this.currentIndex > maxIndex) {
                this.currentIndex = maxIndex;
            }
        }
        
        setupNavigation() {
            // Previous button
            if (this.prevBtn) {
                this.prevBtn.addEventListener('click', () => this.prev());
                // Add aria label
                this.prevBtn.setAttribute('aria-label', `Previous ${this.carouselId} items`);
            }
            
            // Next button
            if (this.nextBtn) {
                this.nextBtn.addEventListener('click', () => this.next());
                // Add aria label
                this.nextBtn.setAttribute('aria-label', `Next ${this.carouselId} items`);
            }
            
            // Keyboard navigation
            this.carousel.setAttribute('tabindex', '0');
            this.carousel.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') this.prev();
                if (e.key === 'ArrowRight') this.next();
                if (e.key === 'Home') this.goTo(0);
                if (e.key === 'End') this.goTo(this.totalItems - this.itemsPerView);
            });
        }
        
        setupTouchEvents() {
            this.carousel.style.cursor = 'grab';
            
            const startDrag = (e) => {
                if (this.isTransitioning) return;
                
                this.isDragging = true;
                this.startX = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
                this.carousel.style.cursor = 'grabbing';
                this.carousel.style.transition = 'none';
                
                if (this.timer) this.stopAutoScroll();
                
                // Prevent text selection during drag
                document.body.style.userSelect = 'none';
            };
            
            const drag = (e) => {
                if (!this.isDragging) return;
                
                e.preventDefault();
                const currentX = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
                const diff = currentX - this.startX;
                
                // Apply drag translation
                this.currentTranslate = this.prevTranslate + diff;
                this.carousel.style.transform = `translateX(${this.currentTranslate}px)`;
            };
            
            const endDrag = () => {
                if (!this.isDragging) return;
                
                this.isDragging = false;
                this.carousel.style.cursor = 'grab';
                this.carousel.style.transition = `transform ${this.config.transitionSpeed}ms ease`;
                document.body.style.userSelect = '';
                
                // Calculate if we should change slide based on drag distance
                const movedBy = this.currentTranslate - this.prevTranslate;
                const threshold = this.carousel.offsetWidth * 0.2;
                
                if (Math.abs(movedBy) > threshold) {
                    if (movedBy > 0) {
                        this.prev();
                    } else {
                        this.next();
                    }
                } else {
                    // Return to original position
                    this.carousel.style.transform = `translateX(${this.prevTranslate}px)`;
                }
                
                if (this.config.autoScroll) this.startAutoScroll();
            };
            
            // Mouse events
            this.carousel.addEventListener('mousedown', startDrag);
            this.carousel.addEventListener('mousemove', drag);
            this.carousel.addEventListener('mouseup', endDrag);
            this.carousel.addEventListener('mouseleave', endDrag);
            
            // Touch events
            this.carousel.addEventListener('touchstart', startDrag, { passive: false });
            this.carousel.addEventListener('touchmove', drag, { passive: false });
            this.carousel.addEventListener('touchend', endDrag);
        }
        
        createIndicators() {
            if (!this.indicatorsContainer) return;
            
            this.indicatorsContainer.innerHTML = '';
            const totalIndicators = Math.ceil(this.totalItems / this.itemsPerView);
            
            for (let i = 0; i < totalIndicators; i++) {
                const indicator = document.createElement('button');
                indicator.className = 'indicator';
                indicator.setAttribute('aria-label', `Go to slide ${i + 1}`);
                indicator.setAttribute('data-index', i * this.itemsPerView);
                
                if (i === 0) indicator.classList.add('active');
                
                indicator.addEventListener('click', () => {
                    this.goTo(i * this.itemsPerView);
                });
                
                this.indicatorsContainer.appendChild(indicator);
            }
        }
        
        updateIndicators() {
            if (!this.indicatorsContainer) return;
            
            const indicators = this.indicatorsContainer.querySelectorAll('.indicator');
            const activeIndicatorIndex = Math.floor(this.currentIndex / this.itemsPerView);
            
            indicators.forEach((indicator, index) => {
                const isActive = index === activeIndicatorIndex;
                indicator.classList.toggle('active', isActive);
                indicator.setAttribute('aria-current', isActive);
            });
        }
        
        updateCarousel() {
            if (this.isTransitioning) return;
            
            this.isTransitioning = true;
            
            // Calculate translate value
            const itemWidth = this.carousel.offsetWidth / this.itemsPerView;
            const gap = 30; // Assuming 30px gap
            const translateValue = -(this.currentIndex * (itemWidth + gap));
            
            this.prevTranslate = translateValue;
            this.carousel.style.transform = `translateX(${translateValue}px)`;
            
            // Update buttons state
            this.updateButtons();
            
            // Update indicators
            if (this.config.showIndicators) {
                this.updateIndicators();
            }
            
            // Update aria live region for screen readers
            this.updateAriaLive();
            
            // Reset transitioning flag after animation
            setTimeout(() => {
                this.isTransitioning = false;
            }, this.config.transitionSpeed);
        }
        
        updateButtons() {
            const maxIndex = Math.max(0, this.totalItems - this.itemsPerView);
            
            if (this.prevBtn) {
                const isDisabled = this.currentIndex === 0 && !this.config.loop;
                this.prevBtn.disabled = isDisabled;
                this.prevBtn.style.opacity = isDisabled ? '0.5' : '1';
                this.prevBtn.style.cursor = isDisabled ? 'not-allowed' : 'pointer';
            }
            
            if (this.nextBtn) {
                const isDisabled = this.currentIndex >= maxIndex && !this.config.loop;
                this.nextBtn.disabled = isDisabled;
                this.nextBtn.style.opacity = isDisabled ? '0.5' : '1';
                this.nextBtn.style.cursor = isDisabled ? 'not-allowed' : 'pointer';
            }
        }
        
        updateAriaLive() {
            // Create or update aria live region for screen readers
            let liveRegion = this.wrapper.querySelector('.carousel-live-region');
            if (!liveRegion) {
                liveRegion = document.createElement('div');
                liveRegion.className = 'carousel-live-region';
                liveRegion.setAttribute('aria-live', 'polite');
                liveRegion.setAttribute('aria-atomic', 'true');
                liveRegion.style.position = 'absolute';
                liveRegion.style.width = '1px';
                liveRegion.style.height = '1px';
                liveRegion.style.overflow = 'hidden';
                liveRegion.style.clip = 'rect(0,0,0,0)';
                this.wrapper.appendChild(liveRegion);
            }
            
            const currentSlide = Math.floor(this.currentIndex / this.itemsPerView) + 1;
            const totalSlides = Math.ceil(this.totalItems / this.itemsPerView);
            liveRegion.textContent = `Slide ${currentSlide} of ${totalSlides}`;
        }
        
        next() {
            const maxIndex = Math.max(0, this.totalItems - this.itemsPerView);
            
            if (this.currentIndex < maxIndex) {
                this.currentIndex++;
            } else if (this.config.loop) {
                this.currentIndex = 0;
            } else {
                return; // Don't go beyond if not looping
            }
            
            this.updateCarousel();
            if (this.config.autoScroll) this.resetAutoScroll();
        }
        
        prev() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
            } else if (this.config.loop) {
                const maxIndex = Math.max(0, this.totalItems - this.itemsPerView);
                this.currentIndex = maxIndex;
            } else {
                return; // Don't go beyond if not looping
            }
            
            this.updateCarousel();
            if (this.config.autoScroll) this.resetAutoScroll();
        }
        
        goTo(index) {
            const maxIndex = Math.max(0, this.totalItems - this.itemsPerView);
            this.currentIndex = Math.min(Math.max(0, index), maxIndex);
            this.updateCarousel();
            if (this.config.autoScroll) this.resetAutoScroll();
        }
        
        startAutoScroll() {
            if (!this.config.autoScroll || this.timer) return;
            
            this.timer = setInterval(() => {
                if (!this.isDragging && !this.isTransitioning) {
                    this.next();
                }
            }, this.config.interval);
        }
        
        stopAutoScroll() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        }
        
        resetAutoScroll() {
            this.stopAutoScroll();
            this.startAutoScroll();
        }
        
        setupResponsive() {
            let resizeTimeout;
            const handleResize = () => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    this.calculateView();
                    if (this.config.showIndicators) {
                        this.createIndicators();
                    }
                    this.updateCarousel();
                }, 250);
            };
            
            window.addEventListener('resize', handleResize);
            
            // Also handle orientation change
            window.addEventListener('orientationchange', () => {
                setTimeout(() => {
                    this.calculateView();
                    this.updateCarousel();
                }, 300);
            });
        }
        
        addCSSClasses() {
            // Add helper classes for styling
            this.wrapper.classList.add('carousel-initialized');
            this.carousel.classList.add('carousel-track');
            
            // Add transition style
            this.carousel.style.transition = `transform ${this.config.transitionSpeed}ms ease`;
        }
        
        refresh() {
            // Recalculate everything
            this.setupCarouselItems();
            this.calculateView();
            if (this.config.showIndicators) {
                this.createIndicators();
            }
            this.updateCarousel();
        }
        
        destroy() {
            // Clean up event listeners and timers
            this.stopAutoScroll();
            
            if (this.prevBtn) {
                const newPrevBtn = this.prevBtn.cloneNode(true);
                this.prevBtn.parentNode.replaceChild(newPrevBtn, this.prevBtn);
            }
            
            if (this.nextBtn) {
                const newNextBtn = this.nextBtn.cloneNode(true);
                this.nextBtn.parentNode.replaceChild(newNextBtn, this.nextBtn);
            }
            
            // Remove custom styles
            this.carousel.style.transform = '';
            this.carousel.style.transition = '';
            this.items?.forEach(item => {
                item.style.flex = '';
                item.removeAttribute('data-carousel-item');
            });
            
            // Remove initialized classes
            this.wrapper.classList.remove('carousel-initialized');
            this.carousel.classList.remove('carousel-track');
        }
    }

    // Enhanced initialization with backward compatibility
    function initEnhancedCarousels() {
        // Check which carousels exist on the page
        const carouselConfigs = [
            {
                wrapper: '.gallery-carousel-wrapper',
                carousel: '.gallery-carousel',
                id: 'galleryIndicators',
                autoScroll: true,
                interval: 4000,
                loop: true
            },
            {
                wrapper: '.achievements-carousel-wrapper',
                carousel: '.achievements-carousel',
                id: 'achievementsIndicators',
                autoScroll: true,
                interval: 6000,
                loop: false,
                itemsPerView: { mobile: 1, tablet: 2, desktop: 3 }
            },
            {
                wrapper: '.video-carousel-wrapper',
                carousel: '.video-carousel',
                id: 'videosIndicators',
                autoScroll: true,
                interval: 7000,
                loop: true,
                itemsPerView: { mobile: 1, tablet: 2, desktop: 2 }
            },
            {
                wrapper: '.activities-carousel-container',
                carousel: '.activities-carousel',
                id: 'activitiesIndicators',
                autoScroll: true,
                interval: 5000,
                loop: true,
                itemsPerView: { mobile: 1, tablet: 2, desktop: 3 }
            }
        ];
        
        const carousels = {};
        
        carouselConfigs.forEach(config => {
            const wrapper = document.querySelector(config.wrapper);
            const carousel = wrapper?.querySelector(config.carousel);
            const indicators = document.getElementById(config.id);
            
            if (wrapper && carousel) {
                carousels[config.id.replace('Indicators', '')] = new ImprovedCarousel(config.wrapper, config.carousel, {
                    indicators: indicators,
                    autoScroll: config.autoScroll,
                    interval: config.interval,
                    itemsPerView: config.itemsPerView,
                    loop: config.loop,
                    showNavButtons: true,
                    showIndicators: !!indicators,
                    touchEnabled: true
                });
                
                console.log(`Initialized ${config.id.replace('Indicators', '')} carousel`);
            }
        });
        
        return carousels;
    }

    // Initialize carousels when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Wait a bit for dynamic content
        setTimeout(() => {
            window.appCarousels = initEnhancedCarousels();
            
            // Expose carousel controls globally for debugging
            if (typeof window !== 'undefined') {
                window.refreshCarousels = function() {
                    Object.values(window.appCarousels || {}).forEach(carousel => {
                        if (carousel && typeof carousel.refresh === 'function') {
                            carousel.refresh();
                        }
                    });
                };
            }
        }, 100);
    });

    // Contact Form Handler
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('.submit-btn');
            const originalText = submitBtn.innerHTML;
            
            const sendingText = currentLang === 'en' ? 'Sending...' : 'पठाइदै...';
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + sendingText;
            submitBtn.disabled = true;
            
            const formData = {
                name: this.querySelector('input[type="text"]').value,
                email: this.querySelector('input[type="email"]').value,
                subject: this.querySelectorAll('input[type="text"]')[1].value,
                message: this.querySelector('textarea').value
            };
            
            try {
                const apiUrl = 'backend/api/contact-submit.php';
                
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const successMsg = currentLang === 'en' 
                        ? 'Thank you! Your message has been sent successfully.'
                        : 'धन्यवाद! तपाईंको सन्देश सफलतापूर्वक पठाइएको छ।';
                    alert(successMsg);
                    contactForm.reset();
                } else {
                    const errorMsg = currentLang === 'en'
                        ? 'Failed to send message: ' + (result.message || 'Please try again.')
                        : 'सन्देश पठाउन असफल: ' + (result.message || 'कृपया पुन: प्रयास गर्नुहोस्।');
                    alert(errorMsg);
                }
            } catch (error) {
                console.error('Error:', error);
                const errorMsg = currentLang === 'en'
                    ? 'Network error. Please check your connection and try again.'
                    : 'नेटवर्क त्रुटि। कृपया आफ्नो जडान जाँच गर्नुहोस् र पुन: प्रयास गर्नुहोस्।';
                alert(errorMsg);
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }

    // Appointment Form Handler
    const appointmentForm = document.getElementById('appointmentForm');
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            const submittingText = currentLang === 'en' ? 'Submitting...' : 'पेश गर्दै...';
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + submittingText;
            submitBtn.disabled = true;
            
            const formData = {
                full_name: this.querySelector('input[type="text"]').value,
                mobile_number: this.querySelector('input[type="tel"]').value,
                address: this.querySelectorAll('input[type="text"]')[1].value,
                purpose: this.querySelector('select').value,
                preferred_date: this.querySelector('input[type="date"]').value,
                message: this.querySelector('textarea').value
            };
            
            // Validation
            if (!formData.full_name || !formData.mobile_number || !formData.purpose) {
                const errorMsg = currentLang === 'en'
                    ? 'Please fill in all required fields.'
                    : 'कृपया सबै आवश्यक फिल्डहरू भर्नुहोस्।';
                alert(errorMsg);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                return;
            }
            
            try {
                const apiUrl = 'backend/api/appointment-submit.php';
                
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const successMsg = currentLang === 'en' 
                        ? 'Thank you! Your appointment request has been submitted successfully.'
                        : 'धन्यवाद! तपाईंको भेटघाट अनुरोध सफलतापूर्वक पेश गरिएको छ।';
                    alert(successMsg);
                    appointmentForm.reset();
                } else {
                    const errorMsg = currentLang === 'en'
                        ? 'Failed to submit appointment: ' + (result.message || 'Please try again.')
                        : 'भेटघाट पेश गर्न असफल: ' + (result.message || 'कृपया पुन: प्रयास गर्नुहोस्।');
                    alert(errorMsg);
                }
           } catch (error) {
                console.error('Error:', error);
                let errorMsg = currentLang === 'en'
                    ? 'Network error. Please check your connection and try again.'
                    : 'नेटवर्क त्रुटि। कृपया आफ्नो जडान जाँच गर्नुहोस् र पुन: प्रयास गर्नुहोस्।';
                
                // Try to parse API error if available
                try {
                    const result = await response.json();
                    if (result && result.message) {
                        errorMsg = result.message;
                    }
                } catch (parseError) {
                    // Ignore if not JSON
                }
                
                alert(errorMsg);
            }
        });
    }

    // Upload Proof Functionality
    const uploadArea = document.getElementById('uploadArea');
    const screenshotUpload = document.getElementById('screenshotUpload');
    const uploadDefault = document.getElementById('uploadDefault');
    const uploadPreview = document.getElementById('uploadPreview');
    const fileName = document.getElementById('fileName');
    const removeFile = document.getElementById('removeFile');

    if (uploadArea && screenshotUpload) {
        uploadArea.addEventListener('click', function() {
            screenshotUpload.click();
        });

        screenshotUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                const maxSize = 5 * 1024 * 1024;

                if (!validTypes.includes(file.type)) {
                    alert(currentLang === 'en' 
                        ? 'Please upload an image file (JPG, PNG, GIF, or WebP)' 
                        : 'कृपया चित्र फाइल अपलोड गर्नुहोस् (JPG, PNG, GIF, वा WebP)');
                    screenshotUpload.value = '';
                    return;
                }

                if (file.size > maxSize) {
                    alert(currentLang === 'en' 
                        ? 'File size must be less than 5MB' 
                        : 'फाइल आकार 5MB भन्दा कम हुनुपर्छ');
                    screenshotUpload.value = '';
                    return;
                }

                if (uploadDefault) uploadDefault.style.display = 'none';
                if (uploadPreview) uploadPreview.style.display = 'flex';
                if (fileName) fileName.textContent = file.name.length > 20 
                    ? file.name.substring(0, 20) + '...' 
                    : file.name;
            }
        });
    }

    if (removeFile) {
        removeFile.addEventListener('click', function(e) {
            e.stopPropagation();
            screenshotUpload.value = '';
            if (uploadDefault) uploadDefault.style.display = 'flex';
            if (uploadPreview) uploadPreview.style.display = 'none';
        });
    }

    // Donation Form Handler
    const donationForm = document.getElementById('donationForm');
    if (donationForm) {
        donationForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('#contactDonateBtn');
            const originalText = submitBtn.innerHTML;
            
            const amount = parseInt(document.getElementById('totalAmount').value) || 0;
            const screenshotFile = document.getElementById('screenshotUpload').files[0];
            
            if (amount < 100) {
                const msg = currentLang === 'en'
                    ? 'Minimum donation amount is NPR 100'
                    : 'न्यूनतम दान रकम रु १०० हो';
                alert(msg);
                return;
            }
            
            if (!screenshotFile) {
                const msg = currentLang === 'en'
                    ? 'Please upload transaction screenshot as proof'
                    : 'कृपया लेनदेन प्रमाणको रूपमा स्क्रिनसट अपलोड गर्नुहोस्';
                alert(msg);
                return;
            }
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' +
                (currentLang === 'en' ? 'Processing...' : 'प्रशोधन गर्दै...');
            submitBtn.disabled = true;
            
            const contactForm = document.getElementById('contactForm');
            let donorName = '';
            let donorEmail = '';
            
            if (contactForm) {
                const contactName = contactForm.querySelector('input[type="text"]');
                const contactEmail = contactForm.querySelector('input[type="email"]');
                
                if (contactName && contactName.value) {
                    donorName = contactName.value;
                }
                if (contactEmail && contactEmail.value) {
                    donorEmail = contactEmail.value;
                }
            }
            
            const formData = new FormData();
            formData.append('amount', amount);
            formData.append('screenshot', screenshotFile);
            formData.append('donor_name', donorName);
            formData.append('donor_email', donorEmail);
            formData.append('donor_phone', '');
            
            try {
                const apiUrl = 'backend/api/donation-submit.php';
                
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const successMsg = currentLang === 'en'
                        ? `Thank you for your donation of NPR ${amount}! Transaction ID: ${result.transaction_id}`
                        : `रु ${amount} को दानको लागि धन्यवाद! लेनदेन आईडी: ${result.transaction_id}`;
                    alert(successMsg);
                    
                    this.reset();
                    document.getElementById('totalAmount').value = '1000';
                    if (document.getElementById('uploadDefault')) 
                        document.getElementById('uploadDefault').style.display = 'flex';
                    if (document.getElementById('uploadPreview')) 
                        document.getElementById('uploadPreview').style.display = 'none';
                        
                } else {
                    const errorMsg = currentLang === 'en'
                        ? 'Failed to process donation: ' + result.message
                        : 'दान प्रशोधन गर्न असफल: ' + result.message;
                    alert(errorMsg);
                }
            } catch (error) {
                console.error('Error:', error);
                const errorMsg = currentLang === 'en'
                    ? 'Network error. Please check your connection and try again.'
                    : 'नेटवर्क त्रुटि। कृपया आफ्नो जडान जाँच गर्नुहोस् र पुन: प्रयास गर्नुहोस्।';
                alert(errorMsg);
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }

    // Input validation for amount
    if (document.getElementById('totalAmount')) {
        const totalAmountInput = document.getElementById('totalAmount');
        totalAmountInput.addEventListener('input', function() {
            let value = parseInt(this.value);
            if (value < 100) {
                this.style.borderColor = '#ff0000';
            } else {
                this.style.borderColor = '';
            }
        });
    }

    // Initialize everything
    document.addEventListener('DOMContentLoaded', () => {
        initTimeline();
    });

    // Update activities when language changes
    const originalUpdateLanguage = window.updateLanguage;
    window.updateLanguage = function(lang) {
        if (originalUpdateLanguage) originalUpdateLanguage(lang);
        if (window.activitiesManager) {
            console.log('Language changed, updating activities...');
            window.activitiesManager.loadActivities();
        }
    };
</script>
</body>
</html>