<?php

namespace Tests\Feature;

use App\Models\SchedulerJob;
use App\Models\Task;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bug Condition Exploration Tests
 * 
 * **CRITICAL**: These tests encode the EXPECTED BEHAVIOR (what should happen after the fix).
 * When run on UNFIXED code, these tests MUST FAIL - failure confirms the bugs exist.
 * 
 * This is an EXPLORATION phase - we want to surface counterexamples that demonstrate the bugs.
 * 
 * **Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5, 1.6**
 */
class BugConditionExplorationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Worker $worker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create test worker
        $this->worker = Worker::create([
            'worker_key' => 'test-worker-' . uniqid(),
            'api_token' => Worker::generateApiToken(),
            'hostname' => 'test-host',
            'status' => 'idle',
            'last_heartbeat_at' => now(),
        ]);
    }

    /**
     * Scenario 1: Worker Task Pull with Missing start_index
     * 
     * **Property 1**: Worker task pull returns complete payload with `start_index` field (default 0 if missing)
     * 
     * **Validates: Requirements 2.1, 2.2**
     * 
     * Expected on unfixed code: 500 error or task with incomplete payload
     */
    public function test_worker_task_pull_with_missing_start_index_returns_complete_payload(): void
    {
        // Create a job
        $job = SchedulerJob::create([
            'user_id' => $this->user->id,
            'name' => 'Test Job with Incomplete Payload',
            'type' => 'data_processing',
            'status' => 'pending', // Job should be pending or running for tasks to be available
            'priority' => 5,
            'total_tasks' => 1,
        ]);

        // Create a task with INCOMPLETE payload (missing start_index)
        $task = Task::create([
            'job_id' => $job->id,
            'task_index' => 0,
            'status' => 'pending',
            'retry_count' => 0,
            'max_retries' => 3,
            'timeout_seconds' => 300,
            'payload' => [
                'type' => 'data_processing',
                'operations' => ['filter', 'transform'],
                // MISSING: start_index, end_index, record_from, record_to, records_count
            ],
        ]);

        // Worker attempts to pull the task
        $response = $this->getJson("/api/tasks/next", [
            'X-Worker-Token' => $this->worker->api_token,
        ]);

        // EXPECTED BEHAVIOR: Should return 200 OK with complete payload
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'worker_key',
            'task' => [
                'id',
                'job_id',
                'task_index',
                'status',
                'payload',
            ],
        ]);

        // CRITICAL: Payload must have start_index field (default 0 if missing)
        $task = $response->json('task');
        $payload = $task['payload'];
        $this->assertIsArray($payload, 'Payload should be an array');
        $this->assertArrayHasKey('start_index', $payload, 'Payload must have start_index field');
        $this->assertIsInt($payload['start_index'], 'start_index must be an integer');
        $this->assertGreaterThanOrEqual(0, $payload['start_index'], 'start_index must be >= 0');
    }

    /**
     * Scenario 1b: Worker Task Pull with Empty Payload
     * 
     * **Property 1**: Worker task pull returns complete payload with required fields
     * 
     * Expected on unfixed code: 500 error or task with incomplete payload
     */
    public function test_worker_task_pull_with_empty_payload_returns_complete_payload(): void
    {
        // Create a job
        $job = SchedulerJob::create([
            'user_id' => $this->user->id,
            'name' => 'Test Job with Empty Payload',
            'type' => 'generic',
            'status' => 'pending', // Job should be pending or running for tasks to be available
            'priority' => 5,
            'total_tasks' => 1,
        ]);

        // Create a task with EMPTY payload
        $task = Task::create([
            'job_id' => $job->id,
            'task_index' => 0,
            'status' => 'pending',
            'retry_count' => 0,
            'max_retries' => 3,
            'timeout_seconds' => 300,
            'payload' => [], // EMPTY PAYLOAD
        ]);

        // Worker attempts to pull the task
        $response = $this->getJson("/api/tasks/next", [
            'X-Worker-Token' => $this->worker->api_token,
        ]);

        // EXPECTED BEHAVIOR: Should return 200 OK with normalized payload
        $response->assertStatus(200);
        
        $task = $response->json('task');
        $payload = $task['payload'];
        $this->assertIsArray($payload, 'Payload should be an array');
        $this->assertArrayHasKey('start_index', $payload, 'Payload must have start_index field');
        $this->assertArrayHasKey('end_index', $payload, 'Payload must have end_index field');
        $this->assertArrayHasKey('records_count', $payload, 'Payload must have records_count field');
    }

    /**
     * Scenario 2: Job Tasks Endpoint with Malformed Payloads
     * 
     * **Property 2**: Job tasks endpoint returns 200 OK with enriched task data even for malformed payloads
     * 
     * **Validates: Requirements 2.3, 2.4**
     * 
     * Expected on unfixed code: 500 error during enrichment
     */
    public function test_job_tasks_endpoint_with_malformed_payloads_returns_200(): void
    {
        // Create a job owned by the user
        $job = SchedulerJob::create([
            'user_id' => $this->user->id,
            'name' => 'Test Job with Malformed Payloads',
            'type' => 'result_processing',
            'status' => 'running',
            'priority' => 5,
            'task_count' => 3,
        ]);

        // Create tasks with various malformed payloads
        Task::create([
            'job_id' => $job->id,
            'task_index' => 0,
            'status' => 'pending',
            'retry_count' => 0,
            'max_retries' => 3,
            'timeout_seconds' => 300,
            'payload' => [], // EMPTY PAYLOAD
        ]);

        Task::create([
            'job_id' => $job->id,
            'task_index' => 1,
            'status' => 'pending',
            'retry_count' => 0,
            'max_retries' => 3,
            'timeout_seconds' => 300,
            'payload' => [
                'type' => 'result_processing',
                // MISSING: start_index, end_index, record_from, record_to, records_count
            ],
        ]);

        Task::create([
            'job_id' => $job->id,
            'task_index' => 2,
            'status' => 'pending',
            'retry_count' => 0,
            'max_retries' => 3,
            'timeout_seconds' => 300,
            'payload' => [
                'type' => 'result_processing',
                'end_index' => 100,
                // MISSING: start_index (but has end_index)
            ],
        ]);

        // Authenticate as the user and request job tasks
        $response = $this->actingAs($this->user)
            ->getJson("/api/jobs/{$job->id}/tasks");

        // EXPECTED BEHAVIOR: Should return 200 OK with enriched task data
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'job_id',
            'job_name',
            'job_type',
            'tasks' => [
                '*' => [
                    'id',
                    'job_id',
                    'task_index',
                    'status',
                    'record_from',
                    'record_to',
                    'records_count',
                    'payload',
                ],
            ],
            'status_counts',
        ]);

        // Verify all tasks are returned with enriched data
        $tasks = $response->json('tasks');
        $this->assertCount(3, $tasks, 'Should return all 3 tasks');

        // Verify each task has enriched fields (even with malformed payloads)
        foreach ($tasks as $task) {
            $this->assertArrayHasKey('record_from', $task, 'Task must have record_from field');
            $this->assertArrayHasKey('record_to', $task, 'Task must have record_to field');
            $this->assertArrayHasKey('records_count', $task, 'Task must have records_count field');
            
            // These fields should be integers or null (not causing errors)
            $this->assertTrue(
                is_int($task['record_from']) || is_null($task['record_from']),
                'record_from must be int or null'
            );
            $this->assertTrue(
                is_int($task['record_to']) || is_null($task['record_to']),
                'record_to must be int or null'
            );
            $this->assertTrue(
                is_int($task['records_count']) || is_null($task['records_count']),
                'records_count must be int or null'
            );
        }
    }

    /**
     * Scenario 3: Undefined Job ID Validation
     * 
     * **Property 3**: Invalid job IDs return 400 Bad Request with validation message
     * 
     * **Validates: Requirements 2.5, 2.6**
     * 
     * Expected on unfixed code: 500 error or unexpected database query
     */
    public function test_undefined_job_id_returns_400_bad_request(): void
    {
        // Authenticate as the user
        $response = $this->actingAs($this->user)
            ->getJson("/api/jobs/undefined");

        // EXPECTED BEHAVIOR: Should return 400 Bad Request with validation error
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'message',
        ]);
        
        $message = $response->json('message');
        $this->assertStringContainsString('valid', strtolower($message), 'Error message should mention validation');
        $this->assertStringContainsString('integer', strtolower($message), 'Error message should mention integer');
    }

    /**
     * Scenario 4: Null Job ID Validation
     * 
     * **Property 3**: Invalid job IDs return 400 Bad Request
     * 
     * Expected on unfixed code: 500 error
     */
    public function test_null_job_id_returns_400_bad_request(): void
    {
        // Authenticate as the user
        $response = $this->actingAs($this->user)
            ->getJson("/api/jobs/null/tasks");

        // EXPECTED BEHAVIOR: Should return 400 Bad Request
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'message',
        ]);
    }

    /**
     * Scenario 5: Non-numeric Job ID Validation
     * 
     * **Property 3**: Invalid job IDs return 400 Bad Request
     * 
     * Expected on unfixed code: May return 500 error or 404
     */
    public function test_non_numeric_job_id_returns_400_bad_request(): void
    {
        // Authenticate as the user
        $response = $this->actingAs($this->user)
            ->getJson("/api/jobs/abc/tasks");

        // EXPECTED BEHAVIOR: Should return 400 Bad Request
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'message',
        ]);
    }

    /**
     * Scenario 6: Negative Job ID Validation
     * 
     * **Property 3**: Invalid job IDs return 400 Bad Request
     * 
     * Expected on unfixed code: May return 404 or 500
     */
    public function test_negative_job_id_returns_400_bad_request(): void
    {
        // Authenticate as the user
        $response = $this->actingAs($this->user)
            ->getJson("/api/jobs/-1");

        // EXPECTED BEHAVIOR: Should return 400 Bad Request
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'message',
        ]);
    }

    /**
     * Scenario 7: Zero Job ID Validation
     * 
     * **Property 3**: Invalid job IDs return 400 Bad Request
     * 
     * Expected on unfixed code: May return 404 or 500
     */
    public function test_zero_job_id_returns_400_bad_request(): void
    {
        // Authenticate as the user
        $response = $this->actingAs($this->user)
            ->getJson("/api/jobs/0");

        // EXPECTED BEHAVIOR: Should return 400 Bad Request
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'message',
        ]);
    }
}
