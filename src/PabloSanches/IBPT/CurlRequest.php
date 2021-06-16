<?php

namespace PabloSanches\IBPT;

use PabloSanches\IBPT\Exceptions\CurlException;

use PabloSanches\IBPT\CurlResponse;

/**
 * CurlRequest Class
 * 
 * This class handles get, post, put and delete HTTP requests
 */
class CurlRequest
{
    /**
     * HTTP code of the last executed request
     *
     * @var integer
     */
    public static $lastHttpCode;

    /**
     * HTTP response headers of last executed request
     *
     * @var array
     */
    public static $lastHttpHeaders = array();

    /**
     * Initialize the curl resource
     *
     * @param string $url
     * @param array $httpHeaders
     * 
     * @return resource
     */
    protected static function init($url, $httpHeaders = array())
    {
        // Create instance
        $chResource = curl_init();

        // Set URL
        curl_setopt($chResource, CURLOPT_URL, $url);

        // Return the transfer as a string
        curl_setopt($chResource, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($chResource, CURLOPT_HEADER, true);
        curl_setopt($chResource, CURLOPT_USERAGENT, 'LojaVirtual/eNotasSDK');
        curl_setopt($chResource, CURLINFO_HEADER_OUT, true);

        // Set HTTP headers
        $headers = array();
        foreach($httpHeaders as $key => $value) {
            $headers[] = "$key: $value";
        }
        curl_setopt($chResource, CURLOPT_HTTPHEADER, $headers);

        return $chResource;
    }

    /**
     * Implement a GET request and return output
     *
     * @param string $url
     * @param array $httpHeaders
     * 
     * @return string
     */
    public static function get($url, $httpHeaders = array())
    {
        $chResource = self::init($url, $httpHeaders);
        return self::processRequest($chResource);
    }

    /**
     * Execute a request
     *
     * @param $chResource
     * 
     * @throws CurlException if curl request is failed with error
     * 
     * @return string
     */
    protected static function processRequest($chResource)
    {
        $output = curl_exec($chResource);
        $info = curl_getinfo($chResource);
        $response = new CurlResponse($output, $info);
        self::$lastHttpCode = curl_getinfo($chResource, CURLINFO_HTTP_CODE);
        
        if (curl_errno($chResource)) {
            throw new CurlException(curl_errno($chResource) . ' : ' . curl_error($chResource));
        }

        // Close curl resource to free up system resources
        curl_close($chResource);

        self::$lastHttpHeaders = $response->getHeaders();
        return $response;
    }
}