<?php require APPROOT . '/views/layouts/frontend/header.php'; ?>

<div class="job-details-container">
    <div class="job-header">
        <h1><?php echo $data['job']->title; ?></h1>
        <h3><?php echo $data['job']->company; ?> - <?php echo $data['job']->location; ?></h3>
        <p class="job-category"><?php echo $data['job']->category_name; ?></p>
    </div>

    <div class="job-description">
        <?php echo nl2br($data['job']->description); ?>
    </div>

    <hr>

    <div class="apply-section">
        <h2>Apply for this Job</h2>
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if(isset($_GET['applied']) && $_GET['applied'] == 'true'): ?>
                <p class="success-message">Your application has been submitted successfully!</p>
            <?php else: ?>
                <form action="<?php echo URLROOT; ?>/jobs/apply/<?php echo $data['job']->id; ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="cover_letter">Cover Letter:</label>
                        <textarea name="cover_letter" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="resume">Upload Resume (PDF):</label>
                        <input type="file" name="resume" class="form-control">
                    </div>
                    <input type="submit" value="Submit Application" class="btn-primary btn-lg">
                </form>
            <?php endif; ?>
        <?php else: ?>
            <p>Please <a href="<?php echo URLROOT; ?>/auth/login">login</a> to apply for this job.</p>
        <?php endif; ?>
    </div>
</div>

<?php require APPROOT . '/views/layouts/frontend/footer.php'; ?>