<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2017/3/28
 * Time: 15:17
 * Email:liyongsheng@meicai.cn
 */

namespace sheng\yii2doc;

use yii\web\Link;
use yii\data\Pagination;
use yii\data\DataProviderInterface;
use yii\base\Model;
class Serializer extends \yii\rest\Serializer
{
    /**
     * Serializes a pagination into an array.
     * @param Pagination $pagination
     * @return array the array representation of the pagination
     * @see addPaginationHeaders()
     */
    protected function serializePagination($pagination)
    {
        return [
            $this->linksEnvelope => Link::serialize($pagination->getLinks(true)),
            $this->metaEnvelope => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->getPageCount(),
                'page' => $pagination->getPage() + 1,
                'pageSize' => $pagination->getPageSize(),
            ],
        ];
    }
    /**
     * Serializes a data provider.
     * @param DataProviderInterface $dataProvider
     * @return array the array representation of the data provider.
     */
    protected function serializeDataProvider($dataProvider)
    {
        if ($this->preserveKeys) {
            $models = $dataProvider->getModels();
        } else {
            $models = array_values($dataProvider->getModels());
        }
        $models = $this->serializeModels($models);

        if (($pagination = $dataProvider->getPagination()) !== false) {
            $this->addPaginationHeaders($pagination);
        }

        if ($this->request->getIsHead()) {
            return null;
        } elseif ($this->collectionEnvelope === null) {
            return $models;
        } else {
            $result = [
                $this->collectionEnvelope => $models,
            ];
            if ($pagination !== false) {
                return array_merge($result, $this->serializePagination($pagination));
            } else {
                return $result;
            }
        }
    }
    /**
     * Serializes the validation errors in a model.
     * @param Model $model
     * @return array the array representation of the errors
     */
    protected function serializeModelErrors($model)
    {
        $this->response->setStatusCode(422, 'Data Validation Failed.');
        $result = [
            'ret'=>-1
        ];
        foreach ($model->getFirstErrors() as $name => $message) {
            $result['data'][] = [
                'field' => $name,
                'message' => $message,
            ];
        }

        return $result;
    }
}