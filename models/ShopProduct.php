<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 14.09.2015
 */
namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use skeeks\cms\measure\models\Measure;
use skeeks\cms\models\CmsContentElement;
use skeeks\modules\cms\money\models\Currency;
use Yii;

/**
 * This is the model class for table "{{%shop_product}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property double $quantity
 * @property string $quantity_trace
 * @property double $weight
 * @property string $price_type
 * @property integer $recur_scheme_length
 * @property string $recur_scheme_type
 * @property integer $trial_price_id
 * @property string $without_order
 * @property string $select_best_price
 * @property integer $vat_id
 * @property string $vat_included
 * @property string $tmp_id
 * @property string $can_buy_zero
 * @property string $negative_amount_trace
 * @property string $barcode_multi
 * @property string $purchasing_price
 * @property string $purchasing_currency
 * @property double $quantity_reserved
 * @property integer $measure_id
 * @property double $width
 * @property double $length
 * @property double $height
 * @property string $subscribe
 *
 * @property Measure            $measure
 * @property CmsContentElement $cmsContentElement
 * @property ShopTypePrice      $trialPrice
 * @property ShopVat            $vat
 * @property Currency           $purchasingCurrency
 */
class ShopProduct extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'recur_scheme_length', 'trial_price_id', 'vat_id', 'measure_id'], 'integer'],
            [['quantity', 'weight', 'purchasing_price', 'quantity_reserved', 'width', 'length', 'height'], 'number'],
            [['quantity_trace', 'price_type', 'recur_scheme_type', 'without_order', 'select_best_price', 'vat_included', 'can_buy_zero', 'negative_amount_trace', 'barcode_multi', 'subscribe'], 'string', 'max' => 1],
            [['tmp_id'], 'string', 'max' => 40],
            [['purchasing_currency'], 'string', 'max' => 3],
            [['quantity_trace', 'can_buy_zero', 'negative_amount_trace'], 'default', 'value' => Cms::BOOL_N],
            [['weight', 'width', 'length', 'height', 'purchasing_price'], 'default', 'value' => 0],
            [['subscribe'], 'default', 'value' => Cms::BOOL_Y],
            [['purchasing_currency'], 'default', 'value' => Yii::$app->money->currencyCode],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'quantity' => Yii::t('app', 'Доступное количество'),
            'quantity_trace' => Yii::t('app', 'Включить количественный учет'),
            'weight' => Yii::t('app', 'Вес (грамм)'),
            'price_type' => Yii::t('app', 'Price Type'),
            'recur_scheme_length' => Yii::t('app', 'Recur Scheme Length'),
            'recur_scheme_type' => Yii::t('app', 'Recur Scheme Type'),
            'trial_price_id' => Yii::t('app', 'Trial Price ID'),
            'without_order' => Yii::t('app', 'Without Order'),
            'select_best_price' => Yii::t('app', 'Select Best Price'),
            'vat_id' => Yii::t('app', 'Ставка НДС'),
            'vat_included' => Yii::t('app', 'НДС включен в цену'),
            'tmp_id' => Yii::t('app', 'Tmp ID'),
            'can_buy_zero' => Yii::t('app', 'Разрешить покупку при отсутствии товара'),
            'negative_amount_trace' => Yii::t('app', 'Разрешить отрицательное количество товара'),
            'barcode_multi' => Yii::t('app', 'Barcode Multi'),
            'purchasing_price' => Yii::t('app', 'Закупочная цена'),
            'purchasing_currency' => Yii::t('app', 'Валюта закупочной цены'),
            'quantity_reserved' => Yii::t('app', 'Зарезервированное количество'),
            'measure_id' => Yii::t('app', 'Measure ID'),
            'width' => Yii::t('app', 'Ширина (мм)'),
            'length' => Yii::t('app', 'Длина (мм)'),
            'height' => Yii::t('app', 'Высота (мм)'),
            'subscribe' => Yii::t('app', 'Разрешить подписку при отсутствии товара'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMeasure()
    {
        return $this->hasOne(Measure::className(), ['id' => 'measure_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentElement()
    {
        return $this->hasOne(CmsContentElement::className(), ['id' => 'id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrialPrice()
    {
        return $this->hasOne(ShopTypePrice::className(), ['id' => 'trial_price_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVat()
    {
        return $this->hasOne(ShopVat::className(), ['id' => 'vat_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurchasingCurrency()
    {
        return $this->hasOne(Currency::className(), ['code' => 'purchasing_currency']);
    }
}