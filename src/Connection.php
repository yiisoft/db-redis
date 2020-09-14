<?php

declare(strict_types=1);

namespace Yiisoft\Db\Redis;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Db\Command\Command;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Redis\Event\AfterOpen;
use Yiisoft\Db\Schema\Schema;
use Yiisoft\Db\Schema\TableSchema;
use Yiisoft\Strings\Inflector;
use Yiisoft\VarDumper\VarDumper;

use function array_keys;
use function array_merge;
use function count;
use function explode;
use function fclose;
use function fgets;
use function fread;
use function fwrite;
use function get_class;
use function get_object_vars;
use function implode;
use function in_array;
use function ini_get;
use function mb_strlen;
use function mb_substr;
use function preg_split;
use function stream_set_timeout;
use function stream_socket_enable_crypto;
use function strtoupper;
use function usleep;
use function version_compare;

final class Connection implements ConnectionInterface
{
    private string $hostname = 'localhost';
    private string $redirectConnectionString = '';
    private int $port = 6379;
    private string $unixSocket = '';
    private ?string $password = null;
    private ?int $database = null;
    private ?float $connectionTimeout = null;
    private ?float $dataTimeout = null;
    private bool $useSSL = false;
    private int $socketClientFlags = STREAM_CLIENT_CONNECT;
    private int $retries = 0;
    private int $retryInterval = 0;
    private array $redisCommands = [
        'APPEND', // Append a value to a key
        'AUTH', // Authenticate to the server
        'BGREWRITEAOF', // Asynchronously rewrite the append-only file
        'BGSAVE', // Asynchronously save the dataset to disk
        'BITCOUNT', // Count set bits in a string
        'BITFIELD', // Perform arbitrary bitfield integer operations on strings
        'BITOP', // Perform bitwise operations between strings
        'BITPOS', // Find first bit set or clear in a string
        'BLPOP', // Remove and get the first element in a list, or block until one is available
        'BRPOP', // Remove and get the last element in a list, or block until one is available
        'BRPOPLPUSH', // Pop a value from a list, push it to another list and return it; or block until one is available
        'CLIENT KILL', // Kill the connection of a client
        'CLIENT LIST', // Get the list of client connections
        'CLIENT GETNAME', // Get the current connection name
        'CLIENT PAUSE', // Stop processing commands from clients for some time
        'CLIENT REPLY', // Instruct the server whether to reply to commands
        'CLIENT SETNAME', // Set the current connection name
        'CLUSTER ADDSLOTS', // Assign new hash slots to receiving node
        'CLUSTER COUNTKEYSINSLOT', // Return the number of local keys in the specified hash slot
        'CLUSTER DELSLOTS', // Set hash slots as unbound in receiving node
        'CLUSTER FAILOVER', // Forces a slave to perform a manual failover of its master.
        'CLUSTER FORGET', // Remove a node from the nodes table
        'CLUSTER GETKEYSINSLOT', // Return local key names in the specified hash slot
        'CLUSTER INFO', // Provides info about Redis Cluster node state
        'CLUSTER KEYSLOT', // Returns the hash slot of the specified key
        'CLUSTER MEET', // Force a node cluster to handshake with another node
        'CLUSTER NODES', // Get Cluster config for the node
        'CLUSTER REPLICATE', // Reconfigure a node as a slave of the specified master node
        'CLUSTER RESET', // Reset a Redis Cluster node
        'CLUSTER SAVECONFIG', // Forces the node to save cluster state on disk
        'CLUSTER SETSLOT', // Bind a hash slot to a specific node
        'CLUSTER SLAVES', // List slave nodes of the specified master node
        'CLUSTER SLOTS', // Get array of Cluster slot to node mappings
        'COMMAND', // Get array of Redis command details
        'COMMAND COUNT', // Get total number of Redis commands
        'COMMAND GETKEYS', // Extract keys given a full Redis command
        'COMMAND INFO', // Get array of specific Redis command details
        'CONFIG GET', // Get the value of a configuration parameter
        'CONFIG REWRITE', // Rewrite the configuration file with the in memory configuration
        'CONFIG SET', // Set a configuration parameter to the given value
        'CONFIG RESETSTAT', // Reset the stats returned by INFO
        'DBSIZE', // Return the number of keys in the selected database
        'DEBUG OBJECT', // Get debugging information about a key
        'DEBUG SEGFAULT', // Make the server crash
        'DECR', // Decrement the integer value of a key by one
        'DECRBY', // Decrement the integer value of a key by the given number
        'DEL', // Delete a key
        'DISCARD', // Discard all commands issued after MULTI
        'DUMP', // Return a serialized version of the value stored at the specified key.
        'ECHO', // Echo the given string
        'EVAL', // Execute a Lua script server side
        'EVALSHA', // Execute a Lua script server side
        'EXEC', // Execute all commands issued after MULTI
        'EXISTS', // Determine if a key exists
        'EXPIRE', // Set a key's time to live in seconds
        'EXPIREAT', // Set the expiration for a key as a UNIX timestamp
        'FLUSHALL', // Remove all keys from all databases
        'FLUSHDB', // Remove all keys from the current database
        'GEOADD', // Add one or more geospatial items in the geospatial index represented using a sorted set
        'GEOHASH', // Returns members of a geospatial index as standard geohash strings
        'GEOPOS', // Returns longitude and latitude of members of a geospatial index
        'GEODIST', // Returns the distance between two members of a geospatial index
        'GEORADIUS', // Query a sorted set representing a geospatial index to fetch members matching a given maximum distance from a point
        'GEORADIUSBYMEMBER', // Query a sorted set representing a geospatial index to fetch members matching a given maximum distance from a member
        'GET', // Get the value of a key
        'GETBIT', // Returns the bit value at offset in the string value stored at key
        'GETRANGE', // Get a substring of the string stored at a key
        'GETSET', // Set the string value of a key and return its old value
        'HDEL', // Delete one or more hash fields
        'HEXISTS', // Determine if a hash field exists
        'HGET', // Get the value of a hash field
        'HGETALL', // Get all the fields and values in a hash
        'HINCRBY', // Increment the integer value of a hash field by the given number
        'HINCRBYFLOAT', // Increment the float value of a hash field by the given amount
        'HKEYS', // Get all the fields in a hash
        'HLEN', // Get the number of fields in a hash
        'HMGET', // Get the values of all the given hash fields
        'HMSET', // Set multiple hash fields to multiple values
        'HSET', // Set the string value of a hash field
        'HSETNX', // Set the value of a hash field, only if the field does not exist
        'HSTRLEN', // Get the length of the value of a hash field
        'HVALS', // Get all the values in a hash
        'INCR', // Increment the integer value of a key by one
        'INCRBY', // Increment the integer value of a key by the given amount
        'INCRBYFLOAT', // Increment the float value of a key by the given amount
        'INFO', // Get information and statistics about the server
        'KEYS', // Find all keys matching the given pattern
        'LASTSAVE', // Get the UNIX time stamp of the last successful save to disk
        'LINDEX', // Get an element from a list by its index
        'LINSERT', // Insert an element before or after another element in a list
        'LLEN', // Get the length of a list
        'LPOP', // Remove and get the first element in a list
        'LPUSH', // Prepend one or multiple values to a list
        'LPUSHX', // Prepend a value to a list, only if the list exists
        'LRANGE', // Get a range of elements from a list
        'LREM', // Remove elements from a list
        'LSET', // Set the value of an element in a list by its index
        'LTRIM', // Trim a list to the specified range
        'MGET', // Get the values of all the given keys
        'MIGRATE', // Atomically transfer a key from a Redis instance to another one.
        'MONITOR', // Listen for all requests received by the server in real time
        'MOVE', // Move a key to another database
        'MSET', // Set multiple keys to multiple values
        'MSETNX', // Set multiple keys to multiple values, only if none of the keys exist
        'MULTI', // Mark the start of a transaction block
        'OBJECT', // Inspect the internals of Redis objects
        'PERSIST', // Remove the expiration from a key
        'PEXPIRE', // Set a key's time to live in milliseconds
        'PEXPIREAT', // Set the expiration for a key as a UNIX timestamp specified in milliseconds
        'PFADD', // Adds the specified elements to the specified HyperLogLog.
        'PFCOUNT', // Return the approximated cardinality of the set(s) observed by the HyperLogLog at key(s).
        'PFMERGE', // Merge N different HyperLogLogs into a single one.
        'PING', // Ping the server
        'PSETEX', // Set the value and expiration in milliseconds of a key
        'PSUBSCRIBE', // Listen for messages published to channels matching the given patterns
        'PUBSUB', // Inspect the state of the Pub/Sub subsystem
        'PTTL', // Get the time to live for a key in milliseconds
        'PUBLISH', // Post a message to a channel
        'PUNSUBSCRIBE', // Stop listening for messages posted to channels matching the given patterns
        'QUIT', // Close the connection
        'RANDOMKEY', // Return a random key from the keyspace
        'READONLY', // Enables read queries for a connection to a cluster slave node
        'READWRITE', // Disables read queries for a connection to a cluster slave node
        'RENAME', // Rename a key
        'RENAMENX', // Rename a key, only if the new key does not exist
        'RESTORE', // Create a key using the provided serialized value, previously obtained using DUMP.
        'ROLE', // Return the role of the instance in the context of replication
        'RPOP', // Remove and get the last element in a list
        'RPOPLPUSH', // Remove the last element in a list, prepend it to another list and return it
        'RPUSH', // Append one or multiple values to a list
        'RPUSHX', // Append a value to a list, only if the list exists
        'SADD', // Add one or more members to a set
        'SAVE', // Synchronously save the dataset to disk
        'SCARD', // Get the number of members in a set
        'SCRIPT DEBUG', // Set the debug mode for executed scripts.
        'SCRIPT EXISTS', // Check existence of scripts in the script cache.
        'SCRIPT FLUSH', // Remove all the scripts from the script cache.
        'SCRIPT KILL', // Kill the script currently in execution.
        'SCRIPT LOAD', // Load the specified Lua script into the script cache.
        'SDIFF', // Subtract multiple sets
        'SDIFFSTORE', // Subtract multiple sets and store the resulting set in a key
        'SELECT', // Change the selected database for the current connection
        'SET', // Set the string value of a key
        'SETBIT', // Sets or clears the bit at offset in the string value stored at key
        'SETEX', // Set the value and expiration of a key
        'SETNX', // Set the value of a key, only if the key does not exist
        'SETRANGE', // Overwrite part of a string at key starting at the specified offset
        'SHUTDOWN', // Synchronously save the dataset to disk and then shut down the server
        'SINTER', // Intersect multiple sets
        'SINTERSTORE', // Intersect multiple sets and store the resulting set in a key
        'SISMEMBER', // Determine if a given value is a member of a set
        'SLAVEOF', // Make the server a slave of another instance, or promote it as master
        'SLOWLOG', // Manages the Redis slow queries log
        'SMEMBERS', // Get all the members in a set
        'SMOVE', // Move a member from one set to another
        'SORT', // Sort the elements in a list, set or sorted set
        'SPOP', // Remove and return one or multiple random members from a set
        'SRANDMEMBER', // Get one or multiple random members from a set
        'SREM', // Remove one or more members from a set
        'STRLEN', // Get the length of the value stored in a key
        'SUBSCRIBE', // Listen for messages published to the given channels
        'SUNION', // Add multiple sets
        'SUNIONSTORE', // Add multiple sets and store the resulting set in a key
        'SWAPDB', // Swaps two Redis databases
        'SYNC', // Internal command used for replication
        'TIME', // Return the current server time
        'TOUCH', // Alters the last access time of a key(s). Returns the number of existing keys specified.
        'TTL', // Get the time to live for a key
        'TYPE', // Determine the type stored at key
        'UNSUBSCRIBE', // Stop listening for messages posted to the given channels
        'UNLINK', // Delete a key asynchronously in another thread. Otherwise it is just as DEL, but non blocking.
        'UNWATCH', // Forget about all watched keys
        'WAIT', // Wait for the synchronous replication of all the write commands sent in the context of the current connection
        'WATCH', // Watch the given keys to determine execution of the MULTI/EXEC block
        'XACK', // Removes one or multiple messages from the pending entries list (PEL) of a stream consumer group
        'XADD', // Appends the specified stream entry to the stream at the specified key
        'XCLAIM', // Changes the ownership of a pending message, so that the new owner is the consumer specified as the command argument
        'XDEL', // Removes the specified entries from a stream, and returns the number of entries deleted
        'XGROUP', // Manages the consumer groups associated with a stream data structure
        'XINFO', // Retrieves different information about the streams and associated consumer groups
        'XLEN', // Returns the number of entries inside a stream
        'XPENDING', // Fetching data from a stream via a consumer group, and not acknowledging such data, has the effect of creating pending entries
        'XRANGE', // Returns the stream entries matching a given range of IDs
        'XREAD', // Read data from one or multiple streams, only returning entries with an ID greater than the last received ID reported by the caller
        'XREADGROUP', // Special version of the XREAD command with support for consumer groups
        'XREVRANGE', // Exactly like XRANGE, but with the notable difference of returning the entries in reverse order, and also taking the start-end range in reverse order
        'XTRIM', // Trims the stream to a given number of items, evicting older items (items with lower IDs) if needed
        'ZADD', // Add one or more members to a sorted set, or update its score if it already exists
        'ZCARD', // Get the number of members in a sorted set
        'ZCOUNT', // Count the members in a sorted set with scores within the given values
        'ZINCRBY', // Increment the score of a member in a sorted set
        'ZINTERSTORE', // Intersect multiple sorted sets and store the resulting sorted set in a new key
        'ZLEXCOUNT', // Count the number of members in a sorted set between a given lexicographical range
        'ZRANGE', // Return a range of members in a sorted set, by index
        'ZRANGEBYLEX', // Return a range of members in a sorted set, by lexicographical range
        'ZREVRANGEBYLEX', // Return a range of members in a sorted set, by lexicographical range, ordered from higher to lower strings.
        'ZRANGEBYSCORE', // Return a range of members in a sorted set, by score
        'ZRANK', // Determine the index of a member in a sorted set
        'ZREM', // Remove one or more members from a sorted set
        'ZREMRANGEBYLEX', // Remove all members in a sorted set between the given lexicographical range
        'ZREMRANGEBYRANK', // Remove all members in a sorted set within the given indexes
        'ZREMRANGEBYSCORE', // Remove all members in a sorted set within the given scores
        'ZREVRANGE', // Return a range of members in a sorted set, by index, with scores ordered from high to low
        'ZREVRANGEBYSCORE', // Return a range of members in a sorted set, by score, with scores ordered from high to low
        'ZREVRANK', // Determine the index of a member in a sorted set, with scores ordered from high to low
        'ZSCORE', // Get the score associated with the given member in a sorted set
        'ZUNIONSTORE', // Add multiple sorted sets and store the resulting sorted set in a new key
        'SCAN', // Incrementally iterate the keys space
        'SSCAN', // Incrementally iterate Set elements
        'HSCAN', // Incrementally iterate hash fields and associated values
        'ZSCAN', // Incrementally iterate sorted sets elements and associated scores
    ];
    private array $pool = [];
    private bool $runEvent = false;
    private EventDispatcherInterface $dispatch;
    private LoggerInterface $logger;
    private string $dsn;

