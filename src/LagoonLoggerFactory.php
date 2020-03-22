<?php

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
class LagoonLoggerFactory {

  const LAGOON_LOGS_MONOLOG_CHANNEL_NAME = 'LagoonLogs';

  const DEFAULT_HOSTNAME = "application-logs.lagoon.svc";

  const DEFAULT_HOSTPORT = "5140";

  const DEFAULT_EXTRA_KEY_FOR_FORMATTER = "ctxt_";

  const LAGOON_LOGS_DEFAULT_SAFE_BRANCH = 'safe_branch_unset';

  const LAGOON_LOGS_DEFAULT_LAGOON_PROJECT = 'project_unset';

  const LAGOON_LOGS_DEFAULT_CHUNK_SIZE_BYTES = 15000;

  const LAGOON_LOGS_FALLBACK_LINE_FORMAT = "LAGOON LOGS FALLBACK: [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";

  /**
   * Create a custom Monolog instance.
   *
   * @param  array $config
   *
   * @return \Monolog\Logger
   */
  public function __invoke() {

    $logger = new Logger('LagoonLogs');
    $connectionString = sprintf("udp://%s:%s", self::DEFAULT_HOSTNAME,
      self::DEFAULT_HOSTPORT);
    $udpHandler = new SocketHandler($connectionString);
    $udpHandler->setChunkSize(self::LAGOON_LOGS_DEFAULT_CHUNK_SIZE_BYTES);
    $udpHandler->setFormatter(new LogstashFormatter(self::getHostProcessIndex(),
      NULL, 'extra', self::DEFAULT_EXTRA_KEY_FOR_FORMATTER, 1));

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
  public static function getHostProcessIndex() {
    return implode('-', [
      getenv('LAGOON_PROJECT') ?: self::LAGOON_LOGS_DEFAULT_LAGOON_PROJECT,
      getenv('LAGOON_GIT_SAFE_BRANCH') ?: self::LAGOON_LOGS_DEFAULT_SAFE_BRANCH,
    ]);
  }

}
