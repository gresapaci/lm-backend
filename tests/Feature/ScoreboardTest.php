<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\CourseModule;
use App\Models\CourseSection;
use App\Models\Scoreboard;
use App\Models\User;
use App\Models\UserCourseProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ScoreboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::create([
            'first_name' => 'Test',
            'last_name' => 'Student',
            'gender' => 'male',
            'email' => uniqid('student_') . '@example.com',
            'password' => bcrypt('password'),
            'acc_status' => true,
        ]);
    }

    private function authHeaders(User $user): array
    {
        $token = JWTAuth::fromUser($user);
        return ['Authorization' => "Bearer {$token}"];
    }

    /**
     * Creates a course with a single module and a single section (one material),
     * and enrolls the given user in it with pending_sections/pending_modules = 1.
     * Returns the section id.
     */
    private function enrollUserInSingleSectionCourse(User $user): int
    {
        $course = Course::create([
            'title' => 'Course ' . uniqid(),
            'description' => 'desc',
        ]);

        $module = CourseModule::create([
            'course_id' => $course->id,
            'title' => 'Module 1',
        ]);

        $section = CourseSection::create([
            'module_id' => $module->id,
            'title' => 'Section 1',
        ]);

        CourseMaterial::create([
            'course_section_id' => $section->id,
            'title' => 'Material 1',
            'type' => 'text',
            'content' => 'Some content',
            'sort_order' => 1,
        ]);

        UserCourseProgress::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'completed_sections' => 0,
            'completed_section_ids' => [],
            'pending_sections' => 1,
            'completed_modules' => 0,
            'completed_module_ids' => [],
            'pending_modules' => 1,
            'awarded' => false,
        ]);

        return $section->id;
    }

    public function test_no_point_awarded_before_course_completion(): void
    {
        $user = $this->makeUser();
        $this->enrollUserInSingleSectionCourse($user);

        $this->assertNull(Scoreboard::where('user_id', $user->id)->first());
    }

    public function test_completing_course_awards_exactly_one_point(): void
    {
        $user = $this->makeUser();
        $sectionId = $this->enrollUserInSingleSectionCourse($user);

        $response = $this->postJson(
            "/api/materials/user/section/{$sectionId}/complete",
            [],
            $this->authHeaders($user)
        );

        $response->assertStatus(200)->assertJson(['success' => true]);

        $scoreboard = Scoreboard::where('user_id', $user->id)->first();
        $this->assertNotNull($scoreboard);
        $this->assertSame(1, $scoreboard->score);
    }

    public function test_repeating_the_same_completion_request_does_not_award_again(): void
    {
        $user = $this->makeUser();
        $sectionId = $this->enrollUserInSingleSectionCourse($user);

        $first = $this->postJson(
            "/api/materials/user/section/{$sectionId}/complete",
            [],
            $this->authHeaders($user)
        );
        $first->assertStatus(200);

        // Repeat the exact same request (simulates a duplicate/retried request).
        $second = $this->postJson(
            "/api/materials/user/section/{$sectionId}/complete",
            [],
            $this->authHeaders($user)
        );
        $second->assertStatus(409);

        $scoreboard = Scoreboard::where('user_id', $user->id)->first();
        $this->assertSame(1, $scoreboard->score);
    }

    public function test_revisiting_completed_course_does_not_award_again(): void
    {
        $user = $this->makeUser();
        $sectionId = $this->enrollUserInSingleSectionCourse($user);

        $this->postJson(
            "/api/materials/user/section/{$sectionId}/complete",
            [],
            $this->authHeaders($user)
        )->assertStatus(200);

        // "Revisit" the completed section's details (read-only, must not re-award).
        $this->getJson(
            "/api/materials/user/section/{$sectionId}",
            $this->authHeaders($user)
        )->assertStatus(200)->assertJson(['course_completed' => true]);

        $scoreboard = Scoreboard::where('user_id', $user->id)->first();
        $this->assertSame(1, $scoreboard->score);
    }

    public function test_completing_a_second_different_course_awards_another_point(): void
    {
        $user = $this->makeUser();
        $sectionOne = $this->enrollUserInSingleSectionCourse($user);
        $sectionTwo = $this->enrollUserInSingleSectionCourse($user);

        $this->postJson(
            "/api/materials/user/section/{$sectionOne}/complete",
            [],
            $this->authHeaders($user)
        )->assertStatus(200);

        $this->postJson(
            "/api/materials/user/section/{$sectionTwo}/complete",
            [],
            $this->authHeaders($user)
        )->assertStatus(200);

        $scoreboard = Scoreboard::where('user_id', $user->id)->first();
        $this->assertSame(2, $scoreboard->score);
    }

    public function test_scoreboard_endpoint_returns_correct_points_and_ranking(): void
    {
        $userA = $this->makeUser();
        $userB = $this->makeUser();

        $sectionA = $this->enrollUserInSingleSectionCourse($userA);
        $sectionB1 = $this->enrollUserInSingleSectionCourse($userB);
        $sectionB2 = $this->enrollUserInSingleSectionCourse($userB);

        // userA completes 1 course (1 point), userB completes 2 courses (2 points).
        $this->postJson("/api/materials/user/section/{$sectionA}/complete", [], $this->authHeaders($userA))
            ->assertStatus(200);
        $this->postJson("/api/materials/user/section/{$sectionB1}/complete", [], $this->authHeaders($userB))
            ->assertStatus(200);
        $this->postJson("/api/materials/user/section/{$sectionB2}/complete", [], $this->authHeaders($userB))
            ->assertStatus(200);

        $response = $this->getJson('/api/scoreboard', $this->authHeaders($userA));
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data);

        // Highest score first.
        $this->assertSame($userB->id, $data[0]['user_id']);
        $this->assertSame(2, $data[0]['score']);
        $this->assertSame($userA->id, $data[1]['user_id']);
        $this->assertSame(1, $data[1]['score']);
    }
}