    public function __construct(EventDispatcherInterface $dispatch, LoggerInterface $logger)
    {
        $this->dispatch = $dispatch;
        $this->logger = $logger;
    }

    /**
     * Closes the connection when this component is being serialized.
     *
     * @return array
     */
    public function __sleep(): array
    {
        unset($this->dispatch);

        $this->close();

        return array_keys(get_object_vars($this));
    }

    /**
     * Return the connection string used to open a socket connection. During a redirect (cluster mode) this will be the
     * target of the redirect.
     *
     * @return string socket connection string
     */
    public function getConnectionString(): string
    {
        if ($this->unixSocket) {
            return 'unix://' . $this->unixSocket;
        }

        return 'tcp://' . ($this->redirectConnectionString ?: "$this->hostname:$this->port");
    }

    /**
     * Return the connection resource if a connection to the target has been established before, `false` otherwise.
     *
     * @return resource|false
     */
    public function getSocket()
    {
        return ArrayHelper::getValue($this->pool, $this->getConnectionString(), false);
    }

    /**
     * Returns a value indicating whether the DB connection is established.
     *
     * @return bool whether the DB connection is established
     */
    public function getIsActive(): bool
    {
        return ArrayHelper::getValue($this->pool, "$this->hostname:$this->port", false) !== false;
    }

