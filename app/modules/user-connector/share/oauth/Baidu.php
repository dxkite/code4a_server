<?php
namespace dxkite\userconnector\oauth;

use suda\template\Manager as TemplateManger;
use cn\atd3\visitor\Visitor;

class Baidu
{
    public static function adminItem($template)
    {
        TemplateManger::include('user-connector:baidu/setting', $template)->render();
    }

    /**
     * 使用百度用户登陆
     * 只允许游客身份使用
     *
     * @param string $code
     * @return bool true|false|id 登陆成功|失败|创建UID
     */
    public static function signin(string $code, bool $bind=false)
    {
        $visitor = visitor();
        $info=self::getAccessToken($code);
        if (isset($info['error'])) {
            return false; // throw new Exception($info['error_description']);
        }
        if ($bind == false && $visitor->isGuest() == false) {
            return false;
        }
        $userInfo= self::getUserInfo($info['access_token']);
        session()->set('baidu_access_token', $info['access_token']);
        $baiduUser = table('baidu_user');
        $userTable = table('user');
        $renameTable =table('oauth2_rename');
        if ($userInfo && isset($userInfo['uid'])) {
            $exist=$baiduUser->select(['id','user','uid'], ['uid'=>$userInfo['uid']])->fetch();
            // 用户名已经使用过百度注册账号
            // 创建临时ID
            if ($exist) {
                // 登陆账号
                if ($visitor->isGuest()) {
                    $visitor->signin($exist['user']);
                }
                // 更新Token
                $baiduUser->update([
                    'access_token'=>$info['access_token'],
                    'refresh_token'=>$info['refresh_token'],
                    'scope'=>$info['scope'],
                    'expires_in'=> time() + $info['expires_in'],
                ], ['uid'=>$userInfo['uid']]);
                return true; // 登陆成功
            } else {
                // 创建新的用户
                $data=[
                    'uid'=>$userInfo['uid'],
                    'uname'=>$userInfo['uname'],
                    'portrait'=>$userInfo['portrait'],
                    'access_token'=>$info['access_token'],
                    'refresh_token'=>$info['refresh_token'],
                    'scope'=>$info['scope'],
                    'expires_in'=> time() + $info['expires_in'],
                ];
                
                if ($visitor->isGuest() || $bind =false) {
                    $newUser=[
                            'signupTime'=>time(),
                            'signupIp'=>request()->ip(),
                            'status'=> $userTable ::STATUS_ACTIVE,
                            'name'=>$userInfo['uname']
                    ];
                    $needRename = false;
                    // 用户名冲突
                    if ($userTable->getByName($userInfo['uname'])) {
                        $newUser['name']='百度用户'.$userInfo['uname'];
                        $needRename = true;
                    }
                    // 创建新用户
                    $userId= $userTable ->insert($newUser);
                    if ($userId) {
                        $data['user']=$userId;
                        // 绑定百度
                        $baiduUser->insert($data);
                        // 登陆用户
                        $visitor->signin($userId);
                        if ($needRename) {
                            $renameTable->insert([
                                'user'=>$userId,
                                'bind'=> $renameTable::BAIDU,
                                'state'=>1,
                            ]);
                        }
                        return $userId;
                    }
                } elseif ($userId =$visitor->getId()) {
                    $data['user']=$userId;
                    // 绑定百度
                    $baiduUser->insert($data);
                }
                return false;
            }
        } else {
            return false; // 登陆失败
        }
    }

    public static function getUserInfo(string $token)
    {
        $data=self::curl('https://openapi.baidu.com/rest/2.0/passport/users/getLoggedInUser?access_token='.$token);
        if ($data) {
            return json_decode($data, true);
        }
        return false;
    }

    public static function getAuthorizeUrl()
    {
        $redirectUrl=u('user-connector:baidu-callback');
        $queryStrArr=[
            'scope'=>setting('baidu-scope', 'basic'),
            'client_id'=>setting('baidu-client-id'),
            'redirect_uri'=>$redirectUrl,
        ];
        $url=setting('baidu-auth-url', 'http://openapi.baidu.com/oauth/2.0/authorize?response_type=code&display=popup');
        return static::urlAppendQuery($url, $queryStrArr);
    }

    protected static function getAccessToken(string $code)
    {
        $redirectUrl=u('user-connector:baidu-callback');
        $queryStrArr=[
            'grant_type'=>setting('baidu-grant-type', 'authorization_code'),
            'code'=>$code,
            'client_id'=>setting('baidu-client-id'),
            'client_secret'=>setting('baidu-client-secret'),
            'redirect_uri'=>$redirectUrl,
        ];
        $baiduUrl=setting('baidu-access-token-url', 'https://openapi.baidu.com/oauth/2.0/token');
        $url=static::urlAppendQuery($baiduUrl, $queryStrArr);
        $json=static::curl($url);
        return json_decode($json, true);
    }

    public static function urlAppendQuery(string $url, array $queryStrArr)
    {
        $parsed=parse_url($url);
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query);
            $queryStrArr=array_merge($query, $queryStrArr);
        }
        $queryStr=http_build_query($queryStrArr);
        $scheme=$parsed['scheme']??'http';
        $host=$parsed['host']??'localhost';
        $port=isset($parsed['port'])  && $parsed['port']!=80?':'.$parsed['port']:'';
        $path=$parsed['path']??'/';
        return $scheme.'://'.$host.$port.$path.'?'.$queryStr;
    }

    public static function curl(string $url)
    {
        $ch=curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 500);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $file=curl_exec($ch);
        curl_close($ch);
        return $file;
    }
}
