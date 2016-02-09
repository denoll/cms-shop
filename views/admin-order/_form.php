<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopOrder */

$this->registerCss(<<<CSS

.sx-dashed
{

    border-bottom: dashed 1px;
}

.sx-dashed:hover
{
    text-decoration: none;
}

.datetimepicker
{
    z-index: 100000 !important;
}
CSS
);


$this->registerJs(<<<JS
(function(sx, $, _)
{
    sx.classes.OrderCallback = sx.classes.Component.extend({

        construct: function (jForm, ajaxQuery, opts)
        {
            var self = this;
            opts = opts || {};

            this._jForm     = jForm;
            this._ajaxQuery = ajaxQuery;

            this.applyParentMethod(sx.classes.Component, 'construct', [opts]); // TODO: make a workaround for magic parent calling
        },

        _init: function()
        {
            var jForm   = this._jForm;
            var ajax    = this._ajaxQuery;

            var handler = new sx.classes.AjaxHandlerStandartRespose(ajax, {
                'blockerSelector' : '#' + jForm.attr('id'),
                'enableBlocker' : true,
            });

            handler.bind('success', function(response)
            {
                $.pjax.reload('#sx-pjax-order-wrapper', {});
                $.fancybox.close();
            });
        }
    });

})(sx, sx.$, sx._);
JS
);

$statusDate = \Yii::$app->formatter->asDatetime($model->status_at);
?>

<h1 style="text-align: center;">Просмотр заказа ID (<?= $model->id ?>), № <?= $model->id ?>, создан <?= \Yii::$app->formatter->asDatetime($model->created_at); ?></h1>

<?php $form = ActiveForm::begin([
    'pjaxOptions' =>
    [
        'id' => 'sx-pjax-order-wrapper'
    ]
]); ?>

<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'General information')); ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'Order')

    ])?>

        <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' =>
            [
                /*[                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Number of order'),
                    'format' => 'raw',
                    'value' => $model->id,
                ],

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Created At'),
                    'format' => 'raw',
                    'value' => \Yii::$app->formatter->asDatetime($model->created_at),
                ],*/

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Last modified'),
                    'format' => 'raw',
                    'value' => \Yii::$app->formatter->asDatetime($model->updated_at),
                ],

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Status'),
                    'format' => 'raw',
                    'value' => <<<HTML

                    <a href="#sx-status-change" class="sx-dashed sx-fancybox" style="color: {$model->status->color}">{$model->status->name}</a>
                    <small>({$statusDate})</small>
