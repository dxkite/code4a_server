<?php
namespace dxkite\android\collection\action;
use dxkite\android\collection\table\Collection;

class CollectionProvider
{
   public static function getCollectionInfo() {
        $table =new Collection;
        $row=$table->query('SELECT count( DISTINCT  device) as number FROM #{@table@}#;')->fetch();
        return ['device'=>$row['number']];
   }
}