    /**
     * Establishes a DB connection.
     *
     * It does nothing if a DB connection has already been established.
     *
     * @throws Exception if connection fails
     */
    public function open(): void
    {
        if ($this->getSocket() !== false) {
            return;
        }

        $this->dsn = $this->getConnectionString() . ', database=' . $this->database;

        $this->logger->log(LogLevel::INFO, 'Opening redis DB connection: ' . $this->dsn . ' ' . __METHOD__);

        $socket = @stream_socket_client(
            $this->getConnectionString(),
            $errorNumber,
            $errorDescription,
            $this->connectionTimeout ?? (float) ini_get('default_socket_timeout'),
            $this->socketClientFlags
        );

        if ($socket) {
            $this->pool[ $this->getConnectionString() ] = $socket;

            if ($this->dataTimeout !== null) {
                stream_set_timeout(
                    $socket, $timeout = (int) $this->dataTimeout, (int) (($this->dataTimeout - $timeout) * 1000000)
                );
            }

            if ($this->useSSL) {
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            }

            if ($this->password !== null) {
                $this->executeCommand('AUTH', [$this->password]);
            }

            if ($this->database !== null) {
                $this->executeCommand('SELECT', [$this->database]);
            }

            if ($this->runEvent) {
                $this->dispatch->dispatch(new AfterOpen());
            }
        } else {
            $message = "Failed to open redis DB connection ($this->dsn): $errorNumber - $errorDescription " . __CLASS__;

            $this->logger->log(
                LogLevel::ERROR,
                $message
            );

            throw new Exception($message, $errorDescription, $errorNumber);
        }
    }

