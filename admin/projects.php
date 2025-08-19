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
    
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['action'])) {
            $action = $_POST['action'];
            
            if ($action === 'add' || $action === 'edit') {
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $technologies = trim($_POST['technologies'] ?? '');
                $demo_url = trim($_POST['demo_url'] ?? '');
                $github_url = trim($_POST['github_url'] ?? '');
                $project_id = $_POST['project_id'] ?? null;
                
                // Handle image upload
                $image_filename = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $image_filename = uniqid() . '.' . $file_extension;
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_filename)) {
                            // File uploaded successfully
                        } else {
                            $error = "Failed to upload image.";
                        }
                    } else {
                        $error = "Invalid file format. Only JPG, PNG, GIF, and WebP are allowed.";
                    }
                }
                
                if (empty($error)) {
                    if ($action === 'add') {
                        $sql = "INSERT INTO projects (title, description, image, technologies, demo_url, github_url) VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$title, $description, $image_filename, $technologies, $demo_url, $github_url]);
                        $message = "Project added successfully!";
                    } else {
                        $sql = "UPDATE projects SET title = ?, description = ?, technologies = ?, demo_url = ?, github_url = ?";
                        $params = [$title, $description, $technologies, $demo_url, $github_url];
                        
                        if (!empty($image_filename)) {
                            $sql .= ", image = ?";
                            $params[] = $image_filename;
                        }
                        
                        $sql .= " WHERE id = ?";
                        $params[] = $project_id;
                        
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);
                        $message = "Project updated successfully!";
                    }
                }
            }
        }
    }
    
    // Handle delete request
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Project deleted successfully!";
    }
    
    // Get projects
    $stmt = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get project for editing
    $editing_project = null;
    if (isset($_GET['edit'])) {
        $id = (int)$_GET['edit'];
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $editing_project = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Projects - Admin</title>
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
                    <h3><i class="bi bi-folder"></i> Manage Projects</h3>
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
                        <a class="nav-link" href="about.php">
                            <i class="bi bi-person"></i> About Me
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="projects.php">
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

                    <!-- Add/Edit Project Form -->
                    <div class="card admin-card mb-4">
                        <div class="card-header">
                            <h5>
                                <i class="bi bi-plus-circle"></i>
                                <?php echo $editing_project ? 'Edit Project' : 'Add New Project'; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="<?php echo $editing_project ? 'edit' : 'add'; ?>">
                                <?php if ($editing_project): ?>
                                    <input type="hidden" name="project_id" value="<?php echo $editing_project['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Title *</label>
                                            <input type="text" class="form-control" id="title" name="title" required
                                                   value="<?php echo htmlspecialchars($editing_project['title'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="technologies" class="form-label">Technologies</label>
                                            <input type="text" class="form-control" id="technologies" name="technologies"
                                                   placeholder="e.g., PHP, MySQL, JavaScript"
                                                   value="<?php echo htmlspecialchars($editing_project['technologies'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description *</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($editing_project['description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="demo_url" class="form-label">Demo URL</label>
                                            <input type="url" class="form-control" id="demo_url" name="demo_url"
                                                   value="<?php echo htmlspecialchars($editing_project['demo_url'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="github_url" class="form-label">GitHub URL</label>
                                            <input type="url" class="form-control" id="github_url" name="github_url"
                                                   value="<?php echo htmlspecialchars($editing_project['github_url'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="image" class="form-label">Project Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                    <small class="form-text text-muted">Accepted formats: JPG, PNG, GIF, WebP</small>
                                    <?php if ($editing_project && !empty($editing_project['image'])): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars($editing_project['image']); ?>" 
                                             class="image-preview mt-2" alt="Current image">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i>
                                        <?php echo $editing_project ? 'Update Project' : 'Add Project'; ?>
                                    </button>
                                    <?php if ($editing_project): ?>
                                        <a href="projects.php" class="btn btn-secondary">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Projects List -->
                    <div class="card admin-card">
                        <div class="card-header">
                            <h5><i class="bi bi-list"></i> All Projects (<?php echo count($projects); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($projects)): ?>
                                <p class="text-muted text-center py-4">No projects added yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Title</th>
                                                <th>Technologies</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($projects as $project): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($project['image'])): ?>
                                                        <img src="../uploads/<?php echo htmlspecialchars($project['image']); ?>" 
                                                             alt="<?php echo htmlspecialchars($project['title']); ?>"
                                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                                    <?php else: ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center"
                                                             style="width: 50px; height: 50px; border-radius: 5px;">
                                                            <i class="bi bi-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($project['title']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo substr(htmlspecialchars($project['description']), 0, 100); ?>...</small>
                                                </td>
                                                <td><?php echo htmlspecialchars($project['technologies']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($project['created_at'])); ?></td>
                                                <td>
                                                    <a href="?edit=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button onclick="confirmDelete('project', <?php echo $project['id']; ?>)" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/script.js"></script>
    <script>
        function confirmDelete(type, id) {
            if (confirm(`Are you sure you want to delete this ${type}?`)) {
                window.location.href = `projects.php?delete=${id}`;
            }
        }
    </script>
</body>
</html>
