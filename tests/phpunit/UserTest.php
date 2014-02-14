<?php
class UserTest extends PHPUnit_Framework_TestCase {

    /**
     * @var User
     */
    private $user;

    public function setUp() {
        $this->user = new User();
    }

    public function testUser() {
        $this->assertInstanceOf('User', $this->user);
    }

    /**
     * @depends testUser
     */
    public function testUserId() {
        $this->assertFalse($this->user->getID());
    }

    public function testGeneratePassword() {
        $pass = $this->user->generatePassword(0);
        $this->assertEmpty($pass);
        $pass = $this->user->generatePassword(7);
        $this->assertEquals(7, strlen($pass));
    }

    public function testUserFields() {
        $fields = $this->user->getFields();
        foreach(
            array(
                'u_id',
                'u_name',
                'u_password',
                'u_vkid',
                'u_fbid',
                'u_is_active'
            ) as $key) {
            $this->assertArrayHasKey($key, $fields);
        }
    }

    public function tearDown() {
        unset($this->user);
    }
}