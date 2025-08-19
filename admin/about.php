<?php
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

try {
    $pdo = getConnection();
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = trim($_POST['name'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $location = trim($_POST['location'] ?? '');
        
        // Handle profile image upload
        $profile_image = '';
        $update_image = false;
        
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $profile_image = 'profile_' . uniqid() . '.' . $file_extension;
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir . $profile_image)) {
                    $update_image = true;
                } else {
                    $error = "Failed to upload profile image.";
                }
            } else {
                $error = "Invalid file format. Only JPG, PNG, GIF, and WebP are allowed.";
            }
        }
        
        if (empty($error)) {
            // Check if about record exists
            $stmt = $pdo->query("SELECT id FROM about WHERE id = 1");
            $about_exists = $stmt->fetch();
            
            if ($about_exists) {
                // Update existing record
                $sql = "UPDATE about SET name = ?, title = ?, bio = ?, email = ?, phone = ?, location = ?";
                $params = [$name, $title, $bio, $email, $phone, $location];
                
                if ($update_image) {
                    $sql .= ", profile_image = ?";
                    $params[] = $profile_image;
                }
                
                $sql .= " WHERE id = 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $message = "About information updated successfully!";
            } else {
                // Insert new record
                $sql = "INSERT INTO about (id, name, title, bio, email, phone, location, profile_image) VALUES (1, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $title, $bio, $email, $phone, $location, $profile_image]);
                $message = "About information created successfully!";
            }
        }
    }
    
    // Get current about information
    $stmt = $pdo->query("SELECT * FROM about WHERE id = 1");
    $about = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage About Me - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h3><i class="bi bi-person"></i> Manage About Me</h3>
                </div>
                <div class="col-auto">
                    <a href="../index.php" class="btn btn-outline-light btn-sm me-2" target="_blank">
                        <i class="bi bi-eye"></i> View Site
                    </a>
                    <a href="logout.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 admin-sidebar">
                <ul class="nav flex-column admin-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">
                            <i class="bi bi-person"></i> About Me
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="projects.php">
                            <i class="bi bi-folder"></i> Projects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="skills.php">
                            <i class="bi bi-gear"></i> Skills
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">
                            <i class="bi bi-envelope"></i> Messages
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="py-4">
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- About Me Form -->
                    <div class="card admin-card">
                        <div class="card-header">
                            <h5>
                                <i class="bi bi-person-fill"></i>
                                Personal Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="name" class="form-label">Full Name *</label>
                                                    <input type="text" class="form-control" id="name" name="name" required
                                                           value="<?php echo htmlspecialchars($about['name'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="title" class="form-label">Professional Title</label>
                                                    <input type="text" class="form-control" id="title" name="title"
                                                           placeholder="e.g., Full Stack Developer"
                                                           value="<?php echo htmlspecialchars($about['title'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="bio" class="form-label">Bio/Description</label>
                                            <textarea class="form-control" id="bio" name="bio" rows="4"
                                                      placeholder="Tell visitors about yourself, your experience, skills, and interests..."><?php echo htmlspecialchars($about['bio'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="profile_image" class="form-label">Profile Image</label>
                                            <input type="file" class="form-control" id="profile_image" name="profile_image" 
                                                   accept="image/*" onchange="previewImage(this)">
                                            <small class="form-text text-muted">Accepted formats: JPG, PNG, GIF, WebP</small>
                                            
                                            <?php if ($about && !empty($about['profile_image'])): ?>
                                                <div class="mt-2">
                                                    <img src="../uploads/<?php echo htmlspecialchars($about['profile_image']); ?>" 
                                                         class="img-thumbnail" style="max-width: 200px; max-height: 200px;" 
                                                         alt="Current profile image" id="current-image">
                                                </div>
                                            <?php endif; ?>
                                            
                                            <img id="image-preview" class="img-thumbnail mt-2" style="max-width: 200px; max-height: 200px; display: none;" alt="Image preview">
                                        </div>
                                    </div>
                                </div>
                                
                                <h6 class="mt-4 mb-3"><i class="bi bi-envelope"></i> Contact Information</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                   value="<?php echo htmlspecialchars($about['email'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" id="phone" name="phone"
                                                   value="<?php echo htmlspecialchars($about['phone'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="location" class="form-label">Location</label>
                                            <input type="text" class="form-control" id="location" name="location"
                                                   placeholder="e.g., New York, USA"
                                                   value="<?php echo htmlspecialchars($about['location'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div>
                                        <?php if ($about): ?>
                                            <small class="text-muted">
                                                <i class="bi bi-clock"></i> 
                                                Last updated: <?php echo date('M j, Y \a\t g:i A', strtotime($about['updated_at'])); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i>
                                        <?php echo $about ? 'Update Information' : 'Save Information'; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Preview Card -->
                    <?php if ($about): ?>
                    <div class="card admin-card mt-4">
                        <div class="card-header">
                            <h5><i class="bi bi-eye"></i> Preview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php if (!empty($about['profile_image'])): ?>
                                <div class="col-md-3 text-center">
                                    <img src="../uploads/<?php echo htmlspecialchars($about['profile_image']); ?>" 
                                         class="img-fluid rounded-circle" style="max-width: 150px;" 
                                         alt="Profile">
                                </div>
                                <div class="col-md-9">
                                <?php else: ?>
                                <div class="col-12">
                                <?php endif; ?>
                                    <h4><?php echo htmlspecialchars($about['name']); ?></h4>
                                    <?php if ($about['title']): ?>
                                        <p class="text-primary fs-5"><?php echo htmlspecialchars($about['title']); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($about['bio']): ?>
                                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($about['bio'])); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex flex-wrap gap-3 mt-3">
                                        <?php if ($about['email']): ?>
                                            <div><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($about['email']); ?></div>
                                        <?php endif; ?>
                                        <?php if ($about['phone']): ?>
                                            <div><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($about['phone']); ?></div>
                                        <?php endif; ?>
                                        <?php if ($about['location']): ?>
                                            <div><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($about['location']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/script.js"></script>
    <script>
        function previewImage(input) {
            const preview = document.getElementById('image-preview');
            const currentImage = document.getElementById('current-image');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    if (currentImage) {
                        currentImage.style.display = 'none';
                    }
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
                if (currentImage) {
                    currentImage.style.display = 'block';
                }
            }
        }
    </script>
</body>
</html>
