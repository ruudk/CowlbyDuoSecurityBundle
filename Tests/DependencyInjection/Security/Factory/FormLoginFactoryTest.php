<?php

namespace Symfony\Bundle\SecurityBundle\Tests\DependencyInjection\Security\Factory;

use Cowlby\Bundle\DuoSecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FormLoginFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $factory;

    public function setUp()
    {
        $this->factory = new FormLoginFactory();
    }

    public function tearDown()
    {
        $this->factory = null;
    }

    public function testGetKey()
    {
        $this->assertEquals('cowlby_duo_security-form_login', $this->factory->getKey(), 'Did not return expected key.');
    }

    public function testCreate()
    {
        $container = new ContainerBuilder();
        $container->register('auth_provider');

        list($authProviderId,
             $listenerId,
             $entryPointId
        ) = $this->factory->create($container, 'foo', array(
            'use_forward' => true,
            'failure_path' => '/foo',
            'success_handler' => 'qux',
            'failure_handler' => 'bar',
            'remember_me' => true
        ), 'user_provider', 'entry_point');

        // auth provider
        $expectedAuthProviderId = 'security.authentication.provider.dao.foo';
        $this->assertEquals($expectedAuthProviderId, $authProviderId, sprintf('%s is not equal to %s.', $expectedAuthProviderId, $authProviderId));

        // listener
        $expectedListenerId = 'cowlby_duo_security.security.authentication.listener.form.foo';
        $this->assertEquals($expectedListenerId, $listenerId, sprintf('%s is not equal to %s.', $expectedListenerId, $listenerId));
        $this->assertTrue($container->hasDefinition($expectedListenerId));
        $definition = $container->getDefinition($expectedListenerId);
        $this->assertEquals(array(
            'index_4' => 'foo',
            'index_5' => new Reference('qux'),
            'index_6' => new Reference('bar'),
            'index_7' => array(
                'use_forward' => true
            )
        ), $definition->getArguments());

        // entry point
        $expectedEntryPoint = 'security.authentication.form_entry_point.foo';
        $this->assertEquals($expectedEntryPoint, $entryPointId, sprintf('%s is not equal to %s.', $expectedEntryPoint, $entryPointId));
    }
}
