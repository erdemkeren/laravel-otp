<?php

namespace Erdemkeren\TemporaryAccess\Tests;

use Erdemkeren\TemporaryAccess\AccessCodeGenerator;
use Erdemkeren\TemporaryAccess\Contracts\AccessCode as AccessCodeContract;
use Erdemkeren\TemporaryAccess\Contracts\AccessCodeGenerator as AccessCodeGeneratorContract;

class AccessCodeGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AccessCodeGenerator
     */
    private $accessCodeGenerator;

    public function setUp()
    {
        $this->accessCodeGenerator = new AccessCodeGenerator('key');
    }

    public function it_shall_be_an_instance_of_access_code_generator_contract()
    {
        $this->assertInstanceOf(AccessCodeGeneratorContract::class, $this->accessCodeGenerator);
    }

    /** @test */
    public function it_shall_generate_new_access_codes()
    {
        $accessCode = $this->accessCodeGenerator->generate();

        $this->assertInstanceOf(AccessCodeContract::class, $accessCode);

        $this->assertEquals(6, strlen($accessCode->plain()));
        $this->assertEquals(64, strlen($accessCode->encrypted()));
    }

    /** @test */
    public function it_shall_generate_access_codes_from_plain_texts()
    {
        $plainText = 'foo';

        $accessCode = $this->accessCodeGenerator->fromPlain($plainText);

        $this->assertEquals(3, strlen($accessCode->plain()));
        $this->assertEquals(64, strlen($accessCode->encrypted()));
    }

    public function it_shall_generate_new_instances_of_itself()
    {

    }
}
