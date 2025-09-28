<?php require APPROOT . '/views/layouts/frontend/header.php'; ?>

<div class="course-details-container">
    <div class="course-header">
        <h1><?php echo $data['course']->title; ?></h1>
        <p class="course-price">$<?php echo $data['course']->price; ?></p>
    </div>

    <div class="course-description">
        <p><?php echo $data['course']->description; ?></p>
    </div>

    <div class="enroll-section">
        <?php if(isset($_SESSION['user_id'])) : ?>
            <?php if($data['isEnrolled']) : ?>
                <p class="enrolled-message">You are already enrolled in this course.</p>
            <?php else : ?>
                <form action="<?php echo URLROOT; ?>/student/courses/enroll/<?php echo $data['course']->id; ?>" method="post">
                    <input type="submit" value="Enroll Now" class="btn-primary btn-lg">
                </form>
            <?php endif; ?>
        <?php else : ?>
            <p>Please <a href="<?php echo URLROOT; ?>/auth/login">login</a> to enroll in this course.</p>
        <?php endif; ?>
    </div>

    <!-- Future section for tutorials -->
    <div class="tutorials-section">
        <h2>Course Content</h2>
        <p>Tutorials will be listed here once you are enrolled.</p>
    </div>
</div>

<?php require APPROOT . '/views/layouts/frontend/footer.php'; ?>