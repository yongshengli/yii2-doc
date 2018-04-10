<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2017/3/24
 * Time: 19:28
 * Email:liyongsheng@meicai.cn
 */

namespace app\components;
use Yii;
use yii\web\Response;
use yii\web\HttpException;

class ErrorHandler extends \yii\web\ErrorHandler
{
    /**
     * Renders the exception.
     * @param \Exception $exception the exception to be rendered.
     */
    protected function renderException($exception)
    {
//        print_r($exception);die;
        if (Yii::$app->has('response')) {
            $response = Yii::$app->getResponse();
            // reset parameters of response to avoid interference with partially created response data
            // in case the error occurred while sending the response.
            $response->isSent = false;
            $response->stream = null;
            $response->data = null;
            $response->content = null;
        } else {
            $response = new Response();
        }

        $result = Yii::$app->runAction($this->errorAction);
        if ($result instanceof Response) {
            $response = $result;
        } else {
            $response->data = $result;
        }

        if ($exception instanceof HttpException) {
            $response->setStatusCode($exception->statusCode);
        } else {
            Yii::$app->log->getLogger()->log($this->convertExceptionToArray($exception),\yii\log\Logger::LEVEL_ERROR);
            $response->setStatusCode(500);
        }

        $response->send();
    }
}