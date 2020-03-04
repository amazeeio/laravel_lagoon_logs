# Lagoon Logs for Laravel
Monolog channel and formatter for Laravel logging into Lagoon.

## Basic Usage

Installing this package makes the channel "LagoonLogs" available in your logging config.

It's important to note that this is essentially a wrapper around a Monolog Logger with a specifically set UDP SocketHandler and LogstashFormatter - therefore, it really only makes sense to use this _when_ deployed to a Lagoon instance.

In a vanilla Laravel 6 installation, this is most easily done by setting the environment variable `LOG_CHANNEL` to `LagoonLogs`.

If you need more control over your logging stack, you can simply add `LagoonLogs` in the appropriate places (such as a stack) in your `./config/logging.php` file.
See [the Laravel 6 docs](https://laravel.com/docs/6.x/logging) for more on customizing logging.