    /**
     * Closes the currently active DB connection.
     *
     * It does nothing if the connection is already closed.
     */
    public function close(): void
    {
        foreach ($this->pool as $socket) {
            $this->dsn = $this->getConnectionString() . ', database=' . $this->database;

            $this->logger->log(LogLevel::INFO, 'Closing DB connection: ' . $this->dsn . ' ' . __METHOD__);

            try {
                $this->executeCommand('QUIT');
            } catch (SocketException $e) {
                /** ignore errors when quitting a closed connection. */
            }

            fclose($socket);
        }

        $this->pool = [];
    }

    /**
     * Returns the name of the DB driver.
     *
     * @return string name of the DB driver.
     */
    public function getDriverName(): string
    {
        return 'redis';
    }

    /**
     * Allows issuing all supported commands via magic methods.
     *
     * ```php
     * $redis->hmset('test_collection', 'key1', 'val1', 'key2', 'val2')
     * ```
     *
     * @param string $name name of the missing method to execute.
     * @param array $params method call arguments.
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function __call(string $name, array $params)
    {
        $redisCommand = strtoupper((new Inflector())->toWords($name, false));

        if (in_array($redisCommand, $this->redisCommands, true)) {
            return $this->executeCommand($redisCommand, $params);
        }
    }

    /**
     * Executes a redis command.
     *
     * For a list of available commands and their parameters see https://redis.io/commands.
     *
     * The params array should contain the params separated by white space, e.g. to execute
     * `SET mykey somevalue NX` call the following:
     *
     * ```php
     * $redis->executeCommand('SET', ['mykey', 'somevalue', 'NX']);
     * ```
     *
     * @param string $name the name of the command.
     * @param array $params list of parameters for the command.
     *
     * @throws Exception for commands that return [error reply](https://redis.io/topics/protocol#error-reply).
     *
     * @return array|bool|null|string Dependent on the executed command this method will return different data types:
     *
     * - `true` for commands that return "status reply" with the message `'OK'` or `'PONG'`.
     * - `string` for commands that return "status reply" that does not have the message `OK`.
     * - `string` for commands that return "integer reply"
     *   as the value is in the range of a signed 64 bit integer.
     * - `string` or `null` for commands that return "bulk reply".
     * - `array` for commands that return "Multi-bulk replies".
     *
     * See [redis protocol description](https://redis.io/topics/protocol)
     *
     * for details on the mentioned reply types.
     */
    public function executeCommand($name, $params = [])
    {
        $this->open();

        $params = array_merge(explode(' ', $name), $params);
        $command = '*' . count($params) . "\r\n";

        foreach ($params as $arg) {
            $command .= '$' . mb_strlen((string) $arg, '8bit') . "\r\n" . $arg . "\r\n";
        }

        $this->logger->log(LogLevel::INFO, "Executing Redis Command: {$name} " . ' ' . __METHOD__);

        if ($this->retries > 0) {
            $tries = $this->retries;
            while ($tries-- > 0) {
                try {
                    return $this->sendCommandInternal($command, $params);
                } catch (SocketException $e) {
                    $this->logger->log(LogLevel::ERROR, $e . ' ' . __METHOD__);

                    /** backup retries, fail on commands that fail inside here. */
                    $retries = $this->retries;

                    $this->retries = 0;

                    $this->close();

                    if ($this->retryInterval > 0) {
                        usleep($this->retryInterval);
                    }

                    $this->open();

                    $this->retries = $retries;
                }
            }
        }

        return $this->sendCommandInternal($command, $params);
    }