HTML

                ],

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Canceled'),
                    'format' => 'raw',
                    'value' => $this->render('_close-order', [
                        'model' => $model
                    ]),
                ],

                /*[                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Date of status change'),
                    'format' => 'raw',
                    'value' => \Yii::$app->formatter->asDatetime($model->status_at),
                ],*/

            ]
        ])?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'Buyer')
    ])?>

        <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' =>
            [
                [                      // the owner name of the model
                    'label'     => \skeeks\cms\shop\Module::t('app', 'User'),
                    'format'    => 'raw',
                    'value'     => (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $model->user]))->run()
                ],

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Type payer'),
                    'format' => 'raw',
                    'value' => $model->personType->name,
                ],

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Profile of buyer'),
                    'format' => 'raw',
                    'value' => Html::a($model->buyer->name . " [{$model->buyer->id}]", \skeeks\cms\helpers\UrlHelper::construct(['/shop/admin-buyer/update', 'pk' => $model->buyer->id ])->enableAdmin(), [
                        'data-pjax' => 0
                    ] ),
                ],


            ]
        ])?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'Customer data')
    ])?>
        <?= \yii\widgets\DetailView::widget([
            'model' => $model->buyer->relatedPropertiesModel,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' => array_keys($model->buyer->relatedPropertiesModel->attributeValues())

        ])?>


    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'Payment order')
    ])?>
        <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' =>
            [
                [                      // the owner name of the model
                    'label'     => \skeeks\cms\shop\Module::t('app', 'Payment method'),
                    'format'    => 'raw',
                    'value'     => $model->paySystem->name,
                ],

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Payed'),
                    'format' => 'raw',
                    'value' => $this->render("_payed", [
                        'model' => $model
                    ])
                ],

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Allow payment'),
                    'format' => 'raw',
                    'value' => $this->render('_payment-allow', [
                        'model' => $model
                    ]),
                ],

                [                      // the owner name of the model
                    'label' => "",
                    'format' => 'raw',
                    'value' => $this->render('_payment', [
                        'model' => $model
                    ])

                ],
            ]
        ])?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'Shipping')
    ])?>

        <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' =>
            [
                [                      // the owner name of the model
                    'label'     => \skeeks\cms\shop\Module::t('app', 'Delivery service'),
                    'format'    => 'raw',
                    'value'     => $model->delivery->name,
                ],


                [                      // the owner name of the model
                    'label' => 'Разрешить доставку',
                    'format' => 'raw',
                    'value' => $this->render('_delivery-allow', [
                        'model' => $model
                    ]),
                ],
            ]
        ])?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => 'Комментарий'
    ])?>
    <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' =>
            [
                [                      // the owner name of the model
                    'label'     => \skeeks\cms\shop\Module::t('app', 'Comment'),
                    'format'    => 'raw',
                    'value'     => $this->render('_comment', [
                        'model' => $model
                    ])

                ],
            ]
        ])?>


    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'The composition of the order')
    ])?>



<?

$json = \yii\helpers\Json::encode([
    'createUrl' => \skeeks\cms\helpers\UrlHelper::construct('/shop/admin-basket/create', [
                    'order_id'      => $model->id,
                ])
                ->setSystemParam(\skeeks\cms\modules\admin\Module::SYSTEM_QUERY_EMPTY_LAYOUT, 'true')
                ->setSystemParam(\skeeks\cms\modules\admin\Module::SYSTEM_QUERY_NO_ACTIONS_MODEL, 'true')
                ->enableAdmin()->toString()
]);

