<?php

declare(strict_types=1);

namespace UniqueIdentityManager\Tests\Integration;

use Predis\Client;
use Predis\ClientInterface;
use Ramsey\Uuid\Uuid;
use UniqueIdentityManager\IdentityGenerator;
use UniqueIdentityManager\Manager;
use UniqueIdentityManager\RedisStorageInterface;
use UniqueIdentityManager\Tests\TestCase;

/**
 * @group third-party
 */
class ManagerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->redis = new Client(
            [
                'tcp://localhost',
            ]
        );

        parent::setUp();
    }

    public function testGeneratingIdentityKeyWithoutCustomerUuid(): void
    {
        $deviceUuid = (string) Uuid::uuid1();

        $identityGenerator = $this->prophesize(IdentityGenerator::class);
        $identityGenerator
            ->generate()
            ->shouldBeCalled()
            ->willReturn(Uuid::uuid1());

        /** @var IdentityGenerator $identityGenerator */
        $identityGenerator = $identityGenerator->reveal();

        $storage = new RedisStorageInterface($this->redis);
        $manager = new Manager($storage, $identityGenerator);

        // Cenario
        // Não existe customerUuuid ainda, pois é um visitante, e o device não contém identificador unico ainda
        // por isso é esperado que se crie um identificador unico para esse device
        $identityKey = $manager->identify(
            $deviceUuid,
            null
        );

        $this->assertSame((string) $identityGenerator->generate(), $identityKey);
    }

    public function testGeneratingIdentityKeyWithCustomerUuidButCustomerDoesNotHaveidentityKey(): void
    {
        $deviceUuid = (string) Uuid::uuid1();
        $customerUuid = (string) Uuid::uuid1();
        $expectedIdentityKey = (string) Uuid::uuid1();

        $identityGenerator = $this->prophesize(IdentityGenerator::class);
        $identityGenerator
            ->generate()
            ->shouldBeCalled()
            ->willReturn(Uuid::fromString($expectedIdentityKey));

        /** @var IdentityGenerator $identityGenerator */
        $identityGenerator = $identityGenerator->reveal();

        $storage = new RedisStorageInterface($this->redis);
        $manager = new Manager($storage, $identityGenerator);

        // Cenario:
        // o device e o customer nao possuem nenhum identificador unico antes criado
        // por isso é esperado que se crie um identificador unico para o device
        // e atualize o customer com esse identificador unico
        $identityKey = $manager->identify(
            $deviceUuid,
            $customerUuid
        );

        $this->assertSame($expectedIdentityKey, $identityKey);
    }

    public function testGeneratingIdentityKeyWithDeviceUuidAndCustomerDoesNotHaveidentityKey(): void
    {
        $deviceUuid = (string) Uuid::uuid1();
        $customerUuid = (string) Uuid::uuid1();
        $expectedIdentityKey = (string) Uuid::uuid1();

        /** @var ClientInterface $redis */
        $redis = $this->redis;
        $redis
            ->set(
                sprintf(
                    Manager::DEVICE_KEY_IDENTIFICATION_NAME,
                    $deviceUuid
                ),
                $expectedIdentityKey
            );

        $storage = new RedisStorageInterface($redis);
        $manager = new Manager($storage);

        // Cenario:
        // O device já possui um identificador, e o customer criou uma conta nova
        // ele ainda não possui nenhum identificador unico (proveniente de um acesso anterior)
        // por isso é esperado que seja retornado o identificador unico do device, para manter a mesma experiencia
        // também é esperado atualizar o identificador do customer, para que os próximos acessos nesse device
        // ou em outros, mantenha a mesma experiencia.
        $identityKey = $manager->identify(
            $deviceUuid,
            $customerUuid
        );

        $this->assertSame($expectedIdentityKey, $identityKey);
    }

    public function testGeneratingIdentityKeyWithCustomerUuidAndCustomerAlreadyHasidentityKey(): void
    {
        $deviceUuid = (string) Uuid::uuid1();
        $customerUuid = (string) Uuid::uuid1();
        $expectedIdentityKey = (string) Uuid::uuid1();

        /** @var ClientInterface $redis */
        $redis = $this->redis;
        $redis
            ->set(
                sprintf(
                    Manager::CUSTOMER_KEY_IDENTIFICATION_NAME,
                    $customerUuid
                ),
                $expectedIdentityKey
            );

        $storage = new RedisStorageInterface($redis);
        $manager = new Manager($storage);

        // Cenário:
        // Customer já contém outro identificador, possívelmente de outro computador
        // a primeira verificação deverá ser pelo uuid do customer
        $identityKey = $manager->identify(
            $deviceUuid,
            $customerUuid
        );

        $this->assertSame($expectedIdentityKey, $identityKey);
    }
}
