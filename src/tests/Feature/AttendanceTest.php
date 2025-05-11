<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Time;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test */
    public function Attendance_Work_Time()
    {
        $now = Carbon::now();

        $formattedDate = $now->format('Y年n月j日');
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $weekday = $weekdays[$now->dayOfWeek];

        $time = $now->format('H:i');

        $users = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($users->first())
            ->get('/attendance')
            ->assertStatus(200)
            ->assertSee($formattedDate . '（' . $weekday . '）')
            ->assertSee($time);
    }

    /** @test */
    public function Attendance_Workout_Status()
    {
        $users = User::factory()->create(['role' => 1,]);
        $this->actingAs($users->first());

        $response = $this->get('/attendance');
        $response->assertSee('勤務外');
    }

    /** @test */
    public function Attendance_Working_Status()
    {
        $users = User::factory()->create(['role' => 1,]);
        Time::factory()->create([
            'user_id' => $users->id,
            'date' => now()->toDateString(),
            'status' => 'working',
        ]);

        $this->actingAs($users->first());
        $response = $this->get('/attendance');
        $response->assertSee('勤務中');
    }

    /** @test */
    public function Attendance_Breaking_Status()
    {
        $users = User::factory()->create(['role' => 1,]);
        Time::factory()->create([
            'user_id' => $users->id,
            'date' => now()->toDateString(),
            'status' => 'breaking',
        ]);

        $this->actingAs($users->first());
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    /** @test */
    public function Attendance_Workout_Completed_Status()
    {
        $users = User::factory()->create(['role' => 1,]);
        Time::factory()->create([
            'user_id' => $users->id,
            'date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $this->actingAs($users->first());
        $response = $this->get('/attendance');
        $response->assertSee('退勤済み');
    }

    /** @test */
    public function Attendance_Work_Start()
    {
        $users = User::factory()->create(['role' => 1,]);
        $this->actingAs($users->first());

        $response = $this->post('/attendance/start');
        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('times', [
            'user_id' => $users->id,
            'status' => 'working',
        ]);
    }

    /** @test */
    public function Attendance_Work_End()
    {
        $users = User::factory()->create(['role' => 1,]);
        $time = Time::factory()->create([
            'user_id' => $users->id,
            'date' => now()->toDateString(),
            'status' => 'working',
        ]);

        $this->actingAs($users->first());
        $this->post('/attendance/end');

        $this->assertDatabaseHas('times', [
            'id' => $time->id,
            'status' => 'completed',
        ]);
    }

}