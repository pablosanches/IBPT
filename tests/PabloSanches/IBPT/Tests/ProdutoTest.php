<?php

namespace PabloSanches\IBPT\Tests;

use Exception;
use PabloSanches\IBPT\Exceptions\ValidatorException;
use PabloSanches\IBPT\Tests\BaseTesting;

class TestProduto extends BaseTesting
{
    public $produto = array(
        "cnpj" => "00.316.147/0001-95",
        "codigo" => "0002",
        "uf" => "MG",
        "descricao" => "Descrição de teste",
        "unidadeMedida" => "UN",
        "valor" => 10.5
    );

    public $produtoValidatorErro = array(
        "cnpj" => "00022212",
        "uf" => "Minas Gerais",
        "valor" => "sanches.webmaster@gmail.com"
    );

   /**
    * TestSimpleResource constructor
    */
   public function __construct()
   { 
       parent::__construct();
   }

   public function testGet()
   {
        $response = static::$IBPT->Produto()->get($this->produto);
        $this->assertTrue(is_array($response));
        $this->assertNotEmpty($response);
   }

   
   public function testValidationException()
   {
        try {
            static::$IBPT->Produto()->get($this->produtoValidatorErro);
            $this->fail("Expected Exception has not been raised.");
        } catch (Exception $e) {
            $this->assertInstanceOf('PabloSanches\IBPT\Exceptions\ValidatorException', $e);
        }
    }
}