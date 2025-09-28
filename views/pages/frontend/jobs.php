<?php require APPROOT . '/views/layouts/frontend/header.php'; ?>

<div class="job-board-container">
    <div class="job-board-header">
        <h1>Find Your Next Opportunity</h1>
        <p>Browse our curated list of jobs from top companies in the tech industry.</p>
    </div>

    <!-- Filtering would be added here in a full implementation -->

    <div class="job-listings">
        <?php if(!empty($data['jobs'])): ?>
            <?php foreach($data['jobs'] as $job): ?>
                <div class="job-card">
                    <div class="job-card-header">
                        <h3><?php echo $job->title; ?></h3>
                        <p class="job-company"><?php echo $job->company; ?> - <span class="job-location"><?php echo $job->location; ?></span></p>
                    </div>
                    <div class="job-card-body">
                        <p class="job-category"><?php echo $job->category_name; ?></p>
                        <a href="<?php echo URLROOT; ?>/jobs/show/<?php echo $job->id; ?>" class="btn-primary">View & Apply</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No job openings at the moment. Please check back later.</p>
        <?php endif; ?>
    </div>
</div>

<?php require APPROOT . '/views/layouts/frontend/footer.php'; ?>