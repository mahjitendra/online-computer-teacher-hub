<?php require APPROOT . '/views/layouts/frontend/header.php'; // We can create a specific teacher header later ?>

<div class="teacher-dashboard-container">
    <div class="courses-header">
        <h1>My Courses</h1>
        <a href="<?php echo URLROOT; ?>/teacher/courses/create" class="btn-primary">Create Course</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data['courses'] as $course) : ?>
                <tr>
                    <td><?php echo $course->title; ?></td>
                    <td>$<?php echo $course->price; ?></td>
                    <td><?php echo ucfirst($course->status); ?></td>
                    <td>
                        <a href="<?php echo URLROOT; ?>/teacher/courses/edit/<?php echo $course->id; ?>" class="btn-secondary">Edit</a>
                        <form action="<?php echo URLROOT; ?>/teacher/courses/delete/<?php echo $course->id; ?>" method="post" style="display:inline;">
                            <input type="submit" value="Delete" class="btn-danger">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require APPROOT . '/views/layouts/frontend/footer.php'; ?>