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
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPaySystem;
use skeeks\cms\shop\models\ShopPersonType;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;

/**
 * Class AdminPaySystemController
 * @package skeeks\cms\shop\controllers
 */
class AdminPaySystemController extends AdminModelEditorController
{
    public $notSubmitParam = 'sx-not-submit';

    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = \Yii::t('skeeks/shop/app', 'Payment systems');
        $this->modelShowAttribute       = "name";
        $this->modelClassName           = ShopPaySystem::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
            [
                'index' =>
                [
                    "gridConfig" =>
                    [
                        'settingsData' =>
                        [
                            'order' => SORT_ASC,
                            'orderBy' => "priority",
                        ]
                    ],

                    "columns"      => [
                        'name',
                        'priority',

                        [
                            'class'         => DataColumn::className(),
                            'attribute'     => "personTypeIds",
                            'filter'        => false,
                            'value'         => function(ShopPaySystem $model)
                            {
                                return implode(", ", ArrayHelper::map($model->personTypes, 'id', 'name'));
                            }
                        ],

                        [
                            'class'         => BooleanColumn::className(),
                            'attribute'     => "active"
                        ]
                    ],
                ],

                'create' =>
                [
                    'callback'         => [$this, 'create'],
                ],

                'update' =>
                [
                    'callback'         => [$this, 'update'],
                ],
            ]
        );
    }


    public function create()
    {
        $rr = new RequestResponse();

        $modelClass = $this->modelClassName;
        /**
         * @var CmsContentProperty $model
         */
        $model = new $modelClass();
        $model->loadDefaultValues();

        if ($post = \Yii::$app->request->post())
        {
            $model->load($post);
        }

        $handler = $model->handler;

        if ($handler)
        {
            if ($post = \Yii::$app->request->post())
            {
                $handler->load($post);
            }
        }

        if ($rr->isRequestPjaxPost())
        {
            if (!\Yii::$app->request->post($this->notSubmitParam))
            {
                $model->component_settings = $handler->toArray();
                $handler->load(\Yii::$app->request->post());

                if ($model->load(\Yii::$app->request->post())
                    && $model->validate() && $handler->validate())
                {
                    $model->save();

                    \Yii::$app->getSession()->setFlash('success', \Yii::t('skeeks/cms','Saved'));

                    return $this->redirect(
                        UrlHelper::constructCurrent()->setCurrentRef()->enableAdmin()->setRoute($this->modelDefaultAction)->normalizeCurrentRoute()
                            ->addData([$this->requestPkParamName => $model->{$this->modelPkAttribute}])
                            ->toString()
                    );
                } else
                {
                    \Yii::$app->getSession()->setFlash('error', \Yii::t('skeeks/cms','Could not save'));
                }
            }
        }

        return $this->render('_form', [
            'model'     => $model,
            'handler'   => $handler,
        ]);
    }


    public function update()
    {
        $rr = new RequestResponse();

        $model = $this->model;

        if ($post = \Yii::$app->request->post())
        {
            $model->load($post);
        }

        $handler = $model->handler;
        if ($handler)
        {
            if ($post = \Yii::$app->request->post())
            {
                $handler->load($post);
            }
        }

        if ($rr->isRequestPjaxPost())
        {
            if (!\Yii::$app->request->post($this->notSubmitParam))
            {
                if ($rr->isRequestPjaxPost())
                {
                    $model->component_settings = $handler->toArray();
                    $handler->load(\Yii::$app->request->post());

                    if ($model->load(\Yii::$app->request->post())
                        && $model->validate() && $handler->validate())
                    {
                        $model->save();

                        \Yii::$app->getSession()->setFlash('success', \Yii::t('app','Saved'));

                        if (\Yii::$app->request->post('submit-btn') == 'apply')
                        {

                        } else
                        {
                            return $this->redirect(
                                $this->indexUrl
                            );
                        }

                        $model->refresh();

                    }
                }
            }
        }

        return $this->render('_form', [
            'model'     => $model,
            'handler'   => $handler,
        ]);
    }

}
