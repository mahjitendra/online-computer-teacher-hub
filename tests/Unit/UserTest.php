<?php

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private $userModel;
    private $db;

    protected function setUp(): void
    {
        // This method is called before each test.
        $this->db = new Database;
        $this->userModel = new User;

        // Create a test user to be used in the tests
        $this->db->query("INSERT INTO users (name, email, password, user_type) VALUES ('Test User', 'test@example.com', 'password', 'student')");
        $this->db->execute();
    }

    public function test_find_user_by_email()
    {
        $found = $this->userModel->findUserByEmail('test@example.com');
        $this->assertTrue($found, "Failed to find the user by email.");

        $notFound = $this->userModel->findUserByEmail('nonexistent@example.com');
        $this->assertFalse($notFound, "Incorrectly found a user that does not exist.");
    }

    protected function tearDown(): void
    {
        // This method is called after each test.
        // Clean up the database by deleting the test user.
        $this->db->query("DELETE FROM users WHERE email = 'test@example.com'");
        $this->db->execute();
    }
}