<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2017/3/27
 * Time: 18:21
 * Email:liyongsheng@meicai.cn
 */

namespace app\components;
use yii\helpers\Url;
use Yii;
trait ApiDocTrait
{

    /**
     * 公共参数
     * @var array
     */
    public $commParams = [
        'string $user_token 用户登录标识 required'
    ];
    /**
     * 需要跳过的控制器
     * @var array
     */
    public $skipControllers=[
        'defaultController'
    ];
    /**
     *
     * @return array
     */
    public function swaggerJson()
    {
        $apiList = $this->createDoc();
        $paths = [];
        foreach($apiList as $c=>$cItem){
            foreach($cItem['methods'] as $a=>$aItem) {
                if(empty($aItem['uri'])){
                    continue;
                }

                $verbs = preg_split("/[\s]+/", $aItem['method']);
                $method = $verbs[0];
                if(!isset($aItem['request-param'])) {
                    $aItem['request-param'] = [];
                }
                $aItem['request-param'] = array_merge($aItem['request-param'], $this->commParams);
                $apiParams = [];
                $tmpPathItem = [
                    'tags'=>[empty($cItem['doc'])?$c:$cItem['doc']],
                    'summary'=>$aItem['doc'],
                    'operationId'=>$a,
                    'responses'=>[
                        '200'=>[
                            'description'=> "successful operation",
                        ]
                    ],
                    "consumes"=> [
                        "application/x-www-form-urlencoded"
                    ],
                    'produces'=>[
                        'application/json'
                    ]
                ];
                if(!empty($aItem['param'])){
                    $apiParams = array_merge($apiParams, $this->swaggerFormatParam($aItem['param'], $method, 'path'));
                }
                if(!empty($aItem['query-param'])){
                    $apiParams = array_merge($apiParams, $this->swaggerFormatParam($aItem['query-param'], $method, 'query'));
                }
                if(!empty($aItem['form-param'])){
                    $apiParams = array_merge($apiParams, $this->swaggerFormatParam($aItem['form-param'], $method, 'formData'));
                }
                if(!empty($aItem['request-param'])) {
                    $apiParams = array_merge($apiParams,$this->swaggerFormatParam($aItem['request-param'], $method));
                }
                if(!empty($aItem['body-param'])) {
                    $apiParams = array_merge($apiParams,$this->swaggerBodyParam($aItem['body-param']));
                    $tmpPathItem['consumes'] = [
                        'application/json'
                    ];
                }
                if(!empty($apiParams)){
                    $tmpPathItem['parameters']=$apiParams;
//                    $tmpPathItem['schema']=[
//                        '$ref'=> "#/definitions/Pet"
//                    ];
                }
                $paths[$aItem['uri']][$method] = $tmpPathItem;
            }
        }
        $host = Yii::$app->request->hostName;
        $port = parse_url(Yii::$app->request->getHostInfo(), PHP_URL_PORT);
        if($port!='80'){
            $host .=':'.$port;
        }
//        print_r($paths);die;
        return [
            'swagger'=>"2.0",
            'info'=>[
                'title'=> Yii::$app->name." api文档",
                'description'=>Yii::$app->name ." api文档",
                'version'=> "1.0.0",
                'contact'=> [
                    'email'=> "liyongsheng@meicai.cn"
                ],
            ],
            'host'=>$host,
            'basePath'=>'/',
            'schemes'=>[parse_url(Yii::$app->request->getHostInfo(), PHP_URL_SCHEME)],
//            'tags'=>[],
            'paths'=>$paths,
        ];
    }
    /**
     * @param array $paramsTextArr
     * @return array
     */
    public function swaggerBodyParam($paramsTextArr){
        $params = [
            'name' => 'body',
            'in' => 'body',
            'collectionFormat'=>'multi'
//             description'=>isset($match[2])?trim($match[2],'\{\}\"\''):'',
        ];
        foreach($paramsTextArr as $paramRow) {
            if(!preg_match('/(\w+)\s+(\$[\w\[\]]+)/i', $paramRow, $match)){
                continue;
            }
            $match = preg_split('/\s/i',$paramRow);
            if(empty($params['schema']['type'])){
                if(preg_match('/\$body\[[0-9]+\]/i', $match[1])){
                    $params['schema']['type'] = 'array';
                    $params['schema']['items'] =[
                        'type'=>'object'
                    ];
                }else{
                    $params['schema']['type'] = 'object';
                }
            }
            if(strpos($paramRow, 'required') && empty($params['required'])){
                $params['required'] =  true;
            }
        }
//        print_r($params);die;
        return [$params];
    }
    /**
     * @param array $paramsTextArr
     * @param string $method
     * @param string $paramIn path,query,formData,body
     * @return array
     */
    public function swaggerFormatParam($paramsTextArr, $method, $paramIn=null)
    {
        $paramsMap = [];
        $method =  strtolower($method);
        $params = [];
        foreach($paramsTextArr as $paramRow) {
            if(!preg_match('/(\w+)\s+(\$[\w\[\]]+)/i', $paramRow, $match)){
                continue;
            }
            $match = preg_split('/\s/i',$paramRow);
            if(empty($paramIn)) {
                if (in_array($method, ['get', 'header'])) {
                    $paramIn = 'query';
                } else {
                    $paramIn = 'formData';
                }
            }
            $tmp = [
                'in'=>$paramIn,
                'type'=>isset($match[0])?$match[0]:'',
                'name'=>isset($match[1])?ltrim($match[1],'$'):'',
                'description'=>isset($match[2])?trim($match[2],'\{\}\"\''):'',
            ];
            if(isset($paramsMap[$tmp['name']])){
                continue;
            }
            if($tmp['name']=='user_token'){
                $tmp['value'] = $_COOKIE['test_user_token']??'';
            }
            $paramsMap[$tmp['name']] = true;
            if(strpos($paramRow, 'required')){
                $tmp['required'] =  true;
            }
            $params[] = $tmp;
        }
        return $params;
    }

