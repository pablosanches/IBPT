<?php

namespace PabloSanches\IBPT;

use PabloSanches\IBPT\Exception\CurlException;

/**
 * CurlResponse Class
 * 
 * This class handles get, post, put and delete HTTP requests
 */
class CurlResponse
{
    /**
     * Headers
     *
     * @var array
     */
    private $headers = [];

    /**
     * Response body
     *
     * @var string
     */
    private $body;

    /**
     * HTTP Status code
     *
     * @var string
     */
    private $httpCode;
    
    /**
     * Initialize the response parse
     *
     * @param string $response
     */
    public function __construct($response, $curlInfo)
    {
        $this->parse($response, $curlInfo);
    }

    /**
     * Parse the response string
     *
     * @param string $response
     * @return object
     */
    private function parse($response, $curlInfo)
    {
        $response = explode("\r\n\r\n", $response);
        if (!empty($response)) {
            // We want the last two parts
            $response = array_slice($response, -2, 2);
            list($headers, $body) = $response;
            foreach (explode("\r\n", $headers) as $header) {
                $pair = explode(': ', $header, 2);
                if (isset($pair[1])) {
                    $headerKey = strtolower($pair[0]);
                    $this->headers[$headerKey] = $pair[1];
                }
            }
        }

        $this->body = $body;
        $this->httpCode = $curlInfo['http_code'];
    }

    /**
     * Get all headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get specific header by key
     *
     * @param string $key
     * 
     * @return string
     */
    public function getHeader($key)
    {
        $key = strtolower($key);
        return isset($this->headers[$key]) ? $this->headers[$key] : null;
    }

    /**
     * Get the body response
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get the http status code
     *
     * @return string
     */
    public function getStatusCode()
    {
        return $this->httpCode;
    }

    /**
     * __toString print the body
     *
     * @return string
     */
    public function __toString()
    {
        $body = $this->getBody();
        $body = $body ? : '';

        return $body;
    }
}