

<?php


Auth::routes();
Route::post('/sso/issue-token', 'Auth\\SsoLoginController@issueToken')->middleware('throttle:20,1')->name('sso.issue-token');
Route::get('/sso/login', 'Auth\\SsoLoginController@login')->name('sso.login');

// Routes for students: TimeTable view/print
Route::group(['namespace' => 'SupportTeam', 'prefix' => 'student', 'middleware' => 'auth'], function(){
    Route::get('timetables/records/show/{ttr}', 'TimeTableController@show_record')->name('student.ttr.show');
    Route::get('timetables/records/print/{ttr}', 'TimeTableController@print_record')->name('student.ttr.print');
});

// Student marks routes
Route::group(['namespace' => 'SupportTeam', 'prefix' => 'student', 'middleware' => 'auth'], function(){
    Route::group(['prefix' => 'marks'], function(){
        Route::get('select_year/{id}', 'MarkController@year_selector')->name('student.marks.year_selector');
        Route::post('select_year', 'MarkController@year_selected')->name('student.marks.year_select');
        Route::post('select_year/{id}', 'MarkController@year_selected');
        Route::get('show/{id}/{year}', 'MarkController@show')->name('student.marks.show');
        Route::get('print/{id}/{exam_id}/{year}', 'MarkController@print_view')->name('student.marks.print');
    });
});
//Route::get('/test', 'TestController@index')->name('test');
Route::get('/privacy-policy', 'HomeController@privacy_policy')->name('privacy_policy');
Route::get('/terms-of-use', 'HomeController@terms_of_use')->name('terms_of_use');
// امسح الـ route القديم وأضف هذا:
Route::get('/bigbluebutton/join/{ttrId}/{ttId?}/{day?}', 'BigBlueButtonController@joinMeeting')
    ->name('bbb.join')
    ->middleware('auth');