    /**
     * Sends RAW command string to the server.
     *
     * @param string $command
     * @param array $params
     *
     * @throws Exception
     * @throws SocketException on connection error.
     *
     * @return array|bool|false|mixed|string|null
     */
    private function sendCommandInternal(string $command, array $params = [])
    {
        $written = @fwrite($this->getSocket(), $command);

        if ($written === false) {
            throw new SocketException("Failed to write to socket.\nRedis command was: " . $command);
        }
        if ($written !== ($len = mb_strlen($command, '8bit'))) {
            throw new SocketException("Failed to write to socket. $written of $len bytes written.\nRedis command was: " . $command);
        }

        return $this->parseResponse($params, $command);
    }

    private function parseResponse(array $params, ?string $command = null)
    {
        $prettyCommand = implode(' ', $params);

        if (($line = fgets($this->getSocket())) === false) {
            throw new SocketException("Failed to read from socket.\nRedis command was: " . $prettyCommand);
        }

        $type = $line[0];
        $line = mb_substr($line, 1, -2, '8bit');

        switch ($type) {
            case '+': // Status reply
                if ($line === 'OK' || $line === 'PONG') {
                    return true;
                }

                return $line;
            case '-': // Error reply
                if ($this->isRedirect($line)) {
                    return $this->redirect($line, $command, $params);
                }

                throw new Exception("Redis error: " . $line . "\nRedis command was: " . $prettyCommand);
            case ':': // Integer reply
                // no cast to int as it is in the range of a signed 64 bit integer
                return $line;
            case '$': // Bulk replies
                if ($line === '-1') {
                    return null;
                }

                $length = (int)$line + 2;
                $data = '';

                while ($length > 0) {
                    if (($block = fread($this->getSocket(), $length)) === false) {
                        throw new SocketException("Failed to read from socket.\nRedis command was: " . $prettyCommand);
                    }
                    $data .= $block;
                    $length -= mb_strlen($block, '8bit');
                }

                return mb_substr($data, 0, -2, '8bit');
            case '*': // Multi-bulk replies
                $count = (int) $line;
                $data = [];
                for ($i = 0; $i < $count; $i++) {
                    $data[] = $this->parseResponse($params);
                }

                return $data;
            default:
                throw new Exception(
                    'Received illegal data from redis: ' . $line . "\nRedis command was: " . $prettyCommand
                );
        }
    }

