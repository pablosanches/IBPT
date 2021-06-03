<?php

namespace PabloSanches\IBPT;

/**
 * Class HttpRequestJson
 * 
 * Prepare the data / headers for JSON requests, make the call and decode the response
 * 
 * @uses CurlRequest
 */
class HttpRequestJson
{
    /**
     * HTTP request headers
     *
     * @var array
     */
    protected static $httpHeaders;

    /**
     * Prepared JSON string to be posted with request
     *
     * @var string
     */
    private static $postDataJSON;

    /**
     * Prepare the data and request headers before making the call
     *
     * @param array $httpHeaders
     * @param array $dataArray
     * 
     * @return void
     */
    protected static function prepareRequest($httpHeaders = array(), $dataArray = array())
    {
        self::$postDataJSON = json_encode($dataArray);

        self::$httpHeaders = $httpHeaders;

        self::$httpHeaders['Accept'] = 'application/json';
        self::$httpHeaders['Content-type'] = 'application/json';
        if (!empty($dataArray)) {
            self::$httpHeaders['Content-Length'] = strlen(self::$postDataJSON);
        }
    }

    /**
     * Implement a GET request and return json decoded output
     *
     * @param string $url
     * @param array $httpHeaders
     * 
     * @return array
     */
    public static function get($url, $httpHeaders = array())
    {
        self::prepareRequest($httpHeaders);

        $response = CurlRequest::get($url, self::$httpHeaders);

        return self::processResponse($response);
    }

    /**
     * Decode JSON response
     *
     * @param string $response
     * 
     * @return array
     */
    protected static function processResponse($response)
    {
        return json_decode($response, true);
    }
}