<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2017/3/21
 * Time: 16:50
 * Email:liyongsheng@meicai.cn
 */

namespace sheng\yii2doc;

use yii\behaviors\TimestampBehavior;

class ActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * 添加默认字段 c_t, u_t 自动更新时间
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'c_t',
                'updatedAtAttribute' => 'u_t',
            ],
        ];
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $expand = array_unique(array_merge($expand, array_keys($this->getRelatedRecords())));
        return parent::toArray($fields, $expand, $recursive);
    }
}