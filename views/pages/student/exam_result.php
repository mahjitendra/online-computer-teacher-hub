<?php require APPROOT . '/views/layouts/frontend/header.php'; ?>

<div class="exam-container result-container">
    <h1>Exam Results</h1>

    <div class="result-summary">
        <h2>Your Score: <?php echo $data['score']; ?> / <?php echo $data['total_questions']; ?></h2>
        <h3>That's <?php echo number_format($data['percentage'], 2); ?>%</h3>
    </div>

    <?php if($data['percentage'] >= 80): // Assuming 80% is the passing grade ?>
        <div class="pass-message">
            <p>Congratulations! You have passed the exam.</p>
            <p>Your certificate will be issued shortly.</p>
            <a href="<?php echo URLROOT; ?>/student/certificates" class="btn-primary">View My Certificates</a>
        </div>
    <?php else: ?>
        <div class="fail-message">
            <p>Unfortunately, you did not pass the exam this time.</p>
            <p>Please review the course materials and try again.</p>
            <a href="<?php echo URLROOT; ?>/courses/show/<?php echo $data['exam']->course_id; ?>" class="btn-secondary">Back to Course</a>
        </div>
    <?php endif; ?>

</div>

<?php require APPROOT . '/views/layouts/frontend/footer.php'; ?>