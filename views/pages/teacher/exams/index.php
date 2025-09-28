<?php require APPROOT . '/views/layouts/frontend/header.php'; ?>

<div class="teacher-dashboard-container">
    <div class="courses-header">
        <h1>Exams for <?php echo $data['course']->title; ?></h1>
        <a href="<?php echo URLROOT; ?>/teacher/exams/create/<?php echo $data['course']->id; ?>" class="btn-primary">Create Exam</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Duration (Mins)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data['exams'] as $exam) : ?>
                <tr>
                    <td><?php echo $exam->title; ?></td>
                    <td><?php echo $exam->duration; ?></td>
                    <td>
                        <a href="<?php echo URLROOT; ?>/teacher/exams/show/<?php echo $exam->id; ?>" class="btn-secondary">View/Add Questions</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require APPROOT . '/views/layouts/frontend/footer.php'; ?>