<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="img/MainLogo.svg">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
    <?php
    session_start();
    require_once 'Customer-portal/db_connection.php';
    
    $isLoggedIn = isset($_SESSION['user_id']);
    $userPhoto = '';
    $userName = '';
    $userInitials = '';
    
    if ($isLoggedIn) {
        $user_id = $_SESSION['user_id'];
        
        // Fetch user data
        $user_query = "SELECT first_name, last_name, profile_image FROM users WHERE id = ?";
        $user_stmt = mysqli_prepare($conn, $user_query);
        mysqli_stmt_bind_param($user_stmt, "i", $user_id);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        
        if ($user = mysqli_fetch_assoc($user_result)) {
            $userName = $user['first_name'] . ' ' . $user['last_name'];
            $userPhoto = !empty($user['profile_image']) ? $user['profile_image'] : '';
            $userInitials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
        }
    }
    ?>
   <style>
        /* Profile dropdown styles */
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .profile-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        .profile-btn:hover {
            background-color: rgba(255,255,255,0.2);
        }
        
        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
        }
        
        .profile-initials {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #3C342C;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: 2px solid #fff;
        }
        
        .profile-name {
            color: white;
            font-weight: 600;
            margin-right: 5px;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: all 0.3s;
        }
        
        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }
        
        .profile-dropdown:hover .dropdown-content {
            display: block;
        }
        
        .logout-btn {
            color: #ff4444 !important;
        }
        
        .logout-btn:hover {
            background-color: #ffeeee !important;
        }
    </style>
</head>
<body>
    <!-- Scroll Progress Indicator -->
    <div class="scroll-progress"></div>
    
    <!-- Back to Top Button -->
    <button id="back-to-top" class="hvr-bob">
        <i class="fas fa-arrow-up"></i>
    </button>
    
      <!-- Header Section -->
    <header class="header">
        <div class="logo hvr-grow">
            <i class="fas fa-recycle logo-icon"></i>
            <span class="logo-text">Junk<span>Value</span></span>
        </div>
        <nav class="top-nav">
            <div class="nav-links">
                <a href="#" class="nav-link hvr-underline-from-center" data-i18n="nav.home">Home</a>
                <a href="#calculator" class="nav-link hvr-underline-from-center" data-i18n="nav.calculator">Calculator</a>
                <a href="#catalog" class="nav-link hvr-underline-from-center" data-i18n="nav.catalog">Catalog</a>
                <a href="#faq" class="nav-link hvr-underline-from-center" data-i18n="nav.faq">FAQ</a>
                <a href="#tips" class="nav-link hvr-underline-from-center" data-i18n="nav.tips">Tips</a>
            </div>
            <div class="auth-buttons">
                <div class="language-switcher">
                    <div class="language-option active" data-lang="en">
                        <img src="https://flagcdn.com/w20/gb.png" class="language-flag" alt="English">
                    </div>
                    <div class="language-option" data-lang="tl">
                        <img src="https://flagcdn.com/w20/ph.png" class="language-flag" alt="Filipino">
                    </div>
                </div>
                
                <?php if ($isLoggedIn): ?>
    <!-- Show profile dropdown when logged in -->
    <div class="profile-dropdown">
        <button class="profile-btn hvr-glow">
            <span class="profile-name"><?php echo htmlspecialchars($userName); ?></span>
            <?php if (!empty($userPhoto)): ?>
                <img src="<?php echo 'Customer-portal/' . htmlspecialchars($userPhoto); ?>" alt="Profile" class="profile-img">
            <?php else: ?>
                <div class="profile-initials"><?php echo $userInitials; ?></div>
            <?php endif; ?>
        </button>
        <div class="dropdown-content">
            <a href="Customer-portal/index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="Customer-portal/Transaction.php"><i class="fas fa-history"></i> Transactions</a>
            <a href="Customer-portal/settings.php"><i class="fas fa-user-cog"></i> Account Settings</a>
            <a href="Customer-portal/Login/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
<?php else: ?>
    <!-- Show login/register buttons when not logged in -->
    <a href="Customer-portal/Login/Login.php" class="login-btn hvr-glow" data-i18n="nav.login">
        <i class="fas fa-sign-in-alt"></i> <span class="login-text">Login</span>
    </a>
    <a href="Customer-portal/Login/Register.php" class="register-btn hvr-pulse-grow" data-i18n="nav.register">
        <i class="fas fa-user-plus"></i> <span class="register-text">Register</span>
    </a>