Route::group(['middleware' => 'auth'], function () {

    Route::get('/', 'HomeController@dashboard')->name('home');
    Route::get('/dashboard', 'HomeController@dashboard')->name('dashboard');
    Route::get('/dashboard/guide/download', 'HomeController@downloadGuide')->name('dashboard.guide.download');



// ========== صفحات المعلم الذكي ==========
Route::get('/tutor', 'TutorController@index')->name('tutor.index');
Route::get('/tutor/upload', 'TutorController@upload')->name('tutor.upload');

// ========== إدارة الملفات ==========
Route::post('/tutor/files', 'FileController@store')->name('tutor.files.store');
Route::get('/tutor/files/{curriculumFile}/download', 'FileController@download')->name('tutor.files.download');
Route::delete('/tutor/files/{curriculumFile}', 'FileController@destroy')->name('tutor.files.destroy');

// ========== API للمحادثة ==========
Route::post('/tutor/chat', 'ChatController@chat')->name('tutor.chat');
Route::post('/tutor/chat/stream', 'ChatController@stream')->name('tutor.chat.stream');
Route::post('/tutor/chat/clear', 'ChatController@clear')->name('tutor.chat.clear');

// ========== Standalone Smart Tutor ==========
Route::group(['prefix' => 'smart-tutor'], function () {
    Route::get('/', 'TutorLearningController@dashboard')->name('smart_tutor.dashboard');
    Route::get('/app', 'TutorLearningController@app')->name('smart_tutor.app');
    Route::get('/progress/{subject}', 'TutorLearningController@getProgress')->name('smart_tutor.progress.get');
    Route::get('/file-session/{file}', 'TutorLearningController@getFileSession')->name('smart_tutor.file_session.get');
    Route::get('/lesson-session/{lesson}', 'TutorLearningController@getLessonSession')->name('smart_tutor.lesson_session.get');
    Route::post('/progress', 'TutorLearningController@updateProgress')->name('smart_tutor.progress');
    Route::post('/quiz-result', 'TutorLearningController@storeQuizResult')->name('smart_tutor.quiz_result');
    Route::post('/file-progress/status', 'TutorLearningController@updateFileStatus')->name('smart_tutor.file_progress.status');
    Route::post('/lesson-progress/status', 'TutorLearningController@updateLessonStatus')->name('smart_tutor.lesson_progress.status');
});

// ========== API للملفات (لـ AJAX) ==========
Route::get('/tutor/api/files', 'FileController@apiIndex')->name('tutor.api.files');


    // Get students of a parent (for AJAX) - from student_records where my_parent_id
    Route::get('/api/students-of-parent/{parentId}', function($parentId) {
        $studentRecords = \App\Models\StudentRecord::where('my_parent_id', $parentId)->get();
        $students = [];
        foreach ($studentRecords as $record) {
            $user = \App\User::find($record->user_id);
            if ($user) {
                $students[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'user_id' => $user->id,
                    'adm_no' => $record->adm_no
                ];
            }
        }
        return response()->json($students);
    })->name('api.students-of-parent');

    Route::group(['prefix' => 'my_account'], function() {
        Route::get('/', 'MyAccountController@edit_profile')->name('my_account');
        Route::put('/', 'MyAccountController@update_profile')->name('my_account.update');
        Route::put('/change_password', 'MyAccountController@change_pass')->name('my_account.change_pass');
    });

    /*************** Clubs *****************/
    Route::group(['prefix' => 'clubs'], function(){
        // Admin Only - يجب تكون أولاً عشان الـ route order
        Route::group(['middleware' => 'teamSA'], function() {
            Route::get('create', 'ClubController@create')->name('clubs.create');
            Route::post('/', 'ClubController@store')->name('clubs.store');
            Route::get('{club}/edit', 'ClubController@edit')->name('clubs.edit');
            Route::put('{club}', 'ClubController@update')->name('clubs.update');
            Route::delete('{club}', 'ClubController@destroy')->name('clubs.destroy');
            Route::get('{club}/members', 'ClubController@members')->name('clubs.members');
            Route::delete('{club}/members/{member}', 'ClubController@removeMember')->name('clubs.removeMember');
            Route::post('{club}/schedule/add', 'ClubController@addSchedule')->name('clubs.schedule.add');
            Route::delete('{club}/schedule/{schedule}', 'ClubController@deleteSchedule')->name('clubs.schedule.delete');
        });

        // Public routes
        Route::get('/', 'ClubController@index')->name('clubs.index');
        Route::get('{club}', 'ClubController@show')->name('clubs.show');
        Route::post('{club}/join', 'ClubController@join')->name('clubs.join');
        Route::post('{club}/leave', 'ClubController@leave')->name('clubs.leave');

        // Chat & Gallery - للأعضاء
        Route::post('{club}/chat/send', 'ClubController@sendChat')->name('clubs.chat.send');
        Route::delete('{club}/chat/{chat}', 'ClubController@deleteChat')->name('clubs.chat.delete');
        Route::get('{club}/messages-api', 'ClubController@getMessages')->name('clubs.messages.api');
        Route::post('{club}/gallery/upload', 'ClubController@uploadGallery')->name('clubs.gallery.upload');
        Route::delete('{club}/gallery/{gallery}', 'ClubController@deleteGallery')->name('clubs.gallery.delete');
        
        // Join BBB Meeting from Club Schedule
        Route::get('{club}/schedule/{schedule}/join-meeting', 'ClubController@joinClubMeeting')->name('clubs.schedule.join_meeting');
    });


    /*************** Support Team *****************/
    Route::group(['namespace' => 'SupportTeam', 'middleware' => 'teamSAT'], function(){

        /*************** Students *****************/
        Route::group(['prefix' => 'students'], function(){
            Route::get('reset_pass/{st_id}', 'StudentRecordController@reset_pass')->name('st.reset_pass');
            Route::get('graduated', 'StudentRecordController@graduated')->name('students.graduated');
            Route::put('not_graduated/{id}', 'StudentRecordController@not_graduated')->name('st.not_graduated');
            Route::get('list/{class_id}', 'StudentRecordController@listByClass')->name('students.list');
            Route::get('{id}/show', 'StudentRecordController@show')->name('students.show');
            Route::get('{id}/edit', 'StudentRecordController@edit')->name('students.edit');
            Route::put('{id}', 'StudentRecordController@update')->name('students.update');
            Route::delete('{id}', 'StudentRecordController@destroy')->name('students.destroy');

            /* Promotions */
            Route::post('promote_selector', 'PromotionController@selector')->name('students.promote_selector');
            Route::get('promotion/manage', 'PromotionController@manage')->name('students.promotion_manage');
            Route::delete('promotion/reset/{pid}', 'PromotionController@reset')->name('students.promotion_reset');
            Route::delete('promotion/reset_all', 'PromotionController@reset_all')->name('students.promotion_reset_all');
            Route::get('promotion/{fc?}/{fs?}/{tc?}/{ts?}', 'PromotionController@promotion')->name('students.promotion');
            Route::post('promote/{fc}/{fs}/{tc}/{ts}', 'PromotionController@promote')->name('students.promote');

        });

        /*************** Users *****************/
        Route::group(['prefix' => 'users'], function(){
            Route::get('reset_pass/{id}', 'UserController@reset_pass')->name('users.reset_pass');
        });

        /*************** Advisory Management *****************/
        Route::group(['prefix' => 'advisory'], function(){
            Route::get('/', 'AdvisoryManagementController@index')->name('advisory.management.index');
            Route::get('create', 'AdvisoryManagementController@create')->name('advisory.management.create');
            Route::post('/', 'AdvisoryManagementController@store')->name('advisory.management.store');
            Route::get('{conversationId}', 'AdvisoryManagementController@show')->name('advisory.management.show');
            Route::post('{conversationId}/assign', 'AdvisoryManagementController@assign')->name('advisory.management.assign');
            Route::post('{conversationId}/assign-by-admin', 'AdvisoryManagementController@assignByAdmin')->name('advisory.management.assignByAdmin');
            Route::post('{conversationId}/message', 'AdvisoryManagementController@sendMessage')->name('advisory.management.sendMessage');
            Route::post('{conversationId}/note', 'AdvisoryManagementController@addNote')->name('advisory.management.addNote');
            Route::put('note/{note}', 'AdvisoryManagementController@updateNote')->name('advisory.management.updateNote');
            Route::delete('note/{note}', 'AdvisoryManagementController@deleteNote')->name('advisory.management.deleteNote');
            Route::post('{conversationId}/status', 'AdvisoryManagementController@updateStatus')->name('advisory.management.updateStatus');
            Route::get('{conversationId}/grades', 'AdvisoryManagementController@studentGrades')->name('advisory.management.grades');
            Route::post('{conversationId}/snapshot-grades', 'AdvisoryManagementController@snapshotGrades')->name('advisory.management.snapshotGrades');
            Route::post('{conversationId}/close', 'AdvisoryManagementController@close')->name('advisory.management.close');
            Route::get('{conversationId}/messages-api', 'AdvisoryManagementController@getMessages')->name('advisory.management.getMessages');
        });

        /*************** TimeTables *****************/
        Route::group(['prefix' => 'timetables'], function(){
            Route::get('/', 'TimeTableController@index')->name('tt.index');

            Route::group(['middleware' => 'teamSA'], function() {
                Route::post('/', 'TimeTableController@store')->name('tt.store');
                Route::put('/{tt}', 'TimeTableController@update')->name('tt.update');
                Route::delete('/{tt}', 'TimeTableController@delete')->name('tt.delete');
            });

            /*************** TimeTable Records *****************/
            Route::group(['prefix' => 'records'], function(){

                Route::group(['middleware' => 'teamSA'], function(){
                    Route::get('manage/{ttr}', 'TimeTableController@manage')->name('ttr.manage');
                    Route::post('/', 'TimeTableController@store_record')->name('ttr.store');
                    Route::get('edit/{ttr}', 'TimeTableController@edit_record')->name('ttr.edit');
                    Route::put('/{ttr}', 'TimeTableController@update_record')->name('ttr.update');
                });

                Route::get('show/{ttr}', 'TimeTableController@show_record')->name('ttr.show');
                Route::get('print/{ttr}', 'TimeTableController@print_record')->name('ttr.print');
                Route::delete('/{ttr}', 'TimeTableController@delete_record')->name('ttr.destroy');

            });

            /*************** Time Slots *****************/
            Route::group(['prefix' => 'time_slots', 'middleware' => 'teamSA'], function(){
                Route::post('/', 'TimeTableController@store_time_slot')->name('ts.store');
                Route::post('/use/{ttr}', 'TimeTableController@use_time_slot')->name('ts.use');
                Route::get('edit/{ts}', 'TimeTableController@edit_time_slot')->name('ts.edit');
                Route::delete('/{ts}', 'TimeTableController@delete_time_slot')->name('ts.destroy');
                Route::put('/{ts}', 'TimeTableController@update_time_slot')->name('ts.update');
            });

            /*************** Attendance (BBB) *****************/
            Route::group(['prefix' => 'attendance'], function(){
                Route::get('upload', 'AttendanceController@create')->name('attendance.create');
                Route::post('upload', 'AttendanceController@store')->name('attendance.store');
                Route::get('/', 'AttendanceController@index')->name('attendance.index');
                Route::get('statistics', 'AttendanceController@statistics')->name('attendance.statistics');
                Route::get('show/{id}', 'AttendanceController@show')->name('attendance.show');
                Route::delete('{id}', 'AttendanceController@destroy')->name('attendance.destroy');
            });

        });

        /*************** Payments *****************/
        Route::group(['prefix' => 'payments'], function(){

            Route::get('manage/{class_id?}', 'PaymentController@manage')->name('payments.manage');
            Route::get('invoice/{id}/{year?}', 'PaymentController@invoice')->name('payments.invoice');
            Route::get('receipts/{id}', 'PaymentController@receipts')->name('payments.receipts');
            Route::get('pdf_receipts/{id}', 'PaymentController@pdf_receipts')->name('payments.pdf_receipts');
            Route::post('select_year', 'PaymentController@select_year')->name('payments.select_year');
            Route::post('select_class', 'PaymentController@select_class')->name('payments.select_class');
            Route::delete('reset_record/{id}', 'PaymentController@reset_record')->name('payments.reset_record');
            Route::post('pay_now/{id}', 'PaymentController@pay_now')->name('payments.pay_now');
        });

        /*************** Pins *****************/
        Route::group(['prefix' => 'pins'], function(){
            Route::get('create', 'PinController@create')->name('pins.create');
            Route::get('/', 'PinController@index')->name('pins.index');
            Route::post('/', 'PinController@store')->name('pins.store');
            Route::get('enter/{id}', 'PinController@enter_pin')->name('pins.enter');
            Route::post('verify/{id}', 'PinController@verify')->name('pins.verify');
            Route::delete('/', 'PinController@destroy')->name('pins.destroy');
        });

        /*************** Marks *****************/
        Route::group(['prefix' => 'marks'], function(){

           // FOR teamSA
            Route::group(['middleware' => 'teamSA'], function(){
                Route::get('batch_fix', 'MarkController@batch_fix')->name('marks.batch_fix');
                Route::put('batch_update', 'MarkController@batch_update')->name('marks.batch_update');
                Route::get('tabulation/{exam?}/{class?}/{sec_id?}', 'MarkController@tabulation')->name('marks.tabulation');
                Route::post('tabulation', 'MarkController@tabulation_select')->name('marks.tabulation_select');
                Route::get('tabulation/print/{exam}/{class}/{sec_id}', 'MarkController@print_tabulation')->name('marks.print_tabulation');
            });

            // FOR teamSAT
       Route::group(['middleware' => 'teamSAT'], function(){
                Route::get('/', 'MarkController@index')->name('marks.index');
                Route::get('manage/{exam}/{class}/{section}/{subject}', 'MarkController@manage')->name('marks.manage');
                Route::put('update/{exam}/{class}/{section}/{subject}', 'MarkController@update')->name('marks.update');
                Route::put('comment_update/{exr_id}', 'MarkController@comment_update')->name('marks.comment_update');
                Route::put('skills_update/{skill}/{exr_id}', 'MarkController@skills_update')->name('marks.skills_update');
                Route::post('selector', 'MarkController@selector')->name('marks.selector');
                Route::get('bulk/{class?}/{section?}', 'MarkController@bulk')->name('marks.bulk');
                Route::post('bulk', 'MarkController@bulk_select')->name('marks.bulk_select');
            });

            Route::get('select_year/{id}', 'MarkController@year_selector')->name('marks.year_selector');
            Route::post('select_year/{id}', 'MarkController@year_selected')->name('marks.year_select');
            Route::get('show/{id}/{year}', 'MarkController@show')->name('marks.show');
            Route::get('print/{id}/{exam_id}/{year}', 'MarkController@print_view')->name('marks.print');

        });

        Route::resource('students', 'StudentRecordController');
        Route::resource('users', 'UserController');
        Route::resource('classes', 'MyClassController');
        Route::resource('sections', 'SectionController');
        Route::resource('subjects', 'SubjectController');
        Route::resource('grades', 'GradeController');
        Route::resource('exams', 'ExamController');
        Route::resource('dorms', 'DormController');
        Route::resource('payments', 'PaymentController');

    });

    /************************ Academic Management (Accountant Only) ****************************/
    Route::group([
        'namespace' => 'SupportTeam',
        'prefix' => 'academic-management',
        'middleware' => ['accountant_only'],
    ], function () {
        Route::get('/', 'AcademicManagementController@index')->name('academic.management.index');
        Route::get('/finder', 'AcademicManagementController@finder')->name('academic.management.finder');
        Route::get('/quizzes', 'AcademicManagementController@quizzes')->name('academic.management.quizzes');
        Route::get('/weeks/manage', 'AcademicManagementController@manageWeeks')->name('academic.management.weeks.manage');
        Route::get('/weeks/{week}/show', 'AcademicManagementController@showWeek')->name('academic.management.weeks.show');
        Route::get('/weekly-content', 'AcademicManagementController@weeklyContent')->name('academic.management.weekly_content');
        Route::get('/weeks/{week}/moodle', 'AcademicManagementController@weekMoodleContents')->name('academic.management.weeks.moodle');
        Route::post('/weeks/{week}/moodle/reorder', 'AcademicManagementController@reorderWeekMoodleContents')->name('academic.management.weeks.moodle.reorder');
        Route::post('/sessions', 'AcademicManagementController@storeSession')->name('academic.management.sessions.store');
        Route::put('/sessions/{session}', 'AcademicManagementController@updateSession')->name('academic.management.sessions.update');
        Route::post('/sessions/attach-class', 'AcademicManagementController@attachClass')->name('academic.management.sessions.attach_class');
        Route::post('/sessions/{session}/weeks/generate', 'AcademicManagementController@generateSessionWeeks')->name('academic.management.sessions.weeks.generate');
        Route::post('/holidays', 'AcademicManagementController@storeHoliday')->name('academic.management.holidays.store');
        Route::put('/holidays/{holiday}', 'AcademicManagementController@updateHoliday')->name('academic.management.holidays.update');
        Route::delete('/holidays/{holiday}', 'AcademicManagementController@destroyHoliday')->name('academic.management.holidays.destroy');
        Route::post('/weeks', 'AcademicManagementController@storeWeek')->name('academic.management.weeks.store');
        Route::post('/weeks/bulk', 'AcademicManagementController@storeWeeksBulk')->name('academic.management.weeks.bulk_store');
        Route::put('/weeks/{week}', 'AcademicManagementController@updateWeek')->name('academic.management.weeks.update');
        Route::delete('/weeks/{week}', 'AcademicManagementController@destroyWeek')->name('academic.management.weeks.destroy');
        Route::post('/units', 'AcademicManagementController@storeUnit')->name('academic.management.units.store');
        Route::put('/units/{unit}', 'AcademicManagementController@updateUnit')->name('academic.management.units.update');
        Route::delete('/units/{unit}', 'AcademicManagementController@destroyUnit')->name('academic.management.units.destroy');
        Route::post('/lessons', 'AcademicManagementController@storeLesson')->name('academic.management.lessons.store');
        Route::put('/lessons/{lesson}', 'AcademicManagementController@updateLesson')->name('academic.management.lessons.update');
        Route::delete('/lessons/{lesson}', 'AcademicManagementController@destroyLesson')->name('academic.management.lessons.destroy');
        Route::post('/files/subject-general', 'AcademicManagementController@uploadSubjectGeneralFile')->name('academic.management.files.subject_general.store');
        Route::put('/files/subject-general/{file}', 'AcademicManagementController@updateSubjectGeneralFile')->name('academic.management.files.subject_general.update');
        Route::delete('/files/subject-general/{file}', 'AcademicManagementController@destroySubjectGeneralFile')->name('academic.management.files.subject_general.destroy');
        Route::post('/files/unit-general', 'AcademicManagementController@uploadUnitGeneralFile')->name('academic.management.files.unit_general.store');
        Route::put('/files/unit-general/{file}', 'AcademicManagementController@updateUnitGeneralFile')->name('academic.management.files.unit_general.update');
        Route::delete('/files/unit-general/{file}', 'AcademicManagementController@destroyUnitGeneralFile')->name('academic.management.files.unit_general.destroy');
        Route::post('/files/lesson', 'AcademicManagementController@uploadLessonFile')->name('academic.management.files.lesson.store');
        Route::put('/files/lesson/{file}', 'AcademicManagementController@updateLessonFile')->name('academic.management.files.lesson.update');
        Route::delete('/files/lesson/{file}', 'AcademicManagementController@destroyLessonFile')->name('academic.management.files.lesson.destroy');
        Route::post('/question-banks/lesson', 'AcademicManagementController@storeLessonQuestionBank')->name('academic.management.question_banks.lesson.store');
        Route::post('/question-banks/lesson/{questionBank}/sync', 'AcademicManagementController@syncLessonQuestionBank')->name('academic.management.question_banks.lesson.sync');
        Route::delete('/question-banks/lesson/{questionBank}', 'AcademicManagementController@destroyLessonQuestionBank')->name('academic.management.question_banks.lesson.destroy');
        Route::post('/quizzes', 'AcademicManagementController@storeQuiz')->name('academic.management.quizzes.store');
        Route::delete('/quizzes/{quiz}', 'AcademicManagementController@destroyQuiz')->name('academic.management.quizzes.destroy');
    });

    /************************ Stripe Payments - Admin Routes ****************************/
    Route::group(['prefix' => 'stripe-payments'], function(){
        // For Parents - عرض الدفعات المستحقة والدفع
        Route::get('pending', 'StripePaymentController@pendingPayments')->name('stripe.pending');
        Route::get('{paymentRecord}/checkout', 'StripePaymentController@showCheckout')->name('stripe.checkout');
        Route::post('{paymentRecord}/process', 'StripePaymentController@processPayment')->name('stripe.process');
        Route::post('confirm', 'StripePaymentController@confirmPayment')->name('stripe.confirm');

        // For Admin - التأكيد أو الرفض
        Route::get('pending-confirmation', 'StripePaymentController@adminPendingPayments')->name('stripe.admin_pending');
        Route::post('{paymentRecord}/confirm', 'StripePaymentController@adminConfirm')->name('stripe.admin_confirm');
        Route::post('{paymentRecord}/reject', 'StripePaymentController@adminReject')->name('stripe.admin_reject');
    });

    /************************ BigBlueButton ****************************/
    Route::group(['prefix' => 'bigbluebutton'], function(){
        Route::get('list-recordings', 'BigBlueButtonController@listRecordings')->name('bbb.list_recordings');
    });

    /************************ AJAX ****************************/
    Route::group(['prefix' => 'ajax'], function() {
        Route::get('get_lga/{state_id}', 'AjaxController@get_lga')->name('get_lga');
        Route::get('get_class_sections/{class_id}', 'AjaxController@get_class_sections')->name('get_class_sections');
        Route::get('get_class_subjects/{class_id}', 'AjaxController@get_class_subjects')->name('get_class_subjects');
    });

});

