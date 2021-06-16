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
        $ch = curl_init();

        // Set URL
        curl_setopt($ch, CURLOPT_URL, $url);

        // Return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'LojaVirtual/eNotasSDK');
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        // Set HTTP headers
        $headers = array();
        foreach($httpHeaders as $key => $value) {
            $headers[] = "$key: $value";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        return $ch;
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
        $ch = self::init($url, $httpHeaders);
        return self::processRequest($ch);
    }

    /**
     * Execute a request
     *
     * @param resource $ch
     * 
     * @throws CurlException if curl request is failed with error
     * 
     * @return string
     */
    protected static function processRequest($ch)
    {
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $response = new CurlResponse($output, $info);
        self::$lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new CurlException(curl_errno($ch) . ' : ' . curl_error($ch));
        }

        // Close curl resource to free up system resources
        curl_close($ch);

        self::$lastHttpHeaders = $response->getHeaders();
        return $response;
    }
}