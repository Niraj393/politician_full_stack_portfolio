<?php
// Fetch dynamic counts for badges
require_once __DIR__ . '/../database/connection.php';
$unreadMessages = 0;
$pendingDonations = 0;
$appointmentsPending = 0;

try {
    $conn = getPDOConnection();
    
    // Unread messages
    $stmt = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
    $unreadMessages = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Pending donations
    $stmt = $conn->query("SELECT COUNT(*) as count FROM donations WHERE status = 'pending'");
    $pendingDonations = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Pending appointments
    $stmt = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'pending'");
    $appointmentsPending = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
} catch (PDOException $e) {
    error_log('Sidebar count error: ' . $e->getMessage());
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Modern Gradient Sidebar with Glass Effect -->
<aside class="sidebar">
    <!-- User Profile Section -->
    <div class="user-profile">
        <div class="avatar-container">
            <?php 
            $initial = strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1));
            $username = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');
            ?>
            <div class="avatar-circle" data-initial="<?php echo $initial; ?>">
                <span class="avatar-text"><?php echo $initial; ?></span>
                <div class="status-indicator online"></div>
            </div>
            <div class="user-info">
                <h3 class="user-name"><?php echo $username; ?></h3>
                <p class="user-role">Super Admin</p>
                <div class="user-stats">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $unreadMessages; ?></span>
                        <span class="stat-label">Unread</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $pendingDonations; ?></span>
                        <span class="stat-label">Pending</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <div class="nav-header">
            <span class="nav-title">Dashboard</span>
        </div>
        
        <ul class="nav-menu">
            <!-- Dashboard -->
            <li class="nav-item <?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>">
                <a href="dashboard.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <span class="nav-text">Dashboard</span>
                    <div class="nav-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </li>
            
            <!-- Messages -->
            <li class="nav-item <?php echo ($current_page === 'messages.php') ? 'active' : ''; ?>">
                <a href="messages.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <span class="nav-text">Messages</span>
                    <?php if ($unreadMessages > 0): ?>
                    <span class="nav-badge pulse"><?php echo $unreadMessages; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <!-- Donations -->
            <li class="nav-item <?php echo ($current_page === 'donations.php') ? 'active' : ''; ?>">
                <a href="donations.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-donate"></i>
                    </div>
                    <span class="nav-text">Donations</span>
                    <?php if ($pendingDonations > 0): ?>
                    <span class="nav-badge warning"><?php echo $pendingDonations; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <!-- Appointments -->
            <li class="nav-item <?php echo ($current_page === 'appointment.php') ? 'active' : ''; ?>">
                <a href="appointment.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <span class="nav-text">Appointments</span>
                    <?php if ($appointmentsPending > 0): ?>
                    <span class="nav-badge"><?php echo $appointmentsPending; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <!-- Content Management Section -->
            <div class="nav-section">
                <span class="section-label">Content Management</span>
            </div>
            
            <!-- Gallery -->
            <li class="nav-item <?php echo ($current_page === 'gallery.php') ? 'active' : ''; ?>">
                <a href="gallery.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-images"></i>
                    </div>
                    <span class="nav-text">Gallery</span>
                    <div class="nav-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </li>
            
            <!-- Timeline -->
            <li class="nav-item <?php echo ($current_page === 'timeline.php') ? 'active' : ''; ?>">
                <a href="timeline.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <span class="nav-text">Timeline</span>
                    <div class="nav-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </li>
            
            <!-- Activities -->
            <li class="nav-item <?php echo ($current_page === 'activities.php') ? 'active' : ''; ?>">
                <a href="activities.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <span class="nav-text">Activities</span>
                    <div class="nav-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </li>
            
            <!-- Videos -->
            <li class="nav-item <?php echo ($current_page === 'videos.php') ? 'active' : ''; ?>">
                <a href="videos.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <span class="nav-text">Videos</span>
                    <div class="nav-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </li>
            
            <!-- Achievements -->
            <li class="nav-item <?php echo ($current_page === 'achievements.php') ? 'active' : ''; ?>">
                <a href="achievements.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <span class="nav-text">Achievements</span>
                    <div class="nav-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </li>
            
            <!-- Settings Section -->
            <div class="nav-section">
                <span class="section-label">Configuration</span>
            </div>
            
            <!-- Settings -->
            <li class="nav-item <?php echo ($current_page === 'settings.php') ? 'active' : ''; ?>">
                <a href="settings.php" class="nav-link">
                    <div class="nav-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <span class="nav-text">Settings</span>
                    <div class="nav-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Footer Section -->
    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn">
            <div class="logout-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <span>Logout</span>
            <div class="logout-hint">
                <i class="fas fa-external-link-alt"></i>
            </div>
        </a>
        <div class="sidebar-info">
            <p class="version">v2.1.0</p>
            <p class="copyright">© <?php echo date('Y'); ?> KP Oli Portfolio</p>
        </div>
    </div>
    
    <!-- Mobile Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
