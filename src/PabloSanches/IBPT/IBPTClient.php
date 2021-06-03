<?php

namespace PabloSanches\IBPT;

use PabloSanches\IBPT\Exceptions\IBPTException;

/**
 * IBPT SDK Client
 */
class IBPTClient
{
    /**
     * Endpoint API
     *
     * @var string
     */
    protected static $endpoint = "https://apidoni.ibpt.org.br/api/v";

    /**
     * List of available resources which can be called from this client
     *
     * @var array
     */
    protected $resources = array(
        "Produto",
        "Servico"
    );

    /**
     * Default API Version
     *
     * @var integer
     */
    public static $defaultAPIVersion = 1;

    /**
     * SDK client configs
     *
     * @var array
     */
    public static $config = array();

    /**
     * SDK client constructor
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        if (!empty($config)) {
            IBPTClient::config($config);
        }
    }

    /**
     * Return IBPTResource instance for a resource
     *
     * @example $IBPT->Produto->get(); // Return a product infos
     * @param string $resourceName
     * @return IBPTResource
     */
    public function __get($resourceName)
    {
        return $this->resourceName();
    }

    public function __call($resourceName, $arguments)
    {
        if (!in_array($resourceName, $this->resources)) {
            throw new IBPTException("Invalid resource name $resourceName.");
        }

        $resourceClassName = __NAMESPACE__ . "\\Resources\\$resourceName";
        $resourceID = !empty($arguments) ? $arguments[0] : null;

        return  new $resourceClassName($resourceID);
    }

    /**
     * Configure the SDK client
     *
     * @param array $config
     * @return void
     */
    public static function config(array $config)
    {
        self::$config = array(
            'APIVersion' => self::$defaultAPIVersion
        );

        foreach ($config as $key => $value) {
            self::$config[$key] = $value;
        }

        self::$config['endpoint'] = self::$endpoint;

        return new IBPTClient();
    }
}