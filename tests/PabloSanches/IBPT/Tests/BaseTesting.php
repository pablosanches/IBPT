<?php

namespace PabloSanches\IBPT\Tests;

use PabloSanches\IBPT\IBPTClient;

class BaseTesting extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IBPTClient $IBPT
     */
    public static $IBPT;

    public static function setUpBeforeClass()
    {
        $config = array(
            'token' => 'OWM2NzllMjYtMGFiOS00MTgwLTlkMDYtZWY3ODkwMjYwNzAw'
        );

        self::$IBPT = IBPTClient::config($config);
    }

    public static function tearDownAfterClass()
    {
        self::$IBPT = null;
    }

    public function testInstance()
    {
        $this->assertInstanceOf('PabloSanches\IBPT\IBPTClient', self::$IBPT);
    }
}