<?php
class UserTest extends PHPUnit_Framework_TestCase {

    const TEST_USER_NAME = 'test@test.com';
    /**
     * @var user
     */
    private $user;

    public function testGeneratePassword() {
        $this->user = new User();
        $pass = $this->user->generatePassword(0);
        $this->assertEmpty($pass);
        $pass = $this->user->generatePassword(7);
        $this->assertEquals(7, strlen($pass));
    }

    public function testUserFields() {
        $this->user = new User();
        $fields = $this->user->getFields();
        foreach(
            array(
                'u_name',
                'u_password',
                'u_fullname'
            ) as $key) {
            $this->assertArrayHasKey($key, $fields);
        }
    }

    public function testCreateUser() {
        $this->user = new User();
        $this->user->create(
            array(
                'u_name' => 'test@test.com',
                'u_fullname' => 'PHPUnit',
                'u_password' => User::generatePassword()
            )
        );
        $this->assertCount(1, E()->getDB()->select(User::USER_TABLE_NAME, 'u_id', array('u_name' => self::TEST_USER_NAME)));
    }

    /**
     * Trying to create user with duplicated u_name should lead
     * to Exception
     *
     * @expectedException   SystemException
     */
    public function  testCreateDuplicatedUser() {
        $this->user = new User();
        $uInfo = array(
            'u_name' => 'test@test.com',
            'u_fullname' => 'PHPUnit',
            'u_password' => User::generatePassword()
        );
        $this->user->create($uInfo);
        $this->user->create($uInfo);
    }

    protected function tearDown() {
        E()->getDB()->modify(QAL::DELETE, User::USER_TABLE_NAME, null, array('u_name' => self::TEST_USER_NAME));
    }
}