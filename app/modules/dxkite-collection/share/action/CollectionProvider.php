<?php
namespace dxkite\android\collection\action;
use dxkite\android\collection\table\Collection;

class CollectionProvider
{
   public static function getCollectionInfo() {
        $table =new Collection;
        $row=$table->query('SELECT count(DISTINCT  device) as number FROM #{@table@}#;')->fetch();
        $boot=$table->query('SELECT count(device) as boot FROM #{@table@}#;')->fetch();
        $ipTimes=$table->query('SELECT count(DISTINCT ip) as ipTimes FROM #{@table@}#;')->fetch();

        return ['device'=>$row['number'],'boot'=>$boot['boot'],'ipTimes'=>$ipTimes['ipTimes']];
   }
}