<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Event;
use App\Events\StudentCreated;

class BulkImportStudentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_bulk_import_students()
    {
        $response = $this->postJson(route('faculty.students.bulk'));
        $response->assertStatus(401);
    }

    public function test_faculty_member_can_bulk_import_students()
    {
        Event::fake([StudentCreated::class]);

        // Create faculty user
        $user = User::factory()->create([
            'first_name' => 'Prof',
            'last_name' => 'Smith',
            'role' => 'faculty',
            'status' => 'active',
        ]);
        Faculty::create([
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        // Build Excel spreadsheet in memory/temp file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = ['student_id', 'first_name', 'last_name', 'email', 'middle_name', 'phone_number'];
        $sheet->fromArray([
            $headers,
            ['2024-009', 'Alice', 'Smith', 'asmith@hms.edu', 'J', '09170001111'],
            ['2024-010', 'Bob', 'Jones', 'bjones', '', ''], // raw email should become bjones@hms.edu
            ['', '', '', '', '', ''] // blank row, should be ignored
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_import_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile(
            $tempPath,
            'students.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($user)
            ->postJson(route('faculty.students.bulk'), [
                'excel_file' => $uploadedFile,
            ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'created' => 2,
            'failed' => 0,
        ]);

        // Clean up temp file
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        // Verify DB records
        $this->assertDatabaseHas('users', [
            'email' => 'asmith@hms.edu',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'middle_name' => 'J',
            'role' => 'student',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'bjones@hms.edu', // converted by controller
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'role' => 'student',
        ]);

        $this->assertDatabaseHas('students', [
            'student_id' => '2024-009',
        ]);

        $this->assertDatabaseHas('students', [
            'student_id' => '2024-010',
        ]);

        Event::assertDispatched(StudentCreated::class, 2);
    }
}
