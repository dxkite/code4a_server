<?php
namespace dxkite\userconnector\response\baidu;

use dxkite\support\visitor\response\Response;
use dxkite\support\visitor\Context;
use dxkite\userconnector\oauth\Baidu;

class CallbackResponse extends Response
{
    public function onVisit(Context $context)
    {
        if ($code=request()->get()->code) {
            if (visitor()->isGuest()) {
                $user=Baidu::signin($code);
                var_dump($user);
            }else{
                $user=Baidu::signin($code,true);
                var_dump($user);
            }
        } else {
            if (visitor()->isGuest()) {
                $this->go(u('user-connector:baidu-signin'));
            } else {
                $this->go(u('user-connector:baidu-bind'));
            }
        }
    }
}