    private function isRedirect(string $line): bool
    {
        return is_string($line) && mb_strpos($line, 'MOVED') === 0;
    }

    private function redirect(string $redirect, string $command, array $params = [])
    {
        $responseParts = preg_split('/\s+/', $redirect);

        $this->redirectConnectionString = ArrayHelper::getValue($responseParts, 2);

        if ($this->redirectConnectionString) {
            $this->logger->log(LogLevel::INFO, 'Redirecting to ' . $this->getConnectionString(), __METHOD__);

            $this->open();

            $response = $this->sendCommandInternal($command, $params);

            $this->redirectConnectionString = '';

            return $response;
        }

        throw new Exception('No hostname found in redis redirect (MOVED): ' . VarDumper::dumpAsString($redirect));
    }

    /**
     * @return float timeout to use for connection to redis. If not set the timeout set in php.ini will be used:
     * `ini_get("default_socket_timeout")`.
     */
    public function getConnectionTimeout(): ?float
    {
        return $this->connectionTimeout;
    }

    /**
     * @return int the redis database to use. This is an integer value starting from 0. Defaults to 0.
     *
     * You can disable the SELECT command sent after connection by setting this property to `null`.
     */
    public function getDatabase(): ?int
    {
        return $this->database;
    }

    /**
     * @return float timeout to use for redis socket when reading and writing data. If not set the php default value
     * will be used.
     */
    public function getDataTimeout(): ?float
    {
        return $this->dataTimeout;
    }

