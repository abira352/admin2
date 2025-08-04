<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crud";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to check if image exists and return appropriate path
function getImagePath($imagePath) {
    if (empty($imagePath)) {
        return 'https://via.placeholder.com/320x250/74b9ff/white?text=No+Image';
    }
    
    // Check if it's a URL
    if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
        return $imagePath;
    }
    
    // Check if it's a local file path
    if (file_exists($imagePath)) {
        return $imagePath;
    }
    
    // Check in uploads directory
    $uploadPath = 'uploads/' . basename($imagePath);
    if (file_exists($uploadPath)) {
        return $uploadPath;
    }
    
    // Default placeholder
    return 'https://via.placeholder.com/320x250/74b9ff/white?text=No+Image';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Store</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #d809f3ff 0%, #092ae3ff 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 40px 20px;
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header h1 {
            color: white;
            font-size: 3em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.2em;
            font-weight: 300;
        }

        .admin-link {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.9);
            color: #0984e3;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .admin-link:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .products-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
        }

        .products-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .products-header h2 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .products-header p {
            color: #666;
            font-size: 1.1em;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }

        .product-image-container {
            width: 100%;
            height: 250px;
            overflow: hidden;
            position: relative;
            background: #f8f9fa;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
            display: block;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .image-error {
            display: none;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2em;
            font-weight: 600;
        }

        .product-info {
            padding: 25px;
        }

        .product-name {
            font-size: 1.4em;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .product-price {
            font-size: 1.8em;
            color: #0984e3;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .product-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .product-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 25px;
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            flex: 1;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #0984e3;
            color: #0984e3;
        }

        .btn-outline:hover {
            background: #0984e3;
            color: white;
        }

        .no-products {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-products h3 {
            font-size: 2em;
            margin-bottom: 15px;
            color: #333;
        }

        .no-products p {
            font-size: 1.1em;
            margin-bottom: 30px;
        }

        .stats-bar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }

        .stats-bar span {
            color: white;
            font-size: 1.1em;
            font-weight: 600;
        }

        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #00b894;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.9em;
            font-weight: 600;
            z-index: 1;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2em;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-link {
                position: static;
                display: block;
                text-align: center;
                margin: 20px auto;
                width: fit-content;
            }
            
            .products-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="admin-link">Admin Panel</a>
    
    <div class="container">
        <div class="header">
            <h1>Product Store</h1>
            <p>Discover amazing products at great prices</p>
        </div>

        <div class="stats-bar">
            <span>Total Products: <?php echo count($products); ?></span>
        </div>

        <div class="products-container">
            <?php if (empty($products)): ?>
                <div class="no-products">
                    <h3>No Products Available</h3>
                    <p>It looks like there are no products in our store yet.</p>
                    <a href="admin.php" class="btn">Go to Admin Panel</a>
                </div>
            <?php else: ?>
                <div class="products-header">
                    <h2>Featured Products</h2>
                    <p>Check out our latest collection</p>
                </div>
                
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-badge">New</div>
                            <div class="product-image-container">
                                <img src="<?php echo htmlspecialchars(getImagePath($product['image'])); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="product-image"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="image-error">
                                    <?php echo htmlspecialchars(substr($product['name'], 0, 20)); ?>
                                </div>
                            </div>
                            
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                                <div class="product-description"><?php echo htmlspecialchars($product['description']); ?></div>
                                
                                <div class="product-actions">
                                    <button class="btn" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                                    <button class="btn btn-outline" onclick="viewDetails(<?php echo $product['id']; ?>)">View Details</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function addToCart(productId) {
            // Add to cart functionality
            alert('Product added to cart! (This is a demo - implement actual cart functionality)');
        }

        function viewDetails(productId) {
            // View details functionality
            alert('Viewing product details for ID: ' + productId + ' (This is a demo - implement actual details page)');
        }

        // Add some interactive animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.product-card');
            
            cards.forEach((card, index) => {
                card.style.animationDelay = (index * 0.1) + 's';
                card.style.animation = 'fadeInUp 0.6s ease forwards';
            });
        });

        // Add CSS animation keyframes
        const style = document.createElement('style');
        style.textContent = `
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
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>