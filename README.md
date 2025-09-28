# Online Computer Teacher Hub

The Online Computer Teacher Hub is a comprehensive, full-featured web application designed to be a centralized platform for computer science education. It connects students, teachers, and employers, providing a seamless experience for learning, teaching, and career development.

## Features

This platform is built from the ground up with a rich feature set, including:

- **Complete User Authentication:** Secure registration and login system for students, teachers, and administrators, with role-based access control.
- **Course Management System:** Teachers can create, edit, and manage their courses, including uploading tutorials and materials. Admins have full oversight.
- **Student Enrollment & Progress Tracking:** Students can browse a catalog of courses, enroll in them, and track their progress through the material.
- **Advanced Exam & Certificate System:** Teachers can build custom exams with various question types. Upon successful completion, students are automatically issued a verifiable certificate.
- **Integrated Job Board:** A full-featured job board allows companies to post opportunities and students to apply for them directly through the platform.
- **Payment Integration:** A (simulated) payment gateway is integrated to handle course fees and subscriptions, allowing for a complete e-commerce experience.
- **RESTful API:** A versioned API provides access to the platform's data and functionality for external clients, such as a mobile app.
- **Full Testing Suite:** The application includes a complete testing environment using PHPUnit to ensure code quality and stability.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

You will need a local server environment with the following components installed:

- PHP (with `curl`, `dom`, `mbstring`, and `mysql` extensions)
- MySQL
- Composer

### Installation

1.  **Clone the repository:**
    ```bash
    git clone <repository-url>
    ```

2.  **Install PHP dependencies:**
    Navigate to the project directory and run the following command to install the necessary packages, including the testing framework.
    ```bash
    composer install
    ```

3.  **Set up the database:**
    - Create a new MySQL database named `online_computer_teacher_hub`.
    - Update the database credentials in `config/app.php` to match your local environment.
    - Run the migration script to create all the necessary tables:
    ```bash
    php scripts/migrate.php
    ```

4.  **Configure your web server:**
    Set the document root of your local web server to the `public` directory of the project.

## Running the Tests

To run the full suite of tests for the application, execute the following command from the root of the project:

```bash
./vendor/bin/phpunit
```

This will run all the unit and feature tests and provide a report on the application's code coverage.