/************************ SUPER ADMIN ****************************/
Route::group(['namespace' => 'SuperAdmin','middleware' => 'super_admin', 'prefix' => 'super_admin'], function(){

    Route::get('/settings', 'SettingController@index')->name('settings');
    Route::put('/settings', 'SettingController@update')->name('settings.update');
    Route::post('/settings/whatsapp-test', 'SettingController@whatsappTest')->name('settings.whatsapp_test');

    // Announcements Routes
    Route::patch('announcements/{announcement}/toggle', 'AnnouncementController@toggleStatus')->name('announcements.toggleStatus');
    Route::resource('announcements', 'AnnouncementController');

    // Islamic Materials Routes
    Route::patch('islamic-materials/{islamicMaterial}/toggle-status', 'IslamicMaterialController@toggleStatus')->name('islamic-materials.toggleStatus');
    Route::resource('islamic-materials', 'IslamicMaterialController', ['names' => ['index' => 'islamic-materials.index', 'create' => 'islamic-materials.create', 'store' => 'islamic-materials.store', 'show' => 'islamic-materials.show', 'edit' => 'islamic-materials.edit', 'update' => 'islamic-materials.update', 'destroy' => 'islamic-materials.destroy']]);

});

/************************ GUARDIANS AND STUDENTS ****************************/
Route::group(['middleware' => 'auth'], function(){
    // Guardians Routes
    Route::resource('guardians', 'GuardianController');
    Route::get('guardians/status/{status}', 'GuardianController@filterByStatus')->name('guardians.filterByStatus');
    Route::get('guardians/search', 'GuardianController@search')->name('guardians.search');

    // Candidates/New Students Routes (from Guardian system)
    Route::group(['prefix' => 'candidates'], function() {
        Route::get('/', 'StudentController@index')->name('candidates.index');
        Route::get('/create', 'StudentController@create')->name('candidates.create');
        Route::post('/', 'StudentController@store')->name('candidates.store');
        Route::get('/{student}', 'StudentController@show')->name('candidates.show');
        Route::get('/{student}/edit', 'StudentController@edit')->name('candidates.edit');
        Route::put('/{student}', 'StudentController@update')->name('candidates.update');
        Route::delete('/{student}', 'StudentController@destroy')->name('candidates.destroy');
        Route::post('/{student}/change-status', 'StudentController@changeStatus')->name('candidates.changeStatus');
        Route::post('/{student}/approve-with-class', 'StudentController@approveWithClass')->name('candidates.approveWithClass');
        Route::get('/guardian/{guardian}', 'StudentController@byGuardian')->name('candidates.byGuardian');
        Route::get('/status/{status}', 'StudentController@filterByStatus')->name('candidates.filterByStatus');
        Route::get('/search', 'StudentController@search')->name('candidates.search');
    });
});