</aside>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
:root {
    --sidebar-bg: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
    --sidebar-width: 280px;
    --sidebar-collapsed: 80px;
    --primary: #6366f1;
    --primary-light: #818cf8;
    --primary-dark: #4f46e5;
    --secondary: #8b5cf6;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #3b82f6;
    --dark: #1f2937;
    --light: #f9fafb;
    --gray: #6b7280;
    --light-gray: #e5e7eb;
    --sidebar-text: #cbd5e1;
    --sidebar-hover: rgba(255, 255, 255, 0.1);
    --sidebar-active: rgba(99, 102, 241, 0.2);
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    --radius-sm: 0.375rem;
    --radius: 0.5rem;
    --radius-md: 0.75rem;
    --radius-lg: 1rem;
    --radius-full: 9999px;
}

/* Modern Sidebar */
.sidebar {
    width: var(--sidebar-width);
    height: 100vh;
    background: var(--sidebar-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    position: fixed;
    left: 0;
    top: 0;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    border-right: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: var(--shadow-xl);
    transition: var(--transition);
    overflow-y: auto;
}

/* User Profile Section */
.user-profile {
    padding: 2rem 1.5rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 1rem;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.05) 0%, transparent 100%);
}

.avatar-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.avatar-circle {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
    transition: var(--transition);
}

.avatar-circle:hover {
    transform: scale(1.05) rotate(5deg);
    box-shadow: 0 12px 30px rgba(99, 102, 241, 0.4);
}

.avatar-text {
    font-size: 2rem;
    font-weight: 700;
    color: white;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.status-indicator {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 14px;
    height: 14px;
    border-radius: var(--radius-full);
    border: 2px solid var(--sidebar-bg);
}

.status-indicator.online {
    background: var(--success);
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
}

.user-info {
    text-align: center;
    width: 100%;
}

.user-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: white;
    margin-bottom: 0.25rem;
    line-height: 1.2;
}

.user-role {
    font-size: 0.875rem;
    color: var(--sidebar-text);
    margin-bottom: 1rem;
    background: rgba(255, 255, 255, 0.05);
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    display: inline-block;
}

.user-stats {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    margin-top: 1rem;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.stat-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: white;
    line-height: 1;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--sidebar-text);
    margin-top: 0.25rem;
    opacity: 0.8;
}

/* Navigation */
.sidebar-nav {
    flex: 1;
    overflow: visible;
    padding: 0 1rem;
}

.sidebar-nav::-webkit-scrollbar {
    width: 6px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-full);
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}

.nav-header {
    padding: 1rem 0.5rem 0.5rem;
    margin-bottom: 0.5rem;
}

.nav-title {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--sidebar-text);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.7;
}

.nav-section {
    padding: 1rem 0.5rem 0.5rem;
    margin-top: 0.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.section-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--sidebar-text);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.7;
}

.nav-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-item {
    margin-bottom: 0.25rem;
    position: relative;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    color: var(--sidebar-text);
    text-decoration: none;
    border-radius: var(--radius);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.nav-link::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 4px;
    background: var(--primary);
    transform: scaleY(0);
    transition: transform 0.3s ease;
    border-radius: 0 var(--radius) var(--radius) 0;
}

.nav-link:hover {
    background: var(--sidebar-hover);
    color: white;
    padding-left: 1.25rem;
}

.nav-link:hover::before {
    transform: scaleY(0.7);
}

.nav-item.active .nav-link {
    background: var(--sidebar-active);
    color: white;
}

