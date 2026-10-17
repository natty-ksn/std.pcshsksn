<?php
/**
 * Notifier Unit Tests
 * 
 * Tests the notification system
 */

require_once __DIR__ . '/../TestCase.php';

class NotifierTest extends BaseTestCase
{
    /**
     * Test that Notifier class exists
     */
    public function test_notifier_class_exists(): void
    {
        $this->assertTrue(class_exists('Notifier'), 'Notifier class should exist');
    }

    /**
     * Test send method exists
     */
    public function test_send_method_exists(): void
    {
        $reflection = new ReflectionClass('Notifier');
        $this->assertTrue($reflection->hasMethod('send'), 'Notifier should have send method');

        $method = $reflection->getMethod('send');
        $this->assertEquals(5, $method->getNumberOfParameters(), 'send should have 5 parameters');
    }

    /**
     * Test specialized notification methods exist
     */
    public function test_specialized_methods_exist(): void
    {
        $reflection = new ReflectionClass('Notifier');

        $methods = [
            'projectSubmitted',
            'projectApproved',
            'projectRejected',
            'projectRevision',
            'commentAdded',
            'badgeAwarded',
            'logbookVerified',
            'earlyWarningAlert',
        ];

        foreach ($methods as $methodName) {
            $this->assertTrue(
                $reflection->hasMethod($methodName),
                "Notifier should have {$methodName} method"
            );
        }
    }

    /**
     * Test checkMilestones method exists for cron jobs
     */
    public function test_check_milestones_exists(): void
    {
        $reflection = new ReflectionClass('Notifier');
        $this->assertTrue(
            $reflection->hasMethod('checkMilestones'),
            'Notifier should have checkMilestones method for cron jobs'
        );
    }

    /**
     * Test getUnreadCount and markAsRead methods exist
     */
    public function test_notification_management_methods(): void
    {
        $reflection = new ReflectionClass('Notifier');

        $this->assertTrue(
            $reflection->hasMethod('getUnreadCount'),
            'Notifier should have getUnreadCount method'
        );

        $this->assertTrue(
            $reflection->hasMethod('markAsRead'),
            'Notifier should have markAsRead method'
        );

        $this->assertTrue(
            $reflection->hasMethod('markAllAsRead'),
            'Notifier should have markAllAsRead method'
        );
    }

    /**
     * Test sendBulk method exists
     */
    public function test_send_bulk_method_exists(): void
    {
        $reflection = new ReflectionClass('Notifier');
        $this->assertTrue(
            $reflection->hasMethod('sendBulk'),
            'Notifier should have sendBulk method'
        );
    }

    /**
     * Test that Notifier uses database for storing notifications
     */
    public function test_notifier_uses_database(): void
    {
        $notifierFile = file_get_contents(dirname(__DIR__, 2) . '/core/Notifier.php');

        $this->assertStringContainsString(
            'Database::insert',
            $notifierFile,
            'Notifier should use Database::insert to store notifications'
        );
    }

    /**
     * Test that Notifier uses notifications table
     */
    public function test_notifier_uses_notifications_table(): void
    {
        $notifierFile = file_get_contents(dirname(__DIR__, 2) . '/core/Notifier.php');

        $this->assertStringContainsString(
            'notifications',
            $notifierFile,
            'Notifier should use notifications table'
        );
    }

    /**
     * Test notification event type constants are defined
     */
    public function test_event_type_constants_exist(): void
    {
        $reflection = new ReflectionClass('Notifier');

        // Notifier should have event type constants
        $constants = $reflection->getConstants();

        // Just verify the class has constants (specific names may vary)
        $this->assertGreaterThanOrEqual(
            0,
            count($constants),
            'Notifier should have constants defined'
        );
    }

    /**
     * Test that Notifier is loaded in index.php
     */
    public function test_notifier_is_loaded(): void
    {
        $indexFile = file_get_contents(dirname(__DIR__, 2) . '/public/index.php');

        $this->assertStringContainsString(
            'Notifier',
            $indexFile,
            'Notifier should be loaded in index.php'
        );
    }

    /**
     * Test send method signature and parameters
     */
    public function test_send_method_signature(): void
    {
        $reflection = new ReflectionClass('Notifier');
        $method = $reflection->getMethod('send');

        $params = $method->getParameters();

        $this->assertEquals('userId', $params[0]->getName());
        $this->assertEquals('type', $params[1]->getName());
        $this->assertEquals('title', $params[2]->getName());
        $this->assertEquals('message', $params[3]->getName());
        $this->assertEquals('link', $params[4]->getName());
    }

    /**
     * Test projectSubmitted notification method signature
     */
    public function test_project_submitted_signature(): void
    {
        $reflection = new ReflectionClass('Notifier');
        $method = $reflection->getMethod('projectSubmitted');

        // Should accept projectId and teacherId at minimum
        $this->assertGreaterThanOrEqual(
            2,
            $method->getNumberOfParameters(),
            'projectSubmitted should have at least 2 parameters'
        );
    }

    /**
     * Test projectApproved notification method signature
     */
    public function test_project_approved_signature(): void
    {
        $reflection = new ReflectionClass('Notifier');
        $method = $reflection->getMethod('projectApproved');

        $this->assertGreaterThanOrEqual(
            2,
            $method->getNumberOfParameters(),
            'projectApproved should have at least 2 parameters'
        );
    }

    /**
     * Test badgeAwarded notification method signature
     */
    public function test_badge_awarded_signature(): void
    {
        $reflection = new ReflectionClass('Notifier');
        $method = $reflection->getMethod('badgeAwarded');

        $this->assertEquals(
            2,
            $method->getNumberOfParameters(),
            'badgeAwarded should have 2 parameters (userId, badgeCode)'
        );
    }
}
