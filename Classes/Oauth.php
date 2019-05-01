<?php
/**
 * Created by PhpStorm.
 * Oauth: xterminate
 * Date: 05.05.2018
 * Time: 0:35
 */

namespace Classes;


use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class Oauth
{
    private $socials;

    /**
     * Oauth constructor.
     * @param ISocial[] $socials
     */
    public function __construct($socials)
    {
        foreach ($socials as $social) {
            $this->socials[$social->getName()] = $social;
        }
    }

    public function has($name)
    {
        return array_key_exists($name, $this->socials);
    }

    /**
     * @param $name
     * @return string
     */
    public function generateAuthUrl($name)
    {
        if (!$this->has($name)) {
            throw new UnprocessableEntityHttpException("Could not found social with name $name");
        }

        /** @var ISocial $social */
        $social = $this->socials[$name];
        return $social->generateAuthUrl();

    }

    public function authorize($name, $code)
    {
        if (!$this->has($name)) {
            throw new UnprocessableEntityHttpException("Could not found social with name $name");
        }

        /** @var ISocial $social */
        $social = $this->socials[$name];
        return $social->authorize($code);
    }

}

//    private static $vk_url = 'http://oauth.vk.com/authorize';
//    private static $ok_url = 'http://www.odnoklassniki.ru/oauth/authorize';
//    private static $mr_url = 'https://connect.mail.ru/oauth/authorize';
//    private static $gp_url = 'https://accounts.google.com/o/oauth2/auth';
//private static $ok_public_key = 'CBAFEOHMEBABABABA';
//    private static $vk_params = array(
//        'client_id' => '6474619',
//        'redirect_uri' => 'http://vote.jesc-russia.com',
//        'response_type' => 'code'
//    );
//    private static $ok_params = array(
//        'client_id' => '1266500608',
//        'response_type' => 'code',
//        'redirect_uri' => 'http://vote.jesc-russia.com'
//    );
//    private static $mr_params = array(
//        'client_id' => '760118',
//        'response_type' => 'code',
//        'redirect_uri' => 'http://vote.jesc-russia.com'
//    );
//    private static $gp_params = array(
//        'redirect_uri' => 'http://vote.jesc-russia.com',
//        'response_type' => 'code',
//        'client_id' => '1014673555154-iguhfljk14clja6j8acjftg144t0i4es.apps.googleusercontent.com',
//        'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile'
//    );
//private static $vk_params_token = array(
//    'client_id' => '6474619',
//    'client_secret' => 'nkjtcKB9h0SaudVH4KFd',
//    'code' => '',
//    'redirect_uri' => 'http://vote.jesc-russia.com'
//);
//private static $ok_params_token = array(
//    'client_id' => '1266500608',
//    'client_secret' => '515319EA34270B415395F34A',
//    'grant_type' => 'authorization_code',
//    'code' => '',
//    'redirect_uri' => 'http://vote.jesc-russia.com'
//);
//private static $gp_params_token = array(
//    'client_id' => '1014673555154-iguhfljk14clja6j8acjftg144t0i4es.apps.googleusercontent.com',
//    'client_secret' => '214rndKnNKn3YWIWS8VOaiRB',
//    'redirect_uri' => 'http://vote.jesc-russia.com',
//    'grant_type' => 'authorization_code',
//    'code' => ''
//);
//private static $mr_params_token = array(
//    'client_id' => '760118',
//    'client_secret' => 'be7dfcc09137829968438679390fcb69',
//    'redirect_uri' => 'http://vote.jesc-russia.com',
//    'grant_type' => 'authorization_code',
//    'code' => ''
//);
//    /**
//     * @param $name
//     * @return Social
//     */
//    public function getSocial($name)
//    {
//        if (!array_key_exists($name, $this->socials)) {
//            throw new UnprocessableEntityHttpException("ISocial instance with name $name does not exist");
//        }
//        return $this->socials[$name];
//    }
//
//    /**
//     * @param array $vk_params
//     *
//     * @return string
//     */
//    public static function authVk()
//    {
//        $result = false;
//        $token = json_decode(file_get_contents('https://oauth.vk.com/access_token' . '?' . urldecode(http_build_query(self::$vk_params_token))), true);
//        if (isset($token['access_token'])) {
//            $params = array(
//                'uids' => $token['user_id'],
//                'fields' => 'uid,first_name,last_name,screen_name,sex,bdate,photo_100',
//                'access_token' => $token['access_token'],
//                'v' => '5.0'
//            );
//            $userInfo = json_decode(file_get_contents('https://api.vk.com/method/users.get' . '?' . urldecode(http_build_query($params))), true);
//            if (isset($userInfo['response'][0]['id'])) {
//                $userInfo = $userInfo['response'][0];
//                $result = true;
//            }
//            if ($result) {
//                $userInfo['auth_type'] = 'vk';
//                $_SESSION['user_info'] = $userInfo;
//
//                return true;
//            }
//        }
//
//        return false;
//    }
//
//    public static function authOk()
//    {
//        $result = false;
//        $url = 'http://api.odnoklassniki.ru/oauth/token.do';
//
//        $curl = \curl_init();
//        curl_setopt($curl, CURLOPT_URL, $url); // url, куда будет отправлен запрос
//        curl_setopt($curl, CURLOPT_POST, 1);
//        curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query(self::$ok_params_token))); // передаём параметры
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//        $result = curl_exec($curl);
//        curl_close($curl);
//        $token = json_decode($result, true);
//        if (isset($token['access_token']) && isset(self::$ok_public_key)) {
//            $params = array(
//                'method' => 'users.getCurrentUser',
//                'access_token' => $token['access_token'],
//                'application_key' => self::$ok_public_key,
//                'format' => 'json',
//                'fields' => 'PIC128X128,FIRST_NAME,LAST_NAME',
//            );
//            $sign = md5("application_key=" .
//                self::$ok_public_key . "fields=" . $params['fields'] .
//                "format=jsonmethod=users.getCurrentUser" .
//                md5("{$token['access_token']}" . self::$ok_params_token['client_secret']));
//            $params['sig'] = $sign;
//            $userInfo = json_decode(file_get_contents('http://api.odnoklassniki.ru/fb.do' . '?' . urldecode(http_build_query($params))), true);
//            if (isset($userInfo['uid'])) {
//                $userInfo['photo_100'] = $userInfo['pic128x128'];
//                $userInfo['id'] = $userInfo['uid'];
//                $result = true;
//            }
//            if ($result) {
//                $userInfo['auth_type'] = 'ok';
//                $_SESSION['user_info'] = $userInfo;
//
//                return true;
//            }
//        }
//
//        return false;
//    }
//
//    public static function authGp()
//    {
//        $result = false;
//        $url = 'https://accounts.google.com/o/oauth2/token';
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, $url);
//        curl_setopt($curl, CURLOPT_POST, 1);
//        curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query(self::$gp_params_token)));
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//        $result = curl_exec($curl);
//        curl_close($curl);
//        $token = json_decode($result, true);
//        if (isset($token['access_token'])) {
//            self::$gp_params_token['access_token'] = $token['access_token'];
//            $userInfo = json_decode(file_get_contents('https://www.googleapis.com/oauth2/v1/userinfo' . '?' . urldecode(http_build_query(self::$gp_params_token))), true);
//            if (isset($userInfo['id'])) {
//                $userInfo['photo_100'] = $userInfo['picture'];
//                $userInfo['first_name'] = $userInfo['given_name'];
//                $userInfo['last_name'] = $userInfo['family_name'];
//                $result = true;
//            }
//            if ($result) {
//                $userInfo['auth_type'] = 'gp';
//                $_SESSION['user_info'] = $userInfo;
//
//                return true;
//            }
//        }
//
//        return false;
//    }
//
//    public static function authMr()
//    {
//        $result = false;
//        $url = 'https://connect.mail.ru/oauth/token';
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, $url);
//        curl_setopt($curl, CURLOPT_POST, 1);
//        curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query(self::$mr_params_token)));
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//        $result = curl_exec($curl);
//        curl_close($curl);
//        $token = json_decode($result, true);
//        if (isset($token['access_token'])) {
//            $sign = md5("app_id=" . self::$mr_params['client_id'] . "method=users.getInfosecure=1session_key={$token['access_token']}" . self::$mr_params_token['client_secret']);
//            $params = array(
//                'method' => 'users.getInfo',
//                'secure' => '1',
//                'app_id' => self::$mr_params['client_id'],
//                'session_key' => $token['access_token'],
//                'sig' => $sign
//            );
//            $userInfo = json_decode(file_get_contents('http://www.appsmail.ru/platform/api' . '?' . urldecode(http_build_query($params))), true);
//            if (isset($userInfo[0]['uid'])) {
//                $userInfo = $userInfo[0];
//                $userInfo['photo_100'] = $userInfo['pic_128'];
//                $userInfo['id'] = $userInfo['uid'];
//                $result = true;
//            }
//            if ($result) {
//                $userInfo['auth_type'] = 'mr';
//                $_SESSION['user_info'] = $userInfo;
//
//                return true;
//            }
//        }
//
//        return false;
//    }
//
//    public static function setCode($code, $social)
//    {
//        switch ($social) {
//            case 'vk':
//                self::$vk_params_token['code'] = $code;
//                break;
//            case 'ok':
//                self::$ok_params_token['code'] = $code;
//                break;
//            case 'mr':
//                self::$mr_params_token['code'] = $code;
//                break;
//            case 'gp':
//                self::$gp_params_token['code'] = $code;
//                break;
//        }
//    }
//
//    public static function getVkAuthUrl()
//    {
//        return self::$vk_url . '?' . urldecode(http_build_query(self::$vk_params));
//    }
//
//    public static function getOkAuthUrl()
//    {
//        return self::$ok_url . '?' . urldecode(http_build_query(self::$ok_params));
//    }
//
//    public static function getMrAuthUrl()
//    {
//        return self::$mr_url . '?' . urldecode(http_build_query(self::$mr_params));
//    }
//
//    public static function getGpAuthUrl()
//    {
//        return self::$gp_url . '?' . urldecode(http_build_query(self::$gp_params));
//    }
//
//    public static function getUser($db, $external_id, $auth_type)
//    {
//        $res = $db->executeQuery('SELECT * FROM user WHERE external_id = ? and auth_type = ?', array(
//            $external_id,
//            $auth_type
//        ));
//
//        return $res->fetch();
//    }
//
//    public static function insertUser($db, $external_id, $first_name, $last_name, $auth_type)
//    {
//        $db->insert('user', array(
//            'external_id' => $external_id,
//            'first_name' => $first_name,
//            'last_name' => $last_name,
//            'auth_type' => $auth_type
//        ));
//    }
//
//    public static function existsInDb($db, $external_id, $auth_type)
//    {
//        $id = $db->fetchColumn('SELECT external_id FROM user WHERE external_id = ? and auth_type = ?', array(
//            $external_id,
//            $auth_type
//        ), 0);
//        if ($id) {
//            return true;
//        }
//
//        return false;
//    }
//
//    /**
//     * @param Request $request
//     * @param Application $app
//     * @param null $type
//     *
//     * @return string
//     * @throws \Exception
//     */
//    public static function authorize(Request $request, Application $app, $type = null)
//    {
//        switch ($type) {
//            case 'vk':
//                break;
//            default:
//                throw new \Exception('Auth type is not selected');
//        }
//    }
//
//    public static function vote($db, $external_id, $singer_id, $auth_type)
//    {
//        $preVote = $db->fetchColumn('select vote_count from vote where poll_option_id = ?;', array($singer_id), 0);
//        $preVote = intval($preVote);
//        if ($preVote > 0) {
//            //$db->executeQuery( 'update vote set vote_count = vote_count + 1 where poll_option_id = ?', array( $singer_id ) );
//        } else {
//            $db->insert('vote', array(
//                'poll_id' => 1,
//                'poll_option_id' => $singer_id,
//                'vote_count' => 1
//            ));
//        }
//        $db->update('user', array(
//            'voted' => '1',
//            'singer_id' => $singer_id
//        ), array('external_id' => $external_id));
//        $date = (new \DateTime('NOW'))->format('Y-m-d H:i:s');
//        $db->insert('vote_log', array(
//            'user_external_id' => $external_id,
//            'user_auth_type' => $auth_type,
//            'singer_id' => $singer_id,
//            'date' => $date
//        ));
//        $_SESSION['user_info']['db'] = self::getUser($db, $external_id, $auth_type);
//    }
//            = [
//            'vk' => [
//                'url' => '',
//                'client_id' => '',
//                'redirect_uri' => '',
//                'response_type' => ''
//            ],
//            'ok' => [
//                'url' => '',
//                'client_id' => '',
//                'response_type' => '',
//                'redirect_uri' => '',
//            ],
//            'mr' => [
//                'url' => '',
//                'client_id' => '',
//                'redirect_uri' => '',
//                'response_type' => ''
//            ],
//            'gp' => [
//                'url' => '',
//                'redirect_uri' => '',
//                'response_type' => '',
//                'client_id' => '',
//                'scope' => ''
//            ]
//        ];