$onclick = new \yii\web\JsExpression(<<<JS
    new sx.classes.AddPosition({$json}).open(); return true;
JS
);
?>

        <?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
            'label'             => "",
            'parentModel'       => $model,
            'relation'          => [
                'order_id'      => 'id',
            ],

            'sort'              => [
                'defaultOrder' =>
                [
                    'updated_at' => SORT_DESC
                ]
            ],

            'controllerRoute'   => 'shop/admin-basket',
            'gridViewOptions'   => [
                'enabledPjax' => false,
            'beforeTableLeft' => <<<HTML
            <a class="btn btn-default btn-sm" onclick="new sx.classes.SelectProduct().open(); return true;"><i class="glyphicon glyphicon-plus"></i>Добавить товар</a>
            <a class="btn btn-default btn-sm" onclick='{$onclick}'><i class="glyphicon glyphicon-plus"></i>Добавить позицию</a>
HTML
        ,

                'columns' => [

                    [
                        'class' => \skeeks\cms\shop\grid\BasketImageGridColumn::className(),
                    ],

                    [
                        'class' => \skeeks\cms\shop\grid\BasketNameGridColumn::className(),
                    ],

                    [
                        'class' => \skeeks\cms\shop\grid\BasketQuantityGridColumn::className(),
                    ],

                    [
                        'class' => \yii\grid\DataColumn::className(),
                        'label' => \skeeks\cms\shop\Module::t('app', 'Price'),
                        'attribute' => 'price',
                        'format' => 'raw',
                        'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                        {
                            if ($shopBasket->discount_value)
                            {
                                return "<span style='text-decoration: line-through;'>" . \Yii::$app->money->intlFormatter()->format($shopBasket->moneyOriginal) . "</span><br />". Html::tag('small', $shopBasket->notes) . "<br />" . \Yii::$app->money->intlFormatter()->format($shopBasket->money) . "<br />" . Html::tag('small', \skeeks\cms\shop\Module::t('app', 'Discount').": " . $shopBasket->discount_value);
                            } else
                            {
                                return \Yii::$app->money->intlFormatter()->format($shopBasket->money) . "<br />" . Html::tag('small', $shopBasket->notes);
                            }

                        }
                    ],

                    [
                        'class' => \skeeks\cms\shop\grid\BasketSumGridColumn::className()
                    ],
                ]
            ],
        ]); ?>


        <div class="row">
            <div class="col-md-8"></div>
            <div class="col-md-4">
                    <div class="sx-result">
                <?
                $this->registerCss(<<<CSS
.sx-result
{
    background-color: #ecf2d3;
}
CSS
);
                ?>
                <?=
                \yii\widgets\DetailView::widget([
                    'model' => $model,
                    "template" => "<tr><th>{label}</th><td style='text-align: right;'>{value}</td></tr>",
                    "options" => ['class' => 'sx-result-table table detail-view'],
                    'attributes' => [
                        [
                            'label' => \skeeks\cms\shop\Module::t('app', 'The total value of the goods'),
                            'value' => \Yii::$app->money->intlFormatter()->format($model->basketsMoney),
                        ],

                        [
                            'label' => \skeeks\cms\shop\Module::t('app', 'Discount, margin'),
                            'value' => \Yii::$app->money->intlFormatter()->format($model->moneyDiscount),
                        ],

                        [
                            'label' => \skeeks\cms\shop\Module::t('app', 'Delivery service'),
                            'value' => \Yii::$app->money->intlFormatter()->format($model->moneyDelivery),
                        ],

                        [
                            'label' => \skeeks\cms\shop\Module::t('app', 'Taxe'),
                            'value' => \Yii::$app->money->intlFormatter()->format($model->moneyVat),
                        ],

                        [
                            'label' => \skeeks\cms\shop\Module::t('app', 'Weight (gramm)'),
                            'value' => $model->weight . " ".\skeeks\cms\shop\Module::t('app', 'g.'),
                        ],

                        [
                            'label' => \skeeks\cms\shop\Module::t('app', 'Already paid'),
                            'value' => \Yii::$app->money->intlFormatter()->format($model->moneySummPaid),
                        ],

                        [
                            'label' => \skeeks\cms\shop\Module::t('app', 'In total'),
                            'format' => 'raw',
                            'value' => Html::tag('b', \Yii::$app->money->intlFormatter()->format($model->money)),
                        ]
                    ]
                ])
                ?>
                    </div>
            </div>
        </div>



<?

\skeeks\cms\shop\assets\ShopAsset::register($this);

    $shopJson = \yii\helpers\Json::encode([

        'backend-add-product' => \skeeks\cms\helpers\UrlHelper::construct([
            '/shop/admin-order/update-order-add-product', 'pk' => $model->id
        ])->enableAdmin()->toString(),

    ]);


$this->registerJs(<<<JS

    sx.classes.SelectProduct = sx.classes.Component.extend({

        open: function()
        {
            $('#sx-add-product .sx-btn-create').click()
            return this;
        }
    });

    sx.classes.AdminShop = sx.classes.shop.App.extend({});
    sx.AdminShop = new sx.classes.AdminShop({$shopJson});
    sx.AdminShop.bind('addProduct', function()
    {
        $.pjax.reload('#sx-pjax-order-wrapper');
    });


    sx.classes.AddPosition = sx.classes.Component.extend({

        open: function()
        {
            var self = this;
            var window = new sx.classes.Window(this.get('createUrl'));
            window.bind("close", function()
            {
                $.pjax.reload('#sx-pjax-order-wrapper');
            });

            window.open();
        }
    });

JS
);

?>

<?= $form->fieldSetEnd(); ?>



