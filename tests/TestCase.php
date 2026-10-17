<?php
/**
 * Base TestCase
 * 
 * Provides common setup and utilities for all tests
 */

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class BaseTestCase extends PHPUnitTestCase
{
    /**
     * Get a mock database connection for testing
     */
    protected function getTestDatabase()
    {
        // For now, we'll test with mock objects since the Database class is a static singleton
        return null;
    }

    /**
     * Assert that an array has specific keys
     */
    protected function assertArrayHasKeys(array $array, array $keys): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array, "Array should have key: {$key}");
        }
    }

    /**
     * Skip test if database is not available
     */
    protected function skipIfNoDatabase(): void
    {
        $this->markTestSkipped('Database integration tests require a test database setup.');
    }
}
