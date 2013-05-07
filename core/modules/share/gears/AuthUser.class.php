<?php
/**
 * Класс AuthUser.
 *
 * @package energine
 * @subpackage kernel
 * @author d.pavka
 * @copyright Energine 2011
 */


/**
 * Аутентифицированный пользователь.
 *
 * @package energine
 * @subpackage kernel
 * @author d.pavka
 */
class AuthUser extends User {
    /**
     * Конструктор класса.
     * параметр введен только для избежания strict ошибки
     *
     * @param false
     * @access private
     * @return void
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
     * Возвращает флаг успеха аутентификации:
     *     true - пользователь успешно аутентифицирован;
     *     false - пользователь является гостем.
     *
     * @access public
     * @return boolean
     */
    public function isAuthenticated() {
        return ($this->getID() === false) ? false : true;
    }


    /**
     * Аутентифицирует пользователя по его имени и SHA-1 хэшу пароля.
     *      *
     * @access public
     * @param string $username имя пользовате
     * @param string $password SHA-1 хэш пароля
     * @return bool | int
     * @static
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
