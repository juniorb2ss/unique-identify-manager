<?php

namespace UniqueIdentityManager\Tests\Unit;

use Predis\ClientInterface;
use Ramsey\Uuid\Uuid;
use UniqueIdentityManager\Exceptions\StorageKeyDoesNotExistsException;
use UniqueIdentityManager\IdentityGenerator;
use UniqueIdentityManager\Manager;
use UniqueIdentityManager\Storage;
use UniqueIdentityManager\Tests\TestCase;

class ManagerTest extends TestCase
{
    public function testGeneratingidentityKeyWithoutCustomerUuid(): void
    {
        $deviceUuid = (string) Uuid::uuid1();

        $identityGenerator = $this->prophesize(IdentityGenerator::class);
        $identityGenerator
            ->generate()
            ->shouldBeCalled()
            ->willReturn(Uuid::fromString('2da90be1-d1de-429f-b5f9-b9f6fbafb8e0'));


        /** @var IdentityGenerator $identityGenerator */
        $identityGenerator = $identityGenerator->reveal();

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
                $identityGenerator->generate()
            )
            ->shouldBeCalled()
            ->willReturn(true);

        /** @var ClientInterface $client */
        $client = $client->reveal();

        $storage = new Storage($client);
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

    public function testGeneratingidentityKeyWithCustomerUuidButCustomerDoesNotHaveidentityKey(): void
    {
        $deviceUuid = 'a6b203a4-c561-4157-820f-408b9bf9aced';
        $customerUuid = '1d60b5e1-f5cb-43cc-96f3-7032c606ead5';
        $expectedidentityKey = '2da90be1-d1de-429f-b5f9-b9f6fbafb8e0';

        $identityGenerator = $this->prophesize(IdentityGenerator::class);
        $identityGenerator
            ->generate()
            ->shouldBeCalled()
            ->willReturn(Uuid::fromString('2da90be1-d1de-429f-b5f9-b9f6fbafb8e0'));


        /** @var IdentityGenerator $identityGenerator */
        $identityGenerator = $identityGenerator->reveal();

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
                $identityGenerator->generate()
            )
            ->shouldBeCalled()
            ->willReturn(true);

        $client
            ->set(
                sprintf(
                    Manager::CUSTOMER_KEY_IDENTIFICATION_NAME,
                    $customerUuid
                ),
                $identityGenerator->generate()
            )
            ->shouldBeCalled()
            ->willReturn(true);

        /** @var ClientInterface $client */
        $client = $client->reveal();

        $storage = new Storage($client);
        $manager = new Manager($storage, $identityGenerator);

        // Cenario:
        // o device e o customer nao possuem nenhum identificador unico antes criado
        // por isso é esperado que se crie um identificador unico para o device
        // e atualize o customer com esse identificador unico
        $identityKey = $manager->identify(
            $deviceUuid,
            $customerUuid
        );

        $this->assertSame($expectedidentityKey, $identityKey);
    }

    public function testGeneratingidentityKeyWithDeviceUuidAndCustomerDoesNotHaveidentityKey(): void
    {
        $deviceUuid = 'a6b203a4-c561-4157-820f-408b9bf9aced';
        $customerUuid = '1d60b5e1-f5cb-43cc-96f3-7032c606ead5';
        $expectedidentityKey = '2da90be1-d1de-429f-b5f9-b9f6fbafb8e0';

        $identityGenerator = $this->prophesize(IdentityGenerator::class);

        /** @var IdentityGenerator $identityGenerator */
        $identityGenerator = $identityGenerator->reveal();

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
            ->willReturn($expectedidentityKey);

        $client
            ->set(
                sprintf(
                    Manager::CUSTOMER_KEY_IDENTIFICATION_NAME,
                    $customerUuid
                ),
                $expectedidentityKey
            )
            ->shouldBeCalled()
            ->willReturn(true);

        /** @var ClientInterface $client */
        $client = $client->reveal();

        $storage = new Storage($client);
        $manager = new Manager($storage, $identityGenerator);

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

        $this->assertSame($expectedidentityKey, $identityKey);
    }

    public function testGeneratingidentityKeyWithCustomerUuidAndCustomerAlreadyHasidentityKey(): void
    {
        $deviceUuid = 'a6b203a4-c561-4157-820f-408b9bf9aced';
        $customerUuid = '1d60b5e1-f5cb-43cc-96f3-7032c606ead5';
        $expectedidentityKey = '2da90be1-d1de-429f-b5f9-b9f6fbafb8e0';

        $identityGenerator = $this->prophesize(IdentityGenerator::class);

        /** @var IdentityGenerator $identityGenerator */
        $identityGenerator = $identityGenerator->reveal();

        $client = $this->prophesize(ClientInterface::class);
        $client
            ->get(
                sprintf(
                    Manager::CUSTOMER_KEY_IDENTIFICATION_NAME,
                    $customerUuid
                )
            )
            ->shouldBeCalled()
            ->willReturn($expectedidentityKey);

        /** @var ClientInterface $client */
        $client = $client->reveal();

        $storage = new Storage($client);
        $manager = new Manager($storage, $identityGenerator);

        // Cenário:
        // Customer já contém outro identificador, possívelmente de outro computador
        // a primeira verificação deverá ser pelo uuid do customer
        $identityKey = $manager->identify(
            $deviceUuid,
            $customerUuid
        );

        $this->assertSame($expectedidentityKey, $identityKey);
    }
}
