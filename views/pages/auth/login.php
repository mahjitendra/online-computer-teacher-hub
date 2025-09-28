<?php require APPROOT . '/views/layouts/frontend/header.php'; ?>

<div class="auth-container">
    <div class="form-container">
        <h2>Login</h2>
        <p>Please fill in your credentials to log in</p>
        <form action="<?php echo URLROOT; ?>/auth/login" method="post">
            <div class="form-group">
                <label for="email">Email: <sup>*</sup></label>
                <input type="email" name="email" class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['email']; ?>">
                <span class="invalid-feedback"><?php echo $data['email_err']; ?></span>
            </div>
            <div class="form-group">
                <label for="password">Password: <sup>*</sup></label>
                <input type="password" name="password" class="form-control <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['password']; ?>">
                <span class="invalid-feedback"><?php echo $data['password_err']; ?></span>
            </div>
            <div class="form-row">
                <div class="col">
                    <input type="submit" value="Login" class="btn-primary btn-block">
                </div>
                <div class="col">
                    <a href="<?php echo URLROOT; ?>/auth/register" class="btn-light btn-block">No account? Register</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require APPROOT . '/views/layouts/frontend/footer.php'; ?>