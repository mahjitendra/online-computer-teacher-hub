<?php require APPROOT . '/views/layouts/frontend/header.php'; ?>

<div class="teacher-form-container">
    <h2>Create a New Course</h2>
    <p>Fill out the form below to create a new course.</p>
    <form action="<?php echo URLROOT; ?>/teacher/courses/create" method="post">
        <div class="form-group">
            <label for="title">Title: <sup>*</sup></label>
            <input type="text" name="title" class="form-control <?php echo (!empty($data['title_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['title']; ?>">
            <span class="invalid-feedback"><?php echo $data['title_err']; ?></span>
        </div>
        <div class="form-group">
            <label for="description">Description: <sup>*</sup></label>
            <textarea name="description" class="form-control <?php echo (!empty($data['description_err'])) ? 'is-invalid' : ''; ?>"><?php echo $data['description']; ?></textarea>
            <span class="invalid-feedback"><?php echo $data['description_err']; ?></span>
        </div>
        <div class="form-group">
            <label for="category_id">Category: <sup>*</sup></label>
            <select name="category_id" class="form-control">
                <?php foreach($data['categories'] as $category): ?>
                    <option value="<?php echo $category->id; ?>"><?php echo $category->name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="price">Price: <sup>*</sup></label>
            <input type="text" name="price" class="form-control" value="<?php echo $data['price']; ?>">
        </div>
        <input type="submit" class="btn-primary" value="Submit">
    </form>
</div>

<?php require APPROOT . '/views/layouts/frontend/footer.php'; ?>