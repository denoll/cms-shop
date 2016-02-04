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
/* @var $cmsUser \skeeks\cms\models\CmsUser */
/* @var $shopFuser \skeeks\cms\shop\models\ShopFuser */
$this->registerCss(<<<CSS
h1 a
{
    border-bottom: 1px dashed;
    text-decoration: none;
}
h1 a:hover
{
    border-bottom: 1px dashed;
    text-decoration: none;
}
#sx-change-user
{
    margin-bottom: 10px;
}
CSS
);
\Yii::$app->shop->setShopFuser($shopFuser);
?>

<?php $form = ActiveForm::begin([
    'id' => 'sx-change-user',
    'method' => 'get',
    'usePjax' => false,
]); ?>
<h1 style="text-align: center;">Новый заказ для покупателя: <a href="#" class="sx-change-user"><?= $shopFuser->user->displayName; ?></a></h1>

<div style="display: none;">
    <?= \skeeks\cms\modules\admin\widgets\formInputs\SelectModelDialogUserInput::widget([
        'id'        => 'cmsUserId',
        'name'      => 'cmsUserId',
    ]); ?>
</div>

<?
$this->registerJs(<<<JS
$('#cmsUserId').on('change', function()
{
    $("#sx-change-user").submit();
});

$('.sx-change-user').on('click', function()
{
    $(".sx-btn-create").click();
});
JS
)
?>

<?php ActiveForm::end(); ?>

<?php $form = ActiveForm::begin([
        'id' => 'sx-create-order',
        'pjaxOptions' =>
        [
            'id' => 'sx-pjax-order-wrapper'
        ]
    ]); ?>



    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'Buyer')
    ])?>


            <?/*= \skeeks\widget\chosen\Chosen::widget([
                'name'          => 'select-person-type',
                'id'            => 'select-person-type',
                'items'         => $shopFuser->getBuyersList(),
                'value'         => $shopFuser->buyer_id ? $shopFuser->buyer_id : (
                    $shopFuser->personType->id ? "shopPersonType-" . $shopFuser->personType->id : ""
                ),
                'placeholder'   => 'Выберите профиль покупателя',
                'allowDeselect' => false,
            ]); */?>

            <?=
                $form->field($shopFuser, 'buyer_id')->widget(
                    \skeeks\cms\widgets\formInputs\EditedSelect::className(),
                    [
                        'items' => \yii\helpers\ArrayHelper::map(
                            $shopFuser->shopBuyers, 'id', 'name'
                        ),

                        'controllerRoute'   => '/shop/admin-buyer',
                        'additionalData'    => [
                            'cms_user_id' => $shopFuser->user->id
                        ],
                        'updateAction'      => 'related-properties',
                        'allowDeselect'     => false
                    ]
                );
            ?>


    <? if ($shopFuser->buyer) : ?>
        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => \skeeks\cms\shop\Module::t('app', 'Customer data')
        ])?>

            <?= \yii\widgets\DetailView::widget([
                'model' =>  $shopFuser,
                'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
                'attributes' =>
                [
                    [                      // the owner name of the model
                        'label' => \skeeks\cms\shop\Module::t('app', 'Type payer'),
                        'format' => 'raw',
                        'value' => $shopFuser->personType->name,
                    ],

                    [                      // the owner name of the model
                        'label' => \skeeks\cms\shop\Module::t('app', 'Profile of buyer'),
                        'format' => 'raw',
                        'value' => Html::a( $shopFuser->buyer->name . " [{$shopFuser->buyer->id}]", \skeeks\cms\helpers\UrlHelper::construct(['/shop/admin-buyer/update', 'pk' =>  $shopFuser->buyer->id ])->enableAdmin(), [
                            'data-pjax' => 0
                        ] ),
                    ],
                ]
            ]); ?>

            <?= \yii\widgets\DetailView::widget([
                'model' => $shopFuser->buyer->relatedPropertiesModel,
                'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
                'attributes' => array_keys($shopFuser->buyer->relatedPropertiesModel->attributeValues())
            ])?>

    <?/* elseif ($shopFuser->personType) : */?>

        <?/* $buyer = $shopFuser->personType->createModelShopBuyer(); */?><!--

        <?/* if ($properties = $buyer->relatedProperties) : */?>
            <?/* foreach ($properties as $property) : */?>
                <?/*= $property->renderActiveForm($form, $buyer); */?>
            <?/* endforeach; */?>
        --><?/* endif; */?>
    <? else : ?>
        Пользователь еще ничего не покупал на сайте. Для него необходимо завести и выбрать данные для профиля покупателя.
        <hr />
    <? endif; ?>


    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'Payment order')
    ])?>

            <?=
                $form->fieldSelect($shopFuser, 'pay_system_id', \yii\helpers\ArrayHelper::map(
                    $shopFuser->paySystems, 'id', 'name'
                ));
            ?>


    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'Shipping')
    ])?>

            <?=
                $form->fieldSelect($shopFuser, 'delivery_id', \yii\helpers\ArrayHelper::map(
                    \skeeks\cms\shop\models\ShopDelivery::find()->active()->all(), 'id', 'name'
                ));
            ?>




    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => \skeeks\cms\shop\Module::t('app', 'The composition of the order')
        ])?>



    <?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
        'label'             => "",
        'parentModel'       => $shopFuser,
        'relation'          => [
            'fuser_id'      => 'id',
        ],

        /*'sort'              => [
            'defaultOrder' =>
            [
                'priority' => 'published_at'
            ]
        ],*/

        'controllerRoute'   => 'shop/admin-basket',
        'gridViewOptions'   => [
            'columns' => [
                /*[
                    'class' => \yii\grid\SerialColumn::className()
                ],*/

                [
                    'class'     => \yii\grid\DataColumn::className(),
                    'format'    => 'raw',
                    'value'     => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                    {
                        $widget = new \skeeks\cms\modules\admin\widgets\AdminImagePreviewWidget([
                            'image' => $shopBasket->product->cmsContentElement->image
                        ]);
                        return $widget->run();
                    }
                ],
                [
                    'class' => \yii\grid\DataColumn::className(),
                    'attribute' => 'name',
                    'format' => 'raw',
                    'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                    {
                        if ($shopBasket->product)
                        {
                            return Html::a($shopBasket->name, $shopBasket->product->cmsContentElement->url, [
                                'target' => '_blank',
                                'titla' => "Смотреть на сайте",
                                'data-pjax' => 0
                            ]);
                        } else
                        {
                            return $shopBasket->name;
                        }

                    }
                ],

                [
                    'class' => \yii\grid\DataColumn::className(),
                    'attribute' => 'quantity',
                    'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                    {
                        return $shopBasket->quantity . " " . $shopBasket->measure_name;
                    }
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
                    'class' => \yii\grid\DataColumn::className(),
                    'label' => \skeeks\cms\shop\Module::t('app', 'Sum'),
                    'attribute' => 'price',
                    'format' => 'raw',
                    'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                    {
                        return \Yii::$app->money->intlFormatter()->format($shopBasket->money->multiply($shopBasket->quantity));
                    }
                ],
            ]
        ],
    ]); ?>



        <div class="row">
            <div class="col-md-8"></div>
            <div class="col-md-4">
                    <div class="sx-result">
                <?

                $model = $shopFuser;

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
                            'value' => \Yii::$app->money->intlFormatter()->format($model->money),
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



    <?= $form->buttonsCreateOrUpdate($shopFuser); ?>


    <?

    $clientData = \yii\helpers\Json::encode([

        'backendFuserSave' => \skeeks\cms\helpers\UrlHelper::construct([
            '/shop/admin-order/create-order-fuser-save', 'shopFuserId' => $shopFuser->id
        ])->enableAdmin()->toString(),

    ]);