/************************ ISLAMIC CORNER (For Students & Parents) ****************************/
Route::group(['middleware' => 'auth'], function(){
    Route::group(['prefix' => 'islamic-corner'], function() {
        Route::get('/', 'IslamicCornerController@index')->name('islamic-corner.index');
        
        // API routes for filtering (must come before dynamic route)
        Route::get('api/stage/{stage}', 'IslamicCornerController@getByStage')->name('islamic-corner.api.stage')->where('stage', 'primary|middle|secondary');
        Route::get('api/class/{classId}', 'IslamicCornerController@getByClass')->name('islamic-corner.api.class')->where('classId', '[0-9]+');

        Route::get('{islamicMaterial}/join/{day}', 'IslamicCornerController@joinMeeting')
            ->name('islamic-corner.join_meeting')
            ->where('islamicMaterial', '[0-9]+');

        // Dynamic route for showing material (constrain to numbers only)
        Route::get('{islamicMaterial}', 'IslamicCornerController@show')->name('islamic-corner.show')->where('islamicMaterial', '[0-9]+');
    });
});
Route::group(['namespace' => 'MyParent','middleware' => ['auth', 'my_parent'],], function(){

    Route::get('/my_children', 'MyController@children')->name('my_children');
    Route::get('/my_children/ai-tutor-progress', 'MyController@aiTutorProgress')->name('my_children.ai_tutor_progress');
    Route::get('/my_children/{studentUserId}/attendance-report', 'MyController@attendanceReport')->name('my_children.attendance_report');

    /*************** Advisory Corner *****************/
    Route::group(['prefix' => 'parent-advisory'], function(){
        Route::get('/', 'AdvisoryCornerController@index')->name('advisory.index');
        Route::get('create', 'AdvisoryCornerController@create')->name('advisory.create');
        Route::post('/', 'AdvisoryCornerController@store')->name('advisory.store');
        Route::get('{conversationId}', 'AdvisoryCornerController@show')->name('advisory.show');
        Route::post('{conversationId}/message', 'AdvisoryCornerController@sendMessage')->name('advisory.sendMessage');
        Route::post('{conversationId}/close', 'AdvisoryCornerController@close')->name('advisory.close');
        Route::get('{conversationId}/grades', 'AdvisoryCornerController@studentGrades')->name('advisory.grades');
        Route::get('{conversationId}/grades/report', 'AdvisoryCornerController@downloadGradesReport')->name('advisory.downloadGradesReport');
        Route::get('{conversationId}/messages-api', 'AdvisoryCornerController@getMessages')->name('advisory.getMessages');
    });


});
