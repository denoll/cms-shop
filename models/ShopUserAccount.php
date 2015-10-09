<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsUser;
use skeeks\modules\cms\money\models\Currency;
use skeeks\modules\cms\money\Money;
use Yii;

/**
 * This is the model class for table "{{%shop_user_account}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $user_id
 * @property string $current_budget
 * @property string $currency_code
 * @property string $locked
 * @property integer $locked_at
 * @property string $notes
 *
 * @property Currency $currencyCode
 * @property CmsUser $user
 * @property Money $money
 */
class ShopUserAccount extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_user_account}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'user_id', 'locked_at'], 'integer'],
            [['user_id', 'currency_code'], 'required'],
            [['current_budget'], 'number'],
            [['notes'], 'string'],
            [['currency_code'], 'string', 'max' => 3],
            [['locked'], 'string', 'max' => 1],
            [['currency_code', 'user_id'], 'unique', 'targetAttribute' => ['currency_code', 'user_id'], 'message' => 'The combination of User ID and Currency Code has already been taken.'],
            [['currency_code'], 'default', 'value' => \Yii::$app->money->currencyCode],
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
            'user_id' => Yii::t('app', 'User'),
            'current_budget' => Yii::t('app', 'Сумма на счете'),
            'currency_code' => Yii::t('app', 'Currency Code'),
            'locked' => Yii::t('app', 'Заблокирован'),
            'locked_at' => Yii::t('app', 'Locked At'),
            'notes' => Yii::t('app', 'Заметки'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['code' => 'currency_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(CmsUser::className(), ['id' => 'user_id']);
    }


    /**
     * @return Money
     */
    public function getMoney()
    {
        return Money::fromString($this->current_budget, $this->currency_code);
    }
}