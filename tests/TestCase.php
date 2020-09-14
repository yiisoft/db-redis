<?php

declare(strict_types=1);

namespace Yiisoft\Db\Redis\Tests;

use PHPUnit\Framework\TestCase as AbstractTestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Cache\Cache;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Db\Connection\ConnectionPool;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Redis\Connection;
use Yiisoft\Db\Redis\Event\AfterOpen;
use Yiisoft\Db\Redis\Tests\Data\Event\AfterCustom;
use Yiisoft\Db\Redis\Tests\Data\Provider\EventDispatcherProvider;
use Yiisoft\Db\TestUtility\IsOneOfAssert;
use Yiisoft\Di\Container;
use Yiisoft\EventDispatcher\Dispatcher\Dispatcher;
use Yiisoft\EventDispatcher\Provider\ListenerCollection;
use Yiisoft\EventDispatcher\Provider\Provider;
use Yiisoft\Injector\Injector;
use Yiisoft\Log\Logger;
use Yiisoft\Yii\Event\InvalidEventConfigurationFormatException;
use Yiisoft\Yii\Event\InvalidListenerConfigurationException;

use function explode;
use function file_get_contents;
use function str_replace;
use function trim;

class TestCase extends AbstractTestCase
{
    protected Aliases $aliases;
    protected CacheInterface $cache;
    protected Connection $connection;
    protected ContainerInterface $container;
    protected array $dataProvider;
    protected EventDispatcherInterface $dispatch;
    protected EventConfigurator $eventConfigurator;
    protected Dsn $dsn;
    protected string $likeEscapeCharSql = '';
    protected array $likeParameterReplacements = [];
    protected ListenerProviderInterface $listener;
    protected LoggerInterface $logger;
    protected Profiler $profiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configContainer();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset(
            $this->aliases,
            $this->cache,
            $this->connection,
            $this->container,
            $this->dataProvider,
            $this->dsn,
            $this->logger,
            $this->profiler
        );
    }

    /**
     * Asserting two strings equality ignoring line endings.
     *
     * @param string $expected
     * @param string $actual
     * @param string $message
     *
     * @return void
     */
    protected function assertEqualsWithoutLE(string $expected, string $actual, string $message = ''): void
    {
        $expected = str_replace("\r\n", "\n", $expected);
        $actual = str_replace("\r\n", "\n", $actual);

        $this->assertEquals($expected, $actual, $message);
    }

    /**
     * Asserts that value is one of expected values.
     *
     * @param mixed $actual
     * @param array $expected
     * @param string $message
     */
    protected function assertIsOneOf($actual, array $expected, $message = ''): void
    {
        self::assertThat($actual, new IsOneOfAssert($expected), $message);
    }

    protected function configContainer(): void
    {
        $this->container = new Container($this->config());
        $this->aliases = $this->container->get(Aliases::class);
        $this->cache = $this->container->get(CacheInterface::class);
        $this->logger = $this->container->get(LoggerInterface::class);
        $this->connection = $this->container->get(Connection::class);
    }

    /**
     * Invokes a inaccessible method.
     *
     * @param object $object
     * @param string $method
     * @param array $args
     * @param bool $revoke whether to make method inaccessible after execution.
     *
     * @throws ReflectionException
     *
     * @return mixed
     */
    protected function invokeMethod(object $object, string $method, array $args = [], bool $revoke = true)
    {
        $reflection = new ReflectionObject($object);

        $method = $reflection->getMethod($method);

        $method->setAccessible(true);

        $result = $method->invokeArgs($object, $args);

        if ($revoke) {
            $method->setAccessible(false);
        }
        return $result;
    }

    /**
     * @param bool $reset whether to clean up the test database.
     *
     * @return Connection
     */
    protected function getConnection($reset = false): Connection
    {
        if ($reset) {
            $this->connection->open();
            $this->connection->flushdb();
        }

        return $this->connection;
    }

    /**
     * Gets an inaccessible object property.
     *
     * @param object $object
     * @param string $propertyName
     * @param bool $revoke whether to make property inaccessible after getting.
     *
     * @throws ReflectionException
     *
     * @return mixed
     */
    protected function getInaccessibleProperty(object $object, string $propertyName, bool $revoke = true)
    {
        $class = new ReflectionClass($object);

        while (!$class->hasProperty($propertyName)) {
            $class = $class->getParentClass();
        }

        $property = $class->getProperty($propertyName);

        $property->setAccessible(true);

        $result = $property->getValue($object);

        if ($revoke) {
            $property->setAccessible(false);
        }

        return $result;
    }

    /**
     * Adjust dbms specific escaping.
     *
     * @param string|array $sql
     *
     * @return string
     */
    protected function replaceQuotes($sql): string
    {
        return str_replace(['[[', ']]'], '`', $sql);
    }

    /**
     * Sets an inaccessible object property to a designated value.
     *
     * @param object $object
     * @param string $propertyName
     * @param $value
     * @param bool $revoke whether to make property inaccessible after setting
     *
     * @throws ReflectionException
     */
    protected function setInaccessibleProperty(object $object, string $propertyName, $value, bool $revoke = true): void
    {
        $class = new ReflectionClass($object);

        while (!$class->hasProperty($propertyName)) {
            $class = $class->getParentClass();
        }

        $property = $class->getProperty($propertyName);

        $property->setAccessible(true);

        $property->setValue($object, $value);

        if ($revoke) {
            $property->setAccessible(false);
        }
    }

    private function config(): array
    {
        $params = $this->params();

        return [
            ContainerInterface::class => static function (ContainerInterface $container) {
                return $container;
            },

            Aliases::class => [
                '@root' => dirname(__DIR__, 1),
                '@data' =>  '@root/tests/Data',
                '@runtime' => '@data/runtime',
            ],

            CacheInterface::class => static function () {
                return new Cache(new ArrayCache());
            },

            LoggerInterface::class => Logger::class,

            Profiler::class => static function (ContainerInterface $container) {
                return new Profiler($container->get(LoggerInterface::class));
            },

            EventDispatcherProvider::class => function (ContainerInterface $container) {
                $listenerCollection = new ListenerCollection();

                $injector = new Injector($container);

                foreach ($this->events() as $eventName => $listeners) {
                    if (!is_string($eventName)) {
                        throw new InvalidEventConfigurationFormatException(
                            'Incorrect event listener format. Format with event name must be used.'
                        );
                    }

                    if (!is_array($listeners)) {
                        $type = $this->isCallable($listeners, $container) ? 'callable' : gettype($listeners);

                        throw new InvalidEventConfigurationFormatException(
                            "Event listeners for $eventName must be an array, $type given."
                        );
                    }

                    foreach ($listeners as $callable) {
                        try {
                            if (!$this->isCallable($callable, $container)) {
                                $type = gettype($listeners);

                                throw new InvalidListenerConfigurationException(
                                    "Listener must be a callable. $type given."
                                );
                            }
                        } catch (ContainerExceptionInterface $exception) {
                            throw new InvalidListenerConfigurationException(
                                "Could not instantiate event listener or listener class has invalid configuration.",
                                0,
                                $exception
                            );
                        }

                        $listener = static function (object $event) use ($injector, $callable, $container) {
                            if (is_array($callable) && !is_object($callable[0])) {
                                $callable = [$container->get($callable[0]), $callable[1]];
                            }

                            return $injector->invoke($callable, [$event]);
                        };
                        $listenerCollection = $listenerCollection->add($listener, $eventName);
                    }
                }

                return new Provider($listenerCollection);
            },

            ListenerProviderInterface::class => EventDispatcherProvider::class,

            EventDispatcherInterface::class => Dispatcher::class,

            Connection::class  => static function (ContainerInterface $container) use ($params) {
                $connection = new Connection(
                    $container->get(EventDispatcherInterface::class),
                    $container->get(LoggerInterface::class)
                );

                $connection->hostname($params['yiisoft/db-redis']['dsn']['host']);
                $connection->port($params['yiisoft/db-redis']['dsn']['port']);
                $connection->database($params['yiisoft/db-redis']['dsn']['database']);
                $connection->password($params['yiisoft/db-redis']['password']);

                ConnectionPool::setConnectionsPool('redis', $connection);

                return $connection;
            }
        ];
    }

    private function events(): array
    {
        return [
            AfterOpen::class => [[AfterCustom::class, 'getSleep']],
        ];
    }

    private function providers(): array
    {
        return [

        ];
    }

    private function params(): array
    {
        return [
            'yiisoft/db-redis' => [
                'dsn' => [
                    'driver' => 'redis',
                    'host' => '127.0.0.1',
                    'database' => 0,
                    'port' => 6379
                ],
                'password' => null,
            ]
        ];
    }

    private function isCallable($definition, Container $container): bool
    {
        if (is_callable($definition)) {
            return true;
        }

        if (
            is_array($definition)
            && array_keys($definition) === [0, 1]
            && is_string($definition[0])
            && $container->has($definition[0])
        ) {
            $object = $container->get($definition[0]);

            return method_exists($object, $definition[1]);
        }

        return false;
    }
}
