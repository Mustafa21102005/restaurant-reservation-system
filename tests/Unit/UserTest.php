<?php

namespace Tests\Unit;

use App\Models\{Ban, Timeout, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // ── isBanned() ────────────────────────────────────────────────

    public function test_user_is_not_banned_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->isBanned());
    }

    public function test_user_is_banned_when_ban_record_exists(): void
    {
        $user = User::factory()->create();

        Ban::create([
            'user_id'   => $user->id,
            'reason'    => 'Test ban',
            'banned_by' => $user->id,
        ]);

        $this->assertTrue($user->isBanned());
    }

    public function test_user_is_not_banned_after_ban_is_soft_deleted(): void
    {
        $user = User::factory()->create();

        $ban = Ban::create([
            'user_id'   => $user->id,
            'reason'    => 'Test ban',
            'banned_by' => $user->id,
        ]);

        $ban->delete();

        $this->assertFalse($user->isBanned());
    }

    public function test_user_with_multiple_bans_is_still_banned(): void
    {
        $user = User::factory()->create();

        Ban::create([
            'user_id'   => $user->id,
            'reason'    => 'First ban',
            'banned_by' => $user->id,
        ]);

        Ban::create([
            'user_id'   => $user->id,
            'reason'    => 'Second ban',
            'banned_by' => $user->id,
        ]);

        $this->assertTrue($user->isBanned());
    }

    // ── isTimedOut() ──────────────────────────────────────────────

    public function test_user_is_not_timed_out_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->isTimedOut());
    }

    public function test_user_is_timed_out_when_active_timeout_exists(): void
    {
        $user = User::factory()->create();

        Timeout::create([
            'user_id'    => $user->id,
            'reason'     => 'Test timeout',
            'expires_at' => now()->addHour(),
            'timeout_by' => $user->id,
        ]);

        $this->assertTrue($user->isTimedOut());
    }

    public function test_user_is_not_timed_out_when_timeout_has_expired(): void
    {
        $user = User::factory()->create();

        Timeout::create([
            'user_id'    => $user->id,
            'reason'     => 'Expired timeout',
            'expires_at' => now()->subHour(),
            'timeout_by' => $user->id,
        ]);

        $this->assertFalse($user->isTimedOut());
    }

    public function test_user_is_not_timed_out_after_timeout_is_soft_deleted(): void
    {
        $user = User::factory()->create();

        $timeout = Timeout::create([
            'user_id'    => $user->id,
            'reason'     => 'Test timeout',
            'expires_at' => now()->addHour(),
            'timeout_by' => $user->id,
        ]);

        $timeout->delete();

        $this->assertFalse($user->isTimedOut());
    }

    public function test_user_with_mixed_timeouts_is_timed_out_if_one_is_active(): void
    {
        $user = User::factory()->create();

        // Expired timeout
        Timeout::create([
            'user_id'    => $user->id,
            'reason'     => 'Old timeout',
            'expires_at' => now()->subHour(),
            'timeout_by' => $user->id,
        ]);

        // Active timeout
        Timeout::create([
            'user_id'    => $user->id,
            'reason'     => 'Active timeout',
            'expires_at' => now()->addHour(),
            'timeout_by' => $user->id,
        ]);

        $this->assertTrue($user->isTimedOut());
    }

    // ── Relationships ─────────────────────────────────────────────

    public function test_user_has_many_bans(): void
    {
        $user = User::factory()->create();

        Ban::create(['user_id' => $user->id, 'reason' => 'Ban 1', 'banned_by' => $user->id]);
        Ban::create(['user_id' => $user->id, 'reason' => 'Ban 2', 'banned_by' => $user->id]);

        $this->assertCount(2, $user->bans);
    }

    public function test_user_has_many_timeouts(): void
    {
        $user = User::factory()->create();

        Timeout::create(['user_id' => $user->id, 'reason' => 'T1', 'expires_at' => now()->addHour(), 'timeout_by' => $user->id]);
        Timeout::create(['user_id' => $user->id, 'reason' => 'T2', 'expires_at' => now()->addHours(2), 'timeout_by' => $user->id]);

        $this->assertCount(2, $user->timeouts);
    }
}
