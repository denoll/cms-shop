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
use skeeks\cms\grid\SiteColumn;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsUser;
use skeeks\cms\modules\admin\actions\AdminAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminModelEditorCreateAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelEditAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelUpdateAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopAffiliate;
use skeeks\cms\shop\models\ShopAffiliatePlan;
use skeeks\cms\shop\models\ShopBasket;
use skeeks\cms\shop\models\ShopBuyer;
use skeeks\cms\shop\models\ShopContent;
use skeeks\cms\shop\models\ShopExtra;
use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\shop\models\ShopTax;
use skeeks\cms\shop\models\ShopVat;
use yii\base\Exception;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * Class AdminExtraController
 * @package skeeks\cms\shop\controllers
 */
class AdminOrderController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = \Yii::t('skeeks/shop/app', 'Orders');
        $this->modelShowAttribute       = "id";
        $this->modelClassName           = ShopOrder::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $view = $this->view;

        return ArrayHelper::merge(parent::actions(),
            [

                'create' =>
                [
                    'visible'         => false,
                ],

                'create-order' =>
                [
                    'class'         => AdminAction::className(),
                    'name'          => \Yii::t('skeeks/shop/app','Place your order'),
                    "icon"          => "glyphicon glyphicon-plus",
                    "callback"      => [$this, 'createOrder'],
                ],

                'index' =>
                [
                    "columns"               => [
                        [
                            'class'     => CreatedAtColumn::className(),
                        ],

                        'id',

                        [
                            'class'     => DataColumn::className(),
                            'attribute'     => 'status_code',
                            'format'     => 'raw',
                            'filter'     => ArrayHelper::map(ShopOrderStatus::find()->all(), 'code', 'name'),
                            'value'     => function(ShopOrder $order)
                            {
                                return Html::label($order->status->name, null, [
                                    'style' => "background: {$order->status->color}",
                                    'class' => "label"
                                ]) . "<br />" .
                                    Html::tag("small", \Yii::$app->formatter->asDatetime($order->status_at) . " (" . \Yii::$app->formatter->asRelativeTime($order->status_at) . ")")
                                ;
                            }
                        ],

                        /*[
                            'class'     => DataColumn::className(),
                            'attribute' => 'buyer_id',
                            'format'    => 'raw',
                            'value'     => function(ShopOrder $model)
                            {
                                if (!$model->buyer)
                                {
                                    return null;
                                }

                                return Html::a($model->buyer->name . " [{$model->buyer->id}]", UrlHelper::construct('shop/admin-buyer/related-properties', ['pk' => $model->buyer->id])->enableAdmin()->toString());
                            }
                        ],*/

                        [
                            'class'         => BooleanColumn::className(),
                            'attribute'     => 'payed',
                            'format'        => 'raw',
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'attribute'     => "canceled",
                            'format'        => "raw",
                            'filter'        => [
                                'Y' => \Yii::t('skeeks/shop/app', 'Yes'),
                                'N' => \Yii::t('skeeks/shop/app', 'No'),
                            ],

                            'value' => function(ShopOrder $shopOrder, $key, $index) use ($view)
                            {
                                $reuslt = "<div>";
                                if ($shopOrder->canceled == "Y")
                                {
                                    $view->registerJs(<<<JS
$('tr[data-key={$key}]').addClass('sx-tr-red');
JS
);

                                    $view->registerCss(<<<CSS
tr.sx-tr-red, tr.sx-tr-red:nth-of-type(odd), tr.sx-tr-red td
{
    background: #FFECEC !important;
}
CSS
);
                                    $reuslt = "<div style='color: red;'>";
                                }

                                $reuslt .=  $shopOrder->canceled == "Y" ? \Yii::t('skeeks/shop/app', 'Yes') : \Yii::t('skeeks/shop/app', 'No');
                                $reuslt .= "</div>";
                                return $reuslt;
                            }
                        ],


                        [
                            'class'         => DataColumn::className(),
                            'attribute'     => "user_id",
                            'label'         => \Yii::t('skeeks/shop/app', 'Buyer'),
                            'format'        => "raw",
                            'value'         => function(ShopOrder $shopOrder)
                            {
                               return (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $shopOrder->user]))->run();
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => false,
                            'format'        => 'raw',
                            'label'         => \Yii::t('skeeks/shop/app', 'Good'),
                            'value'         => function(ShopOrder $model)
                            {
                                if ($model->shopBaskets)
                                {
                                    $result = [];
                                    foreach ($model->shopBaskets as $shopBasket)
                                    {
                                        $money = \Yii::$app->money->intlFormatter()->format($shopBasket->money);
                                        $result[] = Html::a($shopBasket->name, $shopBasket->product->cmsContentElement->url, [
                                                'target' => '_blank',
                                                'data-pjax' => '0'
                                            ]) . <<<HTML
  — $shopBasket->quantity $shopBasket->measure_name
HTML;

                                    }
                                    return implode('<hr style="margin: 0px;"/>', $result);
                                }
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'format'        => 'raw',
                            'attribute'     => 'price',
                            'label'         => \Yii::t('skeeks/shop/app', 'Sum'),
                            'value'         => function(ShopOrder $model)
                            {
                                return \Yii::$app->money->intlFormatter()->format($model->money);
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => ArrayHelper::map(CmsSite::find()->active()->all(), 'id', 'name'),
                            'attribute'     => 'site_id',
                            'format'        => 'raw',
                            'visible'       => false,
                            'label'         => \Yii::t('skeeks/shop/app', 'Site'),
                            'value'         => function(ShopOrder $model)
                            {
                                return $model->site->name . " [{$model->site->code}]";
                            },
                        ],

                        [
                            'filter'        => ArrayHelper::map(\Yii::$app->shop->stores, 'id', 'name'),
                            'attribute'     => 'store_id',
                            'format'        => 'raw',
                            'visible'       => false,
                            'label'         => \Yii::t('skeeks/shop/app', 'Store'),
                            'value'         => function(ShopOrder $model)
                            {
                                return $model->store->name;
                            },
                        ],
                    ],
                ],

                /*"view" =>
                [
                    'class'         => AdminOneModelEditAction::className(),
                    "name"         => \Yii::t('skeeks/shop/app',"Информация"),
                    "icon"          => "glyphicon glyphicon-eye-open",
                    "priority"      => 5,
                    "callback"      => [$this, 'view'],
                ],*/

            ]
        );
    }

    public function view()
    {
        return $this->render($this->action->id, [
            'model' => $this->model
        ]);
    }

    /**
     * @return array
     */
    public function actionPayValidate()
    {
        $rr = new RequestResponse();
        return $rr->ajaxValidateForm($this->model);
    }

    /**
     * @return array
     */
    public function actionValidate()
    {
        $rr = new RequestResponse();
        return $rr->ajaxValidateForm($this->model);
    }

    /**
     * @return array
     */
    public function actionPay()
    {
        $rr = new RequestResponse();

        /**
         * @var $model ShopOrder;
         */
        $model = $this->model;
        if ($model->load(\Yii::$app->request->post()) && $model->save())
        {
            $rr->success = true;

            if ($model->payed != "Y")
            {
                $model->processNotePayment();
            } else
            {
                if (\Yii::$app->request->post('payment-close') == 1)
                {
                    $model->processCloseNotePayment();
                }
            }

            return $rr;
        }
    }

    /**
     * @return array
     */
    public function actionSave()
    {
        $rr = new RequestResponse();

        /**
         * @var $model ShopOrder;
         */
        $model = $this->model;
        if ($model->load(\Yii::$app->request->post()) && $model->save())
        {
            $rr->success = true;
            return $rr;
        }
    }




    /**
     * @return array
     */
    public function actionCreateOrderFuserSave()
    {
        $rr = new RequestResponse();

        $model = null;
        if ($id = \Yii::$app->request->get('shopFuserId'))
        {
            $model = ShopFuser::findOne($id);
        }

        if ($model->load(\Yii::$app->request->post()) && $model->save())
        {
            $rr->success = true;
            return $rr;
        } else
        {
            $rr->success = false;
            print_r($model->getErrors());die;
            $rr->message = implode(',', $model->getFirstError());
            return $rr;
        }
    }


    /**
     * @return array
     */
    public function actionCreateOrderAddProduct()
    {
        $rr = new RequestResponse();

        $shopFuser = null;
        if ($id = \Yii::$app->request->get('shopFuserId'))
        {
            $shopFuser = ShopFuser::findOne($id);
        }


        if ($rr->isRequestAjaxPost())
        {
            $product_id         = \Yii::$app->request->post('product_id');
            $quantity           = \Yii::$app->request->post('quantity');

            /**
             * @var ShopProduct $product
             */
            $product = ShopProduct::find()->where(['id' => $product_id])->one();

            if (!$product)
            {
                $rr->message = \Yii::t('skeeks/shop/app', 'This product is not found, it may be removed.');
                return (array) $rr;
            }

            $shopBasket = ShopBasket::find()->where([
                'fuser_id'      => $shopFuser->id,
                'product_id'    => $product_id,
                'order_id'      => null,
            ])->one();

            if (!$shopBasket)
            {
                $shopBasket = new ShopBasket([
                    'fuser_id'          => $shopFuser->id,
                    'product_id'        => $product->id,
                    'quantity'          => 0,
                ]);
            }

            $shopBasket->quantity                   = $shopBasket->quantity + $quantity;


            if (!$shopBasket->recalculate()->save())
            {
                $rr->success = false;
                $rr->message = \Yii::t('skeeks/shop/app', 'Failed to add item to cart');
            } else
            {
                $rr->success = true;
                $rr->message = \Yii::t('skeeks/shop/app', 'Item added to cart');
            }

            $shopFuser->link('site', \Yii::$app->cms->site);
            $rr->data = $shopFuser->toArray([], $shopFuser->extraFields());
            return (array) $rr;
        } else
        {
            return $this->goBack();
        }
    }

    /**
     * @return array
     */
    public function actionUpdateOrderAddProduct()
    {
        $rr = new RequestResponse();

        if ($this->model)
        {
            $model = $this->model;
        }


        if ($rr->isRequestAjaxPost())
        {
            $product_id         = \Yii::$app->request->post('product_id');
            $quantity           = \Yii::$app->request->post('quantity');

            /**
             * @var ShopProduct $product
             */
            $product = ShopProduct::find()->where(['id' => $product_id])->one();

            if (!$product)
            {
                $rr->message = \Yii::t('skeeks/shop/app', 'This product is not found, it may be removed.');
                return (array) $rr;
            }

            $shopBasket = ShopBasket::find()->where([
                'order_id'      => $model->id,
                'product_id'    => $product_id,
                'fuser_id'      => null,
            ])->one();

            if (!$shopBasket)
            {
                $shopBasket = new ShopBasket([
                    'order_id'          => $model->id,
                    'product_id'        => $product->id,
                    'quantity'          => 0,
                ]);
            }

            $shopBasket->quantity                   = $shopBasket->quantity + $quantity;


            if (!$shopBasket->recalculate()->save())
            {
                $rr->success = false;
                $rr->message = \Yii::t('skeeks/shop/app', 'Failed to add item to cart');
            } else
            {
                $rr->success = true;
                $rr->message = \Yii::t('skeeks/shop/app', 'Item added to cart');
            }

            $rr->data = $model->toArray([], $model->extraFields());
            return (array) $rr;
        } else
        {
            return $this->goBack();
        }
    }



    public function createOrder()
    {
        $cmsUser = null;
        if ($userId = \Yii::$app->request->get('cmsUserId'))
        {
            $cmsUser = CmsUser::findOne($userId);
        }

        if ($cmsUser)
        {
            /**
             * @var $shopFuser ShopFuser
             */
            $shopFuser = ShopFuser::getInstanceByUser($cmsUser);
            $model = $shopFuser;

            $rr = new RequestResponse();

            if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax)
            {
                $model->scenario = ShopFuser::SCENARIO_CREATE_ORDER;
                return $rr->ajaxValidateForm($model);
            }

            if ($rr->isRequestPjaxPost())
            {
                try
                {
                    if ($model->load(\Yii::$app->request->post()) && $model->save())
                    {

                        $model->scenario = ShopFuser::SCENARIO_CREATE_ORDER;

                        if ($model->validate())
                        {
                            $order = ShopOrder::createOrderByFuser($model);

                            if (!$order->isNewRecord)
                            {
                                \Yii::$app->getSession()->setFlash('success',
                                    \Yii::t('skeeks/shop/app', 'The order #{order_id} created successfully', ['order_id' => $order->id])
                                );

                                if (\Yii::$app->request->post('submit-btn') == 'apply')
                                {
                                    return $this->redirect(
                                        UrlHelper::constructCurrent()->setCurrentRef()->enableAdmin()->setRoute($this->modelDefaultAction)->normalizeCurrentRoute()
                                            ->addData([$this->requestPkParamName => $order->id])
                                            ->toString()
                                    );
                                } else
                                {
                                    return $this->redirect(
                                        $this->indexUrl
                                    );
                                }


                            } else
                            {
                                throw new Exception(\Yii::t('skeeks/shop/app', 'Incorrect data of the new order').": " . array_shift($order->getFirstErrors()));
                            }

                        } else
                        {
                            throw new Exception(\Yii::t('skeeks/shop/app', 'Not enogh data for ordering').": " . array_shift($model->getFirstErrors()));
                        }
                    } else
                    {
                        throw new Exception(\Yii::t('skeeks/shop/app','Could not save'));
                    }
                } catch(\Exception $e)
                {
                    \Yii::$app->getSession()->setFlash('error', $e->getMessage());
                }

            }

            return $this->render($this->action->id, [
                'cmsUser'   => $cmsUser,
                'shopFuser' => $model
            ]);
        } else
        {
            return $this->render($this->action->id . "-select-user");
        }
    }

}
