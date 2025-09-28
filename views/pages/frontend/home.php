<?php require APPROOT . '/views/layouts/frontend/header.php'; ?>

<div class="hero-section">
    <h1><?php echo $data['title']; ?></h1>
    <p><?php echo $data['description']; ?></p>
    <a href="<?php echo URLROOT; ?>/courses" class="btn-primary">Browse Courses</a>
</div>

<?php require APPROOT . '/views/layouts/frontend/footer.php'; ?>