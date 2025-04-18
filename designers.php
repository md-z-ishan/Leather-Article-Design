<?php
require_once 'config/db.php';
session_start();

// Get designers from database
$query = "SELECT u.id, u.username, u.email, u.profile_image, u.bio, COUNT(p.id) as product_count 
          FROM users u 
          LEFT JOIN products p ON u.id = p.designer_id 
          WHERE u.role = 'designer' 
          GROUP BY u.id, u.username, u.email, u.profile_image, u.bio 
          ORDER BY u.username ASC";
$designers = $pdo->query($query)->fetchAll();

// If no designers found, use sample data
if (empty($designers)) {
    $designers = [
        [
            'id' => 1,
            'username' => 'John Smith',
            'email' => 'john@example.com',
            'profile_image' => 'designer-1.jpg',
            'bio' => 'Specializing in handcrafted leather wallets and accessories with over 10 years of experience.',
            'product_count' => 15
        ],
        [
            'id' => 2,
            'username' => 'Sarah Johnson',
            'email' => 'sarah@example.com',
            'profile_image' => 'designer-2.jpg',
            'bio' => 'Expert in creating unique leather bags and backpacks with a modern twist.',
            'product_count' => 12
        ],
        [
            'id' => 3,
            'username' => 'Michael Brown',
            'email' => 'michael@example.com',
            'profile_image' => 'designer-3.jpg',
            'bio' => 'Master craftsman specializing in premium leather goods with traditional techniques.',
            'product_count' => 18
        ],
        [
            'id' => 4,
            'username' => 'Emma Wilson',
            'email' => 'emma@example.com',
            'profile_image' => 'designer-4.jpg',
            'bio' => 'Contemporary leather designer focusing on minimalist and functional designs.',
            'product_count' => 10
        ],
        [
            'id' => 5,
            'username' => 'David Lee',
            'email' => 'david@example.com',
            'profile_image' => 'designer-5.jpg',
            'bio' => 'Innovative leather artist creating unique statement pieces and custom designs.',
            'product_count' => 14
        ],
        [
            'id' => 6,
            'username' => 'Lisa Chen',
            'email' => 'lisa@example.com',
            'profile_image' => 'designer-6.jpg',
            'bio' => 'Specializing in luxury leather goods with attention to detail and quality.',
            'product_count' => 16
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Designers - Leather Design Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .designers-header {
            padding: 80px 0;
            background: linear-gradient(rgba(139, 69, 19, 0.05), rgba(139, 69, 19, 0.1));
            text-align: center;
            margin-bottom: 40px;
        }

        .designers-header h1 {
            font-size: 2.8em;
            color: #8B4513;
            margin-bottom: 20px;
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .designers-header p {
            font-size: 1.2em;
            color: #666;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .designers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 40px;
            padding: 0 20px 60px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .designer-card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
        }

        .designer-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(139, 69, 19, 0.2);
        }

        .designer-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-bottom: 3px solid #8B4513;
        }

        .designer-info {
            padding: 25px;
            background: linear-gradient(to bottom, #fff, #f9f9f9);
        }

        .designer-info h3 {
            font-size: 1.8em;
            color: #8B4513;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .designer-info p {
            color: #555;
            margin-bottom: 20px;
            line-height: 1.8;
            font-size: 1.1em;
        }

        .designer-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-top: 1px solid rgba(139, 69, 19, 0.2);
            margin-top: 15px;
        }

        .designer-stats span {
            color: #8B4513;
            font-weight: 500;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .designer-stats i {
            font-size: 1.2em;
        }

        .designer-contact {
            margin-top: 20px;
        }

        .designer-contact a {
            color: #fff;
            background-color: #8B4513;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .designer-contact a:hover {
            background-color: #6b350f;
            transform: translateY(-2px);
        }

        .designer-contact i {
            font-size: 1.1em;
        }

        @media (max-width: 768px) {
            .designers-header {
                padding: 60px 20px;
            }

            .designers-header h1 {
                font-size: 2.2em;
            }

            .designers-grid {
                grid-template-columns: 1fr;
                gap: 30px;
                padding: 20px;
            }

            .designer-image {
                height: 250px;
            }

            .designer-info h3 {
                font-size: 1.6em;
            }
        }

        @media (min-width: 1600px) {
            .designers-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="designers-header">
        <div class="container">
            <h1>Meet Our Designers</h1>
            <p>Discover the talented artisans behind our handcrafted leather products. Each designer brings their unique style and expertise to create exceptional pieces.</p>
        </div>
    </section>

    <section class="designers">
        <div class="container">
            <div class="designers-grid">
                <?php foreach($designers as $designer): ?>
                <div class="designer-card">
                    <img src="<?php echo file_exists('images/designers/' . $designer['profile_image']) ? 'images/designers/' . $designer['profile_image'] : 'https://via.placeholder.com/300x250?text=' . urlencode($designer['username']); ?>" 
                         alt="<?php echo htmlspecialchars($designer['username']); ?>" 
                         class="designer-image">
                    <div class="designer-info">
                        <h3><?php echo htmlspecialchars($designer['username']); ?></h3>
                        <p><?php echo htmlspecialchars($designer['bio'] ?? 'Talented leather artisan creating unique handcrafted pieces.'); ?></p>
                        <div class="designer-stats">
                            <span><i class="fas fa-box"></i> <?php echo $designer['product_count']; ?> Products</span>
                        </div>
                        <div class="designer-contact">
                            <a href="mailto:<?php echo htmlspecialchars($designer['email']); ?>">
                                <i class="fas fa-envelope"></i> Contact Designer
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>