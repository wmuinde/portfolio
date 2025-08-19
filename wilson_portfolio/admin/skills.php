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
                $name = trim($_POST['name'] ?? '');
                $level = (int)($_POST['level'] ?? 50);
                $category = trim($_POST['category'] ?? '');
                $skill_id = $_POST['skill_id'] ?? null;
                
                if (empty($name)) {
                    $error = "Skill name is required.";
                } else {
                    if ($action === 'add') {
                        $sql = "INSERT INTO skills (name, level, category) VALUES (?, ?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$name, $level, $category]);
                        $message = "Skill added successfully!";
                    } else {
                        $sql = "UPDATE skills SET name = ?, level = ?, category = ? WHERE id = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$name, $level, $category, $skill_id]);
                        $message = "Skill updated successfully!";
                    }
                }
            }
        }
    }
    
    // Handle delete request
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM skills WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Skill deleted successfully!";
    }
    
    // Get skills
    $stmt = $pdo->query("SELECT * FROM skills ORDER BY category, name");
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get skill for editing
    $editing_skill = null;
    if (isset($_GET['edit'])) {
        $id = (int)$_GET['edit'];
        $stmt = $pdo->prepare("SELECT * FROM skills WHERE id = ?");
        $stmt->execute([$id]);
        $editing_skill = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get unique categories
    $stmt = $pdo->query("SELECT DISTINCT category FROM skills WHERE category IS NOT NULL AND category != '' ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Skills - Admin</title>
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
                    <h3><i class="bi bi-gear"></i> Manage Skills</h3>
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
                        <a class="nav-link" href="projects.php">
                            <i class="bi bi-folder"></i> Projects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="skills.php">
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

                    <!-- Add/Edit Skill Form -->
                    <div class="card admin-card mb-4">
                        <div class="card-header">
                            <h5>
                                <i class="bi bi-plus-circle"></i>
                                <?php echo $editing_skill ? 'Edit Skill' : 'Add New Skill'; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="<?php echo $editing_skill ? 'edit' : 'add'; ?>">
                                <?php if ($editing_skill): ?>
                                    <input type="hidden" name="skill_id" value="<?php echo $editing_skill['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Skill Name *</label>
                                            <input type="text" class="form-control" id="name" name="name" required
                                                   value="<?php echo htmlspecialchars($editing_skill['name'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="category" class="form-label">Category</label>
                                            <input type="text" class="form-control" id="category" name="category" list="categories"
                                                   placeholder="e.g., Frontend, Backend, Database"
                                                   value="<?php echo htmlspecialchars($editing_skill['category'] ?? ''); ?>">
                                            <datalist id="categories">
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo htmlspecialchars($category); ?>">
                                                <?php endforeach; ?>
                                            </datalist>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="level" class="form-label">Proficiency Level (%)</label>
                                            <div class="input-group">
                                                <input type="range" class="form-range" id="level-range" name="level" 
                                                       min="0" max="100" step="5" 
                                                       value="<?php echo $editing_skill['level'] ?? 50; ?>"
                                                       oninput="updateLevelValue(this.value)">
                                                <input type="number" class="form-control" id="level" name="level" 
                                                       min="0" max="100" style="max-width: 80px;"
                                                       value="<?php echo $editing_skill['level'] ?? 50; ?>"
                                                       oninput="updateLevelRange(this.value)">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i>
                                        <?php echo $editing_skill ? 'Update Skill' : 'Add Skill'; ?>
                                    </button>
                                    <?php if ($editing_skill): ?>
                                        <a href="skills.php" class="btn btn-secondary">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Skills List -->
                    <div class="card admin-card">
                        <div class="card-header">
                            <h5><i class="bi bi-list"></i> All Skills (<?php echo count($skills); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($skills)): ?>
                                <p class="text-muted text-center py-4">No skills added yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Skill Name</th>
                                                <th>Category</th>
                                                <th>Proficiency</th>
                                                <th>Added</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($skills as $skill): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($skill['name']); ?></strong>
                                                </td>
                                                <td>
                                                    <?php if ($skill['category']): ?>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($skill['category']); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Uncategorized</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar" role="progressbar" 
                                                             style="width: <?php echo $skill['level']; ?>%" 
                                                             aria-valuenow="<?php echo $skill['level']; ?>" 
                                                             aria-valuemin="0" aria-valuemax="100">
                                                            <?php echo $skill['level']; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($skill['created_at'])); ?></td>
                                                <td>
                                                    <a href="?edit=<?php echo $skill['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button onclick="confirmDelete('skill', <?php echo $skill['id']; ?>)" class="btn btn-sm btn-outline-danger">
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
                window.location.href = `skills.php?delete=${id}`;
            }
        }
        
        function updateLevelValue(value) {
            document.getElementById('level').value = value;
        }
        
        function updateLevelRange(value) {
            document.getElementById('level-range').value = value;
        }
    </script>
</body>
</html>
