<?php
namespace dxkite\android\message\response\setting;

use dxkite\support\visitor\Context;
use dxkite\user\table\UserTable;
use  dxkite\android\message\Message;
use dxkite\support\file\File;

class ManagerResponse extends \dxkite\support\setting\Response
{
    public function onAdminView($view, $context)
    {
        if (request()->hasPost()) {
            if (request()->get()->action == 'color') {
                Message::editPull(
                    request()->post('message'),
                    request()->post('time', 5000),
                    request()->post('url'),
                    request()->post('color'),
                    request()->post('bgcolor')
                );
                Message:: enableMessage(request()->post('show', true));
                $this->refresh();
                return false;
            } elseif (request()->get()->action == 'image') {
                if (request()->post('add_url')) {
                    Message::editAdUrl(request()->post('add_url', 'http://code4a.atd3.cn'));
                }
                $file=request()->files('image') ;
                if ($file && $file['error']==0) {
                    $file=File::createFromPost('image');
                    Message::editAdImage($file);
                }
            } elseif (request()->get()->action == 'update') {
                $file=request()->files('icon') ;
                if ($file && $file['error']==0) {
                    $file=File::createFromPost('icon');
                    Message::editAppIcon($file);
                }
                $update = request()->post('update');
                Message::editAppInfo(
                    $update['name'],
                    $update['version'],
                    $update['versionInfo'],
                    $update['download']
                );
            }
        }
        $message = setting('androidMessage');
        if ($message) {
            $view->set('message', $message);
        }
        $ads = setting('android-ads-url');
        $image = setting('android-ads-image');
        if ($ads) {
            $view->set('ads', $ads);
        }
        if ($image) {
            $view->set('image', $image);
        }
        $update = setting('android-app-update');
        if ($update) {
            $view->set('update', $update);
        }
        $view->set('show', setting('androidMessageEnable'));
        return true;
    }

    public function adminContent($template)
    {
        \suda\template\Manager::include('android-message:setting/manager', $template)->render();
    }
}
