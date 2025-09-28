<?php require APPROOT . '/views/layouts/frontend/header.php'; ?>

<div class="teacher-form-container">
    <h2>Create a New Exam</h2>
    <p>Fill out the form below to create a new exam for your course.</p>
    <form action="<?php echo URLROOT; ?>/teacher/exams/create/<?php echo $data['course_id']; ?>" method="post">
        <div class="form-group">
            <label for="title">Title: <sup>*</sup></label>
            <input type="text" name="title" class="form-control <?php echo (!empty($data['title_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['title']; ?>">
            <span class="invalid-feedback"><?php echo $data['title_err']; ?></span>
        </div>
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea name="description" class="form-control"><?php echo $data['description']; ?></textarea>
        </div>
        <div class="form-group">
            <label for="duration">Duration (in minutes):</label>
            <input type="number" name="duration" class="form-control" value="<?php echo $data['duration']; ?>">
        </div>
        <input type="submit" class="btn-primary" value="Create Exam">
    </form>
</div>

<?php require APPROOT . '/views/layouts/frontend/footer.php'; ?>