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
                <p class="enrolled-message">You are enrolled in this course.</p>
            <?php else : ?>
                <a href="<?php echo URLROOT; ?>/payment/checkout/<?php echo $data['course']->id; ?>" class="btn-primary btn-lg">Buy Now</a>
            <?php endif; ?>
        <?php else : ?>
            <p>Please <a href="<?php echo URLROOT; ?>/auth/login">login</a> to purchase this course.</p>
        <?php endif; ?>
    </div>

    <!-- Tutorials & Exams Section -->
    <div class="course-content-section">
        <h2>Course Content</h2>
        <?php if($data['isEnrolled']): ?>
            <!-- Tutorials would be listed here -->

            <h3>Exams</h3>
            <?php if(!empty($data['exams'])): ?>
                <ul>
                    <?php foreach($data['exams'] as $exam): ?>
                        <li>
                            <?php echo $exam->title; ?>
                            <a href="<?php echo URLROOT; ?>/student/exams/take/<?php echo $exam->id; ?>" class="btn-secondary">Take Exam</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No exams have been added to this course yet.</p>
            <?php endif; ?>

        <?php else: ?>
            <p>You must be enrolled to view the course content and exams.</p>
        <?php endif; ?>
    </div>
</div>

<?php require APPROOT . '/views/layouts/frontend/footer.php'; ?>