    /**
     * 生成api文档数组
     * @return array
     */
    public function createDoc()
    {
        $controllerDir =  Yii::$app->controllerPath;
        $list  =scandir($controllerDir);
        $apiList = [];
        foreach($list as $file){
            if($file == '.' || $file=='..'){
                continue;
            }
            $className = substr($file, 0, -4);
            if($className == 'DefaultController'){
                continue;
            }
            $fullClassName = Yii::$app->controllerNamespace.'\\'.$className;

            list($apiList[$className]['doc'], $apiList[$className]['methods']) = $this->createApiDoc($fullClassName);
        }
        return $apiList;
    }
    /**
     * 获取当前控制器中全部的接口
     * @param string $className
     * @return array json
     */
    public function createApiDoc($className)
    {
        $ref = new \ReflectionClass($className);
        $route = $this->_getRouteName($ref->getShortName());
        $controllerDoc = $this->_getControllerDoc($ref->getDocComment());
        list($controller) = Yii::$app->createController($route);
        $methods = $ref->getMethods();
        $apiList = [];
        foreach($methods as $methodObj){
            if(substr($methodObj->name, 0, 6)=='action' && $methodObj->name!='actions'){
                $action = substr($methodObj->name, 6);
                $routeAction = $this->_getRouteActionPart($action);
                $apiList[$action] = $this->_getActionParams($methodObj);
                $apiList[$action]['route'] = $route;
                $apiList[$action]['action'] = $routeAction;
                $apiList[$action]['uri'] = Url::to([$route.'/'.$routeAction]);
            }
        }
        if(method_exists($controller, 'actions')) {
            $actions = $controller->actions();
            foreach ($actions as $action => $config) {
                $apiList[$action]['uri'] = Url::to([$route . '/' . $action]);
            }
        }

        unset($controller);
        return [$controllerDoc, $apiList];
    }

    /**
     * 获取控制器注释
     * @param string $doc
     * @return string
     */
    protected function _getControllerDoc($doc)
    {
        $controllerDoc = '';
        if(empty($doc)){
            return $controllerDoc;
        }
        $docLines = explode("\n",$doc);

        foreach($docLines as $ln =>$line){
            if(preg_match('/\*\s+(.+)/i', $line, $math)){
                $controllerDoc = $math[1];
                break;
            }
        }
        return $controllerDoc;
    }
    /**
     * 将action 驼峰转换为uri
     * @param string $action
     * @return string
     */
    protected function _getRouteActionPart($action)
    {
        $formatStr = preg_replace("/([A-Z])/", ",\\1", $action);
        $wordList = explode(',', $formatStr);
        array_walk($wordList,function(&$val, $key){
            $val = strtolower($val);
        });
        return trim(implode('-', $wordList), '-');
    }

    /**
     * 分析action注释
     * @param \ReflectionMethod $methodObj
     * @return array
     */
    protected function _getActionParams(\ReflectionMethod $methodObj)
    {
        $doc = $methodObj->getDocComment();
        $docLines = explode("\n",$doc);
        $docArr = [];
        foreach($docLines as $ln =>$line){
            if(!isset($docArr['doc']) && preg_match('/\*\s+(.+)/i', $line, $math)){
                isset($math[1]) && $docArr['doc'] = $math[1];
            }elseif(empty($math)){
                continue;
            }
            if(!preg_match('/@([a-zA-Z\-]+)\s+(.*)/i', $line, $matchList)){
                continue;
            }
            switch(true){
                case ($matchList[1]=='param' && isset($matchList[2])):
                    $docArr['param'][] = $matchList[2];
                    break;
                case ($matchList[1]=='query-param' && isset($matchList[2])):
                    $docArr['query-param'][] = $matchList[2];
                    break;
                case ($matchList[1]=='form-param' && isset($matchList[2])):
                    $docArr['form-param'][] = $matchList[2];
                    break;
                case ($matchList[1]=='request-param' && isset($matchList[2])):
                    $docArr['request-param'][] = $matchList[2];
                    break;
                case ($matchList[1]=='body-param' && isset($matchList[2])):
                    $docArr['body-param'][] = $matchList[2];
                    break;
                case $matchList[1]=='return' && isset($matchList[2]):
                    $docArr['respond'] = $matchList[2];
                    break;
                case $matchList[1]=='http-method':
                    preg_match('/@([a-zA-Z\-]+)\s+(\w+)/i', $line, $math);
                    $docArr['method'] = isset($matchList['2'])?$matchList['2']:'';
                    break;
            }
        }
        return $docArr;
    }

    /**
     * @param string $className class短名称
     * @return string
     */
    protected function _getRouteName($className)
    {
        $route = substr($className, 0, -10);
        $array= preg_split("/(?=[A-Z])/",$route);
        array_walk($array, function(&$value, $key){
            $value = lcfirst($value);
        });
        return trim(implode('-', $array), '-');
    }
}