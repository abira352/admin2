<?php
session_start();

// Simple authentication check
if (!isset($_SESSION['admin_logged_in'])) {
    $_SESSION['admin_logged_in'] = true; // For demo purposes
}

// Database connection (you'll need to configure this)
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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $image_name = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $image_name = handleImageUpload($_FILES['image']);
                }
                $stmt = $pdo->prepare("INSERT INTO products (name, price, description, image) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_POST['name'], $_POST['price'], $_POST['description'], $image_name]);
                $message = "Product added successfully!";
                break;
            
            case 'edit':
                $image_name = $_POST['current_image']; // Keep current image by default
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $image_name = handleImageUpload($_FILES['image']);
                    // Delete old image if exists
                    if ($_POST['current_image'] && file_exists('uploads/' . $_POST['current_image'])) {
                        unlink('uploads/' . $_POST['current_image']);
                    }
                }
                $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ? WHERE id = ?");
                $stmt->execute([$_POST['name'], $_POST['price'], $_POST['description'], $image_name, $_POST['id']]);
                $message = "Product updated successfully!";
                break;
            
            case 'delete':
                // Get image name before deleting
                $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Delete image file
                if ($product && $product['image'] && file_exists('uploads/' . $product['image'])) {
                    unlink('uploads/' . $product['image']);
                }
                
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = "Product deleted successfully!";
                break;
        }
    }
}

// Function to handle image upload
function handleImageUpload($file) {
    $upload_dir = 'uploads/';
    
    // Create uploads directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.');
    }
    
    if ($file['size'] > $max_size) {
        throw new Exception('File size too large. Maximum 5MB allowed.');
    }
    
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $new_filename;
    } else {
        throw new Exception('Failed to upload image.');
    }
}

// Get all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get single product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
}

$current_page = isset($_GET['page']) ? $_GET['page'] : 'view';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Product Management</title>
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
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            color: white;
            text-align: center;
            margin-bottom: 20px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .nav-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .nav-link {
            padding: 12px 25px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
            border: 2px solid transparent;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.4);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .content {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }

        .btn-warning {
            background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .product-name {
            font-size: 1.2em;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .product-price {
            font-size: 1.1em;
            color: #667eea;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .product-description {
            color: #666;
            margin-bottom: 15px;
        }

        .product-actions {
            display: flex;
            gap: 10px;
        }

        .message {
            padding: 15px;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            color: #155724;
            margin-bottom: 20px;
        }

        .view-site-link {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.9);
            color: #667eea;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .view-site-link:hover {
            background: white;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .nav-links {
                flex-direction: column;
                align-items: center;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <a href="add.php" class="view-site-link" target="_blank">View Site</a>
    
    <div class="container">
        <div class="header">
            <h1>Admin Panel</h1>
            <div class="nav-links">
                <a href="?page=add" class="nav-link <?php echo $current_page == 'add' ? 'active' : ''; ?>"> Add Product</a>
                <a href="?page=view" class="nav-link <?php echo $current_page == 'view' ? 'active' : ''; ?>"> View Products</a>
                <a href="?page=manage" class="nav-link <?php echo $current_page == 'manage' ? 'active' : ''; ?>"> Manage Products</a>
            </div>
        </div>

        <div class="content">
            <?php if (isset($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($current_page == 'add' || $edit_product): ?>
                <h2><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $edit_product ? 'edit' : 'add'; ?>">
                    <?php if ($edit_product): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
                        <input type="hidden" name="current_image" value="<?php echo $edit_product['image']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Product Name:</label>
                        <input type="text" name="name" value="<?php echo $edit_product ? $edit_product['name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Price:</label>
                        <input type="number" step="0.01" name="price" value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description" rows="4" required><?php echo $edit_product ? $edit_product['description'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Choose Image:</label>
                        <input type="file" name="image" accept="image/*" <?php echo $edit_product ? '' : 'required'; ?>>
                        <?php if ($edit_product && $edit_product['image']): ?>
                            <div style="margin-top: 10px;">
                                <p>Current Image:</p>
                                <img src="uploads/<?php echo $edit_product['image']; ?>" alt="Current Image" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn"><?php echo $edit_product ? 'Update' : 'Add'; ?> Product</button>
                    <?php if ($edit_product): ?>
                        <a href="?page=manage" class="btn btn-warning">Cancel</a>
                    <?php endif; ?>
                </form>

            <?php elseif ($current_page == 'view'): ?>
                <h2>All Products</h2>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-image"
                                 onerror="this.src='https://via.placeholder.com/300x200/667eea/white?text=No+Image'">
                            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                            <div class="product-description"><?php echo htmlspecialchars($product['description']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php elseif ($current_page == 'manage'): ?>
                <h2>Manage Products</h2>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-image"
                                 onerror="this.src='https://via.placeholder.com/300x200/667eea/white?text=No+Image'">
                            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                            <div class="product-description"><?php echo htmlspecialchars($product['description']); ?></div>
                            <div class="product-actions">
                                <a href="?page=edit&edit=<?php echo $product['id']; ?>" class="btn btn-warning">Edit</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>