<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITENAME; ?></title>
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/frontend/main.css">
</head>
<body class="bg-gradient">
    <header>
        <nav>
            <div class="logo">
                <a href="<?php echo URLROOT; ?>"><?php echo SITENAME; ?></a>
            </div>
            <ul class="nav-links">
                <li><a href="<?php echo URLROOT; ?>/courses">Courses</a></li>
                <li><a href="<?php echo URLROOT; ?>/jobs">Jobs</a></li>
                <?php if(isset($_SESSION['user_id'])) : ?>
                    <?php if($_SESSION['user_type'] == 'teacher') : ?>
                        <li><a href="<?php echo URLROOT; ?>/teacher/courses">Teacher Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo URLROOT; ?>/auth/logout">Logout</a></li>
                <?php else : ?>
                    <li><a href="<?php echo URLROOT; ?>/auth/login">Login</a></li>
                    <li><a href="<?php echo URLROOT; ?>/auth/register" class="btn-primary">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>