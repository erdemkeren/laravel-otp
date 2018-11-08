<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Erdemkeren\Otp\PasswordGeneratorManager
 */
class PasswordGeneratorManagerTest extends TestCase
{
    /**
     * @var PasswordGeneratorManager
     */
    private $manager;

    public function setUp()
    {
        $this->manager = new PasswordGeneratorManager();
    }

    public function testItRegistersWithFqcn(): void
    {
        $this->manager->register('acme_fqcn', AcmeGenerator::class);

        $this->assertSame('generated9', $this->manager->get('acme_fqcn')(9));
    }

    public function testCreateGeneratorFromCallable(): void
    {
        $this->manager->register('acme_callable', function (int $length) {
            return 'callable_generated'.$length;
        });

        $this->assertSame('callable_generated18', $this->manager->get('acme_callable')(18));
    }

    public function testItDoesNotRegisterStrangeThingsAsGenerators()
    {
        $this->expectException(\UnexpectedValueException::class);

        $this->manager->register('lol', 3.5);
    }

    public function testItDoesNotRegisterNotExistingClassesAsGenerators()
    {
        $this->expectException(\RuntimeException::class);

        $this->manager->register('acme_dependant', NotExistingGenerator::class);
    }

    public function testItDoesNotRegisterNotInstantiableClassesAsGenerators()
    {
        $this->expectException(\RuntimeException::class);

        $this->manager->register('acme_abstract', AcmeAbstractGenerator::class);
    }

    public function testItThrowsExceptionIfTheRequestedGeneratorIsNotRegistered(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $this->manager->get('acme_undef');
    }
}

class AcmeGenerator implements PasswordGeneratorInterface
{
    public function generate(int $length)
    {
        return 'generated'.$length;
    }
}

abstract class AcmeAbstractGenerator implements PasswordGeneratorInterface
{
}
