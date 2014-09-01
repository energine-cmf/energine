<?php
/**
 * @file
 * AuthUser.
 *
 * It contains the definition to:
 * @code
class AuthUser;
@endcode
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
@endcode
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
        //Если есть в сессии данные о юзере
        //это означает что сессия правильная
        if (isset($_SESSION['userID'])) {
            $id = $_SESSION['userID'];
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
        return ($this->getID() === false) ? false : true;
    }


    /**
     * Authenticate user by his name and SHA-1 hash code.
     *
     * @param string $username User name.
     * @param string $password SHA-1 hash code.
     * @return bool|int
     */
    public static function authenticate($username, $password) {
        $username = trim($username);
        $password = sha1(trim($password));
        //Проверяем совпадает ли имя/пароль в SHA1 с данными в таблице
        if($id = simplifyDBResult(E()->getDB()->select(
            'user_users', array('u_id'),
            array(
                'u_name' => $username,
                'u_password' => $password,
                'u_is_active' => 1
            )
        ), 'u_id', true)) {
            return (int)$id; 
        }
        else {
            return false;
        }
    }
}
