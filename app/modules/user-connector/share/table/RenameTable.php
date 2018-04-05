<?php
namespace dxkite\userconnector\table;
use suda\archive\Table;

/**
 * 改名表
 */
class RenameTable extends Table {
    const BAIDU = 1;

    public function __construct(){
        parent::__construct('oauth2_rename');
    }

    public function onBuildCreator($table){
        return $table->fields(
            $table->field('id', 'bigint', 20)->primary()->unsigned()->auto(),
            $table->field('user', 'bigint', 20)->unsigned()->key()->comment('内部UID'),
            $table->field('bind', 'int', 20)->unsigned()->key()->comment('改明绑定'),
            $table->field('state', 'tinyint', 1)->key()
        );
    }
}