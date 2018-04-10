<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2017/3/20
 * Time: 11:42
 * Email:liyongsheng@meicai.cn
 */

namespace app\components;


use yii\base\Exception;
use yii\base\Model;
use yii\httpclient\Client;
use yii\httpclient\Response;
use Yii;


/**
 * Class ApiModel
 * @package app\components
 * @property Client $client
 * @property \yii\httpclient\Request $request
 * @property array $jsonHeader
 */
class OrgClient extends Model
{
    private $_client;

    /** @var string api url */
    public $baseUrl;

    /**
     * 获取 httpClient 对象
     * @return Client
     * @throws Exception
     */
    public function getClient()
    {
        if(empty($this->_client) || $this->_client instanceof  Client){
            if(empty($this->baseUrl)){
                throw new Exception('baseUrl属性没有设置');
            }
            $this->_client = new Client(['baseUrl'=>$this->baseUrl]);
        }
        return $this->_client;
    }

    /**
     * 获取一个新的 http request 对象
     * @return \yii\httpclient\Request
     */
    public function getRequest()
    {
        return $this->createRequest();
    }
    /**
     * @return \yii\httpclient\Request request instance.
     */
    public function createRequest()
    {
        return $this->client->createRequest();
    }
    /**
     * 获取一个新的 http request 对象
     * @return array
     */
    public function getJsonHeader()
    {
        return [
            "Content-type: application/json;charset=utf-8",
            "Accept: application/json",
            "Cache-Control: no-cache",
            "Pragma: no-cache"
        ];
    }

    /**
     * @param Response $respond
     * @return mixed
     */
    public function afterRespond(Response $respond)
    {
        return $respond->getData();
    }
    /**
     * @param $url
     * @param array $data
     * @return array|mixed
     */
    public function sendPostJson($url, $data=[])
    {
        $respond = $this->client->post($url, $data, $this->getJsonHeader())->send();

        return $this->afterRespond($respond);
    }

    /**
     * @param $url
     * @param array $data
     * @return array|mixed
     */
    public function sendPutJson($url, $data=[])
    {
        $respond = $this->client->put($url, $data, $this->getJsonHeader())->send();
        return $this->afterRespond($respond);
    }

    /**
     * @param $url
     * @param array $data
     * @return array|mixed
     */
    public function sendDeleteJson($url, $data=[])
    {
        $respond = $this->client->delete($url,$data, $this->getJsonHeader())->send();
        return $this->afterRespond($respond);
    }
    /**
     * @param string $url
     * @param array $data
     * @return array|mixed
     */
    public function sendGet($url, $data=[])
    {
        $respond = $this->client->get($url, $data)->send();
        return $this->afterRespond($respond);
    }
}