$this->registerJs(<<<JS
(function(sx, $, _)
{
    sx.classes.CreateOrder = sx.classes.Component.extend({

        _onDomReady: function()
        {
            var self = this;

            this.jQueryUser         = $("#shopfuser-user_id");
            this.jQueryBuyer         = $("#shopfuser-buyer_id");
            this.jQueryPaySystem   = $("#shopfuser-pay_system_id");
            this.jQueryPersonType   = $("#shoporder-person_type_id");
            this.jQueryDelivery   = $("#shopfuser-delivery_id");
            this.jQueryForm         = $("#sx-create-order");

            this.jQueryPaySystem.on('change', function()
            {
                var ajax = self.getAjaxQuery();
                ajax.setData(self.jQueryForm.serializeArray());

                var ajaxHandler = new sx.classes.AjaxHandlerStandartRespose(ajax);
                new sx.classes.AjaxHandlerNoLoader(ajax);

                ajaxHandler.bind('success', function()
                {
                    sx.CreateOrder.reload();
                });

                ajax.execute();
            });

            this.jQueryDelivery.on('change', function()
            {
                var ajax = self.getAjaxQuery();
                ajax.setData(self.jQueryForm.serializeArray());

                var ajaxHandler = new sx.classes.AjaxHandlerStandartRespose(ajax);
                new sx.classes.AjaxHandlerNoLoader(ajax);

                ajaxHandler.bind('success', function()
                {
                    sx.CreateOrder.reload();
                });

                ajax.execute();
            });

            this.jQueryBuyer.on('change', function()
            {
                var ajax = self.getAjaxQuery();
                ajax.setData(self.jQueryForm.serializeArray());

                var ajaxHandler = new sx.classes.AjaxHandlerStandartRespose(ajax);
                new sx.classes.AjaxHandlerNoLoader(ajax);

                ajaxHandler.bind('success', function()
                {
                    sx.CreateOrder.reload();
                });

                ajax.execute();
            });

        },

        /**
        *
        * @returns {sx.classes.shop.App.ajaxQuery|Function|sx.classes.shop._App.ajaxQuery|*}
        */
        getAjaxQuery: function()
        {
            return sx.ajax.preparePostQuery(this.get('backendFuserSave'));
        },

        reload: function()
        {
            $.pjax.reload('#sx-pjax-order-wrapper', {});
        }
    });

    sx.CreateOrder = new sx.classes.CreateOrder({$clientData});

})(sx, sx.$, sx._);
JS
);

    ?>
<?php ActiveForm::end(); ?>
