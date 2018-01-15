yii2-doc根据action注释生成swagger所需的json文件
===================================================

效果
---------------------------------------------------
![出错了，图片找不到](demo.jpg)


demo
---------------------------------------------------
```
    /**
     * 这个里是接口名/接口说明
     * @http-method get
     * @request-param string $business_id 业务线id required
     * @request-param string $keyword 关键字(element.code, element.name, org.id)
     * @return array
     */
```
```
    /**
     * 一条业务线元素列表
     * @http-method get
     * @query-param string $business_id 业务线id required
     * @query-param string $keyword 关键字(element.code, element.name, org.id)
     * @return array
     */
```
```
    /**
     * 这个里是接口名/接口说明
     * @http-method post
     * @request-param string $business_id 业务线id required
     * @request-param string $keyword 关键字(element.code, element.name, org.id)
     * @return array
     */
```
```
    /**
     * 这个里是接口名/接口说明
     * @http-method put
     * @body-param array $body httpbody required
     * @return array
     */
```
swagger
---------------------------------------------------

https://swagger.io/
https://github.com/swagger-api/swagger-js