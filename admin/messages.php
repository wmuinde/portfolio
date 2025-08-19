<?php
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';
$counts = ['total' => 0, 'unread' => 0, 'read' => 0];
$messages = [];
$viewing_message = null;

try {
    $pdo = getConnection();
    
    // Handle delete request
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Message deleted successfully!";
    }
    
    // Handle mark as read/unread
    if (isset($_GET['toggle_read'])) {
        $id = (int)$_GET['toggle_read'];
        $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = NOT is_read WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Message status updated!";
    }
    
    // Get search and filter parameters
    $search = $_GET['search'] ?? '';
    $filter_read = $_GET['filter_read'] ?? 'all';
    $sort = $_GET['sort'] ?? 'newest';
    
    // Build query based on filters
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_fill(0, 4, $searchTerm);
    }
    
    if ($filter_read !== 'all') {
        $where_conditions[] = "is_read = ?";
        $params[] = ($filter_read === 'read') ? 1 : 0;
    }
    
    $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);
    
    // Set sort order
    switch($sort) {
        case 'oldest':
            $sort_clause = 'ORDER BY created_at ASC';
            break;
        case 'name':
            $sort_clause = 'ORDER BY name ASC';
            break;
        case 'email':
            $sort_clause = 'ORDER BY email ASC';
            break;
        default:
            $sort_clause = 'ORDER BY created_at DESC';
            break;
    }
    
    // Get messages
    $sql = "SELECT * FROM contact_messages $where_clause $sort_clause";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get message counts
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN is_read = 0 THEN 1 END) as unread,
        COUNT(CASE WHEN is_read = 1 THEN 1 END) as `read`
        FROM contact_messages");
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get message for viewing details
    if (isset($_GET['view'])) {
        $id = (int)$_GET['view'];
        $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
        $viewing_message = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Mark as read if it wasn't already
        if ($viewing_message && !$viewing_message['is_read']) {
            $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
            $stmt->execute([$id]);
            $viewing_message['is_read'] = 1;
        }
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
    <title>Manage Messages - Admin</title>
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
                    <h3><i class="bi bi-envelope"></i> Manage Messages</h3>
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
                        <a class="nav-link" href="skills.php">
                            <i class="bi bi-gear"></i> Skills
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="messages.php">
                            <i class="bi bi-envelope"></i> Messages
                            <?php if ($counts['unread'] > 0): ?>
                                <span class="badge bg-danger rounded-pill"><?php echo $counts['unread']; ?></span>
                            <?php endif; ?>
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

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card admin-card text-center">
                                <div class="card-body">
                                    <i class="bi bi-envelope-fill display-4 text-primary"></i>
                                    <h3 class="mt-2"><?php echo $counts['total']; ?></h3>
                                    <p class="text-muted">Total Messages</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card admin-card text-center">
                                <div class="card-body">
                                    <i class="bi bi-envelope display-4 text-warning"></i>
                                    <h3 class="mt-2"><?php echo $counts['unread']; ?></h3>
                                    <p class="text-muted">Unread</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card admin-card text-center">
                                <div class="card-body">
                                    <i class="bi bi-envelope-check display-4 text-success"></i>
                                    <h3 class="mt-2"><?php echo $counts['read']; ?></h3>
                                    <p class="text-muted">Read</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($viewing_message): ?>
                    <!-- Message Detail View -->
                    <div class="card admin-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><i class="bi bi-envelope-open"></i> Message Details</h5>
                            <div>
                                <a href="messages.php" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-left"></i> Back to List
                                </a>
                                <button onclick="confirmDelete('message', <?php echo $viewing_message['id']; ?>)" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>From:</strong></td>
                                            <td><?php echo htmlspecialchars($viewing_message['name']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td><a href="mailto:<?php echo htmlspecialchars($viewing_message['email']); ?>"><?php echo htmlspecialchars($viewing_message['email']); ?></a></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Subject:</strong></td>
                                            <td><?php echo htmlspecialchars($viewing_message['subject'] ?: 'No subject'); ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Received:</strong></td>
                                            <td><?php echo date('M j, Y \a\t g:i A', strtotime($viewing_message['created_at'])); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <?php if ($viewing_message['is_read']): ?>
                                                    <span class="badge bg-success">Read</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Unread</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h6><strong>Message:</strong></h6>
                                <div class="bg-light p-3 rounded">
                                    <?php echo nl2br(htmlspecialchars($viewing_message['message'])); ?>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="mailto:<?php echo htmlspecialchars($viewing_message['email']); ?>?subject=Re: <?php echo htmlspecialchars($viewing_message['subject'] ?: 'Your message'); ?>" class="btn btn-primary">
                                    <i class="bi bi-reply"></i> Reply via Email
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Filters and Search -->
                    <div class="card admin-card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <?php if (isset($_GET['view'])): ?>
                                    <input type="hidden" name="view" value="<?php echo (int)$_GET['view']; ?>">
                                <?php endif; ?>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Search messages..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="filter_read">
                                        <option value="all" <?php echo $filter_read === 'all' ? 'selected' : ''; ?>>All Messages</option>
                                        <option value="unread" <?php echo $filter_read === 'unread' ? 'selected' : ''; ?>>Unread Only</option>
                                        <option value="read" <?php echo $filter_read === 'read' ? 'selected' : ''; ?>>Read Only</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="sort">
                                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                        <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                                        <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>By Name</option>
                                        <option value="email" <?php echo $sort === 'email' ? 'selected' : ''; ?>>By Email</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Messages List -->
                    <div class="card admin-card">
                        <div class="card-header">
                            <h5><i class="bi bi-list"></i> Messages (<?php echo count($messages); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($messages)): ?>
                                <p class="text-muted text-center py-4">
                                    <?php if (!empty($search) || $filter_read !== 'all'): ?>
                                        No messages found matching your criteria.
                                        <br><a href="messages.php" class="btn btn-sm btn-outline-primary mt-2">Clear Filters</a>
                                    <?php else: ?>
                                        No messages received yet.
                                    <?php endif; ?>
                                </p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Status</th>
                                                <th>From</th>
                                                <th>Subject</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($messages as $msg): ?>
                                            <tr class="<?php echo !$msg['is_read'] ? 'table-warning' : ''; ?>">
                                                <td>
                                                    <?php if ($msg['is_read']): ?>
                                                        <i class="bi bi-envelope-open text-success" title="Read"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-envelope-fill text-warning" title="Unread"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($msg['name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($msg['email']); ?></small>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($msg['subject'] ?: 'No subject'); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo substr(htmlspecialchars($msg['message']), 0, 100); ?>
                                                        <?php if (strlen($msg['message']) > 100): ?>...<?php endif; ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php echo date('M j, Y', strtotime($msg['created_at'])); ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo date('g:i A', strtotime($msg['created_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <a href="?view=<?php echo $msg['id']; ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="?toggle_read=<?php echo $msg['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Toggle Read Status">
                                                        <?php if ($msg['is_read']): ?>
                                                            <i class="bi bi-envelope"></i>
                                                        <?php else: ?>
                                                            <i class="bi bi-envelope-open"></i>
                                                        <?php endif; ?>
                                                    </a>
                                                    <button onclick="confirmDelete('message', <?php echo $msg['id']; ?>)" class="btn btn-sm btn-outline-danger" title="Delete">
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
                window.location.href = `messages.php?delete=${id}`;
            }
        }
    </script>
</body>
</html>
