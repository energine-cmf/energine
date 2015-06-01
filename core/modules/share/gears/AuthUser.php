<?php
/**
 * @file
 * AuthUser.
 *
 * It contains the definition to:
 * @code
class AuthUser;
 * @endcode
 *
 * @author d.pavka
 * @copyright Energine 2011
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;

/**
 * Authenticated user.
 *
 * @code
class AuthUser;
 * @endcode
 */
class AuthUser extends User {
    //todo VZ: Why not to use 0 as the default user id?
    /**
     * Constructor.
     * The parameter is used for preventing "strict" error.
     *
     * @param bool|int $id
     *
     * @todo избавиться от hardcoded имен полей формы?
     */
    public function __construct($id = false) {
        if(isset($_SESSION['UID'])){
            $id = $_SESSION['UID'];
        }
        parent::__construct($id);
    }

    /**
     * Check if the user authenticated.
     *
     * The return value mean:
     *   - true - the user is successfully authenticated;
     *   - false - the user is guest.
     *
     * @return boolean
     */
    public function isAuthenticated() {
        return (bool)$this->getID();
    }


    /**
     * Authenticate user by his name and password
     *
     * @param string $username User name.
     * @param string $password SHA-1 hash code.
     * @return bool|int
     */
    public static function authenticate($username, $password) {
        $res = E()->getDB()->query('SELECT u_id, u_password FROM user_users WHERE u_name = %s', $username);
        $result = false;
        while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
            if (password_verify($password, $row['u_password'])) {
                $result = $row['u_id'];
                unset($row, $res);
                break;
            }
        }

        return $result;

    }
}
