<?php
namespace dxkite\android\collection\hook;

use suda\template\Manager as TemplateManger;
use dxkite\android\collection\action\CollectionProvider;
class Setting
{
    public static function infoPanel($template)
    {
        $info=CollectionProvider::getCollectionInfo();
        // var_dump($info);
        TemplateManger::include('android-collection:info', $template)->set('collection',$info)->render();
    }
}
