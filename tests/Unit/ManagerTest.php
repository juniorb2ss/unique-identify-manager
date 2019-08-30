<?php

namespace UniqueIdentityManager\Tests\Unit;

use Predis\ClientInterface;
use Ramsey\Uuid\Uuid;
use UniqueIdentityManager\Exceptions\StorageKeyDoesNotExistsException;
use UniqueIdentityManager\IdentifyGenerator;
use UniqueIdentityManager\Manager;
use UniqueIdentityManager\Storage;
use UniqueIdentityManager\Tests\TestCase;

class ManagerTest extends TestCase
{
    public function testGeneratingIdentifyKeyWithoutCustomerUuid(): void
    {
        $deviceUuid = (string) Uuid::uuid4();

        $identifyGenerator = $this->prophesize(IdentifyGenerator::class);
        $identifyGenerator
            ->generate()
            ->shouldBeCalled()
            ->willReturn(Uuid::fromString('2da90be1-d1de-429f-b5f9-b9f6fbafb8e0'));


        /** @var IdentifyGenerator $identifyGenerator */
        $identifyGenerator = $identifyGenerator->reveal();

        $client = $this->prophesize(ClientInterface::class);
        $client
            ->get(
                sprintf(
                    Manager::CUSTOMER_KEY_IDENTIFICATION_NAME,
                    null
                )
            )
            ->shouldBeCalled()
            ->willThrow(new StorageKeyDoesNotExistsException());

        $client
            ->get(
                sprintf(
                    Manager::DEVICE_KEY_IDENTIFICATION_NAME,
                    $deviceUuid
                )
            )
            ->shouldBeCalled()
            ->willThrow(new StorageKeyDoesNotExistsException());

        $client
            ->set(
                sprintf(
                    Manager::DEVICE_KEY_IDENTIFICATION_NAME,
                    $deviceUuid
                ),
                $identifyGenerator->generate()
            )
            ->shouldBeCalled()
            ->willReturn(true);

        /** @var ClientInterface $client */
        $client = $client->reveal();

        $storage = new Storage($client);
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
        $deviceUuid = 'a6b203a4-c561-4157-820f-408b9bf9aced';
        $customerUuid = '1d60b5e1-f5cb-43cc-96f3-7032c606ead5';
        $expectedIdentifyKey = '2da90be1-d1de-429f-b5f9-b9f6fbafb8e0';

        $identifyGenerator = $this->prophesize(IdentifyGenerator::class);
        $identifyGenerator
            ->generate()
            ->shouldBeCalled()
            ->willReturn(Uuid::fromString('2da90be1-d1de-429f-b5f9-b9f6fbafb8e0'));


        /** @var IdentifyGenerator $identifyGenerator */
        $identifyGenerator = $identifyGenerator->reveal();

        $client = $this->prophesize(ClientInterface::class);
        $client
            ->get(
                sprintf(
                    Manager::CUSTOMER_KEY_IDENTIFICATION_NAME,
                    $customerUuid
                )
            )
            ->shouldBeCalled()
            ->willThrow(new StorageKeyDoesNotExistsException());

        $client
            ->get(
                sprintf(
                    Manager::DEVICE_KEY_IDENTIFICATION_NAME,
                    $deviceUuid
                )
            )
            ->shouldBeCalled()
            ->willThrow(new StorageKeyDoesNotExistsException());

        $client
            ->set(
                sprintf(
                    Manager::DEVICE_KEY_IDENTIFICATION_NAME,
                    $deviceUuid
                ),
                $identifyGenerator->generate()
            )
            ->shouldBeCalled()
            ->willReturn(true);

        $client
            ->set(
                sprintf(
                    Manager::CUSTOMER_KEY_IDENTIFICATION_NAME,
                    $customerUuid
                ),
                $identifyGenerator->generate()
            )
            ->shouldBeCalled()
            ->willReturn(true);

        /** @var ClientInterface $client */
        $client = $client->reveal();

        $storage = new Storage($client);
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
        $deviceUuid = 'a6b203a4-c561-4157-820f-408b9bf9aced';
        $customerUuid = '1d60b5e1-f5cb-43cc-96f3-7032c606ead5';
        $expectedIdentifyKey = '2da90be1-d1de-429f-b5f9-b9f6fbafb8e0';

        $identifyGenerator = $this->prophesize(IdentifyGenerator::class);

        /** @var IdentifyGenerator $identifyGenerator */
        $identifyGenerator = $identifyGenerator->reveal();

        $client = $this->prophesize(ClientInterface::class);
        $client
            ->get(
                sprintf(
                    Manager::CUSTOMER_KEY_IDENTIFICATION_NAME,
                    $customerUuid
                )
            )
            ->shouldBeCalled()
            ->willThrow(new StorageKeyDoesNotExistsException());

        $client
            ->get(
                sprintf(
                    Manager::DEVICE_KEY_IDENTIFICATION_NAME,
                    $deviceUuid
                )
            )
            ->shouldBeCalled()
            ->willReturn($expectedIdentifyKey);

        $client
            ->set(
                sprintf(
                    Manager::CUSTOMER_KEY_IDENTIFICATION_NAME,
                    $customerUuid
                ),
                $expectedIdentifyKey
            )
            ->shouldBeCalled()
            ->willReturn(true);

        /** @var ClientInterface $client */
        $client = $client->reveal();

        $storage = new Storage($client);
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
        $deviceUuid = 'a6b203a4-c561-4157-820f-408b9bf9aced';
        $customerUuid = '1d60b5e1-f5cb-43cc-96f3-7032c606ead5';
        $expectedIdentifyKey = '2da90be1-d1de-429f-b5f9-b9f6fbafb8e0';

        $identifyGenerator = $this->prophesize(IdentifyGenerator::class);

        /** @var IdentifyGenerator $identifyGenerator */
        $identifyGenerator = $identifyGenerator->reveal();

        $client = $this->prophesize(ClientInterface::class);
        $client
            ->get(
                sprintf(
                    Manager::CUSTOMER_KEY_IDENTIFICATION_NAME,
                    $customerUuid
                )
            )
            ->shouldBeCalled()
            ->willReturn($expectedIdentifyKey);

        /** @var ClientInterface $client */
        $client = $client->reveal();

        $storage = new Storage($client);
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
