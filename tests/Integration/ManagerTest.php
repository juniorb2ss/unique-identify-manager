<?php

namespace UniqueIdentifyManager\Tests\Integration;

use Predis\Client;
use Predis\ClientInterface;
use Ramsey\Uuid\Uuid;
use UniqueIdentifyManager\IdentifyGenerator;
use UniqueIdentifyManager\Manager;
use UniqueIdentifyManager\Storage;
use UniqueIdentifyManager\Tests\TestCase;

class ManagerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->redis = new Client(
            [
                'tcp://redis'
            ]
        );

        parent::setUp();
    }

    public function testGeneratingIdentifyKeyWithoutCustomerUuid(): void
    {
        $deviceUuid = (string) Uuid::uuid4();

        $identifyGenerator = $this->prophesize(IdentifyGenerator::class);
        $identifyGenerator
            ->generate()
            ->shouldBeCalled()
            ->willReturn(Uuid::uuid4());


        /** @var IdentifyGenerator $identifyGenerator */
        $identifyGenerator = $identifyGenerator->reveal();

        $storage = new Storage($this->redis);
        $manager = new Manager($storage, $identifyGenerator);

        // Cenario
        // Não existe customerUuuid ainda, pois é um visitante, e o device não contém identificador unico ainda
        // por isso é esperado que se crie um identificador unico para esse device
        $identifyKey = $manager->identify(
            $deviceUuid,
            null
        );

        $this->assertSame((string) $identifyGenerator->generate(), $identifyKey);
    }

    public function testGeneratingIdentifyKeyWithCustomerUuidButCustomerDoesNotHaveIdentifyKey(): void
    {
        $deviceUuid = (string) Uuid::uuid4();
        $customerUuid = (string) Uuid::uuid4();
        $expectedIdentifyKey = (string) Uuid::uuid4();

        $identifyGenerator = $this->prophesize(IdentifyGenerator::class);
        $identifyGenerator
            ->generate()
            ->shouldBeCalled()
            ->willReturn(Uuid::fromString($expectedIdentifyKey));

        /** @var IdentifyGenerator $identifyGenerator */
        $identifyGenerator = $identifyGenerator->reveal();

        $storage = new Storage($this->redis);
        $manager = new Manager($storage, $identifyGenerator);

        // Cenario:
        // o device e o customer nao possuem nenhum identificador unico antes criado
        // por isso é esperado que se crie um identificador unico para o device
        // e atualize o customer com esse identificador unico
        $identifyKey = $manager->identify(
            $deviceUuid,
            $customerUuid
        );

        $this->assertSame($expectedIdentifyKey, $identifyKey);
    }

    public function testGeneratingIdentifyKeyWithDeviceUuidAndCustomerDoesNotHaveIdentifyKey(): void
    {
        $deviceUuid = (string) Uuid::uuid4();
        $customerUuid = (string) Uuid::uuid4();
        $expectedIdentifyKey = (string) Uuid::uuid4();

        $identifyGenerator = $this->prophesize(IdentifyGenerator::class);

        /** @var IdentifyGenerator $identifyGenerator */
        $identifyGenerator = $identifyGenerator->reveal();

        /** @var ClientInterface $redis */
        $redis = $this->redis;
        $redis
            ->set(
                sprintf(
                    Manager::DEVICE_KEY_IDENTIFICATION_NAME,
                    $deviceUuid
                ),
                $expectedIdentifyKey
            );

        $storage = new Storage($redis);
        $manager = new Manager($storage, $identifyGenerator);

        // Cenario:
        // O device já possui um identificador, e o customer criou uma conta nova
        // ele ainda não possui nenhum identificador unico (proveniente de um acesso anterior)
        // por isso é esperado que seja retornado o identificador unico do device, para manter a mesma experiencia
        // também é esperado atualizar o identificador do customer, para que os próximos acessos nesse device
        // ou em outros, mantenha a mesma experiencia.
        $identifyKey = $manager->identify(
            $deviceUuid,
            $customerUuid
        );

        $this->assertSame($expectedIdentifyKey, $identifyKey);
    }

    public function testGeneratingIdentifyKeyWithCustomerUuidAndCustomerAlreadyHasIdentifyKey(): void
    {
        $deviceUuid = (string) Uuid::uuid4();
        $customerUuid = (string) Uuid::uuid4();
        $expectedIdentifyKey = (string) Uuid::uuid4();

        $identifyGenerator = $this->prophesize(IdentifyGenerator::class);

        /** @var IdentifyGenerator $identifyGenerator */
        $identifyGenerator = $identifyGenerator->reveal();

        /** @var ClientInterface $redis */
        $redis = $this->redis;
        $redis
            ->set(
                sprintf(
                    Manager::CUSTOMER_KEY_IDENTIFICATION_NAME,
                    $customerUuid
                ),
                $expectedIdentifyKey
            );

        $storage = new Storage($redis);
        $manager = new Manager($storage, $identifyGenerator);

        // Cenário:
        // Customer já contém outro identificador, possívelmente de outro computador
        // a primeira verificação deverá ser pelo uuid do customer
        $identifyKey = $manager->identify(
            $deviceUuid,
            $customerUuid
        );

        $this->assertSame($expectedIdentifyKey, $identifyKey);
    }
}
