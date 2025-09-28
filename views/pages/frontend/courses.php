<?php require APPROOT . '/views/layouts/frontend/header.php'; ?>

<div class="course-listing-container">
    <h1>Available Courses</h1>
    <p>Browse our catalog of expert-led courses.</p>

    <div class="course-grid">
        <?php if(!empty($data['courses'])) : ?>
            <?php foreach($data['courses'] as $course) : ?>
                <div class="course-card">
                    <div class="course-card-thumbnail">
                        <img src="<?php echo URLROOT; ?>/assets/images/course-thumbnails/default.jpg" alt="Course thumbnail">
                    </div>
                    <div class="course-card-content">
                        <h3><?php echo $course->title; ?></h3>
                        <p>Price: $<?php echo $course->price; ?></p>
                        <a href="<?php echo URLROOT; ?>/student/courses/show/<?php echo $course->id; ?>" class="btn-primary">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No courses available at the moment. Please check back later.</p>
        <?php endif; ?>
    </div>
</div>

<?php require APPROOT . '/views/layouts/frontend/footer.php'; ?>