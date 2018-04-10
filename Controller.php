<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2017/3/22
 * Time: 14:31
 * Email:liyongsheng@meicai.cn
 */

namespace sheng\yii2doc;

use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\base\Exception;
use Yii;
use yii\base\InlineAction;
use yii\rest\Action;
use yii\web\MethodNotAllowedHttpException;

class Controller extends \yii\rest\Controller
{
    use ApiDocTrait;
    public $serializer = [
//        'class' => 'app\components\Serializer',
        'collectionEnvelope' => 'items'
    ];
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'verbFilter' => [
                'class' => VerbFilter::className(),
                'actions' => $this->verbs(),
            ],
        ];
    }
    /**
     * Creates an action based on the given action ID.
     * The method first checks if the action ID has been declared in [[actions()]]. If so,
     * it will use the configuration declared there to create the action object.
     * If not, it will look for a controller method whose name is in the format of `actionXyz`
     * where `Xyz` stands for the action ID. If found, an [[InlineAction]] representing that
     * method will be created and returned.
     * @param string $id the action ID.
     * @return Action the newly created action instance. Null if the ID doesn't resolve into any action.
     */
    public function createAction($id)
    {
        if ($id === '') {
            $id = $this->defaultAction;
        }

        $actionMap = $this->actions();
        if (isset($actionMap[$id])) {
            /** @var Action $action */
            $action = Yii::createObject($actionMap[$id], [$id, $this]);
            $method = new \ReflectionMethod($action, 'run');
            $this->verbFilter($method);
            return $action;
        } elseif (preg_match('/^[a-z0-9\\-_]+$/', $id) && strpos($id, '--') === false && trim($id, '-') === $id) {
            $methodName = 'action' . str_replace(' ', '', ucwords(implode(' ', explode('-', $id))));
            if (method_exists($this, $methodName)) {
                $method = new \ReflectionMethod($this, $methodName);
                if ($method->isPublic() && $method->getName() === $methodName) {
                    $this->verbFilter($method);
                    return new InlineAction($id, $this, $methodName);
                }
            }
        }

        return null;
    }

    /**
     * @param \ReflectionMethod $methodObj
     * @throws Exception
     * @return bool
     */
    public function verbFilter(\ReflectionMethod $methodObj){
        if(YII_ENV=='prod'){
            return true;
        }
        $docArr = $this->_getActionParams($methodObj);
        if(empty($docArr)){
            throw new Exception('action not write annotation');
        }
        if(empty($docArr['method'])){
            throw new Exception('action not write http-method annotation');
        }
        $verbs = preg_split("/[\s]+/", $docArr['method']);
        $verb = Yii::$app->getRequest()->getMethod();
        $allowed = array_map('strtoupper', $verbs);
        if (!in_array($verb, $allowed)) {
            // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.7
            Yii::$app->getResponse()->getHeaders()->set('Allow', implode(', ', $allowed));
            throw new MethodNotAllowedHttpException('Method Not Allowed. This url can only handle the following request methods: ' . implode(', ', $allowed) . '.');
        }
    }
    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        if(!isset($result['data']) || !isset($result['ret'])){
            $result =[
                'data'=>$result,
                'ret'=>1
            ];
        }
        if($result['ret']!==1 && !isset($result['code'])){
            $result['code'] = 0;
        }
        return $result;
    }
}