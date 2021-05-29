<?php 

namespace Monkey\Services;

use Monkey\Storage\Config;
use Monkey\Web\Trash;


/**
 * Very basical Authentication class
 */
class Auth
{
    static $config = [];
    static $model = null;
    static $model_name = null;
    static $login_field = null;
    static $pass_field = null;

    /**
     * Given a clear password, return a Hash made with an algorithm and a cost
     * 
     * @param string $password
     * @param string $algorithm (Algorithm to use, BCRYPT by default)
     * @param int $cost Cost (8 by default)
     */
    public static function create_password(string $password, string $algorithm=PASSWORD_BCRYPT, int $cost=8) : string
    {
        return password_hash($password, $algorithm, ["cost"=>$cost]);
    }


    /**
     * Initialize the component
     * And check if everything's right in the app configuration
     */
    public static function init()
    {
        self::$config = Config::get("auth") ?? ["enabled" => false];
        if (self::$config["enabled"] !== true) return null;

        if (!isset($_SESSION["m_auth_logged"])) $_SESSION["m_auth_logged"] = false;
        if (!isset($_SESSION["m_auth_attempt"])) $_SESSION["m_auth_attempt"] = 0;

        $model_name = self::$config["model"];
        if (!class_exists($model_name, false)) $model_name = "Models\\".$model_name;
        if (!class_exists($model_name, true)) Trash::fatal("Auth : $model_name Model does not exists !");

        $model = new $model_name();
        $fields = $model_name::get_fields();

        $login_field = self::$config["login_field"];
        if (!in_array($login_field, $fields)) Trash::fatal("Auth : $model_name does not have a $login_field public field");

        $pass_field = self::$config["pass_field"];
        if (!in_array($login_field, $fields)) Trash::fatal("Auth : $model_name does not have a $pass_field public field");

        self::$model = $model;
        self::$login_field = $login_field;
        self::$pass_field = $pass_field;
        self::$model_name = $model_name;

        if (self::is_logged())
        {
            if ($_SESSION["m_auth_duration"] === 0)
            {
                self::logout();
            } 
            else 
            {
                if ($_SESSION["m_auth_duration"] ?? -1 == 0)
                {
                    $_SESSION["m_auth_duration"] = self::$config["duration"] ;
                } 
                else 
                {
                    $_SESSION["m_auth_duration"] +=  self::$config["hop_duration"] ??  300;
                    if ($_SESSION["m_auth_duration"] > self::$config["duration"] ?? 3600)
                    {
                        $_SESSION["m_auth_duration"] =  self::$config["duration"] ?? 3600;
                    }
                }
            }
        }
    }


    /**
     * Given a login and a password, this function check 
     * a user with this login and password exists
     * 
     * @param string|int $login email/login, or whatever is in your login field
     * @param string $password Password to check for
     */
    public static function check(string|int $login, string $password) : bool
    {
        $user = self::$model_name::get_all()->where(self::$login_field, $login)->limit(1)->execute();
        if (count($user) === 0) return false;

        $user = $user[0];
        $pfield = self::$pass_field;
        
        $salt_field = self::$config["salt_field"];
        if ( (!is_null($salt_field)) &&  isset($user->$salt_field))
        {
            $password .= $user->$salt_field;
        }

        return password_verify($password, $user->$pfield);
    }

    
    /**
     * Given a login and a password, this function check 
     * does try to log a user
     * 
     * @param string|int $login email/login, or whatever is in your login field
     * @param string $password Password to check for
     * @return bool was the authentication successful ?
     */
    public static function attempt(string|int $login, string $password): bool
    {
        if (self::check($login, $password))
        {
            $_SESSION["m_auth_attempt"] = 0;
            $u = self::$model_name::get_all()->where(self::$login_field, $login)->limit(1)->execute();
            self::login($u[0]);
            return true;
        } 
        else 
        {
            $_SESSION["m_auth_attempt"]++;
            self::logout();
            return false;
        }
    }


    /**
     * Get the total failed attempts number
     */
    public static function attempts() : int 
    {
        return $_SESSION["m_auth_attempt"];
    }


    /**
     * Log a user and save it into the session
     */
    public static function login($user): void
    {
        $_SESSION["m_auth_user"] = $user;
        $_SESSION["m_auth_logged"] = true;
        $_SESSION["m_auth_token"] = bin2hex(random_bytes(32));
        $_SESSION["m_auth_duration"] =  self::$config["duration"] ?? 3600;
    }


    /**
     * Get the client token
     */    
    public static function token() : string|null
    {
        if (!isset($_SESSION["m_auth_token"])) return null;
        return $_SESSION["m_auth_token"];
    }


    /**
     * Logout a user and save it into the session
     */
    public static function logout() : void
    {
        if (isset($_SESSION["m_auth_user"])) unset($_SESSION["m_auth_user"]);
        if (isset($_SESSION["m_auth_token"])) unset($_SESSION["m_auth_token"]);
        $_SESSION["m_auth_logged"] = false;
    }

    
    /**
     * Check if the user is authenticated
     * 
     * @return bool Is the user authenticated
     */
    public static function is_logged(): bool
    {
        return ($_SESSION["m_auth_logged"] === true);
    }


    /**
     * @return mixed return the user model object, it not authenticated
     * or not logged, return true
     */
    public static function get_user() : mixed
    {
        if (!isset($_SESSION["m_auth_user"])) return null;
        return $_SESSION["m_auth_user"];
    }
}