<?php
/**
 * About Page
 * BukoJuice Application
 */

$pageTitle = 'About';
$pageStyles = ['dashboard', 'about'];
$currentPage = 'about';

require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

require_once dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="dashboard-layout">
    <?php require_once dirname(dirname(__DIR__)) . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="menu-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <h1 class="page-title">About</h1>
            </div>
            <div class="header-right">
                <button class="header-btn" data-theme-toggle>
                    <svg class="theme-icon-sun" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <svg class="theme-icon-moon" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
            </div>
        </header>

        <!-- Page Content -->
        <div class="dashboard-content about-page">
            <!-- Hero Section -->
            <div class="about-hero">
                <div class="about-hero-content">
                    <h1>Track Your Money<br>Like Never Before</h1>
                    <p>BukoJuice is your personal finance companion that helps you take control of your spending, save more, and achieve your financial goals with ease.</p>
                </div>
            </div>

            <!-- Features Section -->
            <div class="about-section">
                <div class="section-header">
                    <h2>Why Choose <?= APP_NAME ?>?</h2>
                    <p>Powerful features designed to make money management simple and effective for everyone.</p>
                </div>

                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                            </svg>
                        </div>
                        <h3>Easy Tracking</h3>
                        <p>Record your income and expenses in seconds with our intuitive interface. Categorize transactions automatically.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="20" x2="18" y2="10"></line>
                                <line x1="12" y1="20" x2="12" y2="4"></line>
                                <line x1="6" y1="20" x2="6" y2="14"></line>
                            </svg>
                        </div>
                        <h3>Visual Reports</h3>
                        <p>Beautiful charts and graphs that help you understand your spending patterns at a glance.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <circle cx="12" cy="12" r="6"></circle>
                                <circle cx="12" cy="12" r="2"></circle>
                            </svg>
                        </div>
                        <h3>Budget Goals</h3>
                        <p>Set monthly budgets and savings goals. Get alerts when you're approaching your limits.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </div>
                        <h3>Secure & Private</h3>
                        <p>Your financial data is encrypted and stored securely. We never share your information.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                <line x1="12" y1="18" x2="12.01" y2="18"></line>
                            </svg>
                        </div>
                        <h3>Mobile Friendly</h3>
                        <p>Access your finances anywhere, anytime. Our responsive design works on any device.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                <line x1="7" y1="7" x2="7.01" y2="7"></line>
                            </svg>
                        </div>
                        <h3>Custom Categories</h3>
                        <p>Create your own categories to match your lifestyle and track what matters most to you.</p>
                    </div>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="about-section">
                <div class="stats-section">
                    <div class="stat-item">
                        <div class="stat-number">10K+</div>
                        <div class="stat-label">Active Users</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">1M+</div>
                        <div class="stat-label">Transactions Tracked</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Countries</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">4.9</div>
                        <div class="stat-label">User Rating</div>
                    </div>
                </div>
            </div>

            <!-- Team Section -->
            <div class="about-section">
                <div class="section-header">
                    <h2>Meet Our Team</h2>
                    <p>The passionate people behind BukoJuice who work hard to help you achieve financial freedom.</p>
                </div>

                <div class="team-grid">
                    <div class="team-card">
                        <div class="team-avatar">JD</div>
                        <h3>John Doe</h3>
                        <p class="team-role">Founder & CEO</p>
                        <p>Passionate about making financial literacy accessible to everyone.</p>
                        <div class="team-social">
                            <a href="#" title="Twitter">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                                </svg>
                            </a>
                            <a href="#" title="LinkedIn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                                    <rect x="2" y="9" width="4" height="12"></rect>
                                    <circle cx="4" cy="4" r="2"></circle>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="team-card">
                        <div class="team-avatar">JS</div>
                        <h3>Jane Smith</h3>
                        <p class="team-role">Lead Developer</p>
                        <p>Full-stack developer with 10+ years of experience building scalable applications.</p>
                        <div class="team-social">
                            <a href="#" title="GitHub">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path>
                                </svg>
                            </a>
                            <a href="#" title="LinkedIn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                                    <rect x="2" y="9" width="4" height="12"></rect>
                                    <circle cx="4" cy="4" r="2"></circle>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="team-card">
                        <div class="team-avatar">MJ</div>
                        <h3>Mike Johnson</h3>
                        <p class="team-role">UX Designer</p>
                        <p>Creating beautiful and intuitive experiences that users love to interact with.</p>
                        <div class="team-social">
                            <a href="#" title="Dribbble">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M8.56 2.75c4.37 6.03 6.02 9.42 8.03 17.72m2.54-15.38c-3.72 4.35-8.94 5.66-16.88 5.85m19.5 1.9c-3.5-.93-6.63-.82-8.94 0-2.58.92-5.01 2.86-7.44 6.32"></path>
                                </svg>
                            </a>
                            <a href="#" title="LinkedIn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                                    <rect x="2" y="9" width="4" height="12"></rect>
                                    <circle cx="4" cy="4" r="2"></circle>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="cta-section">
                <h2>Ready to Take Control?</h2>
                <p>Join thousands of users who are already managing their finances better with BukoJuice.</p>
                <div class="cta-buttons">
                    <a href="<?= APP_URL ?>/pages/dashboard/" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        Go to Dashboard
                    </a>
                    <a href="<?= APP_URL ?>/pages/transactions/?action=add" class="btn btn-outline">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Add Transaction
                    </a>
                </div>
            </div>

            <!-- Footer Info -->
            <div style="text-align: center; padding: 2rem 0; color: var(--text-muted); font-size: 0.875rem;">
                <p><?= APP_NAME ?> v<?= APP_VERSION ?> • Made with ❤️ in Philippines</p>
                <p style="margin-top: 0.5rem;">© <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
            </div>
        </div>
    </main>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
