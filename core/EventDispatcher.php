<?php
namespace Core;

class EventDispatcher
{
    /**
     * Registered event listeners.
     * Structure: ['event_name' => [['callback' => fn, 'once' => bool]]]
     *
     * @var array
     */
    private static array $listeners = [];

    /**
     * Wildcard listeners (event pattern => [[callback, once]]).
     * Keys use `*` as wildcard, e.g. 'portfolio.*'.
     *
     * @var array
     */
    private static array $wildcardListeners = [];

    /**
     * Event log for debugging/auditing.
     * Only populated when APP_DEBUG=true.
     *
     * @var array
     */
    private static array $eventLog = [];

    /**
     * Maximum event log size.
     */
    private const MAX_LOG_SIZE = 200;

    /**
     * Register an event listener.
     *
     * @param string $eventName Event name (supports `*` wildcard, e.g. 'portfolio.*')
     * @param callable $callback
     * @param bool $once If true, listener is removed after first trigger
     */
    public static function listen(string $eventName, callable $callback, bool $once = false): void
    {
        if (str_contains($eventName, '*')) {
            self::$wildcardListeners[$eventName][] = ['callback' => $callback, 'once' => $once];
        } else {
            if (!isset(self::$listeners[$eventName])) {
                self::$listeners[$eventName] = [];
            }
            self::$listeners[$eventName][] = ['callback' => $callback, 'once' => $once];
        }
    }

    /**
     * Register a one-time event listener (removed after first trigger).
     *
     * @param string $eventName
     * @param callable $callback
     */
    public static function once(string $eventName, callable $callback): void
    {
        self::listen($eventName, $callback, once: true);
    }

    /**
     * Fire an event and call all registered listeners.
     *
     * @param string $eventName
     * @param mixed ...$args
     */
    public static function dispatch(string $eventName, ...$args): void
    {
        // Log the event if debug mode
        if (defined('APP_DEBUG') && (Env::get('APP_DEBUG', 'false') === 'true')) {
            self::logEvent($eventName, $args);
        }

        // Direct listeners
        if (isset(self::$listeners[$eventName])) {
            $toRemove = [];
            foreach (self::$listeners[$eventName] as $index => $listener) {
                $listener['callback'](...$args);
                if ($listener['once']) {
                    $toRemove[] = $index;
                }
            }
            // Remove one-time listeners (reverse order to preserve indices)
            foreach (array_reverse($toRemove) as $index) {
                unset(self::$listeners[$eventName][$index]);
            }
        }

        // Wildcard listeners
        foreach (self::$wildcardListeners as $pattern => $listeners) {
            if (self::matchesPattern($pattern, $eventName)) {
                $toRemove = [];
                foreach ($listeners as $index => $listener) {
                    $listener['callback']($eventName, ...$args);
                    if ($listener['once']) {
                        $toRemove[] = $index;
                    }
                }
                foreach (array_reverse($toRemove) as $index) {
                    unset(self::$wildcardListeners[$pattern][$index]);
                }
            }
        }
    }

    /**
     * Check if an event name matches a wildcard pattern.
     * E.g. pattern 'portfolio.*' matches 'portfolio.created'
     */
    private static function matchesPattern(string $pattern, string $eventName): bool
    {
        if ($pattern === $eventName) {
            return true;
        }
        // Convert wildcard pattern to regex
        $regex = '/^' . str_replace(['\\*', '\\'], ['.*', '\\\\'], preg_quote($pattern, '/')) . '$/';
        return (bool) preg_match($regex, $eventName);
    }

    /**
     * Remove a specific listener from an event.
     *
     * @param string $eventName
     * @param callable $callback
     * @return bool True if listener was found and removed
     */
    public static function forgetListener(string $eventName, callable $callback): bool
    {
        if (!isset(self::$listeners[$eventName])) {
            return false;
        }
        foreach (self::$listeners[$eventName] as $index => $listener) {
            if ($listener['callback'] === $callback) {
                unset(self::$listeners[$eventName][$index]);
                return true;
            }
        }
        return false;
    }

    /**
     * Remove all listeners for a given event.
     *
     * @param string $eventName
     */
    public static function clearListeners(string $eventName): void
    {
        unset(self::$listeners[$eventName]);
    }

    /**
     * Check if an event has any listeners.
     */
    public static function hasListeners(string $eventName): bool
    {
        if (isset(self::$listeners[$eventName]) && !empty(self::$listeners[$eventName])) {
            return true;
        }
        foreach (self::$wildcardListeners as $pattern => $listeners) {
            if (self::matchesPattern($pattern, $eventName) && !empty($listeners)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the event log (debug only).
     *
     * @return array
     */
    public static function getEventLog(): array
    {
        return self::$eventLog;
    }

    /**
     * Clear the event log.
     */
    public static function clearEventLog(): void
    {
        self::$eventLog = [];
    }

    /**
     * Log an event dispatch (debug mode only).
     */
    private static function logEvent(string $eventName, array $args): void
    {
        self::$eventLog[] = [
            'event' => $eventName,
            'args' => $args,
            'time' => date('Y-m-d H:i:s'),
            'listener_count' => (self::$listeners[$eventName] ?? []) ? count(self::$listeners[$eventName]) : 0,
        ];

        // Prevent unbounded memory growth
        if (count(self::$eventLog) > self::MAX_LOG_SIZE) {
            array_shift(self::$eventLog);
        }
    }
}