<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Транзакции по заказу')); ?>

    <?= \skeeks\cms\modules\admin\widgets\GridView::widget([
    'dataProvider' => new \yii\data\ArrayDataProvider([
        'models' => $model->shopUserTransacts
    ]),

    'columns' =>
    [
        [
            'class' => \skeeks\cms\grid\CreatedAtColumn::className()
        ],

        [
            'class'     => \yii\grid\DataColumn::className(),
            'label'     => \skeeks\cms\shop\Module::t('app', 'User'),
            'format'    => 'raw',
            'value'     => function(\skeeks\cms\shop\models\ShopUserTransact $shopUserTransact)
            {
                return (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $shopUserTransact->cmsUser]))->run();
            }
        ],

        [
            'class' => \yii\grid\DataColumn::className(),
            'attribute' => 'type',
            'label' => \skeeks\cms\shop\Module::t('app', 'Сумма'),
            'format' => 'raw',
            'value' => function(\skeeks\cms\shop\models\ShopUserTransact $shopUserTransact)
            {
                return ($shopUserTransact->debit == "Y" ? "+" : "-") . \Yii::$app->money->intlFormatter()->format($shopUserTransact->money);
            }
        ],

        'descriptionText'
    ]
]); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'History of changes')); ?>

        <?= \skeeks\cms\modules\admin\widgets\GridView::widget([
            'dataProvider' => new \yii\data\ArrayDataProvider([
                'models' => $model->shopOrderChanges
            ]),

            'columns' =>
            [
                [
                    'class' => \skeeks\cms\grid\UpdatedAtColumn::className()
                ],

                [
                    'class'     => \yii\grid\DataColumn::className(),
                    'label'     => \skeeks\cms\shop\Module::t('app', 'User'),
                    'format'    => 'raw',
                    'value'     => function(\skeeks\cms\shop\models\ShopOrderChange $shopOrderChange)
                    {
                        return (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $shopOrderChange->createdBy]))->run();
                    }
                ],

                [
                    'class' => \yii\grid\DataColumn::className(),
                    'attribute' => 'type',
                    'label' => \skeeks\cms\shop\Module::t('app', 'Transaction'),
                    'format' => 'raw',
                    'value' => function(\skeeks\cms\shop\models\ShopOrderChange $shopOrderChange)
                    {
                        return \skeeks\cms\shop\models\ShopOrderChange::types()[$shopOrderChange->type];
                    }
                ],
                [
                    'class' => \yii\grid\DataColumn::className(),
                    'attribute' => 'type',
                    'label' => \skeeks\cms\shop\Module::t('app', 'Description'),
                    'format' => 'raw',
                    'value' => function(\skeeks\cms\shop\models\ShopOrderChange $shopOrderChange)
                    {
                        return $shopOrderChange->description;
                    }
                ],


            ]
        ]); ?>

<?= $form->fieldSetEnd(); ?>

<?/*= $form->buttonsCreateOrUpdate($model); */?>
<?php ActiveForm::end(); ?>


