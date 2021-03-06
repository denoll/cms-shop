<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
namespace skeeks\cms\shop\controllers;

use skeeks\cms\components\Cms;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\CreatedAtColumn;
use skeeks\cms\grid\CreatedByColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\modules\admin\widgets\AdminImagePreviewWidget;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopViewedProduct;
use skeeks\cms\shop\widgets\AdminBuyerUserWidget;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class AdminOrderStatusController
 * @package skeeks\cms\shop\controllers
 */
class AdminViewedProductController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = \Yii::t('skeeks/shop/app', 'Viewed products');
        $this->modelShowAttribute       = "name";
        $this->modelClassName           = ShopViewedProduct::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
            [
                'create' =>
                [
                    'visible' => false
                ],

                'update' =>
                [
                    'visible' => false
                ],

            ]
        );
    }

}