.nav-item.active .nav-link::before {
    transform: scaleY(1);
}

.nav-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.05);
    border-radius: var(--radius);
    transition: var(--transition);
}

.nav-item.active .nav-icon {
    background: rgba(99, 102, 241, 0.2);
    color: var(--primary);
}

.nav-text {
    flex: 1;
    font-size: 0.9375rem;
    font-weight: 500;
    transition: var(--transition);
}

.nav-arrow {
    font-size: 0.75rem;
    opacity: 0.5;
    transition: var(--transition);
}

.nav-link:hover .nav-arrow {
    opacity: 1;
    transform: translateX(3px);
}

/* Badges */
.nav-badge {
    font-size: 0.6875rem;
    font-weight: 700;
    padding: 0.125rem 0.5rem;
    border-radius: var(--radius-full);
    min-width: 20px;
    text-align: center;
    background: var(--danger);
    color: white;
    animation: badgePulse 2s infinite;
}

.nav-badge.warning {
    background: var(--warning);
}

.nav-badge.pulse {
    animation: badgePulse 1.5s infinite;
}

@keyframes badgePulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
    }
    50% {
        box-shadow: 0 0 0 6px rgba(239, 68, 68, 0);
    }
}

/* Footer */
.sidebar-footer {
    padding: 1.5rem 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.2);
}

.logout-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    background: rgba(239, 68, 68, 0.1);
    color: #fca5a5;
    text-decoration: none;
    border-radius: var(--radius);
    transition: var(--transition);
    margin-bottom: 1rem;
}

.logout-btn:hover {
    background: rgba(239, 68, 68, 0.2);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(239, 68, 68, 0.2);
}

.logout-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(239, 68, 68, 0.2);
    border-radius: var(--radius);
    font-size: 1rem;
}

.logout-hint {
    font-size: 0.75rem;
    opacity: 0.5;
    transition: var(--transition);
}

.logout-btn:hover .logout-hint {
    opacity: 1;
    transform: translateX(3px);
}

.sidebar-info {
    text-align: center;
}

.version {
    font-size: 0.75rem;
    color: var(--sidebar-text);
    opacity: 0.7;
    margin-bottom: 0.25rem;
}

.copyright {
    font-size: 0.6875rem;
    color: var(--sidebar-text);
    opacity: 0.5;
}

/* Mobile Toggle Button */
.sidebar-toggle {
    display: none;
    position: fixed;
    top: 1rem;
    left: 1rem;
    z-index: 1001;
    width: 44px;
    height: 44px;
    background: var(--primary);
    border: none;
    border-radius: var(--radius);
    color: white;
    font-size: 1.25rem;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: var(--shadow-lg);
}

.sidebar-toggle:hover {
    background: var(--primary-dark);
    transform: scale(1.1);
}

/* Mobile Overlay */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    z-index: 999;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.sidebar-overlay.active {
    display: block;
    opacity: 1;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .sidebar {
        transform: translateX(-100%);
        box-shadow: var(--shadow-xl);
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .sidebar-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .sidebar-overlay.active {
        display: block;
    }
}

@media (max-width: 768px) {
    .sidebar {
        width: 280px;
    }
    
    .user-name {
        font-size: 1.125rem;
    }
    
    .user-role {
        font-size: 0.8125rem;
    }
}

@media (max-width: 480px) {
    .sidebar {
        width: 100%;
        max-width: 320px;
    }
    
    .avatar-circle {
        width: 70px;
        height: 70px;
    }
    
    .avatar-text {
        font-size: 1.75rem;
    }
    
    .nav-link {
        padding: 0.75rem 1rem;
    }
}

/* Collapsed State (Optional) */
.sidebar.collapsed {
    width: var(--sidebar-collapsed);
}

.sidebar.collapsed .nav-text,
.sidebar.collapsed .user-info,
.sidebar.collapsed .nav-arrow,
.sidebar.collapsed .logout-btn span,
.sidebar.collapsed .logout-hint,
.sidebar.collapsed .sidebar-info,
.sidebar.collapsed .nav-badge,
.sidebar.collapsed .nav-title,
.sidebar.collapsed .section-label {
    display: none;
}

.sidebar.collapsed .nav-link {
    justify-content: center;
    padding: 0.875rem;
}

.sidebar.collapsed .nav-link:hover .nav-text {
    display: block;
    position: absolute;
    left: calc(100% + 1rem);
    background: var(--sidebar-bg);
    padding: 0.5rem 1rem;
    border-radius: var(--radius);
    white-space: nowrap;
    z-index: 1001;
    box-shadow: var(--shadow-lg);
}

.sidebar.collapsed .logout-btn {
    justify-content: center;
    padding: 0.875rem;
}

.sidebar.collapsed .logout-btn:hover span {
    display: block;
    position: absolute;
    left: calc(100% + 1rem);
    background: var(--sidebar-bg);
    padding: 0.5rem 1rem;
    border-radius: var(--radius);
    white-space: nowrap;
    z-index: 1001;
    box-shadow: var(--shadow-lg);
}

/* Active Link Animation */
.nav-item.active .nav-icon {
    animation: iconBounce 0.5s ease;
}

@keyframes iconBounce {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2);
    }
}