<div style="display: none;">
    <div id="sx-payment-container" style="min-width: 500px; max-width: 500px;">
        <h2>Оплата заказа:</h2><hr />
        <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
            'validationUrl'     => \skeeks\cms\helpers\UrlHelper::construct(['shop/admin-order/pay-validate', 'pk' => $model->id])->enableAdmin()->toString(),
            'action'            => \skeeks\cms\helpers\UrlHelper::construct(['shop/admin-order/pay', 'pk' => $model->id])->enableAdmin()->toString(),

            'afterValidateCallback'                     => new \yii\web\JsExpression(<<<JS
                function(jForm, ajax){
                    new sx.classes.OrderCallback(jForm, ajax);
                };
JS
    ),

        ]); ?>

            <?= $form->fieldSelect($model, 'status_code', \yii\helpers\ArrayHelper::map(
                \skeeks\cms\shop\models\ShopOrderStatus::find()->all(), 'code', 'name'
            )); ?>

            <?= $form->field($model, 'pay_voucher_num'); ?>
            <?= $form->field($model, 'pay_voucher_at')->widget(
                \kartik\datecontrol\DateControl::classname(), [
                'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
            ]); ?>

            <button class="btn btn-primary">Сохранить</button>

        <?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>

    </div>


    <div id="sx-payment-container-close" style="min-width: 500px; max-width: 500px;">
        <h2>Изменение данных по оплате:</h2><hr />
        <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
            'validationUrl'     => \skeeks\cms\helpers\UrlHelper::construct(['shop/admin-order/pay-validate', 'pk' => $model->id])->enableAdmin()->toString(),
            'action'            => \skeeks\cms\helpers\UrlHelper::construct(['shop/admin-order/pay', 'pk' => $model->id])->enableAdmin()->toString(),

            'afterValidateCallback'                     => new \yii\web\JsExpression(<<<JS
                function(jForm, ajax){
                    new sx.classes.OrderCallback(jForm, ajax);
                };
JS
    ),

        ]); ?>

            <?= $form->fieldSelect($model, 'status_code', \yii\helpers\ArrayHelper::map(
                \skeeks\cms\shop\models\ShopOrderStatus::find()->all(), 'code', 'name'
            )); ?>

            <?= $form->field($model, 'pay_voucher_num'); ?>
            <?= $form->field($model, 'pay_voucher_at')->widget(
                \kartik\datecontrol\DateControl::classname(), [
                'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
            ]); ?>

            <p>
                <?= Html::checkbox('payment-close', false, ['label' => 'Отменить оплату']); ?>
            </p>
            <p>
                <?= Html::checkbox('payment-close-on-client', false, ['label' => 'Вернуть средства на внутренний счет']); ?>
            </p>
            <button class="btn btn-primary">Сохранить</button>

        <?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>

    </div>

    <div id="sx-status-change" style="min-width: 500px; max-width: 500px;">
        <h2>Изменение статуса:</h2><hr />
        <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
            'validationUrl'     => \skeeks\cms\helpers\UrlHelper::construct(['shop/admin-order/validate', 'pk' => $model->id])->enableAdmin()->toString(),
            'action'            => \skeeks\cms\helpers\UrlHelper::construct(['shop/admin-order/save', 'pk' => $model->id])->enableAdmin()->toString(),

            'afterValidateCallback'                     => new \yii\web\JsExpression(<<<JS
                function(jForm, ajax){
                    new sx.classes.OrderCallback(jForm, ajax);
                };
JS
    ),

        ]); ?>

            <?= $form->fieldSelect($model, 'status_code', \yii\helpers\ArrayHelper::map(
                \skeeks\cms\shop\models\ShopOrderStatus::find()->all(), 'code', 'name'
            )); ?>

            <button class="btn btn-primary">Сохранить</button>

        <?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>

    </div>

    <div id="sx-close-order" style="min-width: 500px; max-width: 500px;">
        <h2>Отмена заказа:</h2><hr />
        <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
            'validationUrl'     => \skeeks\cms\helpers\UrlHelper::construct(['shop/admin-order/validate', 'pk' => $model->id])->enableAdmin()->toString(),
            'action'            => \skeeks\cms\helpers\UrlHelper::construct(['shop/admin-order/save', 'pk' => $model->id])->enableAdmin()->toString(),

            'afterValidateCallback'                     => new \yii\web\JsExpression(<<<JS
                function(jForm, ajax){
                    new sx.classes.OrderCallback(jForm, ajax);
                };
JS
    ),

        ]); ?>

            <?= $form->fieldRadioListBoolean($model, 'canceled'); ?>
            <?= $form->field($model, 'reason_canceled')->textarea(['rows' => 5]) ?>

            <button class="btn btn-primary">Сохранить</button>

        <?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>

    </div>

    <div id="sx-allow-payment" style="min-width: 500px; max-width: 500px;">
        <h2>Разрешение оплаты:</h2><hr />
        <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
            'validationUrl'     => \skeeks\cms\helpers\UrlHelper::construct(['shop/admin-order/validate', 'pk' => $model->id])->enableAdmin()->toString(),
            'action'            => \skeeks\cms\helpers\UrlHelper::construct(['shop/admin-order/save', 'pk' => $model->id])->enableAdmin()->toString(),

            'afterValidateCallback'                     => new \yii\web\JsExpression(<<<JS
                function(jForm, ajax){
                    new sx.classes.OrderCallback(jForm, ajax);
                };
JS
    ),

        ]); ?>

            <?=
                $form->fieldSelect($model, 'pay_system_id', \yii\helpers\ArrayHelper::map(
                    $model->paySystems, 'id', 'name'
                ));
            ?>

            <?= $form->fieldRadioListBoolean($model, 'allow_payment'); ?>

            <button class="btn btn-primary">Сохранить</button>

        <?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>

    </div>

    <div id="sx-allow-delivery" style="min-width: 500px; max-width: 500px;">
        <h2>Доставка:</h2><hr />
        <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
            'validationUrl'     => \skeeks\cms\helpers\UrlHelper::construct(['shop/admin-order/validate', 'pk' => $model->id])->enableAdmin()->toString(),
            'action'            => \skeeks\cms\helpers\UrlHelper::construct(['shop/admin-order/save', 'pk' => $model->id])->enableAdmin()->toString(),

            'afterValidateCallback'                     => new \yii\web\JsExpression(<<<JS
                function(jForm, ajax){
                    new sx.classes.OrderCallback(jForm, ajax);
                };
JS
    ),

        ]); ?>

            <?=
                $form->fieldSelect($model, 'delivery_id', \yii\helpers\ArrayHelper::map(
                    \skeeks\cms\shop\models\ShopDelivery::find()->active()->all(), 'id', 'name'
                ));
            ?>

            <?= $form->fieldRadioListBoolean($model, 'allow_delivery'); ?>

            <button class="btn btn-primary">Сохранить</button>

        <?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>

    </div>


    <div id="sx-comment" style="min-width: 500px; max-width: 500px;">
        <h2>Комментарий:</h2><hr />
        <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
            'validationUrl'     => \skeeks\cms\helpers\UrlHelper::construct(['shop/admin-order/validate', 'pk' => $model->id])->enableAdmin()->toString(),
            'action'            => \skeeks\cms\helpers\UrlHelper::construct(['shop/admin-order/save', 'pk' => $model->id])->enableAdmin()->toString(),

            'afterValidateCallback'                     => new \yii\web\JsExpression(<<<JS
                function(jForm, ajax){
                    new sx.classes.OrderCallback(jForm, ajax);
                };
JS
    ),

        ]); ?>

            <?= $form->field($model, 'comments')->textarea([
                    'rows' => 5
                ])->hint(\skeeks\cms\shop\Module::t('app', 'Internal comment, the customer (buyer) does not see'));
             ?>

            <button class="btn btn-primary">Сохранить</button>

        <?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>

    </div>
</div>





<div style="display: none;">
    <?=
        \skeeks\cms\modules\admin\widgets\formInputs\CmsContentElementInput::widget([
            'baseRoute'     => '/shop/tools/select-cms-element',
            'name'          => 'sx-add-product',
            'id'            => 'sx-add-product',
            'closeWindow'   => false,
        ]);
    ?>
</div>

<?

$this->registerJs(<<<JS
(function(sx, $, _)
{
    _.each(sx.components, function(Component, key)
    {
        if (Component instanceof sx.classes.SelectCmsElement)
        {
            Component.bind('change', function(e, data)
            {
                sx.AdminShop.addProduct(data.id);
            });

            Component.unbind('change', function(e, data)
            {
                sx.AdminShop.addProduct(data.id);
            });
        }
    });
})(sx, sx.$, sx._);
JS
);
?>