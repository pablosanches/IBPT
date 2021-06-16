<?php

namespace PabloSanches\IBPT\Resources;

use PabloSanches\IBPT\IBPTClient;
use PabloSanches\IBPT\CurlRequest;
use PabloSanches\IBPT\HttpRequestJson;
use PabloSanches\IBPT\Validators\Validator;
use PabloSanches\IBPT\Exceptions\IBPTException;
use PabloSanches\IBPT\Exceptions\CurlException;
use PabloSanches\IBPT\Exceptions\ValidatorException;

abstract class IBPTResource
{
    /**
     * The ID of resource
     *
     * @var string
     */
    public $id;

    /**
     * HTTP response headers of the last executed request
     *
     * @var array
     */
    public static $lastHttpResponseHeaders = array();

    /**
     * HTTP request headers
     *
     * @var array
     */
    protected $httpHeaders = array();

    /**
     * The final endpoint of the API Resource
     *
     * @var string
     */
    protected $resourceEndpoint;

    /**
     * Key of the API Resource which is used to fetch data from request responses
     *
     * @var string
     */
    protected $resourceKey;

    /**
     * Config data
     *
     * @var array
     */
    private $config = array();

    public function __construct($id = NULL)
    {
        $config = IBPTClient::$config;
        $this->id = $id;

        if (empty($config['token'])) {
            throw new IBPTException("Token is required.");
        }
        
        $this->config = $config;
    }

    public function getResourceName()
    {
        return substr(
            get_called_class(), 
            strrpos(get_called_class(), '\\') + 1);
    }

    public function getResourcePostKey()
    {
        return $this->resourceKey;
    }

    public function pluralizeKey()
    {
        return $this->resourceKey . 's';
    }

    protected function getResourcePath()
    {
        return $this->pluralizeKey();
    }

    public function generateUrl($urlParams)
    {
        $url = '{resourceName}';
        $url = strtr(
            $url, 
            array('{resourceName}' => $this->pluralizeKey())
        );

        $url .= '?token=' . IBPTClient::$config['token'];
        $url .= '&' . http_build_query($urlParams);
        $url = IBPTClient::$config['endpoint'] . IBPTClient::$defaultAPIVersion . '/' . $url;
        
        return $url;
    }

    public function get($urlParams = array(), $url = null, $dataKey = null)
    {
        if (!$url) {
            $url = $this->generateUrl($urlParams);
        }
        
        $failures = Validator::validate($this->resourceKey, $urlParams);
        if (empty($failures)) {

            $response = HttpRequestJson::get($url, $this->httpHeaders);
            if (!$dataKey) {
                $dataKey = $this->id ? $this->resourceKey : $this->pluralizeKey();
            }
            return $this->processResponse($response, $dataKey);
        } else {
            throw new ValidatorException(Validator::printFailures($failures));
        }

        
    }

    protected function toString($array)
    {
        if (!is_array($array)) {
            return (string) $array;
        }

        $string = '';
        $i = 0;
        foreach ($array as $key => $value) {
            $string .= ($i === $key ? '' : "$key - ") . $this->toString($value) . ', ';
            $i++;
        }

        $string = rtrim($string, ', ');
        return $string;
    }

    protected function processResponse($responseArray, $dataKey = null)
    {
        self::$lastHttpResponseHeaders = CurlRequest::$lastHttpHeaders;

        if ($responseArray === null) {
            $httpOk = 200; // Request successful
            $httpCreated = 201; // Create successful
            $httpDeleted = 204; //Delete successful

            $httpCode = CurlRequest::$lastHttpCode;

            if (
                $httpCode != null && 
                $httpCode != $httpOk && 
                $httpCode != $httpCreated && 
                $httpCode != $httpDeleted
            ) {
                throw new CurlException("Request failed with HTTP Code $httpCode.", $httpCode);
            }
        }

        if (isset($responseArray['errors'])) {
            $message = $this->toString($responseArray['errors']);

            throw new IBPTException($message, CurlRequest::$lastHttpCode);
        }

        if ($dataKey && isset($responseArray[$dataKey])) {
            return $responseArray[$dataKey];
        } else {
            return $responseArray;
        }
    }
}