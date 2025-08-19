<?php 
require_once 'config.php';

// Get about information
try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT * FROM about WHERE id = 1");
    $about = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get projects
    $stmt = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get skills
    $stmt = $pdo->query("SELECT * FROM skills ORDER BY category, level DESC");
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($about['name'] ?? 'Portfolio'); ?> - Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home"><?php echo htmlspecialchars($about['name'] ?? 'Portfolio'); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#skills">Skills</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#projects">Projects</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section d-flex align-items-center">
        <div class="container text-center text-white">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <?php if (!empty($about['profile_image'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($about['profile_image']); ?>" alt="Profile" class="profile-img mb-4">
                    <?php endif; ?>
                    <h1 class="display-4 mb-3"><?php echo htmlspecialchars($about['name'] ?? 'Your Name'); ?></h1>
                    <h2 class="h3 mb-4"><?php echo htmlspecialchars($about['title'] ?? 'Your Title'); ?></h2>
                    <p class="lead mb-4"><?php echo htmlspecialchars($about['bio'] ?? 'Your bio goes here.'); ?></p>
                    <a href="#contact" class="btn btn-primary btn-lg">Get In Touch</a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="section-title">About Me</h2>
                    <p class="section-subtitle">Get to know me better</p>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-lg-6">
                    <h4>Who Am I?</h4>
                    <p><?php echo nl2br(htmlspecialchars($about['bio'] ?? 'Tell us about yourself here.')); ?></p>
                </div>
                <div class="col-lg-6">
                    <h4>Contact Information</h4>
                    <ul class="list-unstyled contact-info">
                        <li><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($about['email'] ?? ''); ?></li>
                        <li><i class="bi bi-phone"></i> <?php echo htmlspecialchars($about['phone'] ?? ''); ?></li>
                        <li><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($about['location'] ?? ''); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Skills Section -->
    <section id="skills" class="section-padding bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="section-title">Skills</h2>
                    <p class="section-subtitle">My technical expertise</p>
                </div>
            </div>
            <div class="row mt-5">
                <?php 
                $categories = [];
                foreach ($skills as $skill) {
                    $categories[$skill['category']][] = $skill;
                }
                foreach ($categories as $category => $categorySkills): 
                ?>
                <div class="col-lg-4 mb-4">
                    <h5><?php echo htmlspecialchars($category); ?></h5>
                    <?php foreach ($categorySkills as $skill): ?>
                    <div class="skill-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span><?php echo htmlspecialchars($skill['name']); ?></span>
                            <span><?php echo $skill['level']; ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: <?php echo $skill['level']; ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section id="projects" class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="section-title">Projects</h2>
                    <p class="section-subtitle">Some of my recent work</p>
                </div>
            </div>
            <div class="row mt-5">
                <?php foreach ($projects as $project): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card project-card h-100">
                        <?php if (!empty($project['image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($project['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($project['title']); ?>">
                        <?php else: ?>
                            <div class="card-img-top placeholder-img d-flex align-items-center justify-content-center">
                                <i class="bi bi-image fs-1 text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($project['description']); ?></p>
                            <p class="text-muted small">
                                <strong>Technologies:</strong> <?php echo htmlspecialchars($project['technologies']); ?>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-flex gap-2">
                                <?php if (!empty($project['demo_url']) && $project['demo_url'] !== '#'): ?>
                                    <a href="<?php echo htmlspecialchars($project['demo_url']); ?>" class="btn btn-primary btn-sm" target="_blank">
                                        <i class="bi bi-eye"></i> Demo
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($project['github_url']) && $project['github_url'] !== '#'): ?>
                                    <a href="<?php echo htmlspecialchars($project['github_url']); ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                        <i class="bi bi-github"></i> Code
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section-padding bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="section-title">Contact Me</h2>
                    <p class="section-subtitle">Let's work together</p>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-lg-8 mx-auto">
                    <form action="contact.php" method="POST" class="contact-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control" name="name" placeholder="Your Name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" name="subject" placeholder="Subject">
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" name="message" rows="5" placeholder="Your Message" required></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($about['name'] ?? 'Portfolio'); ?>. All rights reserved.</p>
            <p>
                <a href="admin/" class="text-white-50 text-decoration-none small">Admin Panel</a>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