    /**
     * @return string the hostname or ip address to use for connecting to the redis server. Defaults to 'localhost'.
     *
     * If {@see unixSocket} is specified, hostname and {@see port} will be ignored.
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * @return string the password for establishing DB connection. Defaults to null meaning no AUTH command is sent.
     *
     * {@see https://redis.io/commands/auth}
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return array redis redirect socket connection pool.
     */
    public function getPool(): array
    {
        return $this->pool;
    }

    /**
     * @return int the port to use for connecting to the redis server. Default port is 6379.
     *
     * If {@see unixSocket} is specified, {@see hostname} and port will be ignored.
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return string if the query gets redirected, use this as the temporary new hostname.
     */
    public function getRedirectConnectionString(): string
    {
        return $this->redirectConnectionString;
    }

    /**
     * @return array List of available redis commands.
     *
     * {@see https://redis.io/commands}
     */
    public function getRedisCommands(): array
    {
        return $this->redisCommands;
    }

    /**
     * @return int The number of times a command execution should be retried when a connection failure occurs.
     *
     * This is used in {@see executeCommand()} when a {@see SocketException} is thrown.
     *
     * Defaults to 0 meaning no retries on failure.
     */
    public function getRetries(): int
    {
        return $this->retries;
    }

    /**
     * @return int The retry interval in microseconds to wait between retry.
     *
     * This is used in {@see executeCommand()} when a {@see SocketException} is thrown.
     *
     * Defaults to 0 meaning no wait.
     */
    public function getRetryInterval(): int
    {
        return $this->retryInterval;
    }

    public function getRunEvent(): bool
    {
        return $this->runEvent;
    }

    /**
     * @return integer Bitmask field which may be set to any combination of connection flags passed to
     * [stream_socket_client()](https://www.php.net/manual/en/function.stream-socket-client.php).
     *
     * Currently the select of connection flags is limited to `STREAM_CLIENT_CONNECT` (default),
     * `STREAM_CLIENT_ASYNC_CONNECT` and `STREAM_CLIENT_PERSISTENT`.
     *
     * > Warning: `STREAM_CLIENT_PERSISTENT` will make PHP reuse connections to the same server. If you are using
     * > multiple connection objects to refer to different redis {@see $database|databases} on the same {@see port},
     * > redis commands may get executed on the wrong database. `STREAM_CLIENT_PERSISTENT` is only safe to use if you
     * > use only one database.
     * >
     * > You may still use persistent connections in this case when disambiguating ports as described
     * > in [a comment on the PHP manual](https://www.php.net/manual/en/function.stream-socket-client.php#105393)
     * > e.g. on the connection used for session storage, specify the port as:
     * >
     * > ```php
     * > 'port' => '6379/session'
     * > ```
     *
     * {@see https://www.php.net/manual/en/function.stream-socket-client.php}
     */
    public function getSocketClientFlags(): int
    {
        return $this->socketClientFlags;
    }

