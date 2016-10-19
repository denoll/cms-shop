<?
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.04.2016
 */
/* @var $this yii\web\View */
/* @var $yandexKassa skeeks\cms\shop\paySystems\YandexKassaPaySystem */
/* @var $model \skeeks\cms\shop\models\ShopOrder */

$yandexKassa = $model->paySystem->paySystemHandler;
$returnUrl = $model->publicUrl;
$money = $model->money->convertToCurrency("RUB");

\yii\web\JqueryAsset::register($this);
$this->registerJs(<<<JS
    $('#yandexKassa').submit();
JS
)
?>
<div style="text-align: center; margin: 100px; font-size: 20px;">
    Wait, is redirected to the payment system...
</div>
<div style="display: none">
    <form action="<?php echo $yandexKassa->baseUrl; ?>" method="post" id="yandexKassa">
        <!-- Обязательные поля -->
        <input name="shopId" value="<?php echo $yandexKassa->shop_id; ?>" type="hidden"/>
        <input name="shopArticleId" value="<?php echo $yandexKassa->shop_id; ?>" type="hidden"/>
        <input name="scid" value="<?php echo $yandexKassa->shop_id; ?>" type="hidden"/>
        <input name="orderNumber" value="<?php echo $model->id; ?>" type="hidden"/>
        <input name="sum" value="<?= $money->getValue(); ?>" type="hidden">
        <input name="customerNumber" value="<?php echo $model->id; ?>" type="hidden"/>

        <input name="shopSuccessURL" value="<?php echo $returnUrl; ?>" type="hidden"/>
        <input name="shopFailURL" value="<?php echo $returnUrl; ?>" type="hidden"/>
        <input name="shopDefaultUrl" value="<?php echo $returnUrl; ?>" type="hidden"/>

        <input name="paymentType" value="AC" type="hidden"/>
        <input type="hidden" name="rebillingOn" value="true">
        <input type="submit" value="Заплатить"/>
    </form>
 </div>
