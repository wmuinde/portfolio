<?php
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get dashboard statistics
try {
    $pdo = getConnection();
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM projects");
    $projectCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM skills");
    $skillCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages");
    $messageCount = $stmt->fetch()['count'];
    
    // Get recent messages
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
    $recentMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error loading dashboard data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Portfolio</title>
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
                    <h3><i class="bi bi-speedometer2"></i> Portfolio Admin Panel</h3>
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
                        <a class="nav-link active" href="index.php">
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
                    <h2>Dashboard</h2>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card admin-card text-center">
                                <div class="card-body">
                                    <i class="bi bi-folder display-4 text-primary"></i>
                                    <h3 class="mt-2"><?php echo $projectCount; ?></h3>
                                    <p class="text-muted">Projects</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card admin-card text-center">
                                <div class="card-body">
                                    <i class="bi bi-gear display-4 text-success"></i>
                                    <h3 class="mt-2"><?php echo $skillCount; ?></h3>
                                    <p class="text-muted">Skills</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card admin-card text-center">
                                <div class="card-body">
                                    <i class="bi bi-envelope display-4 text-warning"></i>
                                    <h3 class="mt-2"><?php echo $messageCount; ?></h3>
                                    <p class="text-muted">Messages</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card admin-card text-center">
                                <div class="card-body">
                                    <i class="bi bi-calendar display-4 text-info"></i>
                                    <h3 class="mt-2"><?php echo date('d'); ?></h3>
                                    <p class="text-muted"><?php echo date('M Y'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Messages -->
                    <div class="card admin-card">
                        <div class="card-header">
                            <h5><i class="bi bi-envelope"></i> Recent Messages</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentMessages)): ?>
                                <p class="text-muted">No messages yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Subject</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentMessages as $message): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($message['name']); ?></td>
                                                <td><?php echo htmlspecialchars($message['email']); ?></td>
                                                <td><?php echo htmlspecialchars($message['subject'] ?: 'No subject'); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($message['created_at'])); ?></td>
                                                <td>
                                                    <a href="messages.php#message-<?php echo $message['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-end mt-3">
                                    <a href="messages.php" class="btn btn-primary">View All Messages</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card admin-card">
                                <div class="card-header">
                                    <h5><i class="bi bi-lightning"></i> Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="projects.php?action=add" class="btn btn-primary">
                                            <i class="bi bi-plus-circle"></i> Add New Project
                                        </a>
                                        <a href="skills.php?action=add" class="btn btn-success">
                                            <i class="bi bi-plus-circle"></i> Add New Skill
                                        </a>
                                        <a href="about.php" class="btn btn-info">
                                            <i class="bi bi-pencil"></i> Update About Info
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card admin-card">
                                <div class="card-header">
                                    <h5><i class="bi bi-info-circle"></i> System Info</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                                    <p><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                                    <p><strong>Last Login:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/script.js"></script>
</body>
</html>
