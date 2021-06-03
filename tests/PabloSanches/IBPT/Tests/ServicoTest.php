<?php

namespace PabloSanches\IBPT\Tests;

use PabloSanches\IBPT\Tests\BaseTesting;

class TestServico extends BaseTesting
{
    public $servico = array(
        "cnpj" => "00.316.147/0001-95",
        "codigo" => "0002",
        "uf" => "MG",
        "descricao" => "Descrição do servico",
        "unidadeMedida" => "UN",
        "valor" => 10.5
    );

   /**
    * TestSimpleResource constructor
    */
   public function __construct()
   { 
       parent::__construct();
   }

   public function testGetServico()
   {
        $response = static::$IBPT->Servico()->get($this->servico);
        $this->assertTrue(is_array($response));
        $this->assertNotEmpty($response);
   }
}