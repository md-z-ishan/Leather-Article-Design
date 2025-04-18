<!-- Header -->
<header class="header">
    <div class="container">
        <div class="logo">
            <a href="index.php">Leather Design Hub</a>
        </div>
        <nav class="nav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="designers.php">Designers</a></li>
                <li><a href="custom-orders.php">Custom Orders</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<style>
    .header {
        background-color: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        padding: 15px 0;
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .logo a {
        font-size: 1.5em;
        font-weight: bold;
        color: #8B4513;
        text-decoration: none;
    }

    .nav ul {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .nav ul li {
        margin-left: 20px;
    }

    .nav ul li a {
        color: #333;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s;
    }

    .nav ul li a:hover {
        color: #8B4513;
    }

    .nav ul li a i {
        margin-right: 5px;
    }

    @media (max-width: 768px) {
        .container {
            flex-direction: column;
            padding: 10px;
        }

        .logo {
            margin-bottom: 10px;
        }

        .nav ul {
            flex-wrap: wrap;
            justify-content: center;
        }

        .nav ul li {
            margin: 5px 10px;
        }
    }
</style>