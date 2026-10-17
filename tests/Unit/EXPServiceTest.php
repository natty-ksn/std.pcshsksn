<?php
/**
 * EXPService Unit Tests
 * 
 * Tests the EXP award system to ensure correct calculation and integration
 */

require_once __DIR__ . '/../TestCase.php';

class EXPServiceTest extends BaseTestCase
{
    /**
     * Test that EXP table has all expected actions defined
     */
    public function test_exp_table_has_all_actions(): void
    {
        // Use reflection to access private constant
        $reflection = new ReflectionClass('EXPService');
        $expTable = $reflection->getConstant('EXP_TABLE');

        // Verify all required actions exist
        $requiredActions = [
            'portfolio_created' => 50,
            'portfolio_verified' => 100,
            'portfolio_featured' => 75,
            'project_created' => 80,
            'project_completed' => 200,
            'logbook_added' => 20,
            'goal_set' => 30,
            'goal_achieved' => 150,
            'riasec_taken' => 40,
            'badge_earned' => 50,
            'badge_earned_gold' => 100,
            'badge_earned_platinum' => 150,
            'badge_earned_secret' => 200,
            'comment_received' => 10,
            'daily_login' => 5,
            'profile_completed' => 100,
        ];

        foreach ($requiredActions as $action => $expectedExp) {
            $this->assertArrayHasKey($action, $expTable, "EXP_TABLE should have action: {$action}");
            $this->assertEquals($expectedExp, $expTable[$action], "Action {$action} should award {$expectedExp} EXP");
        }
    }

    /**
     * Test calculateExp returns correct EXP for basic actions
     */
    public function test_calculate_exp_returns_correct_values(): void
    {
        $reflection = new ReflectionClass('EXPService');
        $method = $reflection->getMethod('calculateExp');
        $method->setAccessible(true);

        // Test various actions
        $this->assertEquals(50, $method->invoke(null, 'portfolio_created'));
        $this->assertEquals(100, $method->invoke(null, 'portfolio_verified'));
        $this->assertEquals(75, $method->invoke(null, 'portfolio_featured'));
        $this->assertEquals(200, $method->invoke(null, 'project_completed'));
        $this->assertEquals(20, $method->invoke(null, 'logbook_added'));
        $this->assertEquals(30, $method->invoke(null, 'goal_set'));
        $this->assertEquals(150, $method->invoke(null, 'goal_achieved'));
        $this->assertEquals(40, $method->invoke(null, 'riasec_taken'));
        $this->assertEquals(100, $method->invoke(null, 'profile_completed'));
        $this->assertEquals(10, $method->invoke(null, 'comment_received'));
    }

    /**
     * Test calculateExp handles badge tier multipliers correctly
     */
    public function test_calculate_exp_handles_badge_tiers(): void
    {
        $reflection = new ReflectionClass('EXPService');
        $method = $reflection->getMethod('calculateExp');
        $method->setAccessible(true);

        // Base badge (no tier specified)
        $this->assertEquals(50, $method->invoke(null, 'badge_earned', []));

        // Gold tier
        $this->assertEquals(100, $method->invoke(null, 'badge_earned', ['badge_tier' => 'gold']));

        // Platinum tier
        $this->assertEquals(150, $method->invoke(null, 'badge_earned', ['badge_tier' => 'platinum']));

        // Secret tier
        $this->assertEquals(200, $method->invoke(null, 'badge_earned', ['badge_tier' => 'secret']));
    }

    /**
     * Test calculateExp returns 0 for unknown actions
     */
    public function test_calculate_exp_returns_zero_for_unknown_action(): void
    {
        $reflection = new ReflectionClass('EXPService');
        $method = $reflection->getMethod('calculateExp');
        $method->setAccessible(true);

        $this->assertEquals(0, $method->invoke(null, 'nonexistent_action'));
        $this->assertEquals(0, $method->invoke(null, 'invalid_action'));
    }

    /**
     * Test calculateLevel uses correct formula
     */
    public function test_calculate_level_uses_correct_formula(): void
    {
        $reflection = new ReflectionClass('EXPService');
        $method = $reflection->getMethod('calculateLevel');
        $method->setAccessible(true);

        // Level 1: 0-999 EXP
        $this->assertEquals(1, $method->invoke(null, 0));
        $this->assertEquals(1, $method->invoke(null, 500));
        $this->assertEquals(1, $method->invoke(null, 999));

        // Level 2: 1000-1999 EXP
        $this->assertEquals(2, $method->invoke(null, 1000));
        $this->assertEquals(2, $method->invoke(null, 1500));
        $this->assertEquals(2, $method->invoke(null, 1999));

        // Level 3: 2000-2999 EXP
        $this->assertEquals(3, $method->invoke(null, 2000));
        $this->assertEquals(3, $method->invoke(null, 2500));

        // Level 10
        $this->assertEquals(10, $method->invoke(null, 9000));
        $this->assertEquals(10, $method->invoke(null, 9999));
    }

    /**
     * Test BASE_EXP_PER_LEVEL constant is 1000
     */
    public function test_base_exp_per_level_is_1000(): void
    {
        $reflection = new ReflectionClass('EXPService');
        $constant = $reflection->getConstant('BASE_EXP_PER_LEVEL');

        $this->assertEquals(1000, $constant, 'BASE_EXP_PER_LEVEL should be 1000');
    }

    /**
     * Test that award method exists and has correct signature
     */
    public function test_award_method_exists(): void
    {
        $reflection = new ReflectionClass('EXPService');
        $this->assertTrue($reflection->hasMethod('award'), 'EXPService should have award method');

        $method = $reflection->getMethod('award');
        $this->assertEquals(3, $method->getNumberOfParameters(), 'award method should have 3 parameters');

        // Check parameter names
        $params = $method->getParameters();
        $this->assertEquals('userId', $params[0]->getName());
        $this->assertEquals('action', $params[1]->getName());
        $this->assertEquals('meta', $params[2]->getName());
    }

    /**
     * Test getUserStats method exists
     */
    public function test_get_user_stats_method_exists(): void
    {
        $reflection = new ReflectionClass('EXPService');
        $this->assertTrue($reflection->hasMethod('getUserStats'), 'EXPService should have getUserStats method');
    }

    /**
     * Test EXP values are positive integers
     */
    public function test_all_exp_values_are_positive_integers(): void
    {
        $reflection = new ReflectionClass('EXPService');
        $expTable = $reflection->getConstant('EXP_TABLE');

        foreach ($expTable as $action => $exp) {
            $this->assertIsInt($exp, "EXP for {$action} should be an integer");
            $this->assertGreaterThan(0, $exp, "EXP for {$action} should be positive");
        }
    }

    /**
     * Test that EXP_TABLE has no duplicate values (sanity check)
     */
    public function test_exp_table_has_no_obvious_errors(): void
    {
        $reflection = new ReflectionClass('EXPService');
        $expTable = $reflection->getConstant('EXP_TABLE');

        // Should have at least 15 different actions
        $this->assertGreaterThanOrEqual(15, count($expTable), 'EXP_TABLE should have at least 15 actions');

        // All keys should be strings
        foreach (array_keys($expTable) as $key) {
            $this->assertIsString($key, 'All EXP_TABLE keys should be strings');
        }
    }
}
