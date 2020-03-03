<?php

namespace amazeeio\LagoonLogs;

use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\SocketHandler;
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
    $udpHandler->setFormatter(new LogstashFormatter(self::getHostProcessIndex(),
      NULL, 'extra', self::DEFAULT_EXTRA_KEY_FOR_FORMATTER, 1));
    $logger->pushHandler($udpHandler);
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
