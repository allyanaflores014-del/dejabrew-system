<!-- Menu button for phones (css/navbar-mobile.css and js/navbar-mobile.js).
     The link and script are in this file so every page that includes the header gets them. -->
<link rel="stylesheet" href="css/navbar-mobile.css">
<script src="js/navbar-mobile.js" defer></script>

<header class="navbar">
    <div class="navbar-inner">
        <a href="index.php" class="logo">DejaBrew</a>

        <!-- Hamburger button: it is hidden on desktop and only shows on phones -->
        <button type="button" class="nav-toggle" id="nav-toggle" aria-label="Open menu" aria-expanded="false"
            aria-controls="main-nav">
            <span class="nav-toggle__bar"></span>
            <span class="nav-toggle__bar"></span>
            <span class="nav-toggle__bar"></span>
        </button>

        <nav id="main-nav" aria-label="Main navigation">
            <ul class="nav-links">
                <li><a href="index.php#menu">Menu</a></li>
                <li><a href="receipt.php" class="activity-link">
                        My Activity
                        <span class="activity-count" id="activity-count" hidden>0</span>
                    </a>
                </li>
                <li>
                    <a href="cart.php" class="icon-link cart-link" aria-label="Cart">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="9" cy="20" r="1.4" />
                            <circle cx="18" cy="20" r="1.4" />
                            <path d="M2 3h3l2.7 12.4a1 1 0 0 0 1 .8h8.8a1 1 0 0 0 1-.8L20.5 7H6" />
                        </svg>
                        <!-- Number of items; JavaScript shows it once something is added -->
                        <span class="cart-count" id="cart-count" hidden>0</span>
                    </a>
                </li>
                <li>
                    <?php if (isLoggedIn()): ?>
                        <!-- Logged in: logout icon -->
                        <a href="logout.php" class="icon-link" aria-label="Log out">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <path d="M16 17l5-5-5-5" />
                                <path d="M21 12H9" />
                            </svg>
                        </a>
                    <?php else: ?>
                        <!-- Guest: Sign In link -->
                        <a href="login.php">Sign In</a>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </div>
</header>