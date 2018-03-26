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

    /**
     * 编辑消息推送
     * @acl editPull
     * @param string $message
     * @param integer $time
     * @param string $url
     * @param string $color
     * @param string $bgColor
     * @return void
     */
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

    /**
     * 编辑广告图片
     * @acl editAds
     * @param File $image
     * @return void
     */
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

    /**
     * 编辑广告信息
     * @acl editAds
     * @param File $image
     * @return void
     */
    public static function editAdUrl(string $url)
    {
        return  setting_set('android-ads-url', $url);
    }

    /**
     * 开启消息推送
     *
     * @acl editPull
     * @param boolean $enable
     * @return void
     */
    public static function enableMessage(bool $enable=true)
    {
        return setting_set('androidMessageEnable', $enable);
    }

    
    public static function update()
    {
        $update = setting('android-app-update');
        if ($update) {
            $updateImageId = setting('android-app-update-icon',0);
            $update['icon'] =   u('support:upload', ['id'=> $updateImageId]);
        }
        return $update;
    }

    /**
     * 编辑App ICON
     *
     * @acl editUpdate
     * @param File $image
     * @return void
     */
    public static function editAppIcon(File $image)
    {
        $upload=Media::saveFile($image);
        if ($id=setting('android-app-update-icon')) {
            if (isset($id)) {
                Media::delete($id);
            }
        }
        if ($upload->getId()>0) {
            return setting_set('android-app-update-icon',$upload->getId());
        }
        return false;
    }

    /**
     * 编辑App Info
     *
     * @acl editUpdate
     * @param string $name
     * @param string $version
     * @param string $info
     * @param string $download
     * @return void
     */
    public static function editAppInfo(string $name, string $version, string $info, string $download)
    {
        $info= [
            'name'=>$name,
            'version'=>$version,
            'versionInfo'=>$info,
            'download'=>$download
        ];
        return setting_set('android-app-update', $info);
    }
}
