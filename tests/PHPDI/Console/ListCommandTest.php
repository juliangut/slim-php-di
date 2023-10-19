<?php

/*
 * slim-php-di (https://github.com/juliangut/slim-php-di).
 * Slim Framework PHP-DI container implementation.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-php-di
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\Slim\PHPDI\Tests\Console;

use Jgut\Slim\PHPDI\Command\ListCommand;
use Jgut\Slim\PHPDI\Container;
use Jgut\Slim\PHPDI\ContainerBuilder;
use Jgut\Slim\PHPDI\Tests\Stubs\ConsoleOutputStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;

use function DI\decorate;
use function DI\env;
use function DI\get;

/**
 * @internal
 */
class ListCommandTest extends TestCase
{
    public function testDefaultEntries(): void
    {
        $container = ContainerBuilder::build();

        $command = new ListCommand($container);

        $input = new ArgvInput([], $command->getDefinition());
        $input->setOption('full', 1);

        $output = new ConsoleOutputStub();

        static::assertSame(0, $command->execute($input, $output));
        static::assertStringContainsString(
            'Psr\Http\Message\ResponseFactoryInterface   Factory',
            $output->getOutput(),
        );
        static::assertStringContainsString('DI\Container', $output->getOutput());
        static::assertStringContainsString('DI\FactoryInterface', $output->getOutput());
        static::assertStringContainsString('Invoker\InvokerInterface', $output->getOutput());
        static::assertStringContainsString('Invoker\InvokerInterface', $output->getOutput());
        static::assertStringContainsString('Jgut\Slim\PHPDI\Configuration', $output->getOutput());
        static::assertStringContainsString('Psr\Container\ContainerInterface', $output->getOutput());
        static::assertStringContainsString('Psr\Http\Message\ResponseFactoryInterface', $output->getOutput());
        static::assertStringContainsString('Psr\Http\Message\StreamFactoryInterface', $output->getOutput());
        static::assertStringContainsString('Slim\App', $output->getOutput());
        static::assertStringContainsString('Slim\Interfaces\CallableResolverInterface', $output->getOutput());
        static::assertStringContainsString('Slim\Interfaces\DispatcherInterface', $output->getOutput());
        static::assertStringContainsString('Slim\Interfaces\InvocationStrategyInterface', $output->getOutput());
        static::assertStringContainsString('Slim\Interfaces\MiddlewareDispatcherInterface', $output->getOutput());
        static::assertStringContainsString('Slim\Interfaces\RouteCollectorInterface', $output->getOutput());
        static::assertStringContainsString('Slim\Interfaces\RouteResolverInterface', $output->getOutput());
        static::assertStringContainsString('Slim\Interfaces\ServerRequestCreatorInterface', $output->getOutput());
    }

    public function testEntriesTypes(): void
    {
        $container = ContainerBuilder::build();

        // Add extra types to
        $container->set('slim_env', env('FAKE_ENV_VAR'));
        $container->set('slim_environment', decorate(static fn(string $value): string => $value));
        $container->set('settings', []);
        $container->set('custom_settings', get('settings'));

        $command = new ListCommand($container);

        $input = new ArgvInput([], $command->getDefinition());
        $input->setOption('full', 1);

        $output = new ConsoleOutputStub();

        static::assertSame(0, $command->execute($input, $output));
        static::assertStringContainsString('Factory', $output->getOutput());
        static::assertStringContainsString('Object', $output->getOutput());
        static::assertStringContainsString('Value', $output->getOutput());
        static::assertStringContainsString('variable', $output->getOutput());
        static::assertStringContainsString('Decorate', $output->getOutput());
        static::assertStringContainsString('Array', $output->getOutput());
        static::assertStringContainsString('Alias', $output->getOutput());
    }

    public function testSearchNoEntries(): void
    {
        $container = Container::create([]);

        $command = new ListCommand($container);

        $input = new ArgvInput([], $command->getDefinition());
        $input->setArgument('search', '/|#|~|%|!');

        $output = new ConsoleOutputStub();

        static::assertSame(1, $command->execute($input, $output));
        static::assertStringContainsString('No container entries to show', $output->getOutput());
    }

    public function testSearchRegex(): void
    {
        $container = ContainerBuilder::build();
        $container->set('app_env', env('FAKE_ENV_VAR', 'prod'));

        $command = new ListCommand($container);

        $input = new ArgvInput([], $command->getDefinition());
        $input->setArgument('search', '/app/i');

        $output = new ConsoleOutputStub();

        static::assertSame(0, $command->execute($input, $output));
        static::assertStringContainsString('Slim\App', $output->getOutput());
        static::assertStringContainsString('app_env', $output->getOutput());
    }

    public function testSearchString(): void
    {
        $container = ContainerBuilder::build();
        $container->set('slim_env', env('FAKE_ENV_VAR', env('ANOTHER_FAKE_ENV_VAR', 'mock')));

        $command = new ListCommand($container);

        $input = new ArgvInput([], $command->getDefinition());
        $input->setArgument('search', 'Slim');

        $output = new ConsoleOutputStub();

        static::assertSame(0, $command->execute($input, $output));

        static::assertStringContainsString(
            'Slim\App   Factory',
            $output->getOutput(),
        );
        static::assertStringContainsString(
            'Slim\Interfaces\CallableResolverInterface   Factory',
            $output->getOutput(),
        );
        static::assertStringContainsString(
            'Slim\Interfaces\DispatcherInterface   Factory',
            $output->getOutput(),
        );
        static::assertStringContainsString(
            'Slim\Interfaces\InvocationStrategyInterface   Factory',
            $output->getOutput(),
        );
        static::assertStringContainsString(
            'Slim\Interfaces\MiddlewareDispatcherInterface   Factory',
            $output->getOutput(),
        );
        static::assertStringContainsString(
            'Slim\Interfaces\RouteCollectorInterface   Factory',
            $output->getOutput(),
        );
        static::assertStringContainsString(
            'Slim\Interfaces\RouteResolverInterface   Factory',
            $output->getOutput(),
        );
        static::assertStringContainsString(
            'Slim\Interfaces\ServerRequestCreatorInterface   Factory',
            $output->getOutput(),
        );
        static::assertStringContainsString(
            'slim_env   Environment variable (FAKE_ENV_VAR)',
            $output->getOutput(),
        );
    }
}
