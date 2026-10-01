<?php

namespace App\Observers;

use App\Helpers\Qs;
use App\Models\Student;
use App\User;
use App\Models\StudentRecord;
use App\Models\Guardian as GuardianNextGen;
use App\Models\MyClass;
use App\Models\Section;
use App\Mail\StudentApprovalNotification;
use App\Services\MoodleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class StudentObserver
{
    /**
     * Handle the Student "updated" event.
     */
    public function updated(Student $student)
    {
        // Check if status changed to "Approved"
        if ($student->wasChanged('status') && $student->status === 'Approved') {
            $this->syncToMainDatabase($student);
        }
    }

    /**
     * Sync student and guardian data to main database
     */
    private function syncToMainDatabase(Student $student)
    {
        try {
            DB::beginTransaction();

            \Log::info("Starting sync for student: {$student->full_name}");

            // Generate email if needed (don't save to nextgen db, only use for nextschool)
            $studentEmail = $student->email ?? strtolower($student->first_name . '.' . $student->last_name . '@nextgen.school');

            $parentUser = null;

            // 1. Create or update Guardian (Parent) in main database
            if ($student->guardian) {
                $guardian = $student->guardian;
                \Log::info("Processing guardian: {$guardian->full_name}, Email: {$guardian->email}");
                
                // Ensure guardian has email
                if (empty($guardian->email)) {
                    \Log::warning("Guardian {$guardian->full_name} does not have email, skipping parent creation.");
                } else {
                    $parentUser = User::where('email', $guardian->email)->first();

                    if (!$parentUser) {
                        $parentData = [
                            'name' => $guardian->full_name,
                            'username' => $guardian->email,
                            'email' => $guardian->email,
                            'phone' => $guardian->phone ?? null,
                            'phone2' => $guardian->phone2 ?? null,
                            'address' => $guardian->address ?? null,
                            'gender' => $guardian->gender ?? 'M',
                            'user_type' => 'parent',
                            'code' => 'P' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                            'password' => bcrypt('Password@123')
                        ];
                        $parentUser = User::create($parentData);
                        \Log::info("Created parent user: {$parentUser->id} for {$guardian->full_name} with email: {$parentUser->email}");
                    } else {
                        \Log::info("Parent user already exists: {$parentUser->id} with email: {$parentUser->email}");
                    }
                }
            } else {
                \Log::warning("Student {$student->full_name} has no guardian");
            }

            // 2. Create or update Student User in main database
            $studentUser = User::where('email', $studentEmail)->first();

            if (!$studentUser) {
                $userData = [
                    'name' => $student->full_name,
                    'username' => $studentEmail,
                    'email' => $studentEmail,
                    'phone' => $student->phone ?? null,
                    'dob' => $student->date_of_birth ?? $student->dob,
                    'gender' => $student->gender ?? 'M',
                    'address' => $student->address ?? null,
                    'religion_status' => $student->religion_status ?? null,
                    'user_type' => 'Student',
                    'code' => 'S' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                    'password' => bcrypt('Password@123')
                ];
                $studentUser = User::create($userData);
                \Log::info("Created student user: {$studentUser->id} for {$student->full_name}");
            } else {
                // Update if exists
                $studentUser->update([
                    'name' => $student->full_name,
                    'dob' => $student->date_of_birth ?? $student->dob,
                    'phone' => $student->phone ?? $studentUser->phone,
                    'gender' => $student->gender ?? $studentUser->gender,
                    'address' => $student->address ?? $studentUser->address,
                    'religion_status' => $student->religion_status ?? $studentUser->religion_status,
                ]);
                \Log::info("Updated existing student user: {$studentUser->id}");
            }

            // 3. Create StudentRecord in main database
            $parentId = $parentUser ? $parentUser->id : null;

            // Get class and section from session
            $classId = session('approval_class_id');
            $sectionId = session('approval_section_id');

            \Log::info("Session data - ClassId: {$classId}, SectionId: {$sectionId}");

            if (!$classId || !$sectionId) {
                \Log::error("Class ID or Section ID not found in session for student {$student->full_name}");
                throw new \Exception("Class and Section are required for approval");
            }

            $recordData = [
                'my_class_id' => $classId,
                'section_id' => $sectionId,
                'my_parent_id' => $parentId,
                'year_admitted' => date('Y'),
                'session' => Qs::getSetting('current_session') ?? (date('Y') . '/' . (date('Y') + 1)),
                'age' => $student->age,
            ];

            $studentRecord = StudentRecord::updateOrCreate(
                ['user_id' => $studentUser->id],
                $recordData
            );

            \Log::info("Created/Updated student record: {$studentRecord->id} with class_id: {$classId}, section_id: {$sectionId}");

            // Sync student account to Moodle (create or update)
            try {
                \Log::info('Moodle sync start (student approval)', ['user_id' => $studentUser->id]);
                $nameParts = preg_split('/\s+/', trim($studentUser->name), 2);
                $firstname = $nameParts[0] ?? $studentUser->name;
                $lastname = $nameParts[1] ?? $nameParts[0] ?? $studentUser->name;
                $email = $studentUser->email ?: strtolower(str_replace(['/', ' '], '.', $studentUser->username)) . '@nextgene.uk';

                app(MoodleService::class)->createOrUpdateUser([
                    'username' => $studentUser->username,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'email' => $email,
                    'auth' => 'manual',
                ]);
                \Log::info('Moodle sync done (student approval)', ['user_id' => $studentUser->id]);
            } catch (\Throwable $e) {
                \Log::error('Moodle sync failed on student approval', [
                    'user_id' => $studentUser->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Send approval email to parent
            $this->sendApprovalEmail($student, $parentUser, $studentUser, $classId, $sectionId);

            DB::commit();

            \Log::info("Successfully synced student {$student->full_name} to main database");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error syncing student to main database: " . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    /**
     * Send approval email to parent
     */
    private function sendApprovalEmail(Student $student, ?User $parentUser, User $studentUser, $classId, $sectionId)
    {
        // Check if parent exists and has valid email
        if (!$parentUser) {
            \Log::warning("No parent user found for student {$student->full_name}");
            return;
        }

        if (empty($parentUser->email)) {
            \Log::warning("Parent user {$parentUser->id} has no email address");
            return;
        }

        // Validate email format
        if (!filter_var($parentUser->email, FILTER_VALIDATE_EMAIL)) {
            \Log::warning("Parent email is not valid format: {$parentUser->email}");
            return;
        }

        try {
            $myClass = MyClass::find($classId);
            $section = Section::find($sectionId);

            if (!$myClass || !$section) {
                \Log::warning("Class or Section not found for email. ClassId: {$classId}, SectionId: {$sectionId}");
                return;
            }

            \Log::info("Attempting to send approval email to: {$parentUser->email} for student {$student->full_name}");

            Mail::to($parentUser->email)->send(
                new StudentApprovalNotification(
                    $student,
                    $parentUser,
                    $studentUser,
                    $myClass,
                    $section
                )
            );

            \Log::info("✓ Approval email successfully sent to parent: {$parentUser->email}");

        } catch (\Exception $mailException) {
            \Log::error("Error sending approval email to {$parentUser->email}: " . $mailException->getMessage());
            // Log the full exception for debugging
            \Log::error("Email exception details: " . $mailException->getTraceAsString());
        }
    }
}
