<?php
namespace dxkite\android\message;

use dxkite\support\proxy\ProxyObject;
use  dxkite\support\file\Media;
use dxkite\support\file\File;

class Message extends ProxyObject
{
    public static function pull()
    {
        $message = setting('androidMessage', false);
        if ($message && setting('androidMessageEnable', false)) {
            return $message;
        }
        return false;
    }

    public static function pullAd()
    {
        $image= setting('android-ads-image', false);
        if ($image) {
            $image['url']=setting('android-ads-url');
            return $image;
        }
        return false;
    }

    public static function editPull(string $message, int $time=10000, string $url=null, string $color='#222222', string $bgColor='#EEEEEE')
    {
        $message = [
            'message' => $message,
            'url'=>$url,
            'time'=>$time,
            'color'=>$color,
            'backgroundColor'=> $bgColor,
            'touchable' => !empty($url),
            'create'=>time(),
        ];
        return setting_set('androidMessage', $message);
    }

    public static function editAdImage(File $image)
    {
        $upload=Media::saveFile($image);
        if ($old=setting('android-ads-image')) {
            if (isset($old['imageId'])) {
                Media::delete($old['imageId']);
            }
        }
        if ($upload->getId()>0) {
            $ads=[
                'image'=>  u('support:upload', ['id'=>$upload->getId()]),
                'imageId'=> $upload->getId(),
            ];
            return setting_set('android-ads-image', $ads);
        }
        return false;
    }

    public static function editAdUrl(string $url)
    {
        return  setting_set('android-ads-url', $url);
    }

    public static function enableMessage(bool $enable=true)
    {
        return setting_set('androidMessageEnable', $enable);
    }

    public static function update() {
        return  [
            'icon'=>'http://code4a.atd3.cn/icon',
            'name'=>'Code4A',
            'version'=>'1.0.0-beta',
            'versionInfo'=>'添加了用户更新',
            'download'=>'https://github.com/TTHHR/code4a/releases/tag/v1.0.0-beta'
        ];
    }
}
