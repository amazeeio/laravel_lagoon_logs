<?php

declare(strict_types=1);

namespace amazeeio\LagoonLogs;

use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\FallbackGroupHandler;
use Monolog\Handler\SocketHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class LagoonLoggerFactory
 *
 * @package amazeeio\LagoonLogs
 */
class LagoonLoggerFactory
{
    public const string LAGOON_LOGS_MONOLOG_CHANNEL_NAME = 'LagoonLogs';

    private const string DEFAULT_HOSTNAME = "application-logs.lagoon.svc";

    private const string DEFAULT_HOSTPORT = "5140";

    private const string DEFAULT_EXTRA_KEY_FOR_FORMATTER = "ctxt_";

    private const string LAGOON_LOGS_DEFAULT_SAFE_BRANCH = 'safe_branch_unset';

    private const string LAGOON_LOGS_DEFAULT_LAGOON_PROJECT = 'project_unset';

    private const int LAGOON_LOGS_DEFAULT_CHUNK_SIZE_BYTES = 15000;

    private const string LAGOON_LOGS_FALLBACK_LINE_FORMAT =
        "LAGOON LOGS FALLBACK: [%datetime%] %channel%.%level_name%: " .
        "%message% %context% %extra%\n";

    /**
     * Create a custom Monolog instance.
     *
     * @param  array<string, mixed>  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config): Logger
    {
        $logger = new Logger(self::LAGOON_LOGS_MONOLOG_CHANNEL_NAME);
        $connectionString = sprintf("udp://%s:%s", self::DEFAULT_HOSTNAME, self::DEFAULT_HOSTPORT);
        $udpHandler = new SocketHandler($connectionString);
        $udpHandler->setChunkSize(self::LAGOON_LOGS_DEFAULT_CHUNK_SIZE_BYTES);
        $udpHandler->setFormatter(new LogstashFormatter(
            self::getHostProcessIndex(),
            null,
            'extra',
            self::DEFAULT_EXTRA_KEY_FOR_FORMATTER,
            1
        ));

    // We want to wrap the group in a failure handler so that if
    // the logstash instance isn't available, it pushes to std
    // which will be available via the docker logs
        $fallbackHandler = new StreamHandler('php://stdout');

        $failureGroupHandler = new FallbackGroupHandler([$udpHandler, $fallbackHandler]);

        $logger->pushHandler($failureGroupHandler);

        return $logger;
    }

    /**
     * Interrogates environment to get the correct process index for logging
     *
     * @return string
     */
    public static function getHostProcessIndex(): string
    {
        return implode('-', [
            getenv('LAGOON_PROJECT') ?: self::LAGOON_LOGS_DEFAULT_LAGOON_PROJECT,
            getenv('LAGOON_GIT_SAFE_BRANCH') ?: self::LAGOON_LOGS_DEFAULT_SAFE_BRANCH,
        ]);
    }
}
