<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 25.05.2015
 */

namespace skeeks\cms\shop\dashboards;

use skeeks\cms\base\Widget;
use skeeks\cms\base\WidgetRenderable;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\modules\admin\base\AdminDashboardWidget;
use skeeks\cms\modules\admin\base\AdminDashboardWidgetRenderable;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;

/**
 * Class AboutCmsDashboard
 * @package skeeks\cms\modules\admin\dashboards
 */
class ReportOrderDashboard extends AdminDashboardWidgetRenderable
{
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => \Yii::t('skeeks/shop/app', 'Reports on orders')
        ]);
    }

    public $viewFile = 'report-order';
    public $name;

    public function init()
    {
        parent::init();

        if (!$this->name)
        {
            $this->name = \Yii::t('skeeks/shop/app', 'Reports on orders');
        }
    }

    /**
     * @return array
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['name'], 'string'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'name'                           => \Yii::t('skeeks/shop/app', 'Name'),
        ]);
    }

    /**
     * @param ActiveForm $form
     */
    public function renderConfigForm(ActiveForm $form = null)
    {
        echo $form->field($this, 'name');
    }
}