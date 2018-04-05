<?php
namespace dxkite\userconnector\response\baidu;

use dxkite\support\visitor\response\VisitorResponse;
use dxkite\support\visitor\Context;
use dxkite\userconnector\oauth\Baidu;

class SigninResponse extends VisitorResponse
{
    public function onGuestVisit(Context $context)
    {
        if ($access=session()->get('baidu_access_token')) {
            if ($userInfo=Baidu::getUserInfo($access)) {
                $exist=table('baidu_user')->select(['id','user','uid'], ['uid'=>$userInfo['uid']])->fetch();
                if ($exist) {
                    visitor()->signin($exist['user']);
                    echo '登陆成功 ' . $exist['user'];
                    return;
                }
            }
        }
        $this->go(Baidu::getAuthorizeUrl());
    }
    
    public function onUserVisit(Context $context)
    {
        $this->go(u('user-connector:baidu-bind'));
    }
}
