<?php

/**
 * Created by PhpStorm.
 * User: david
 * Date: 2017/3/20
 * Time: 09:42
 * Email:liyongsheng@meicai.cn
 */

namespace app\components;

use app\components\OrgClient;
use Yii;

class GisNewClient extends OrgClient
{
    public $baseUrl = GIS_NEW_SERVICE;

    public function getClient()
    {
        $client = parent::getClient();
        $client->setTransport('yii\httpclient\CurlTransport');
        return $client;
    }

    public function getSaleArea($ids)
    {
        $data = json_encode(['ids' => $ids]);
        $res = $this->sendPostJson('api/gisservice/salearea/getByIds', $data);
        return $res;
    }


    public function getSiteArea($ids)
    {
        $data = json_encode(['ids' => $ids]);
        return $this->sendPostJson('api/gisservice/sitearea/getByIds', $data);
    }


    /**
     * 获取所有城市
     * @return array|mixed
     */
    public function getAllCity()
    {
        $res = $this->sendPostJson('api/gisservice/bizCity/fetchAll','{}');
        if(!empty($res['data'])){
            $res['data'] = array_map(function($row){
                $row['id'] = $row['cityId'];
                $row['short_name'] = $row['shortName'];
                return $row;
            },$res['data']);
        }
        return $res;
    }

    /**
     * 通过城市ID获取城市下所有站区
     * @param $city_id
     * @return array|mixed
     */
    public function getSiteAreaByCity($city_id)
    {
        $data = json_encode(['city_id' => $city_id]);
        return $this->sendPostJson('api/gisservice/sitearea/getByCityId', $data);
    }

    /**
     * 获取城市ID对应的所有售卖区
     * @param $city_id
     * @return array|mixed
     */
    public function getSaleAreaByCity($city_id)
    {
        $data = json_encode(['city_id' => $city_id]);
        return $this->sendPostJson('api/gisservice/salearea/getByCityId', $data);
    }

   public function  getAllWarehouse(){
     $data = json_encode(['status' => 1]);
     return $this->sendPostJson('api/gisservice/warehouse/fetchAll', $data);
    }

    public function  getAllSaleArea(){
        $data = json_encode(['status' => 1]);
        return $this->sendPostJson('api/gisservice/salearea/fetchAll', $data);
    }

}