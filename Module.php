<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.08.2015
 */
namespace skeeks\cms\shop;
/**
 * Class Module
 * @package skeeks\cms\reviews2
 */
class Module extends \skeeks\cms\base\Module
{
    public $controllerNamespace = 'skeeks\cms\shop\controllers';

    /**
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            "version"               => file_get_contents(__DIR__ . "/VERSION"),

            "name"          => "Магазин",
        ]);
    }

}