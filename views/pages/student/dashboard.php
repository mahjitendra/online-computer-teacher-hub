<?php require APPROOT . '/views/layouts/frontend/header.php'; ?>

<div class="dashboard-container">
    <h1><?php echo $data['title']; ?></h1>
    <p>Welcome, <?php echo $data['user_name']; ?>!</p>
    <p>You have successfully logged in.</p>
    <a href="<?php echo URLROOT; ?>/auth/logout" class="btn-primary">Logout</a>
</div>

<?php require APPROOT . '/views/layouts/frontend/footer.php'; ?>