<?php endif; ?>
            </div>
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
        </nav>
    </header>

    <!-- Mobile Menu -->
    <div class="mobile-menu">
        <div class="language-switcher-mobile">
            <div class="language-option active" data-lang="en">
                <img src="https://flagcdn.com/w20/gb.png" class="language-flag" alt="English">
                <span>English</span>
            </div>
            <div class="language-option" data-lang="tl">
                <img src="https://flagcdn.com/w20/ph.png" class="language-flag" alt="Filipino">
                <span>Filipino</span>
            </div>
        </div>
        <a href="#" class="mobile-nav-link" data-i18n="nav.home">Home</a>
        <a href="#calculator" class="mobile-nav-link" data-i18n="nav.calculator">Calculator</a>
        <a href="#catalog" class="mobile-nav-link" data-i18n="nav.catalog">Catalog</a>
        <a href="#faq" class="mobile-nav-link" data-i18n="nav.faq">FAQ</a>
        <a href="#tips" class="mobile-nav-link" data-i18n="nav.tips">Tips</a>
        <div class="mobile-auth-buttons">
            <?php if ($isLoggedIn): ?>
                <a href="Customer-portal/index.php" class="mobile-login-btn"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="Customer-portal/Login/logout.php" class="mobile-register-btn logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="Customer-portal/Login/Login.php" class="mobile-login-btn" data-i18n="nav.login">Login</a>
                <a href="Customer-portal/Login/Register.php" class="mobile-register-btn" data-i18n="nav.register">Register</a>
            <?php endif; ?>
        </div>
    </div>
    <!-- Calculator Modal -->
    <div id="calculator-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h3 data-i18n="modal.calculate_value">Calculate Value</h3>
            <div class="modal-calculator-form">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-recycle"></i> <span data-i18n="modal.material">Material</span>
                    </label>
                    <input type="text" id="modal-material" readonly>
                </div>
                <div class="form-group">
                    <label for="modal-weight" class="form-label">
                        <i class="fas fa-weight-hanging"></i> <span data-i18n="modal.enter_weight">Enter Weight (kg)</span>
                    </label>
                    <div class="input-with-icon">
                        <input type="number" id="modal-weight" placeholder="0.00" step="0.01">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                </div>
                <button id="modal-calculate-btn" class="calculate-button hvr-radial-out">
                    <i class="fas fa-calculator"></i> <span data-i18n="modal.calculate">Calculate</span>
                </button>
                <div class="result-box hvr-glow">
                    <div class="result-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="result-content">
                        <p class="result-label" data-i18n="modal.estimated_value">Estimated Value:</p>
                        <h3 id="modal-result-value">₱0.00</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick View Modal -->
    <div id="quick-view-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div class="quick-view-content">
                <div class="quick-view-image">
                    <img id="modal-item-image" src="" alt="">
                </div>
                <div class="quick-view-info">
                    <h3 id="modal-item-title"></h3>
                    <div class="item-meta">
                        <span class="price" id="modal-item-price"></span>
                        <span class="rating" id="modal-item-rating"></span>
                    </div>
                    <p class="recycling-tip" id="modal-item-tip"></p>
                    <button class="quick-view-calculate-btn item-button hvr-sweep-to-right">
                        <i class="fas fa-calculator"></i> <span data-i18n="modal.calculate_value">Calculate Value</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <main class="main-content">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title animate__animated animate__fadeInDown">
                        <span data-i18n="hero.title_part1">Turn Your</span> <span class="highlight" data-i18n="hero.title_junk">Junk</span> <span data-i18n="hero.title_part2">Into</span> <span class="highlight" data-i18n="hero.title_value">Value</span>
                    </h1>
                    <p class="hero-subtitle animate__animated animate__fadeIn animate__delay-1s" data-i18n="hero.subtitle">
                        Get the best prices for your recyclable materials while saving the planet
                    </p>
                    <div class="hero-buttons animate__animated animate__fadeInUp animate__delay-2s">
                        <a href="#calculator" class="hero-button hvr-bounce-to-right">
                            <i class="fas fa-calculator"></i> <span data-i18n="hero.try_calculator">Try Our Calculator</span>
                        </a>
                        <a href="#catalog" class="hero-link hvr-sweep-to-right">
                            <i class="fas fa-list"></i> <span data-i18n="hero.view_catalog">View Catalog</span>
                        </a>
                    </div>
                </div>
                <div class="hero-image animate__animated animate__fadeInRight animate__delay-1s">
                    <img src="img/wow.jpg" alt="Recycling Illustration" loading="lazy">
                </div>
            </div>
            
            <!-- Stats Counter -->
            <div class="stats-container animate__animated animate__fadeInUp animate__delay-3s">
                <div class="stat-item">
                    <!-- Stats content -->
                </div>
                <div class="stat-item">
                    <!-- Stats content -->
                </div>
                <div class="stat-item">
                    <!-- Stats content -->
                </div>
            </div>
            
            <!-- Scrolling Arrow -->
            <div class="scroll-down animate__animated animate__fadeIn animate__delay-4s">
                <a href="#calculator" class="hvr-bob">
                    <i class="fas fa-chevron-down"></i>
                </a>
            </div>
        </section>

        <!-- Calculator Section -->
        <section id="calculator" class="calculator-section">
            <div class="section-header">
                <h2 class="section-title animate__animated animate__fadeIn">
                    <span class="title-decorator">//</span> <span data-i18n="calculator.title">Price Calculator</span> <span class="title-decorator">//</span>
                </h2>
                <p class="section-subtitle animate__animated animate__fadeIn animate__delay-1s" data-i18n="calculator.subtitle">
                    Estimate how much your recyclables are worth
                </p>
            </div>
            
            <div class="calculator-container">
                <div class="calculator-box animate__animated">
                    <div class="calculator-form">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-recycle"></i> <span data-i18n="calculator.what_selling">What are you selling?</span>
                            </label>
                            <div class="material-selector">
                                <div class="material-options">
                                    <input type="radio" name="material" id="copper" value="copper" checked>
                                    <label for="copper" class="material-option hvr-grow">
                                        <img src="img/coppericon.png" alt="Copper" loading="lazy">
                                        <span data-i18n="materials.copper">Copper Wire</span>
                                        <div class="custom-radio"></div>
                                    </label>
                                    
                                    <input type="radio" name="material" id="pet" value="pet">
                                    <label for="pet" class="material-option hvr-grow">
                                        <img src="img/petbottleicon.png" alt="PET" loading="lazy">
                                        <span data-i18n="materials.pet">PET Bottles</span>
                                        <div class="custom-radio"></div>
                                    </label>
                                    
                                    <input type="radio" name="material" id="aluminum" value="aluminum">
                                    <label for="aluminum" class="material-option hvr-grow">
                                        <img src="img/aluicon.png" alt="Aluminum" loading="lazy">
                                        <span data-i18n="materials.aluminum">Aluminum Cans</span>
                                        <div class="custom-radio"></div>
                                    </label>
                                    
                                    <input type="radio" name="material" id="cardboard" value="cardboard">
                                    <label for="cardboard" class="material-option hvr-grow">
                                        <img src="img/box.png" alt="Cardboard" loading="lazy">
                                        <span data-i18n="materials.cardboard">Cardboard</span>
                                        <div class="custom-radio"></div>
                                    </label>
                                    
                                    <input type="radio" name="material" id="steel" value="steel">
                                    <label for="steel" class="material-option hvr-grow">
                                        <img src="img/steel.png" alt="Steel" loading="lazy">
                                        <span data-i18n="materials.steel">Steel</span>
                                        <div class="custom-radio"></div>
                                    </label>
                                    
                                    <input type="radio" name="material" id="glass" value="glass">
                                    <label for="glass" class="material-option hvr-grow">
                                        <img src="img/glass.png" alt="Glass" loading="lazy">
                                        <span data-i18n="materials.glass">Glass Bottles</span>
                                        <div class="custom-radio"></div>
                                    </label>
                                    
                                    <input type="radio" name="material" id="computer" value="computer">
                                    <label for="computer" class="material-option hvr-grow">
                                        <img src="img/computer.png" alt="Computer" loading="lazy">
                                        <span data-i18n="materials.computer">Computer Parts</span>
                                        <div class="custom-radio"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="weight" class="form-label">
                                <i class="fas fa-weight-hanging"></i> <span data-i18n="calculator.enter_weight">Enter Weight (kg)</span>
                            </label>
                            <div class="input-with-icon">
                                <input type="number" id="weight" placeholder="0.00" step="0.01">
                                <i class="fas fa-balance-scale"></i>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button id="calculate-btn" class="calculate-button hvr-radial-out">
                                <i class="fas fa-calculator"></i> <span data-i18n="calculator.calculate">Calculate Value</span>
                            </button>
                            <button id="reset-btn" class="reset-button hvr-radial-in">
                                <i class="fas fa-redo"></i> <span data-i18n="calculator.reset">Reset</span>
                            </button>
                        </div>
                        
                        <div class="result-box hvr-glow">
                            <div class="result-icon">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="result-content">
                                <p class="result-label" data-i18n="calculator.estimated_value">Estimated Value:</p>
                                <h3 id="result-value">₱0.00</h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="calculator-info">
                        <div class="info-card accepted-items hvr-float">
                            <h3 class="info-card-title">
                                <i class="fas fa-check-circle"></i> <span data-i18n="calculator.accepted_items">Accepted Items</span>
                            </h3>
                            <ul class="info-card-list">
                                <li> 
                                    <i class="fas fa-circle"></i> Copper Wire</span> <span>₱285.70/kg</span>
                                </li>
                                <li>
                                    <i class="fas fa-circle"></i> PET Bottles</span> <span>₱28.57/kg</span>
                                </li>
                                <li>
                                    <i class="fas fa-circle"></i>Aluminum Cans</span> <span>₱68.57/kg</span>
                                </li>
                                <li>
                                    <i class="fas fa-circle"></i> Cardboard</span> <span>₱17.14/kg</span>
                                </li>
                                <li>
                                    <i class="fas fa-circle"></i> Steel</span> <span>₱45.71/kg</span>
                                </li>
                                <li>
                                    <i class="fas fa-circle"></i> Glass Bottles</span> <span>₱14.29/kg</span>
                                </li>
                                <li>
                                    <i class="fas fa-circle"></i>Computer Parts</span> <span>₱85.71/kg</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="info-card preparation-tips hvr-float">
                            <h3 class="info-card-title">
                                <i class="fas fa-lightbulb"></i> <span data-i18n="calculator.preparation_tips">Preparation Tips</span>
                            </h3>
                            <ul class="info-card-list">
                                <li>
                                    <i class="fas fa-check"></i> Clean materials get better prices</span>
                                </li>
                                <li>
                                    <i class="fas fa-check"></i> Sort by material type</span>
                                </li>
                                <li>
                                    <i class="fas fa-check"></i> Remove non-recyclable parts</span>
                                </li>
                                <li>
                                    <i class="fas fa-check"></i> Bundle similar items together</span>
                                </li>
                                <li>
                                    <i class="fas fa-check"></i> Call ahead for large quantities</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Catalog Section -->
        <section id="catalog" class="catalog-section">
            <div class="section-header">
                <h2 class="section-title animate__animated animate__fadeIn">
                    <span class="title-decorator">//</span> <span data-i18n="catalog.title">Item Catalog</span> <span class="title-decorator">//</span>
                </h2>
                <p class="section-subtitle animate__animated animate__fadeIn animate__delay-1s" data-i18n="catalog.subtitle">
                    Browse our accepted materials and their current prices
                </p>
            </div>
            
            <div class="catalog-filter">
                <button class="filter-btn active hvr-sweep-to-right" data-filter="all" data-i18n="catalog.all_items">All Items</button>
                <button class="filter-btn hvr-sweep-to-right" data-filter="metal" data-i18n="catalog.metals">Metals</button>
                <button class="filter-btn hvr-sweep-to-right" data-filter="plastic" data-i18n="catalog.plastics">Plastics</button>
                <button class="filter-btn hvr-sweep-to-right" data-filter="paper" data-i18n="catalog.paper">Paper</button>
                <button class="filter-btn hvr-sweep-to-right" data-filter="electronics" data-i18n="catalog.electronics">Electronics</button>
            </div>
            
            <div class="catalog-grid">
                <!-- Copper Wire -->
                <div class="catalog-item animate__animated" data-category="metal">
                    <div class="item-badge" data-i18n="catalog.best_price">Best Price!</div>
                    <div class="item-image-container hvr-bob">
                        <img src="img/Copper.jpg" alt="Copper Wire" loading="lazy">
                        <div class="item-overlay">
                            <button class="quick-view-btn hvr-radial-out" data-item="copper">
                                <i class="fas fa-eye"></i> <span data-i18n="catalog.quick_view">Quick View</span>
                            </button>
                        </div>
                    </div>
                    <div class="item-info">
                        <h3 data-i18n="materials.copper">Copper Wire</h3>
                        <div class="item-meta">
                            <span class="price">₱285.70/kg</span>
                            <span class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </span>
                        </div>
                        <p class="recycling-tip">
                            <i class="fas fa-info-circle"></i> <span data-i18n="tips.copper_tip">Strip insulation for higher value</span>
                        </p>
                        <button class="item-button hvr-sweep-to-right" data-item="copper">
                            <i class="fas fa-calculator"></i> <span data-i18n="catalog.calculate_value">Calculate Value</span>
                        </button>
                    </div>
                </div>
                
                <!-- PET Bottles -->
                <div class="catalog-item animate__animated" data-category="plastic">
                    <div class="item-image-container hvr-bob">
                        <img src="img/Plastic.webp" alt="PET Bottles" loading="lazy">
                        <div class="item-overlay">
                            <button class="quick-view-btn hvr-radial-out" data-item="pet">
                                <i class="fas fa-eye"></i> <span data-i18n="catalog.quick_view">Quick View</span>
                            </button>
                        </div>
                    </div>
                    <div class="item-info">
                        <h3 data-i18n="materials.pet">PET Bottles</h3>
                        <div class="item-meta">
                            <span class="price">₱28.57/kg</span>
                            <span class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </span>
                        </div>
                        <p class="recycling-tip">
                            <i class="fas fa-info-circle"></i> <span data-i18n="tips.pet_tip">Remove caps and rinse clean</span>
                        </p>
                        <button class="item-button hvr-sweep-to-right" data-item="pet">
                            <i class="fas fa-calculator"></i> <span data-i18n="catalog.calculate_value">Calculate Value</span>
                        </button>
                    </div>
                </div>
                
                <!-- Aluminum Cans -->
                <div class="catalog-item animate__animated" data-category="metal">
                    <div class="item-image-container hvr-bob">
                        <img src="img/aluminum.jpg" alt="Aluminum Cans" loading="lazy">
                        <div class="item-overlay">
                            <button class="quick-view-btn hvr-radial-out" data-item="aluminum">
                                <i class="fas fa-eye"></i> <span data-i18n="catalog.quick_view">Quick View</span>
                            </button>
                        </div>
                    </div>
                    <div class="item-info">
                        <h3 data-i18n="materials.aluminum">Aluminum Cans</h3>
                        <div class="item-meta">
                            <span class="price">₱68.57/kg</span>
                            <span class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </span>
                        </div>
                        <p class="recycling-tip">
                            <i class="fas fa-info-circle"></i> <span data-i18n="tips.aluminum_tip">Crush to save space</span>
                        </p>
                        <button class="item-button hvr-sweep-to-right" data-item="aluminum">
                            <i class="fas fa-calculator"></i> <span data-i18n="catalog.calculate_value">Calculate Value</span>
                        </button>
                    </div>
                </div>
                
                <!-- Cardboard -->
                <div class="catalog-item animate__animated" data-category="paper">
                    <div class="item-image-container hvr-bob">
                        <img src="img/cardboard.jpg" alt="Cardboard" loading="lazy">
                        <div class="item-overlay">
                            <button class="quick-view-btn hvr-radial-out" data-item="cardboard">
                                <i class="fas fa-eye"></i> <span data-i18n="catalog.quick_view">Quick View</span>
                            </button>
                        </div>
                    </div>
                    <div class="item-info">
                        <h3 data-i18n="materials.cardboard">Cardboard</h3>
                        <div class="item-meta">
                            <span class="price">₱17.14/kg</span>
                            <span class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <i class="far fa-star"></i>
                            </span>
                        </div>
                        <p class="recycling-tip">
                            <i class="fas fa-info-circle"></i> <span data-i18n="tips.cardboard_tip">Flatten boxes before bringing</span>
                        </p>
                        <button class="item-button hvr-sweep-to-right" data-item="cardboard">
                            <i class="fas fa-calculator"></i> <span data-i18n="catalog.calculate_value">Calculate Value</span>
                        </button>
                    </div>
                </div>
                
                <!-- Steel -->
                <div class="catalog-item animate__animated" data-category="metal">
                    <div class="item-image-container hvr-bob">
                        <img src="img/steel.jpg" alt="Steel" loading="lazy">
                        <div class="item-overlay">
                            <button class="quick-view-btn hvr-radial-out" data-item="steel">
                                <i class="fas fa-eye"></i> <span data-i18n="catalog.quick_view">Quick View</span>
                            </button>
                        </div>
                    </div>
                    <div class="item-info">
                        <h3 data-i18n="materials.steel">Steel</h3>
                        <div class="item-meta">
                            <span class="price">₱45.71/kg</span>
                            <span class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </span>
                        </div>
                        <p class="recycling-tip">
                            <i class="fas fa-info-circle"></i> <span data-i18n="tips.steel_tip">Remove non-metal attachments</span>
                        </p>
                        <button class="item-button hvr-sweep-to-right" data-item="steel">
                            <i class="fas fa-calculator"></i> <span data-i18n="catalog.calculate_value">Calculate Value</span>
                        </button>
                    </div>
                </div>
                
                <!-- Glass Bottles -->
                <div class="catalog-item animate__animated" data-category="glass">
                    <div class="item-image-container hvr-bob">
                        <img src="img/glass.jpg" alt="Glass Bottles" loading="lazy">
                        <div class="item-overlay">
                            <button class="quick-view-btn hvr-radial-out" data-item="glass">
                                <i class="fas fa-eye"></i> <span data-i18n="catalog.quick_view">Quick View</span>
                            </button>
                        </div>
                    </div>
                    <div class="item-info">
                        <h3 data-i18n="materials.glass">Glass Bottles</h3>
                        <div class="item-meta">
                            <span class="price">₱14.29/kg</span>
                            <span class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                            </span>
                        </div>
                        <p class="recycling-tip">
                            <i class="fas fa-info-circle"></i> <span data-i18n="tips.glass_tip">Separate by color if possible</span>
                        </p>
                        <button class="item-button hvr-sweep-to-right" data-item="glass">
                            <i class="fas fa-calculator"></i> <span data-i18n="catalog.calculate_value">Calculate Value</span>
                        </button>
                    </div>
                </div>
                
                <!-- Computer Parts -->
                <div class="catalog-item animate__animated" data-category="electronics">
                    <div class="item-badge" data-i18n="catalog.popular">Popular!</div>
                    <div class="item-image-container hvr-bob">
                        <img src="img/computer.jpg" alt="Computer Parts" loading="lazy">
                        <div class="item-overlay">
                            <button class="quick-view-btn hvr-radial-out" data-item="computer">
                                <i class="fas fa-eye"></i> <span data-i18n="catalog.quick_view">Quick View</span>
                            </button>
                        </div>
                    </div>
                    <div class="item-info">
                        <h3 data-i18n="materials.computer">Computer Parts</h3>
                        <div class="item-meta">
                            <span class="price">₱85.71/kg</span>
                            <span class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </span>
                        </div>
                        <p class="recycling-tip">
                            <i class="fas fa-info-circle"></i> <span data-i18n="tips.computer_tip">Remove batteries if present</span>
                        </p>
                        <button class="item-button hvr-sweep-to-right" data-item="computer">
                            <i class="fas fa-calculator"></i> <span data-i18n="catalog.calculate_value">Calculate Value</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="catalog-cta animate__animated animate__fadeIn">
                <p data-i18n="catalog.bulk_pricing">Have large quantities or special items? We might pay even more!</p>
                <button class="cta-button hvr-radial-out">
                    <i class="fas fa-phone-alt"></i> <span data-i18n="catalog.contact_bulk">Contact Us for Bulk Pricing</span>
                </button>
            </div>
        </section>

        <!-- Price Trend Section -->
        <section class="price-trend-section">
            <div class="section-header">
                <h2 class="section-title animate__animated animate__fadeIn">
                    <span class="title-decorator">//</span> <span data-i18n="trends.title">Price Trends</span> <span class="title-decorator">//</span>
                </h2>
                <p class="section-subtitle animate__animated animate__fadeIn animate__delay-1s" data-i18n="trends.subtitle">
                    Track market prices for recyclable materials
                </p>
            </div>
            
            <div class="trend-container">
                <div class="trend-selector">
                    <button class="trend-btn active hvr-sweep-to-right" data-period="monthly" data-i18n="trends.monthly">Monthly</button>
                    <button class="trend-btn hvr-sweep-to-right" data-period="quarterly" data-i18n="trends.quarterly">Quarterly</button>
                    <button class="trend-btn hvr-sweep-to-right" data-period="yearly" data-i18n="trends.yearly">Yearly</button>
                </div>
                
                <div class="chart-container animate__animated animate__fadeIn">
                    <canvas id="priceChart"></canvas>
                </div>
                
                <div class="trend-legend">
                    <div class="legend-item">
                        <span class="legend-color copper"></span>
                        <span class="legend-label" data-i18n="materials.copper">Copper Wire</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color pet"></span>
                        <span class="legend-label" data-i18n="materials.pet">PET Bottles</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color aluminum"></span>
                        <span class="legend-label" data-i18n="materials.aluminum">Aluminum Cans</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section id="faq" class="faq-section">
            <div class="section-header">
                <h2 class="section-title animate__animated animate__fadeIn">
                    <span class="title-decorator">//</span> <span data-i18n="faq.title">Frequently Asked Questions</span> <span class="title-decorator">//</span>
                </h2>
                <p class="section-subtitle animate__animated animate__fadeIn animate__delay-1s" data-i18n="faq.subtitle">
                    Get answers to common questions about our recycling services
                </p>
            </div>
            
            <div class="faq-container">
                <div class="faq-accordion">
                    <!-- FAQ Item 1 -->
                    <div class="faq-item animate__animated animate__fadeInLeft">
                        <div class="faq-question">
                            <h3 data-i18n="faq.q1">How to prepare items for sale?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p data-i18n="faq.a1_part1">Clean and sort your materials by type. Remove any non-recyclable components like plastic labels from metal items or batteries from electronics. For best prices:</p>
                            <ul>
                                <li data-i18n="faq.a1_point1">Rinse containers to remove food residue</li>
                                <li data-i18n="faq.a1_point2">Separate materials by type (metals, plastics, paper)</li>
                                <li data-i18n="faq.a1_point3">Remove any attachments or non-recyclable parts</li>
                                <li data-i18n="faq.a1_point4">Bundle similar items together</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- FAQ Item 2 -->
                    <div class="faq-item animate__animated animate__fadeInRight">
                        <div class="faq-question">
                            <h3 data-i18n="faq.q2">Do you accept broken electronics?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p data-i18n="faq.a2_part1">Yes, we accept broken electronics but the price may be lower than working items. Please remove any batteries before bringing them in. We accept:</p>
                            <ul>
                                <li data-i18n="faq.a2_point1">Computers and laptops (working or not)</li>
                                <li data-i18n="faq.a2_point2">Mobile phones and tablets</li>
                                <li data-i18n="faq.a2_point3">Televisions and monitors</li>
                                <li data-i18n="faq.a2_point4">Printers and scanners</li>
                                <li data-i18n="faq.a2_point5">Other electronic devices</li>
                            </ul>
                            <p><strong data-i18n="faq.a2_note">Note:</strong> <span data-i18n="faq.a2_part2">Some items may require special handling fees.</span></p>
                        </div>
                    </div>
                    
                    <!-- FAQ Item 3 -->
                    <div class="faq-item animate__animated animate__fadeInLeft">
                        <div class="faq-question">
                            <h3 data-i18n="faq.q3">What are your business hours?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p data-i18n="faq.a3_part1">Our standard business hours are:</p>
                            <table class="hours-table">
                                <tr>
                                    <td data-i18n="faq.hours_weekdays">Monday - Friday</td>
                                    <td>8:00 AM - 6:00 PM</td>
                                </tr>
                                <tr>
                                    <td data-i18n="faq.hours_saturday">Saturday</td>
                                    <td>9:00 AM - 4:00 PM</td>
                                </tr>
                                <tr>
                                    <td data-i18n="faq.hours_sunday">Sunday</td>
                                    <td data-i18n="faq.closed">Closed</td>
                                </tr>
                            </table>
                            <p data-i18n="faq.a3_part2">We're closed on major public holidays. Extended hours may be available by appointment for commercial customers.</p>
                        </div>
                    </div>
                    
                    <!-- FAQ Item 4 -->
                    <div class="faq-item animate__animated animate__fadeInRight">
                        <div class="faq-question">
                            <h3 data-i18n="faq.q4">Is there a minimum weight requirement?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p data-i18n="faq.a4_part1">No minimum weight, but items under 1kg may be paid at a slightly lower rate due to handling costs. For the best value:</p>
                            <ul>
                                <li data-i18n="faq.a4_point1">Small quantities (under 1kg): Standard rate minus 10% handling fee</li>
                                <li data-i18n="faq.a4_point2">Medium quantities (1-10kg): Standard rate</li>
                                <li data-i18n="faq.a4_point3">Large quantities (10kg+): Potential for premium pricing</li>
                                <li data-i18n="faq.a4_point4">Commercial quantities (100kg+): Special bulk rates available</li>
                            </ul>
                            <p data-i18n="faq.a4_part2">We recommend saving up your recyclables to bring in larger quantities for better pricing.</p>
                        </div>
                    </div>
                    
                    <!-- FAQ Item 5 -->
                    <div class="faq-item animate__animated animate__fadeInLeft">
                        <div class="faq-question">
                            <h3 data-i18n="faq.q5">How do I get paid for my items?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p data-i18n="faq.a5_part1">We offer several convenient payment options:</p>
                            <div class="payment-options">
                                <div class="payment-option">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <h4 data-i18n="faq.payment_cash">Cash</h4>
                                    <p data-i18n="faq.payment_cash_desc">Immediate payment upon weighing</p>
                                </div>
                                <div class="payment-option">
                                    <i class="fas fa-mobile-alt"></i>
                                    <h4 data-i18n="faq.payment_mobile">Mobile Payment</h4>
                                    <p data-i18n="faq.payment_mobile_desc">GCash, PayMaya, or bank transfer</p>
                                </div>
                                <div class="payment-option">
                                    <i class="fas fa-gift"></i>
                                    <h4 data-i18n="faq.payment_credit">Store Credit</h4>
                                    <p data-i18n="faq.payment_credit_desc">Get 10% bonus when choosing credit</p>
                                </div>
                            </div>
                            <p data-i18n="faq.a5_part2">All payments are made immediately after weighing and inspection of your materials.</p>
                        </div>
                    </div>
                    
                    <!-- FAQ Item 6 -->
                    <div class="faq-item animate__animated animate__fadeInRight">
                        <div class="faq-question">
                            <h3 data-i18n="faq.q6">Do you offer pickup services?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p data-i18n="faq.a6_part1">Yes! We offer convenient pickup services for qualifying customers:</p>
                            <div class="pickup-options">
                                <div class="pickup-option">
                                    <h4 data-i18n="faq.pickup_residential">Residential Pickup</h4>
                                    <ul>
                                        <li data-i18n="faq.pickup_res_point1">Minimum 50kg of materials</li>
                                        <li data-i18n="faq.pickup_res_point2">₱500 pickup fee (deducted from payout)</li>
                                        <li data-i18n="faq.pickup_res_point3">Scheduled 2-3 days in advance</li>
                                    </ul>
                                </div>
                                <div class="pickup-option">
                                    <h4 data-i18n="faq.pickup_commercial">Commercial Pickup</h4>
                                    <ul>
                                        <li data-i18n="faq.pickup_com_point1">Minimum 200kg of materials</li>
                                        <li data-i18n="faq.pickup_com_point2">Free pickup for qualifying businesses</li>
                                        <li data-i18n="faq.pickup_com_point3">Regular scheduled pickups available</li>
                                    </ul>
                                </div>
                            </div>
                            <p data-i18n="faq.a6_part2">Contact us to schedule a pickup or to see if you qualify for free pickup services.</p>
                        </div>
                    </div>
                </div>
                
                <div class="faq-cta animate__animated animate__fadeIn">
                    <div class="cta-box">
                        <i class="fas fa-question-circle"></i>
                        <h3 data-i18n="faq.cta_title">Still have questions?</h3>
                        <p data-i18n="faq.cta_text">Our team is ready to help you with any additional questions you may have about our recycling services.</p>
                        <button class="cta-button hvr-radial-out">
                            <i class="fas fa-headset"></i> <span data-i18n="faq.contact_support">Contact Support</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recycling Tips Section -->
        <section id="tips" class="tips-section">
            <div class="section-header">
                <h2 class="section-title animate__animated animate__fadeIn">
                    <span class="title-decorator">//</span> <span data-i18n="tips.title">Recycling Tips</span> <span class="title-decorator">//</span>
                </h2>
                <p class="section-subtitle animate__animated animate__fadeIn animate__delay-1s" data-i18n="tips.subtitle">
                    Maximize your earnings with our expert recycling advice
                </p>
            </div>
            
            <div class="tips-container">
                <div class="tips-grid">
                    <!-- Tip 1 -->
                    <div class="tip-card animate__animated animate__fadeInUp">
                        <div class="tip-icon hvr-buzz">
                            <i class="fas fa-sort"></i>
                        </div>
                        <h3 data-i18n="tips.tip1_title">Proper Sorting</h3>
                        <p data-i18n="tips.tip1_text">Separate materials by type (metals, plastics, glass) to maximize their recycling potential and get better prices. Mixed materials often get the lowest rates.</p>
                        <button class="tip-more hvr-sweep-to-right" data-i18n="tips.read_more">Read More</button>
                    </div>
                    
                    <!-- Tip 2 -->
                    <div class="tip-card animate__animated animate__fadeInUp">
                        <div class="tip-icon hvr-buzz">
                            <i class="fas fa-broom"></i>
                        </div>
                        <h3 data-i18n="tips.tip2_title">Clean Materials</h3>
                        <p data-i18n="tips.tip2_text">Rinse containers and remove food residue. Clean materials are worth more and are easier to process. Food contamination can reduce value by up to 30%.</p>
                        <button class="tip-more hvr-sweep-to-right" data-i18n="tips.read_more">Read More</button>
                    </div>
                    
                    <!-- Tip 3 -->
                    <div class="tip-card animate__animated animate__fadeInUp">
                        <div class="tip-icon hvr-buzz">
                            <i class="fas fa-compress-alt"></i>
                        </div>
                        <h3 data-i18n="tips.tip3_title">Compact When Possible</h3>
                        <p data-i18n="tips.tip3_text">Flatten boxes, crush cans, and cut large items to save space and reduce transportation costs. More compact materials often qualify for bulk pricing.</p>
                        <button class="tip-more hvr-sweep-to-right" data-i18n="tips.read_more">Read More</button>
                    </div>
                    
                    <!-- Tip 4 -->
                    <div class="tip-card animate__animated animate__fadeInUp">
                        <div class="tip-icon hvr-buzz">
                            <i class="fas fa-battery-three-quarters"></i>
                        </div>
                        <h3 data-i18n="tips.tip4_title">Battery Safety</h3>
                        <p data-i18n="tips.tip4_text">Remove batteries from electronics and recycle them separately to prevent fires and contamination. Lithium batteries can be especially dangerous if damaged.</p>
                        <button class="tip-more hvr-sweep-to-right" data-i18n="tips.read_more">Read More</button>
                    </div>
                    
                    <!-- Tip 5 -->
                    <div class="tip-card animate__animated animate__fadeInUp">
                        <div class="tip-icon hvr-buzz">
                            <i class="fas fa-tags"></i>
                        </div>
                        <h3 data-i18n="tips.tip5_title">Remove Labels</h3>
                        <p data-i18n="tips.tip5_text">Peel off non-recyclable components like plastic labels from glass jars and metal cans. These contaminants can reduce the quality of recycled materials.</p>
                        <button class="tip-more hvr-sweep-to-right" data-i18n="tips.read_more">Read More</button>
                    </div>
                    
                    <!-- Tip 6 -->
                    <div class="tip-card animate__animated animate__fadeInUp">
                        <div class="tip-icon hvr-buzz">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h3 data-i18n="tips.tip6_title">Check Local Rates</h3>
                        <p data-i18n="tips.tip6_text">Prices fluctuate - call ahead or check our price trends before bringing in large quantities. Some materials have seasonal price variations.</p>
                        <button class="tip-more hvr-sweep-to-right" data-i18n="tips.read_more">Read More</button>
                    </div>
                </div>
                
                <div class="bonus-tips animate__animated animate__fadeIn">
                    <div class="bonus-header">
                        <i class="fas fa-star"></i>
                        <h3 data-i18n="tips.pro_tips">Pro Recycling Tips</h3>
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="bonus-content">
                        <div class="bonus-tip">
                            <i class="fas fa-check-circle"></i>
                            <p data-i18n="tips.pro_tip1">Copper wire with insulation removed fetches 30% higher prices than insulated wire.</p>
                        </div>
                        <div class="bonus-tip">
                            <i class="fas fa-check-circle"></i>
                            <p data-i18n="tips.pro_tip2">Keep aluminum separate from other metals for better pricing - mixed metals get the lowest rate.</p>
                        </div>
                        <div class="bonus-tip">
                            <i class="fas fa-check-circle"></i>
                            <p data-i18n="tips.pro_tip3">Electronics with intact components may be worth more as parts than scrap - ask about our component buyback program.</p>
                        </div>
                        <div class="bonus-tip">
                            <i class="fas fa-check-circle"></i>
                            <p data-i18n="tips.pro_tip4">Separate colored glass from clear glass - they have different recycling streams and clear glass is more valuable.</p>
                        </div>
                        <div class="bonus-tip">
                            <i class="fas fa-check-circle"></i>
                            <p data-i18n="tips.pro_tip5">Bundle similar items together to save time during weighing - use twine or rubber bands for easy handling.</p>
                        </div>
                        <div class="bonus-tip">
                            <i class="fas fa-check-circle"></i>
                            <p data-i18n="tips.pro_tip6">Bring materials in during weekday mornings for fastest service - weekends tend to be busiest.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials-section">
            <div class="section-header">
                <h2 class="section-title animate__animated animate__fadeIn">
                    <span class="title-decorator">//</span> <span data-i18n="testimonials.title">What Our Customers Say</span> <span class="title-decorator">//</span>
                </h2>
                <p class="section-subtitle animate__animated animate__fadeIn animate__delay-1s" data-i18n="testimonials.subtitle">
                    Hear from people who turned their junk into value
                </p>
            </div>
            
            <div class="testimonials-container">
                <div class="testimonial-slider">
                    <!-- Testimonial 1 -->
                    <div class="testimonial animate__animated animate__fadeIn">
                        <div class="testimonial-content">
                            <div class="quote-icon">
                                <i class="fas fa-quote-left"></i>
                            </div>
                            <p class="testimonial-text" data-i18n="testimonials.testimonial1">
                                I've been using JunkValue for over a year now and they always give me the best prices for my recyclables. Their calculator is spot on and the staff is super friendly!
                            </p>
                            <div class="testimonial-author">
                                <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" alt="Maria Santos" loading="lazy">
                                <div class="author-info">
                                    <h4 data-i18n="testimonials.author1_name">Maria Santos</h4>
                                    <p data-i18n="testimonials.author1_title">Regular Customer</p>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 2 -->
                    <div class="testimonial animate__animated animate__fadeIn">
                        <div class="testimonial-content">
                            <div class="quote-icon">
                                <i class="fas fa-quote-left"></i>
                            </div>
                            <p class="testimonial-text" data-i18n="testimonials.testimonial2">
                                As a small business, we generate a lot of cardboard waste. JunkValue's commercial pickup service has been a game-changer for us - we save on disposal costs and even make money!
                            </p>
                            <div class="testimonial-author">
                                <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" alt="Juan Dela Cruz" loading="lazy">
                                <div class="author-info">
                                    <h4 data-i18n="testimonials.author2_name">Juan Dela Cruz</h4>
                                    <p data-i18n="testimonials.author2_title">Business Owner</p>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 3 -->
                    <div class="testimonial animate__animated animate__fadeIn">
                        <div class="testimonial-content">
                            <div class="quote-icon">
                                <i class="fas fa-quote-left"></i>
                            </div>
                            <p class="testimonial-text" data-i18n="testimonials.testimonial3">
                                I was surprised how much my old computer parts were worth! The team at JunkValue helped me identify valuable components I didn't even know could be recycled for cash.
                            </p>
                            <div class="testimonial-author">
                                <img src="https://images.unsplash.com/photo-1552058544-f2b08422138a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" alt="Robert Lim" loading="lazy">
                                <div class="author-info">
                                    <h4 data-i18n="testimonials.author3_name">Robert Lim</h4>
                                    <p data-i18n="testimonials.author3_title">First-time Customer</p>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-controls">
                    <button class="testimonial-prev hvr-grow">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="testimonial-dots">
                        <span class="dot active"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </div>
                    <button class="testimonial-next hvr-grow">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </section>

        <!-- Location Section -->
        <section class="location-section">
            <div class="section-header">
                <h2 class="section-title animate__animated animate__fadeIn">
                    <span class="title-decorator">//</span> <span data-i18n="location.title">Location & Hours</span> <span class="title-decorator">//</span>
                </h2>
                <p class="section-subtitle animate__animated animate__fadeIn animate__delay-1s" data-i18n="location.subtitle">
                    Visit us or schedule a pickup
                </p>
            </div>
            
            <div class="location-container">
                <div class="location-info animate__animated animate__fadeInLeft">
                    <div class="info-card">
                        <h3 class="info-card-title">
                            <i class="fas fa-map-marker-alt"></i> <span data-i18n="location.visit_us">Visit Us</span>
                        </h3>
                        <div class="info-card-content">
                            <p>P2GP+JVX, Topaz, Novaliches, Quezon City, Metro Manila</p>
                            <button class="directions-btn hvr-sweep-to-right">
                                <i class="fas fa-directions"></i> <span data-i18n="location.get_directions">Get Directions</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <h3 class="info-card-title">
                            <i class="fas fa-clock"></i> <span data-i18n="location.business_hours">Business Hours</span>
                        </h3>
                        <div class="info-card-content">
                            <table class="hours-table">
                                <tr>
                                    <td data-i18n="location.weekdays">Monday - Friday</td>
                                    <td>8:00 AM - 6:00 PM</td>
                                </tr>
                                <tr>
                                    <td data-i18n="location.saturday">Saturday</td>
                                    <td>9:00 AM - 4:00 PM</td>
                                </tr>
                                <tr>
                                    <td data-i18n="location.sunday">Sunday</td>
                                    <td data-i18n="location.closed">Closed</td>
                                </tr>
                            </table>
                            <p class="holiday-notice">
                                <i class="fas fa-info-circle"></i> <span data-i18n="location.holiday_notice">Closed on public holidays</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <h3 class="info-card-title">
                            <i class="fas fa-phone-alt"></i> <span data-i18n="location.contact_us">Contact Us</span>
                        </h3>
                        <div class="info-card-content">
                            <p><i class="fas fa-phone"></i> (02) 8994 4218</p>
                            <p><i class="fas fa-envelope"></i> info@bestlink.edu.ph</p>
                            <div class="social-links">
                                <a href="#" class="hvr-bob"><i class="fab fa-facebook"></i></a>
                                <a href="#" class="hvr-bob"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="hvr-bob"><i class="fab fa-instagram"></i></a>
                                <a href="#" class="hvr-bob"><i class="fab fa-viber"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="location-map animate__animated animate__fadeInRight">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3858.926396504214!2d121.0223089757197!3d14.7563094857499!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b0f9086d659d%3A0x3db09a502a5b3d8f!2sBestlink%20College%20of%20the%20Philippines%20-%20Millionaires%20Village!5e0!3m2!1sen!2sph!4v1710860612709!5m2!1sen!2sph" 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy"
                        title="Bestlink College of the Philippines - Millionaires Village Campus Location">
                    </iframe>
                    <div class="map-overlay hvr-sweep-to-right">
                        <i class="fas fa-expand"></i> <span data-i18n="location.view_larger">View Larger Map</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Newsletter Section -->
        <section class="newsletter-section">
            <div class="newsletter-container animate__animated animate__fadeIn">
                <div class="newsletter-text">
                    <h3 data-i18n="newsletter.title">Stay Updated on Price Changes</h3>
                    <p data-i18n="newsletter.subtitle">Subscribe to our newsletter for weekly price updates and recycling tips</p>
                </div>
                <div class="newsletter-form">
                    <input type="email" placeholder="Your email address" data-i18n-placeholder="newsletter.email_placeholder">
                    <button class="subscribe-btn hvr-radial-out">
                        <i class="fas fa-paper-plane"></i> <span data-i18n="newsletter.subscribe">Subscribe</span>
                    </button>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-column">
                <div class="footer-logo">
                    <i class="fas fa-recycle"></i>
                    <span>Junk<span>Value</span></span>
                </div>
                <p class="footer-about" data-i18n="footer.about">
                    Turning your recyclable materials into cash while helping the environment. We offer the best prices in Quezon City for your scrap metals, plastics, paper, and electronics.
                </p>
                <div class="footer-social">
                    <a href="#" class="hvr-bob"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="hvr-bob"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="hvr-bob"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="hvr-bob"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <div class="footer-column">
                <h3 class="footer-title" data-i18n="footer.quick_links">Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="#" class="hvr-forward"><i class="fas fa-chevron-right"></i> <span data-i18n="nav.home">Home</span></a></li>
                    <li><a href="#calculator" class="hvr-forward"><i class="fas fa-chevron-right"></i> <span data-i18n="nav.calculator">Calculator</span></a></li>
                    <li><a href="#catalog" class="hvr-forward"><i class="fas fa-chevron-right"></i> <span data-i18n="nav.catalog">Catalog</span></a></li>
                    <li><a href="#faq" class="hvr-forward"><i class="fas fa-chevron-right"></i> <span data-i18n="nav.faq">FAQ</span></a></li>
                    <li><a href="#tips" class="hvr-forward"><i class="fas fa-chevron-right"></i> <span data-i18n="nav.tips">Tips</span></a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3 class="footer-title" data-i18n="footer.services">Services</h3>
                <ul class="footer-links">
                    <li><a href="#" class="hvr-forward"><i class="fas fa-chevron-right"></i> <span data-i18n="footer.residential">Residential Recycling</span></a></li>
                    <li><a href="#" class="hvr-forward"><i class="fas fa-chevron-right"></i> <span data-i18n="footer.commercial">Commercial Recycling</span></a></li>
                    <li><a href="#" class="hvr-forward"><i class="fas fa-chevron-right"></i> <span data-i18n="footer.electronics">Electronics Buyback</span></a></li>
                    <li><a href="#" class="hvr-forward"><i class="fas fa-chevron-right"></i> <span data-i18n="footer.bulk_pickup">Bulk Pickup Services</span></a></li>
                    <li><a href="#" class="hvr-forward"><i class="fas fa-chevron-right"></i> <span data-i18n="footer.community">Community Events</span></a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3 class="footer-title" data-i18n="footer.contact_info">Contact Info</h3>
                <ul class="footer-contact">
                    <li><i class="fas fa-map-marker-alt"></i> P2GP+JVX, Topaz, Novaliches, Quezon City</li>
                    <li><i class="fas fa-phone"></i> (02) 8994 4218</li>
                    <li><i class="fas fa-envelope"></i> info@bestlink.edu.ph</li>
                    <li><i class="fas fa-clock"></i> <span data-i18n="footer.hours">Mon-Fri: 8AM-6PM, Sat: 9AM-4PM</span></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-copyright">
                &copy; 2025 JunkValue. <span data-i18n="footer.rights">All Rights Reserved.</span>
            </div>
            <div class="footer-legal">
                <a href="privacy-policy.html" data-i18n="footer.privacy">Privacy Policy</a>
                <a href="terms-of-service.html" data-i18n="footer.terms">Terms of Service</a>
                <a href="contact-us.html" data-i18n="footer.contact">Contact Us</a>
            </div>
        </div>
    </footer>

    <script>
        // Language translation object
        const translations = {
            'en': {
                // Navigation
                'nav.home': 'Home',
                'nav.calculator': 'Calculator',
                'nav.catalog': 'Catalog',
                'nav.faq': 'FAQ',
                'nav.tips': 'Tips',
                'nav.login': 'Login',
                'nav.register': 'Register',
                
                // Hero Section
                'hero.title_part1': 'Turn Your',
                'hero.title_junk': 'Junk',
                'hero.title_part2': 'Into',
                'hero.title_value': 'Value',
                'hero.subtitle': 'Get the best prices for your recyclable materials while saving the planet',
                'hero.try_calculator': 'Try Our Calculator',
                'hero.view_catalog': 'View Catalog',
                
                // Calculator Section
                'calculator.title': 'Price Calculator',
                'calculator.subtitle': 'Estimate how much your recyclables are worth',
                'calculator.what_selling': 'What are you selling?',
                'calculator.enter_weight': 'Enter Weight (kg)',
                'calculator.calculate': 'Calculate Value',
                'calculator.reset': 'Reset',
                'calculator.estimated_value': 'Estimated Value:',
                'calculator.accepted_items': 'Accepted Items',
                'calculator.preparation_tips': 'Preparation Tips',
                
                // Materials
                'materials.copper': 'Copper Wire',
                'materials.pet': 'PET Bottles',
                'materials.aluminum': 'Aluminum Cans',
                'materials.cardboard': 'Cardboard',
                'materials.steel': 'Steel',
                'materials.glass': 'Glass Bottles',
                'materials.computer': 'Computer Parts',
                
                // Tips
                'tips.clean_materials': 'Clean materials get better prices',
                'tips.sort_by_type': 'Sort by material type',
                'tips.remove_non_recyclable': 'Remove non-recyclable parts',
                'tips.bundle_items': 'Bundle similar items together',
                'tips.call_ahead': 'Call ahead for large quantities',
                'tips.copper_tip': 'Strip insulation for higher value',
                'tips.pet_tip': 'Remove caps and rinse clean',
                'tips.aluminum_tip': 'Crush to save space',
                'tips.cardboard_tip': 'Flatten boxes before bringing',
                'tips.steel_tip': 'Remove non-metal attachments',
                'tips.glass_tip': 'Separate by color if possible',
                'tips.computer_tip': 'Remove batteries if present',
                
                // Catalog Section
                'catalog.title': 'Item Catalog',
                'catalog.subtitle': 'Browse our accepted materials and their current prices',
                'catalog.all_items': 'All Items',
                'catalog.metals': 'Metals',
                'catalog.plastics': 'Plastics',
                'catalog.paper': 'Paper',
                'catalog.electronics': 'Electronics',
                'catalog.best_price': 'Best Price!',
                'catalog.quick_view': 'Quick View',
                'catalog.calculate_value': 'Calculate Value',
                'catalog.popular': 'Popular!',
                'catalog.bulk_pricing': 'Have large quantities or special items? We might pay even more!',
                'catalog.contact_bulk': 'Contact Us for Bulk Pricing',
                
                // Price Trends
                'trends.title': 'Price Trends',
                'trends.subtitle': 'Track market prices for recyclable materials',
                'trends.monthly': 'Monthly',
                'trends.quarterly': 'Quarterly',
                'trends.yearly': 'Yearly',
                
                // FAQ Section
                'faq.title': 'Frequently Asked Questions',
                'faq.subtitle': 'Get answers to common questions about our recycling services',
                'faq.q1': 'How to prepare items for sale?',
                'faq.a1_part1': 'Clean and sort your materials by type. Remove any non-recyclable components like plastic labels from metal items or batteries from electronics. For best prices:',
                'faq.a1_point1': 'Rinse containers to remove food residue',
                'faq.a1_point2': 'Separate materials by type (metals, plastics, paper)',
                'faq.a1_point3': 'Remove any attachments or non-recyclable parts',
                'faq.a1_point4': 'Bundle similar items together',
                'faq.q2': 'Do you accept broken electronics?',
                'faq.a2_part1': 'Yes, we accept broken electronics but the price may be lower than working items. Please remove any batteries before bringing them in. We accept:',
                'faq.a2_point1': 'Computers and laptops (working or not)',
                'faq.a2_point2': 'Mobile phones and tablets',
                'faq.a2_point3': 'Televisions and monitors',
                'faq.a2_point4': 'Printers and scanners',
                'faq.a2_point5': 'Other electronic devices',
                'faq.a2_note': 'Note:',
                'faq.a2_part2': 'Some items may require special handling fees.',
                'faq.q3': 'What are your business hours?',
                'faq.a3_part1': 'Our standard business hours are:',
                'faq.hours_weekdays': 'Monday - Friday',
                'faq.hours_saturday': 'Saturday',
                'faq.hours_sunday': 'Sunday',
                'faq.closed': 'Closed',
                'faq.a3_part2': "We're closed on major public holidays. Extended hours may be available by appointment for commercial customers.",
                'faq.q4': 'Is there a minimum weight requirement?',
                'faq.a4_part1': 'No minimum weight, but items under 1kg may be paid at a slightly lower rate due to handling costs. For the best value:',
                'faq.a4_point1': 'Small quantities (under 1kg): Standard rate minus 10% handling fee',
                'faq.a4_point2': 'Medium quantities (1-10kg): Standard rate',
                'faq.a4_point3': 'Large quantities (10kg+): Potential for premium pricing',
                'faq.a4_point4': 'Commercial quantities (100kg+): Special bulk rates available',
                'faq.a4_part2': 'We recommend saving up your recyclables to bring in larger quantities for better pricing.',
                'faq.q5': 'How do I get paid for my items?',
                'faq.a5_part1': 'We offer several convenient payment options:',
                'faq.payment_cash': 'Cash',
                'faq.payment_cash_desc': 'Immediate payment upon weighing',
                'faq.payment_mobile': 'Mobile Payment',
                'faq.payment_mobile_desc': 'GCash, PayMaya, or bank transfer',
                'faq.payment_credit': 'Store Credit',
                'faq.payment_credit_desc': 'Get 10% bonus when choosing credit',
                'faq.a5_part2': 'All payments are made immediately after weighing and inspection of your materials.',
                'faq.q6': 'Do you offer pickup services?',
                'faq.a6_part1': 'Yes! We offer convenient pickup services for qualifying customers:',
                'faq.pickup_residential': 'Residential Pickup',
                'faq.pickup_res_point1': 'Minimum 50kg of materials',
                'faq.pickup_res_point2': '₱500 pickup fee (deducted from payout)',
                'faq.pickup_res_point3': 'Scheduled 2-3 days in advance',
                'faq.pickup_commercial': 'Commercial Pickup',
                'faq.pickup_com_point1': 'Minimum 200kg of materials',
                'faq.pickup_com_point2': 'Free pickup for qualifying businesses',
                'faq.pickup_com_point3': 'Regular scheduled pickups available',
                'faq.a6_part2': 'Contact us to schedule a pickup or to see if you qualify for free pickup services.',
                'faq.cta_title': 'Still have questions?',
                'faq.cta_text': 'Our team is ready to help you with any additional questions you may have about our recycling services.',
                'faq.contact_support': 'Contact Support',
                
                // Tips Section
                'tips.title': 'Recycling Tips',
                'tips.subtitle': 'Maximize your earnings with our expert recycling advice',
                'tips.tip1_title': 'Proper Sorting',
                'tips.tip1_text': 'Separate materials by type (metals, plastics, glass) to maximize their recycling potential and get better prices. Mixed materials often get the lowest rates.',
                'tips.tip2_title': 'Clean Materials',
                'tips.tip2_text': 'Rinse containers and remove food residue. Clean materials are worth more and are easier to process. Food contamination can reduce value by up to 30%.',
                'tips.tip3_title': 'Compact When Possible',
                'tips.tip3_text': 'Flatten boxes, crush cans, and cut large items to save space and reduce transportation costs. More compact materials often qualify for bulk pricing.',
                'tips.tip4_title': 'Battery Safety',
                'tips.tip4_text': 'Remove batteries from electronics and recycle them separately to prevent fires and contamination. Lithium batteries can be especially dangerous if damaged.',
                'tips.tip5_title': 'Remove Labels',
                'tips.tip5_text': 'Peel off non-recyclable components like plastic labels from glass jars and metal cans. These contaminants can reduce the quality of recycled materials.',
                'tips.tip6_title': 'Check Local Rates',
                'tips.tip6_text': 'Prices fluctuate - call ahead or check our price trends before bringing in large quantities. Some materials have seasonal price variations.',
                'tips.read_more': 'Read More',
                'tips.pro_tips': 'Pro Recycling Tips',
                'tips.pro_tip1': 'Copper wire with insulation removed fetches 30% higher prices than insulated wire.',
                'tips.pro_tip2': 'Keep aluminum separate from other metals for better pricing - mixed metals get the lowest rate.',
                'tips.pro_tip3': 'Electronics with intact components may be worth more as parts than scrap - ask about our component buyback program.',
                'tips.pro_tip4': 'Separate colored glass from clear glass - they have different recycling streams and clear glass is more valuable.',
                'tips.pro_tip5': 'Bundle similar items together to save time during weighing - use twine or rubber bands for easy handling.',
                'tips.pro_tip6': 'Bring materials in during weekday mornings for fastest service - weekends tend to be busiest.',
                
                // Testimonials
                'testimonials.title': 'What Our Customers Say',
                'testimonials.subtitle': 'Hear from people who turned their junk into value',
                'testimonials.testimonial1': "I've been using JunkValue for over a year now and they always give me the best prices for my recyclables. Their calculator is spot on and the staff is super friendly!",
                'testimonials.author1_name': 'Maria Santos',
                'testimonials.author1_title': 'Regular Customer',
                'testimonials.testimonial2': "As a small business, we generate a lot of cardboard waste. JunkValue's commercial pickup service has been a game-changer for us - we save on disposal costs and even make money!",
                'testimonials.author2_name': 'Juan Dela Cruz',
                'testimonials.author2_title': 'Business Owner',
                'testimonials.testimonial3': "I was surprised how much my old computer parts were worth! The team at JunkValue helped me identify valuable components I didn't even know could be recycled for cash.",
                'testimonials.author3_name': 'Robert Lim',
                'testimonials.author3_title': 'First-time Customer',
                
                // Location Section
                'location.title': 'Location & Hours',
                'location.subtitle': 'Visit us or schedule a pickup',
                'location.visit_us': 'Visit Us',
                'location.get_directions': 'Get Directions',
                'location.business_hours': 'Business Hours',
                'location.weekdays': 'Monday - Friday',
                'location.saturday': 'Saturday',
                'location.sunday': 'Sunday',
                'location.holiday_notice': 'Closed on public holidays',
                'location.contact_us': 'Contact Us',
                'location.view_larger': 'View Larger Map',
                
                // Newsletter
                'newsletter.title': 'Stay Updated on Price Changes',
                'newsletter.subtitle': 'Subscribe to our newsletter for weekly price updates and recycling tips',
                'newsletter.email_placeholder': 'Your email address',
                'newsletter.subscribe': 'Subscribe',
                
                // Footer
                'footer.about': 'Turning your recyclable materials into cash while helping the environment. We offer the best prices in Quezon City for your scrap metals, plastics, paper, and electronics.',
                'footer.quick_links': 'Quick Links',
                'footer.services': 'Services',
                'footer.residential': 'Residential Recycling',
                'footer.commercial': 'Commercial Recycling',
                'footer.electronics': 'Electronics Buyback',
                'footer.bulk_pickup': 'Bulk Pickup Services',
                'footer.community': 'Community Events',
                'footer.contact_info': 'Contact Info',
                'footer.hours': 'Mon-Fri: 8AM-6PM, Sat: 9AM-4PM',
                'footer.rights': 'All Rights Reserved.',
                'footer.privacy': 'Privacy Policy',
                'footer.terms': 'Terms of Service',
                'footer.contact': 'Contact Us',
                
                // Modal
                'modal.calculate_value': 'Calculate Value',
                'modal.material': 'Material',
                'modal.enter_weight': 'Enter Weight (kg)',
                'modal.calculate': 'Calculate',
                'modal.estimated_value': 'Estimated Value:'
            },
            'tl': {
                // Navigation
                'nav.home': 'Home',
                'nav.calculator': 'Calculator',
                'nav.catalog': 'Catalog',
                'nav.faq': 'FAQ',
                'nav.tips': 'Tips',
                'nav.login': 'Mag-login',
                'nav.register': 'Magrehistro',
                
                // Hero Section
                'hero.title_part1': 'Gawing',
                'hero.title_junk': 'Pera Ang',
                'hero.title_part2': 'Basura',
                'hero.title_value': 'Mo',
                'hero.subtitle': 'Kumita mula sa iyong mga recyclable materials habang tumutulong sa kalikasan',
                'hero.try_calculator': 'Subukan ang Aming Calculator',
                'hero.view_catalog': 'Tingnan ang Catalog',
                
                // Calculator Section
                'calculator.title': 'Price Calculator',
                'calculator.subtitle': 'Alamin ang halaga ng iyong mga recyclables',
                'calculator.what_selling': 'Ano ang iyong ibinebenta?',
                'calculator.enter_weight': 'Ilagay ang Timbang (kg)',
                'calculator.calculate': 'Kalkulahin ang Halaga',
                'calculator.reset': 'I-reset',
                'calculator.estimated_value': 'Tinatayang Halaga:',
                'calculator.accepted_items': 'Tinatanggap na Items',
                'calculator.preparation_tips': 'Mga Tip sa Paghahanda',
                
                // Materials
                'materials.copper': 'Tanso',
                'materials.pet': 'Plastik bote',
                'materials.aluminum': 'Aluminum Cans',
                'materials.cardboard': 'Karton/Papel',
                'materials.steel': 'Bakal',
                'materials.glass': 'Bote',
                'materials.computer': 'Parte ng Computer',
                
                // Tips
                'tips.clean_materials': 'Mas malinis, mas mataas ang presyo',
                'tips.sort_by_type': 'Ihiwalay ayon sa uri',
                'tips.remove_non_recyclable': 'Alisin ang hindi recyclable na parte',
                'tips.bundle_items': 'Ipagsama ang magkakatulad na items',
                'tips.call_ahead': 'Tumawag muna para sa malalaking dami',
                'tips.copper_tip': 'Alisin ang insulation para mas mataas ang halaga',
                'tips.pet_tip': 'Alisin ang takip at banlawan',
                'tips.aluminum_tip': 'Pigain para makatipid ng espasyo',
                'tips.cardboard_tip': 'Ipatag bago dalhin',
                'tips.steel_tip': 'Alisin ang mga hindi metal na parte',
                'tips.glass_tip': 'Ihiwalay ayon sa kulay kung maaari',
                'tips.computer_tip': 'Alisin ang baterya kung meron',
                
                // Catalog Section
                'catalog.title': 'Item Catalog',
                'catalog.subtitle': 'Tingnan ang aming tinatanggap na materyales at kanilang presyo',
                'catalog.all_items': 'Lahat ng Items',
                'catalog.metals': 'Mga Metal',
                'catalog.plastics': 'Plastik',
                'catalog.paper': 'Papel',
                'catalog.electronics': 'Electronics',
                'catalog.best_price': 'Pinakamagandang Presyo!',
                'catalog.quick_view': 'Mabilisang Tingin',
                'catalog.calculate_value': 'Kalkulahin ang Halaga',
                'catalog.popular': 'Popular!',
                'catalog.bulk_pricing': 'Maramihan o espesyal na items? Maaari kaming magbigay ng mas mataas na presyo!',
                'catalog.contact_bulk': 'Makipag-ugnayan para sa Bulk Pricing',
                
                // Price Trends
                'trends.title': 'Price Trends',
                'trends.subtitle': 'Subaybayan ang presyo ng mga recyclable materials',
                'trends.monthly': 'Buwanan',
                'trends.quarterly': 'Quarterly',
                'trends.yearly': 'Taunan',
                
                // FAQ Section
                'faq.title': 'Mga Madalas Itanong',
                'faq.subtitle': 'Mga sagot sa karaniwang tanong tungkol sa aming recycling services',
                'faq.q1': 'Paano ihanda ang mga items para ibenta?',
                'faq.a1_part1': 'Linisin at pagbukud-bukurin ang iyong mga materyales ayon sa uri. Alisin ang anumang hindi recyclable na bahagi tulad ng plastic labels mula sa metal o baterya mula sa electronics. Para sa pinakamagandang presyo:',
                'faq.a1_point1': 'Banlawan ang mga lalagyan para matanggal ang dumi',
                'faq.a1_point2': 'Ihiwalay ang mga materyales ayon sa uri (metal, plastik, papel)',
                'faq.a1_point3': 'Alisin ang anumang nakakabit o hindi recyclable na parte',
                'faq.a1_point4': 'Ipagsama ang magkakatulad na items',
                'faq.q2': 'Tumatanggap ba kayo ng sirang electronics?',
                'faq.a2_part1': 'Oo, tumatanggap kami ng sirang electronics ngunit maaaring mas mababa ang presyo kumpara sa gumaganang items. Mangyaring alisin ang anumang baterya bago dalhin. Tumatanggap kami ng:',
                'faq.a2_point1': 'Mga computer at laptop (gumagana man o hindi)',
                'faq.a2_point2': 'Mobile phones at tablets',
                'faq.a2_point3': 'Telebisyon at monitor',
                'faq.a2_point4': 'Printer at scanner',
                'faq.a2_point5': 'Iba pang electronic devices',
                'faq.a2_note': 'Paalala:',
                'faq.a2_part2': 'Ang ilang items ay maaaring mangailangan ng espesyal na handling fees.',
                'faq.q3': 'Ano ang inyong oras ng operasyon?',
                'faq.a3_part1': 'Ang aming standard na oras ng operasyon ay:',
                'faq.hours_weekdays': 'Lunes - Biyernes',
                'faq.hours_saturday': 'Sabado',
                'faq.hours_sunday': 'Linggo',
                'faq.closed': 'Sarado',
                'faq.a3_part2': "Kami ay sarado sa mga pangunahing pampublikong holiday. Extended hours ay maaaring available sa appointment para sa commercial customers.",
                'faq.q4': 'May minimum na timbang ba?',
                'faq.a4_part1': 'Walang minimum na timbang, ngunit ang mga items na wala pang 1kg ay maaaring bayaran ng mas mababang rate dahil sa handling costs. Para sa pinakamagandang halaga:',
                'faq.a4_point1': 'Maliliit na dami (wala pang 1kg): Standard rate minus 10% handling fee',
                'faq.a4_point2': 'Katamtamang dami (1-10kg): Standard rate',
                'faq.a4_point3': 'Malalaking dami (10kg+): Potensyal para sa premium pricing',
                'faq.a4_point4': 'Commercial quantities (100kg+): Special bulk rates available',
                'faq.a4_part2': 'Inirerekumenda naming tipunin muna ang iyong mga recyclables para makapagdala ng mas malaking dami para sa mas magandang presyo.',
                'faq.q5': 'Paano ako mababayaran para sa aking mga items?',
                'faq.a5_part1': 'Nag-aalok kami ng ilang maginhawang payment options:',
                'faq.payment_cash': 'Cash',
                'faq.payment_cash_desc': 'Agad na bayad pagkatapos timbangin',
                'faq.payment_mobile': 'Mobile Payment',
                'faq.payment_mobile_desc': 'GCash, PayMaya, o bank transfer',
                'faq.payment_credit': 'Store Credit',
                'faq.payment_credit_desc': 'Makakuha ng 10% bonus kapag pinili ang credit',
                'faq.a5_part2': 'Ang lahat ng bayad ay ginagawa agad pagkatapos timbangin at inspeksyon ng iyong mga materyales.',
                'faq.q6': 'Nag-aalok ba kayo ng pickup services?',
                'faq.a6_part1': 'Oo! Nag-aalok kami ng maginhawang pickup services para sa qualifying customers:',
                'faq.pickup_residential': 'Residential Pickup',
                'faq.pickup_res_point1': 'Minimum na 50kg ng materyales',
                'faq.pickup_res_point2': '₱500 pickup fee (ibabawas sa payout)',
                'faq.pickup_res_point3': 'Iskedyul 2-3 araw bago',
                'faq.pickup_commercial': 'Commercial Pickup',
                'faq.pickup_com_point1': 'Minimum na 200kg ng materyales',
                'faq.pickup_com_point2': 'Libreng pickup para sa qualifying businesses',
                'faq.pickup_com_point3': 'Available ang regular na iskedyul ng pickup',
                'faq.a6_part2': 'Makipag-ugnayan sa amin para mag-iskedyul ng pickup o para malaman kung kwalipikado ka para sa libreng pickup services.',
                'faq.cta_title': 'May tanong pa?',
                'faq.cta_text': 'Handa ang aming team na tulungan ka sa anumang karagdagang tanong tungkol sa aming recycling services.',
                'faq.contact_support': 'Makipag-ugnayan sa Support',
                
                // Tips Section
                'tips.title': 'Mga Tip sa Pagre-recycle',
                'tips.subtitle': 'Palakihin ang iyong kinikita gamit ang aming expert recycling advice',
                'tips.tip1_title': 'Tamang Paghihiwalay',
                'tips.tip1_text': 'Ihiwalay ang mga materyales ayon sa uri (metal, plastik, salamin) para mas mataas ang halaga. Ang magkahalong materyales ay karaniwang may pinakamababang presyo.',
                'tips.tip2_title': 'Malinis na Materyales',
                'tips.tip2_text': 'Banlawan ang mga lalagyan at alisin ang dumi. Ang malilinis na materyales ay mas mataas ang halaga at mas madaling iproseso. Ang kontaminasyon ng pagkain ay maaaring magpababa ng halaga hanggang 30%.',
                'tips.tip3_title': 'Pigain Kung Maaari',
                'tips.tip3_text': 'Ipatag ang mga kahon, pigain ang mga lata, at putulin ang malalaking items para makatipid ng espasyo at mabawasan ang transportation costs. Ang mas compact na materyales ay kadalasang kwalipikado para sa bulk pricing.',
                'tips.tip4_title': 'Ligtas sa Baterya',
                'tips.tip4_text': 'Alisin ang mga baterya mula sa electronics at i-recycle ang mga ito nang hiwalay para maiwasan ang sunog at kontaminasyon. Ang lithium batteries ay maaaring mapanganib lalo na kung nasira.',
                'tips.tip5_title': 'Alisin ang Mga Label',
                'tips.tip5_text': 'Tanggalin ang mga hindi recyclable na bahagi tulad ng plastic labels mula sa glass jars at metal cans. Ang mga contaminants na ito ay maaaring magpababa ng kalidad ng recycled materials.',
                'tips.tip6_title': 'Tingnan ang Lokal na Presyo',
                'tips.tip6_text': 'Nagbabago-bago ang presyo - tumawag muna o tingnan ang aming price trends bago magdala ng malalaking dami. Ang ilang materyales ay may seasonal price variations.',
                'tips.read_more': 'Magbasa Pa',
                'tips.pro_tips': 'Pro Recycling Tips',
                'tips.pro_tip1': 'Ang tansong wire na walang insulation ay 30% mas mataas ang halaga kaysa sa insulated wire.',
                'tips.pro_tip2': 'Ihiwalay ang aluminum sa iba pang metals para sa mas magandang presyo - ang magkahalong metals ay may pinakamababang rate.',
                'tips.pro_tip3': 'Ang electronics na may buong components ay maaaring mas mataas ang halaga bilang parts kaysa scrap - magtanong tungkol sa aming component buyback program.',
                'tips.pro_tip4': 'Ihiwalay ang may kulay na salamin sa malinaw na salamin - may iba silang recycling streams at ang malinaw na salamin ay mas mahalaga.',
                'tips.pro_tip5': 'Ipagsama ang magkakatulad na items para makatipid ng oras sa pagtimbang - gumamit ng tali o rubber bands para madaling hawakan.',
                'tips.pro_tip6': 'Magdala ng materyales sa umaga ng weekdays para sa pinakamabilis na serbisyo - ang weekends ay karaniwang mas maraming tao.',
                
                // Testimonials
                'testimonials.title': 'Ang Sabi ng Aming Mga Customer',
                'testimonials.subtitle': 'Pakinggan ang mga taong ginawang pera ang kanilang basura',
                'testimonials.testimonial1': "Mahigit isang taon ko nang ginagamit ang JunkValue at palagi silang nagbibigay ng pinakamagandang presyo para sa aking mga recyclables. Tumpak ang kanilang calculator at napakabait ng staff!",
                'testimonials.author1_name': 'Maria Santos',
                'testimonials.author1_title': 'Regular na Customer',
                'testimonials.testimonial2': "Bilang isang maliit na negosyo, marami kaming cardboard waste. Ang commercial pickup service ng JunkValue ay naging game-changer para sa amin - nakakatipid kami sa disposal costs at kumikita pa!",
                'testimonials.author2_name': 'Juan Dela Cruz',
                'testimonials.author2_title': 'May-ari ng Negosyo',
                'testimonials.testimonial3': "Nagulat ako sa halaga ng aking mga lumang computer parts! Tinulungan ako ng team ng JunkValue na makilala ang mga valuable components na hindi ko alam na pwedeng i-recycle para kumita.",
                'testimonials.author3_name': 'Robert Lim',
                'testimonials.author3_title': 'Unang Beses na Customer',
                
                // Location Section
                'location.title': 'Lokasyon at Oras',
                'location.subtitle': 'Bisitahin kami o mag-iskedyul ng pickup',
                'location.visit_us': 'Bisitahin Kami',
                'location.get_directions': 'Kunin ang Direksyon',
                'location.business_hours': 'Oras ng Operasyon',
                'location.weekdays': 'Lunes - Biyernes',
                'location.saturday': 'Sabado',
                'location.sunday': 'Linggo',
                'location.holiday_notice': 'Sarado sa mga pampublikong holiday',
                'location.contact_us': 'Makipag-ugnayan',
                'location.view_larger': 'Tingnan ang Mas Malaking Mapa',
                
                // Newsletter
                'newsletter.title': 'Manatiling Updated sa Mga Pagbabago sa Presyo',
                'newsletter.subtitle': 'Mag-subscribe sa aming newsletter para sa weekly price updates at recycling tips',
                'newsletter.email_placeholder': 'Ang iyong email address',
                'newsletter.subscribe': 'Mag-subscribe',
                
                // Footer
                'footer.about': 'Gawing pera ang iyong mga recyclable materials habang tumutulong sa kalikasan. Nag-aalok kami ng pinakamagandang presyo sa Quezon City para sa iyong scrap metals, plastics, papel, at electronics.',
                'footer.quick_links': 'Mabilisang Links',
                'footer.services': 'Mga Serbisyo',
                'footer.residential': 'Residential Recycling',
                'footer.commercial': 'Commercial Recycling',
                'footer.electronics': 'Electronics Buyback',
                'footer.bulk_pickup': 'Bulk Pickup Services',
                'footer.community': 'Community Events',
                'footer.contact_info': 'Impormasyon ng Kontak',
                'footer.hours': 'Lunes-Biyernes: 8AM-6PM, Sabado: 9AM-4PM',
                'footer.rights': 'Lahat ng Karapatan ay Reserbado.',
                'footer.privacy': 'Patakaran sa Privacy',
                'footer.terms': 'Mga Tuntunin ng Serbisyo',
                'footer.contact': 'Makipag-ugnayan',
                
                // Modal
                'modal.calculate_value': 'Kalkulahin ang Halaga',
                'modal.material': 'Materyal',
                'modal.enter_weight': 'Ilagay ang Timbang (kg)',
                'modal.calculate': 'Kalkulahin',
                'modal.estimated_value': 'Tinatayang Halaga:'
            }
        };

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Language switching functionality
            const languageOptions = document.querySelectorAll('.language-option');
            
            function switchLanguage(lang) {
                // Update active language button
                languageOptions.forEach(option => {
                    option.classList.remove('active');
                    if (option.getAttribute('data-lang') === lang) {
                        option.classList.add('active');
                    }
                });
                
                // Update all elements with data-i18n attribute
                document.querySelectorAll('[data-i18n]').forEach(element => {
                    const key = element.getAttribute('data-i18n');
                    if (translations[lang] && translations[lang][key]) {
                        element.textContent = translations[lang][key];
                    }
                });
                
                // Update placeholder texts
                document.querySelectorAll('[data-i18n-placeholder]').forEach(element => {
                    const key = element.getAttribute('data-i18n-placeholder');
                    if (translations[lang] && translations[lang][key]) {
                        element.placeholder = translations[lang][key];
                    }
                });
                
                // Store language preference
                localStorage.setItem('preferredLanguage', lang);
            }
            
            // Set initial language based on user preference or default to English
            const preferredLanguage = localStorage.getItem('preferredLanguage') || 'en';
            switchLanguage(preferredLanguage);
            
            // Add click event for language options
            languageOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const lang = this.getAttribute('data-lang');
                    switchLanguage(lang);
                });
            });

            // Price Calculator Functionality
            const prices = {
                'copper': 285.70,
                'pet': 28.57,
                'aluminum': 68.57,
                'cardboard': 17.14,
                'steel': 45.71,
                'glass': 14.29,
                'computer': 85.71
            };

            // Main calculator functions
            function calculateValue(material, weight, resultElement) {
                if (!material || isNaN(weight)) {
                    alert("Please select a material and enter a valid weight");
                    return;
                }

                const totalValue = weight * prices[material];
                animateValue(resultElement, 0, totalValue, 1000);
            }

            function animateValue(element, start, end, duration) {
                let startTimestamp = null;
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const value = Math.floor(progress * (end - start) + start);
                    element.textContent = `₱${value.toFixed(2)}`;
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    }
                };
                window.requestAnimationFrame(step);
            }

            // Main calculator
            const calculateBtn = document.getElementById('calculate-btn');
            const resetBtn = document.getElementById('reset-btn');
            const weightInput = document.getElementById('weight');
            const resultValue = document.getElementById('result-value');
            const materialRadios = document.querySelectorAll('input[name="material"]');

            calculateBtn.addEventListener('click', function() {
                let selectedMaterial;
                for (const radio of materialRadios) {
                    if (radio.checked) {
                        selectedMaterial = radio.value;
                        break;
                    }
                }
                const weight = parseFloat(weightInput.value);
                calculateValue(selectedMaterial, weight, resultValue);
            });

            resetBtn.addEventListener('click', function() {
                weightInput.value = '';
                resultValue.textContent = '₱0.00';
            });

            // Modal calculator
            const modalCalculateBtn = document.getElementById('modal-calculate-btn');
            const modalWeightInput = document.getElementById('modal-weight');
            const modalResultValue = document.getElementById('modal-result-value');
            const modalMaterialInput = document.getElementById('modal-material');

            modalCalculateBtn.addEventListener('click', function() {
                const material = modalMaterialInput.value.toLowerCase();
                const weight = parseFloat(modalWeightInput.value);
                calculateValue(material, weight, modalResultValue);
            });

            // Mobile Menu Toggle
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const mobileMenu = document.querySelector('.mobile-menu');
            
            mobileMenuBtn.addEventListener('click', function() {
                mobileMenu.classList.toggle('active');
                this.querySelector('i').classList.toggle('fa-times');
                this.querySelector('i').classList.toggle('fa-bars');
            });

            // FAQ Accordion
            const faqQuestions = document.querySelectorAll('.faq-question');
            
            faqQuestions.forEach(question => {
                question.addEventListener('click', function() {
                    const answer = this.nextElementSibling;
                    const icon = this.querySelector('i');
                    
                    // Close all other answers first
                    document.querySelectorAll('.faq-answer').forEach(item => {
                        if (item !== answer && item.style.maxHeight) {
                            item.style.maxHeight = null;
                            item.previousElementSibling.querySelector('i').classList.remove('fa-chevron-up');
                            item.previousElementSibling.querySelector('i').classList.add('fa-chevron-down');
                        }
                    });
                    
                    // Toggle current answer
                    if (answer.style.maxHeight) {
                        answer.style.maxHeight = null;
                        icon.classList.remove('fa-chevron-up');
                        icon.classList.add('fa-chevron-down');
                    } else {
                        answer.style.maxHeight = answer.scrollHeight + 'px';
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-up');
                    }
                });
            });

            // Catalog Filter
            const filterButtons = document.querySelectorAll('.filter-btn');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    const filterValue = this.getAttribute('data-filter');
                    const catalogItems = document.querySelectorAll('.catalog-item');
                    
                    catalogItems.forEach(item => {
                        if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });

            // Testimonial Slider
            let currentTestimonial = 0;
            const testimonials = document.querySelectorAll('.testimonial');
            const dots = document.querySelectorAll('.dot');
            
            function showTestimonial(index) {
                testimonials.forEach(testimonial => {
                    testimonial.style.display = 'none';
                });
                
                dots.forEach(dot => {
                    dot.classList.remove('active');
                });
                
                testimonials[index].style.display = 'block';
                dots[index].classList.add('active');
                currentTestimonial = index;
            }
            
            document.querySelector('.testimonial-next').addEventListener('click', function() {
                let nextIndex = (currentTestimonial + 1) % testimonials.length;
                showTestimonial(nextIndex);
            });
            
            document.querySelector('.testimonial-prev').addEventListener('click', function() {
                let prevIndex = (currentTestimonial - 1 + testimonials.length) % testimonials.length;
                showTestimonial(prevIndex);
            });
            
            dots.forEach((dot, index) => {
                dot.addEventListener('click', function() {
                    showTestimonial(index);
                });
            });
            
            // Initialize first testimonial
            showTestimonial(0);

            // Stats Counter Animation
            const statNumbers = document.querySelectorAll('.stat-number');
            
            function animateStats() {
                statNumbers.forEach(stat => {
                    const target = parseInt(stat.getAttribute('data-count'));
                    const duration = 2000; // 2 seconds
                    const increment = target / (duration / 16); // 60fps
                    let current = 0;
                    
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            clearInterval(timer);
                            current = target;
                        }
                        stat.textContent = Math.floor(current);
                    }, 16);
                });
            }
            
            // Animate stats when they come into view
            const statsObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateStats();
                        statsObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });
            
            document.querySelector('.stats-container').querySelectorAll('.stat-number').forEach(stat => {
                statsObserver.observe(stat);
            });

            // Back to Top Button
            const backToTopBtn = document.getElementById('back-to-top');
            
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopBtn.style.display = 'block';
                } else {
                    backToTopBtn.style.display = 'none';
                }
            });
            
            backToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Scroll Progress Indicator
            const scrollProgress = document.querySelector('.scroll-progress');
            
            window.addEventListener('scroll', function() {
                const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
                const scrolled = (window.pageYOffset / scrollHeight) * 100;
                scrollProgress.style.width = scrolled + '%';
            });

            // Initialize Price Chart
            const ctx = document.getElementById('priceChart').getContext('2d');
            const priceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                    datasets: [
                        {
                            label: 'Copper Wire (₱/kg)',
                            data: [270, 275, 280, 285.7, 290, 288, 285],
                            borderColor: '#D97A41',
                            backgroundColor: 'rgba(217, 122, 65, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            pointBackgroundColor: '#D97A41',
                            pointRadius: 4,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'PET Bottles (₱/kg)',
                            data: [25, 26, 27, 28.57, 29, 28.5, 28],
                            borderColor: '#6A7F46',
                            backgroundColor: 'rgba(106, 127, 70, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            pointBackgroundColor: '#6A7F46',
                            pointRadius: 4,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Aluminum Cans (₱/kg)',
                            data: [65, 66, 67, 68.57, 69, 68.5, 68],
                            borderColor: '#3C342C',
                            backgroundColor: 'rgba(60, 52, 44, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            pointBackgroundColor: '#3C342C',
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });

            // Trend Period Selector
            const trendButtons = document.querySelectorAll('.trend-btn');
            
            trendButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    trendButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    const period = this.getAttribute('data-period');
                    
                    // Update chart data based on period
                    if (period === 'monthly') {
                        priceChart.data.labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'];
                        priceChart.data.datasets[0].data = [270, 275, 280, 285.7, 290, 288, 285];
                        priceChart.data.datasets[1].data = [25, 26, 27, 28.57, 29, 28.5, 28];
                        priceChart.data.datasets[2].data = [65, 66, 67, 68.57, 69, 68.5, 68];
                    } else if (period === 'quarterly') {
                        priceChart.data.labels = ['Q1 2024', 'Q2 2024', 'Q3 2024', 'Q4 2024', 'Q1 2025'];
                        priceChart.data.datasets[0].data = [275, 282, 285, 280, 285.7];
                        priceChart.data.datasets[1].data = [26, 27.5, 28, 27, 28.57];
                        priceChart.data.datasets[2].data = [66, 67.5, 68, 67, 68.57];
                    } else if (period === 'yearly') {
                        priceChart.data.labels = ['2020', '2021', '2022', '2023', '2024', '2025'];
                        priceChart.data.datasets[0].data = [220, 235, 250, 265, 280, 285.7];
                        priceChart.data.datasets[1].data = [20, 22, 24, 26, 27.5, 28.57];
                        priceChart.data.datasets[2].data = [55, 58, 60, 63, 66, 68.57];
                    }
                    
                    priceChart.update();
                });
            });

            // Modal functionality
            const calculatorModal = document.getElementById('calculator-modal');
            const quickViewModal = document.getElementById('quick-view-modal');
            const closeModalButtons = document.querySelectorAll('.close-modal');
            const quickViewButtons = document.querySelectorAll('.quick-view-btn');
            const catalogCalculateButtons = document.querySelectorAll('.catalog-grid .item-button');
            
            // Modal open/close functions
            function openModal(modal) {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
            
            function closeModal(modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
            
            // Close modals when clicking X
            closeModalButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    closeModal(modal);
                });
            });
            
            // Close modals when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target.classList.contains('modal')) {
                    closeModal(event.target);
                }
            });
            
            // Quick View functionality
            quickViewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const item = this.getAttribute('data-item');
                    const itemElement = this.closest('.catalog-item');
                    
                    // Set modal content based on item
                    document.getElementById('modal-item-title').textContent = itemElement.querySelector('h3').textContent;
                    document.getElementById('modal-item-price').textContent = itemElement.querySelector('.price').textContent;
                    document.getElementById('modal-item-rating').innerHTML = itemElement.querySelector('.rating').innerHTML;
                    document.getElementById('modal-item-tip').innerHTML = itemElement.querySelector('.recycling-tip').innerHTML;
                    document.getElementById('modal-item-image').src = itemElement.querySelector('img').src;
                    document.getElementById('modal-item-image').alt = itemElement.querySelector('img').alt;
                    
                    // Set the material for the calculator in the modal
                    document.querySelector('.quick-view-calculate-btn').setAttribute('data-item', item);
                    
                    openModal(quickViewModal);
                });
            });
            
            // Catalog Calculate buttons
            catalogCalculateButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const item = this.getAttribute('data-item');
                    const itemName = this.closest('.catalog-item').querySelector('h3').textContent;
                    
                    // Set modal content
                    document.getElementById('modal-material').value = itemName;
                    
                    // Store the item type in a data attribute for calculation
                    document.getElementById('modal-calculate-btn').setAttribute('data-item', item);
                    
                    openModal(calculatorModal);
                });
            });
            
            // Quick View modal calculate button
            document.querySelector('.quick-view-calculate-btn').addEventListener('click', function() {
                const item = this.getAttribute('data-item');
                const itemName = document.getElementById('modal-item-title').textContent;
                
                // Set calculator modal content
                document.getElementById('modal-material').value = itemName;
                document.getElementById('modal-calculate-btn').setAttribute('data-item', item);
                
                closeModal(quickViewModal);
                openModal(calculatorModal);
            });

            // Animate elements when they come into view
            const animateOnScroll = () => {
                const elements = document.querySelectorAll('.animate__animated');
                elements.forEach(element => {
                    const elementPosition = element.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.3;
                    
                    if (elementPosition < screenPosition) {
                        element.style.opacity = '1';
                        element.classList.add('animate__fadeIn');
                    }
                });
            };

            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // Run once on load
        });
       

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // ... all your existing JavaScript code ...
        });
    </script>
</body>
</html>