    /**
     * @return string the unix socket path (e.g. `/var/run/redis/redis.sock`) to use for connecting to the redis server.
     * This can be used instead of {@see hostname} and {@see port} to connect to the server using a unix socket. If a
     * unix socket path is specified, {@see hostname} and {@see port} will be ignored.
     */
    public function getUnixSocket(): string
    {
        return $this->unixSocket;
    }

    /**
     * @return bool Send sockets over SSL protocol. Default state is false.
     */
    public function getUseSSL(): bool
    {
        return $this->useSSL;
    }

    public function connectionTimeout(float $value): void
    {
        $this->connectionTimeout = $value;
    }

    public function database(int $value): void
    {
        $this->database = $value;
    }

    public function dataTimeout(float $value): void
    {
        $this->dataTimeout = $value;
    }

    public function hostname(string $value): void
    {
        $this->hostname = $value;
    }

    public function password(?string $value): void
    {
        $this->password = $value;
    }

    public function port(int $value): void
    {
        $this->port = $value;
    }

    public function pool(array $value): void
    {
        $this->pool = $value;
    }

    public function redirectConnectionString(string $value): void
    {
        $this->redirectConnectionString = $value;
    }

    public function redisCommands(array $value): void
    {
        $this->redisCommands = $value;
    }

    public function retries(int $value): void
    {
        $this->retries = $value;
    }

    public function retryInterval(int $value): void
    {
        $this->retryInterval = $value;
    }

    public function runEvent(bool $value): void
    {
        $this->runEvent = $value;
    }

    public function socketClientFlags(int $value): void
    {
        $this->socketClientFlags = $value;
    }

    public function unixSocket(string $value): void
    {
        $this->unixSocket = $value;
    }

    public function useSSL(bool $useSSL): void
    {
        $this->useSSL = $useSSL;

    }

    /**
     * Creates a command for execution.
     *
     * @param string|null $sql the SQL statement to be executed
     * @param array $params the parameters to be bound to the SQL statement
     *
     * @throws NotSupportedException
     *
     * @return Command the DB command
     */
    public function createCommand(?string $sql = null, array $params = []): Command
    {
        throw new NotSupportedException(get_class($this) . ' does not support Command::class.');
    }

    /**
     * @return string the Data Source Name, or DSN, contains the information required to connect to the database.
     *
     * Please refer to the [PHP manual](https://secure.php.net/manual/en/pdo.construct.php) on the format of the DSN
     * string.
     */
    public function getDsn(): string
    {
        return $this->dsn;
    }

    /**
     * Returns the schema information for the database opened by this connection.
     *
     * @throws NotSupportedException
     *
     * @return Schema the schema information for the database opened by this connection.
     */
    public function getSchema(): Schema
    {
        throw new NotSupportedException(get_class($this) . ' does not support Schema::class.');
    }

    /**
     * Returns a server version as a string comparable by {@see version_compare()}.
     *
     * @throws Exception
     *
     * @return string server version as a string.
     */
    public function getServerVersion(): string
    {
        $version = (explode("\r\n", $this->executeCommand('INFO', ['server'])));

        return $version[1];
    }

    /**
     * Obtains the schema information for the named table.
     *
     * @param string $name table name.
     * @param bool $refresh whether to reload the table schema even if it is found in the cache.
     *
     * @throws NotSupportedException
     *
     * @return TableSchema|null
     */
    public function getTableSchema($name, $refresh = false): ?TableSchema
    {
        throw new NotSupportedException(get_class($this) . ' does not support TableShema::class.');
    }

    /**
     * Whether to enable read/write splitting by using {@see setSlaves()} to read data. Note that if {@see setSlaves()}
     * is empty, read/write splitting will NOT be enabled no matter what value this property takes.
     *
     * @param bool $value
     *
     * @throws NotSupportedException
     */
    public function setEnableSlaves(bool $value): void
    {
        throw new NotSupportedException(get_class($this) . ' does not support SetEnableSlaves() method.');
    }
}
