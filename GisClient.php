<?php

/**
 * Created by PhpStorm.
 * User: david
 * Date: 2017/3/20
 * Time: 09:42
 * Email:liyongsheng@meicai.cn
 */
namespace  app\components;

use app\components\OrgClient;
use Yii;

class GisClient extends OrgClient
{
    public $baseUrl = GIS_SERVICE;




    public function getSaleArea($ids)
    {
        return (new GisNewClient())->getSaleArea($ids);
//        return $this->sendPostJson('salearea/getbyids', ['ids' => $ids]);
    }


    public function getSiteArea($ids)
    {
        return (new GisNewClient())->getSiteArea($ids);
//        return $this->sendPostJson('sitearea/getbyids', ['ids' => $ids]);
    }


    /**
     * 获取所有城市
     * @return array|mixed
     */
    public function getAllCity()
    {
        return (new GisNewClient())->getAllCity();
//        return $this->sendPostJson('areadivision/operatingcity');
    }

    /**
     * 通过城市ID获取城市下所有站区
     * @param $city_id
     * @return array|mixed
     */
    public function getSiteAreaByCity($city_id)
    {
        return (new GisNewClient())->getSiteAreaByCity($city_id);
        //return $this->sendPostJson('sitearea/getsiteareasbycityid', ['city_id' => $city_id]);
    }

    /**
     * 获取城市ID对应的所有售卖区
     * @param $city_id
     * @return array|mixed
     */
    public function getSaleAreaByCity($city_id)
    {
        return (new GisNewClient())->getSaleAreaByCity($city_id);
//        return $this->sendPostJson('salearea/getbycityid', ['city_id' => $city_id]);
    }


    /**
     * @param string $token
     * @param string $entry url
     * @param array $params
     * @return array|mixed
     * @throws ApiModelException
     */




}