/* Hover Effects */
.nav-icon i {
    transition: transform 0.3s ease;
}

.nav-link:hover .nav-icon i {
    transform: scale(1.1);
}

/* Smooth Transitions */
.nav-link,
.nav-icon,
.nav-text,
.nav-badge,
.logout-btn {
    transition: var(--transition);
}

/* Gradient Text Effect */
.user-name {
    background: linear-gradient(135deg, #fff 0%, #cbd5e1 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Loading Animation for Stats */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.user-stats {
    animation: fadeIn 0.5s ease forwards;
}

/* Tooltip for Collapsed State */
.tooltip {
    position: absolute;
    left: calc(100% + 10px);
    background: var(--sidebar-bg);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: var(--radius);
    font-size: 0.875rem;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transform: translateX(-10px);
    transition: all 0.3s ease;
    box-shadow: var(--shadow);
    z-index: 1001;
}

.nav-link:hover .tooltip {
    opacity: 1;
    visibility: visible;
    transform: translateX(0);
}

/* Focus States for Accessibility */
.nav-link:focus,
.logout-btn:focus,
.sidebar-toggle:focus {
    outline: 2px solid var(--primary-light);
    outline-offset: 2px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const sidebar = document.querySelector('.sidebar');
    const sidebarNav = document.querySelector('.sidebar-nav');
    const sidebarToggle = document.getElementById('sidebarToggle');
    let sidebarOverlay = document.getElementById('sidebarOverlay');

    /* ===============================
       Overlay create
    =============================== */
    if (!sidebarOverlay) {
        sidebarOverlay = document.createElement('div');
        sidebarOverlay.className = 'sidebar-overlay';
        sidebarOverlay.id = 'sidebarOverlay';
        document.body.appendChild(sidebarOverlay);
    }

    /* ===============================
       Mobile toggle
    =============================== */
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });
    }

    sidebarOverlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
    });

    /* ==================================================
       🔥 MAIN FIX — SIDEBAR SCROLL JUMP FIX
       (THIS IS THE IMPORTANT PART)
    ================================================== */
    document.querySelectorAll('.nav-link').forEach(link => {

        link.addEventListener('click', function (e) {

            // Save sidebar scroll position
            const sidebarScrollTop = sidebarNav.scrollTop;

            // Allow normal navigation, but restore scroll immediately
            setTimeout(() => {
                sidebarNav.scrollTop = sidebarScrollTop;
            }, 0);
        });

    });

    /* ===============================
       Active nav highlight (UI only)
    =============================== */
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function () {
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            const parent = this.closest('.nav-item');
            if (parent) parent.classList.add('active');
        });
    });

    /* ===============================
       Close sidebar on mobile link click
    =============================== */
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 1024) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            }
        });
    });

    /* ===============================
       Resize behavior
    =============================== */
    window.addEventListener('resize', () => {
        if (window.innerWidth > 1024) {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        }
    });

    /* ===============================
       ESC + Ctrl/Cmd + B
    =============================== */
    document.addEventListener('keydown', function (e) {

        if (e.key === 'Escape') {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        }

        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
            e.preventDefault();
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        }
    });

});
</script>
