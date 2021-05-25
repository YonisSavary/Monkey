<?php 

namespace Monkey\Services;

use Kernel\Model\ModelParser;
use Monkey\Storage\Config;
use Monkey\Web\Trash;


/**
 * Very basical Authentication class
 */
class Auth
{
    static $model = null;
    static $model_name = null;
    static $login_field = null;
    static $pass_field = null;

    /**
     * Given a clear password, return a Hash made
     * with BCRYPT and a cost of 8
     * 
     * @param string $password
     */
    public static function create_password(string $password) : string
    {
        return password_hash($password, PASSWORD_BCRYPT, ["cost"=>8]);
    }


    /**
     * Initialize the component
     * And check if everything's right in the app configuration
     */
    public static function init()
    {
        if (!isset($_SESSION["m_auth_logged"])) $_SESSION["m_auth_logged"] = false;
        if (Config::get("auth")["enabled"] !== true) return null;

        $model_name = Config::get("auth")["model"];
        if (!class_exists($model_name, false)) $model_name = "Models\\".$model_name;
        if (!class_exists($model_name, true)) Trash::fatal("$model_name Model does not exists !", true);

        $model = new $model_name();
        $parser = new ModelParser($model_name);
        $fields = $parser->get_model_fields();


        $login_field = Config::get("auth")["login_field"];
        if (!in_array($login_field, $fields)) Trash::fatal("$model_name does not have a $login_field public field", true);

        $pass_field = Config::get("auth")["pass_field"];
        if (!in_array($login_field, $fields)) Trash::fatal("$model_name does not have a $pass_field public field", true);

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
                $_SESSION["m_auth_duration"] +=  Config::get("auth")["hop_duration"] ??  300;
                if ($_SESSION["m_auth_duration"] > Config::get("auth")["duration"] ?? 3600)
                {
                    $_SESSION["m_auth_duration"] =  Config::get("auth")["duration"] ?? 3600;
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
        
        $salt_field = Config::get("auth")["salt_field"];
        if ( (!is_null($salt_field)) &&  isset($user->$salt_field)){
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
            if (!isset($_SESSION["m_auth_attempt"])) $_SESSION["m_auth_attempt"] = 0;
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
        $_SESSION["m_auth_duration"] =  Config::get("auth")["duration"] ?? 3600;
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