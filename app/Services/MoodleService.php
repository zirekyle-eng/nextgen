<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MoodleService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.moodle.url'), '/');
        $this->token = (string) config('services.moodle.token');
    }

    public function isConfigured(): bool
    {
        return !empty($this->baseUrl) && !empty($this->token);
    }

    private function call(string $function, array $params = [])
    {
        if (empty($this->baseUrl) || empty($this->token)) {
            \Log::error('MoodleService missing config', [
                'base_url' => $this->baseUrl,
                'token_set' => !empty($this->token),
            ]);
            return ['error' => 'Moodle config missing'];
        }

        $client = $this->applyTlsOptions(Http::asForm());

        $response = $client->post($this->baseUrl . '/webservice/rest/server.php', array_merge([
            'wstoken' => $this->token,
            'wsfunction' => $function,
            'moodlewsrestformat' => 'json',
        ], $params));

        $body = $response->body();
        $json = $response->json();
        if (!is_array($json) && $body !== '') {
            $json = json_decode($body, true);
        }
        if (!is_array($json)) {
            \Log::error('MoodleService invalid JSON response', [
                'function' => $function,
                'status' => $response->status(),
                'body' => $body,
            ]);
            return ['raw_body' => $body];
        }
        if (!$response->successful() || (is_array($json) && isset($json['exception']))) {
            \Log::error('MoodleService call failed', [
                'function' => $function,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

        return $json;
    }

    /**
     * Sanitize username for Moodle - remove slashes, keep lowercase
     */
    private function sanitizeUsername(string $username): string
    {
        // Replace slashes with dots
        $username = str_replace('/', '.', $username);
        // Convert to lowercase for Moodle
        $username = strtolower($username);
        // Trim and limit to 100 characters
        return trim(mb_substr($username, 0, 100));
    }

    public function getUserByUsername(string $username): ?array
    {
        $result = $this->call('core_user_get_users', [
            'criteria[0][key]' => 'username',
            'criteria[0][value]' => $username,
        ]);

        if (!empty($result['users'][0])) {
            return $result['users'][0];
        }

        return null;
    }

    public function getUserByEmail(string $email): ?array
    {
        $result = $this->call('core_user_get_users', [
            'criteria[0][key]' => 'email',
            'criteria[0][value]' => $email,
        ]);

        if (!empty($result['users'][0])) {
            return $result['users'][0];
        }

        return null;
    }

    public function createUser(array $user)
    {
        // Validate and sanitize parameters
        $firstname = mb_substr(trim($user['firstname']), 0, 100);
        $lastname = mb_substr(trim($user['lastname']), 0, 100);
        
        // Use provided email (already cleaned in controller)
        $email = !empty($user['email']) ? trim($user['email']) : '';
        // Username = Email
        $username = $email;
        
        $params = [
            'users[0][username]' => $username,
            'users[0][firstname]' => $firstname,
            'users[0][lastname]' => $lastname,
            'users[0][email]' => $email,
            'users[0][auth]' => $user['auth'] ?? 'oidc',
        ];
        
        \Log::info('MoodleService creating user with params', [
            'username' => $username,
            'email' => $email,
            'firstname' => $firstname,
            'lastname' => $lastname,
        ]);
        
        return $this->call('core_user_create_users', $params);
    }

    public function updateUser(int $id, array $user)
    {
        $firstname = mb_substr(trim($user['firstname']), 0, 100);
        $lastname = mb_substr(trim($user['lastname']), 0, 100);
        
        // Use provided email (already cleaned in controller)
        $email = !empty($user['email']) ? trim($user['email']) : '';
        
        $params = [
            'users[0][id]' => $id,
            'users[0][firstname]' => $firstname,
            'users[0][lastname]' => $lastname,
            'users[0][email]' => $email,
        ];
        
        \Log::info('MoodleService updating user', [
            'user_id' => $id,
            'email' => $email,
            'firstname' => $firstname,
            'lastname' => $lastname,
        ]);
        
        return $this->call('core_user_update_users', $params);
    }

    public function createOrUpdateUser(array $user)
    {
        // Use email as username for lookup
        $email = !empty($user['email']) ? trim($user['email']) : '';
        $existing = $this->getUserByUsername($email);
        if ($existing) {
            return $this->updateUser((int) $existing['id'], $user);
        }

        return $this->createUser($user);
    }

    public function getCourseByShortName(string $shortName): ?array
    {
        $result = $this->call('core_course_get_courses_by_field', [
            'field' => 'shortname',
            'value' => $shortName,
        ]);

        if (!empty($result['courses'][0])) {
            return $result['courses'][0];
        }

        return null;
    }

    public function createCourse(string $fullName, string $shortName, ?int $categoryId = null): ?int
    {
        $categoryId = $categoryId ?: (int) config('services.moodle.category_id', 1);
        $result = $this->call('core_course_create_courses', [
            'courses[0][fullname]' => mb_substr(trim($fullName), 0, 254),
            'courses[0][shortname]' => mb_substr(trim($shortName), 0, 100),
            'courses[0][categoryid]' => max(1, $categoryId),
            'courses[0][visible]' => 1,
        ]);

        if (!empty($result[0]['id'])) {
            return (int) $result[0]['id'];
        }

        return null;
    }

    public function createOrGetCourse(string $fullName, string $shortName, ?int $categoryId = null): ?int
    {
        $existing = $this->getCourseByShortName($shortName);
        if ($existing && !empty($existing['id'])) {
            return (int) $existing['id'];
        }

        return $this->createCourse($fullName, $shortName, $categoryId);
    }

    public function enrolUsers(int $courseId, array $enrolments): array
    {
        $params = [];
        foreach (array_values($enrolments) as $idx => $enrolment) {
            $params["enrolments[{$idx}][roleid]"] = (int) $enrolment['roleid'];
            $params["enrolments[{$idx}][userid]"] = (int) $enrolment['userid'];
            $params["enrolments[{$idx}][courseid]"] = $courseId;
        }

        return $this->call('enrol_manual_enrol_users', $params);
    }

    public function syncCourseFileFromPath(
        int $courseId,
        string $absolutePath,
        string $displayName,
        string $activityType = 'resource',
        array $context = []
    ): array
    {
        if (!config('services.moodle.file_sync_enabled', false)) {
            return ['ok' => false, 'skipped' => true, 'error' => 'file_sync_disabled'];
        }

        if (!is_file($absolutePath)) {
            return ['ok' => false, 'error' => 'file_not_found'];
        }

        $upload = $this->uploadFileToDraft($absolutePath);
        if (empty($upload['itemid'])) {
            return ['ok' => false, 'error' => 'draft_upload_failed'];
        }

        $function = $this->resolveFileSyncFunction($activityType);
        if ($function === '' && $this->shouldFallbackToResource($activityType)) {
            $function = trim((string) config('services.moodle.file_sync_function', ''));
            $activityType = 'resource';
        }
        if ($function === '') {
            return ['ok' => false, 'error' => 'file_sync_function_not_set_for_activity', 'activity_type' => $activityType];
        }

        $intro = 'Synced from Laravel';
        if (!empty($context['subject']) || !empty($context['year'])) {
            $intro = trim(
                'Synced from Laravel' .
                (!empty($context['subject']) ? ' | Subject: ' . $context['subject'] : '') .
                (!empty($context['year']) ? ' | Year: ' . $context['year'] : '')
            );
        }

        $itemId = (int) $upload['itemid'];
        $activityName = trim((string) ($context['title'] ?? ''));
        if ($activityName === '') {
            $activityName = mb_substr(trim($displayName), 0, 255);
        }

        $activityIntro = trim((string) ($context['intro'] ?? ''));
        if ($activityIntro === '') {
            $activityIntro = $intro;
        }

        $sectionNumber = isset($context['section']) ? max(0, (int) $context['section']) : 0;
        $visible = array_key_exists('visible', $context) ? ((int) ((bool) $context['visible'])) : 1;

        $params = [
            'courseid' => $courseId,
            'name' => $activityName,
            'intro' => $activityIntro,
            'itemid' => $itemId,
            'filename' => (string) ($upload['filename'] ?? basename($absolutePath)),
            'section' => $sectionNumber,
            'visible' => $visible,
            'showdescription' => 0,
        ];

        if (
            strtolower(trim($activityType)) === 'assignment' &&
            (bool) config('services.moodle.assignment_supports_dates', false)
        ) {
            if (!empty($context['available_from'])) {
                $params['allowsubmissionsfromdate'] = (int) $context['available_from'];
            }
            if (!empty($context['due_at'])) {
                $params['duedate'] = (int) $context['due_at'];
            }
            if (!empty($context['cutoff_at'])) {
                $params['cutoffdate'] = (int) $context['cutoff_at'];
            }
        }

        if (
            strtolower(trim($activityType)) === 'quiz' &&
            (bool) config('services.moodle.quiz_supports_dates', false)
        ) {
            if (!empty($context['timeopen'])) {
                $params['timeopen'] = (int) $context['timeopen'];
            }
            if (!empty($context['timeclose'])) {
                $params['timeclose'] = (int) $context['timeclose'];
            }
            if (!empty($context['timelimit'])) {
                $params['timelimit'] = (int) $context['timelimit'];
            }
        }

        $result = $this->call($function, $params);
        if (is_array($result) && isset($result['exception'])) {
            if ($this->shouldFallbackToResource($activityType)) {
                $resourceFunction = trim((string) config('services.moodle.file_sync_function', ''));
                if ($resourceFunction !== '' && $resourceFunction !== $function) {
                    $fallbackResult = $this->call($resourceFunction, $params);
                    if (!is_array($fallbackResult) || !isset($fallbackResult['exception'])) {
                        return [
                            'ok' => true,
                            'upload' => $upload,
                            'response' => $fallbackResult,
                            'activity_type' => 'resource',
                            'fallback' => true,
                            'fallback_from' => $activityType,
                        ];
                    }

                    return [
                        'ok' => false,
                        'error' => (string) ($fallbackResult['message'] ?? 'file_sync_call_failed'),
                        'response' => $fallbackResult,
                        'fallback' => true,
                        'fallback_from' => $activityType,
                        'primary_response' => $result,
                    ];
                }
            }

            return ['ok' => false, 'error' => (string) ($result['message'] ?? 'file_sync_call_failed'), 'response' => $result];
        }

        return ['ok' => true, 'upload' => $upload, 'response' => $result, 'activity_type' => $activityType];
    }

    public function importQuestionBankFromPath(
        int $courseId,
        string $absolutePath,
        string $displayName,
        array $context = []
    ): array {
        if (!config('services.moodle.file_sync_enabled', false)) {
            return ['ok' => false, 'skipped' => true, 'error' => 'file_sync_disabled'];
        }

        if (!is_file($absolutePath)) {
            return ['ok' => false, 'error' => 'file_not_found'];
        }

        $function = trim((string) config('services.moodle.question_bank_sync_function', ''));
        if ($function === '') {
            return ['ok' => false, 'skipped' => true, 'error' => 'question_bank_sync_function_not_set'];
        }

        $upload = $this->uploadFileToDraft($absolutePath);
        if (empty($upload['itemid'])) {
            return ['ok' => false, 'error' => 'draft_upload_failed'];
        }

        $displayName = mb_substr(trim($displayName), 0, 255);
        $categoryName = mb_substr(trim((string) ($context['categoryname'] ?? $displayName)), 0, 255);
        if ($displayName === '') {
            $displayName = $categoryName !== '' ? $categoryName : 'Question Bank Import';
        }
        if ($categoryName === '') {
            $categoryName = $displayName;
        }

        $params = [
            'courseid' => $courseId,
            'name' => $displayName,
            'categoryname' => $categoryName,
            'itemid' => (int) $upload['itemid'],
            'filename' => (string) ($upload['filename'] ?? basename($absolutePath)),
        ];

        if (!empty($context['contextid'])) {
            $params['contextid'] = (int) $context['contextid'];
        }
        if (!empty($context['parentcategoryid'])) {
            $params['parentcategoryid'] = (int) $context['parentcategoryid'];
        }
        if (!empty($context['subject'])) {
            $params['subject'] = mb_substr(trim((string) $context['subject']), 0, 255);
        }
        if (!empty($context['year'])) {
            $params['year'] = mb_substr(trim((string) $context['year']), 0, 255);
        }
        if (!empty($context['lessonname'])) {
            $params['lessonname'] = mb_substr(trim((string) $context['lessonname']), 0, 255);
        }
        if (!empty($context['week'])) {
            $params['week'] = (int) $context['week'];
        }
        if (!empty($context['unit'])) {
            $params['unit'] = (int) $context['unit'];
        }
        if (!empty($context['lesson'])) {
            $params['lesson'] = (int) $context['lesson'];
        }
        if (!empty($context['questioncount'])) {
            $params['questioncount'] = (int) $context['questioncount'];
        }

        $result = $this->call($function, $params);
        if (is_array($result) && isset($result['exception'])) {
            return [
                'ok' => false,
                'error' => (string) ($result['message'] ?? 'question_bank_import_failed'),
                'response' => $result,
            ];
        }

        return ['ok' => true, 'upload' => $upload, 'response' => $result];
    }

    public function syncCourseSectionName(int $courseId, int $section, string $name): array
    {
        $function = trim((string) config('services.moodle.section_sync_function', ''));
        if ($function === '') {
            return ['ok' => false, 'skipped' => true, 'error' => 'section_sync_function_not_set'];
        }

        $result = $this->call($function, [
            'courseid' => $courseId,
            'section' => max(0, $section),
            'name' => mb_substr(trim($name), 0, 255),
        ]);

        if (is_array($result) && isset($result['exception'])) {
            return ['ok' => false, 'error' => (string) ($result['message'] ?? 'section_sync_failed'), 'response' => $result];
        }

        return ['ok' => true, 'response' => $result];
    }

    public function syncCourseSectionLabel(int $courseId, int $section, string $title, ?string $text = null): array
    {
        $function = trim((string) config('services.moodle.label_sync_function', ''));
        if ($function === '') {
            return ['ok' => false, 'skipped' => true, 'error' => 'label_sync_function_not_set'];
        }

        $name = mb_substr(trim($title), 0, 255);
        $intro = trim((string) ($text ?? $title));

        $result = $this->call($function, [
            'courseid' => $courseId,
            'section' => max(0, $section),
            'name' => $name,
            'intro' => $intro,
            'visible' => 1,
            'showdescription' => 0,
        ]);

        if (is_array($result) && isset($result['exception'])) {
            return ['ok' => false, 'error' => (string) ($result['message'] ?? 'label_sync_failed'), 'response' => $result];
        }

        return ['ok' => true, 'response' => $result];
    }

    public function moveCourseModuleAfter(int $cmid, int $afterCmid): array
    {
        $function = trim((string) config('services.moodle.module_move_function', ''));
        if ($function === '') {
            return ['ok' => false, 'skipped' => true, 'error' => 'module_move_function_not_set'];
        }

        $afterCmid = max(0, $afterCmid);
        $result = $this->call($function, [
            'cmid' => max(1, $cmid),
            'aftercmid' => $afterCmid,
        ]);

        if (is_array($result) && isset($result['exception'])) {
            return ['ok' => false, 'error' => (string) ($result['message'] ?? 'module_move_failed'), 'response' => $result];
        }

        return ['ok' => true, 'response' => $result];
    }

    public function getCourseSectionModules(int $courseId, int $sectionNumber): array
    {
        if ($courseId <= 0) {
            return ['ok' => false, 'error' => 'invalid_course_id'];
        }

        $result = $this->call('core_course_get_contents', [
            'courseid' => $courseId,
        ]);

        if (!is_array($result)) {
            return ['ok' => false, 'error' => 'invalid_response'];
        }
        if (isset($result['exception'])) {
            return ['ok' => false, 'error' => (string) ($result['message'] ?? 'moodle_exception'), 'response' => $result];
        }

        $modules = [];
        foreach ($result as $section) {
            if (!is_array($section)) {
                continue;
            }
            if ((int) ($section['section'] ?? -1) !== (int) $sectionNumber) {
                continue;
            }
            if (empty($section['modules']) || !is_array($section['modules'])) {
                break;
            }
            foreach ($section['modules'] as $module) {
                if (!is_array($module)) {
                    continue;
                }
                $cmid = isset($module['id']) ? (int) $module['id'] : 0;
                if ($cmid <= 0) {
                    continue;
                }
                $modules[] = [
                    'cmid' => $cmid,
                    'name' => (string) ($module['name'] ?? ''),
                    'modname' => (string) ($module['modname'] ?? ''),
                    'instance' => isset($module['instance']) ? (int) $module['instance'] : 0,
                    'url' => (string) ($module['url'] ?? ''),
                ];
            }
            break;
        }

        return [
            'ok' => true,
            'section' => (int) $sectionNumber,
            'modules' => $modules,
        ];
    }

    public function findCourseModuleIdByInstance(int $courseId, string $moduleName, int $instanceId): ?int
    {
        $moduleName = strtolower(trim($moduleName));
        if ($courseId <= 0 || $instanceId <= 0 || $moduleName === '') {
            return null;
        }

        $result = $this->call('core_course_get_contents', [
            'courseid' => $courseId,
        ]);
        if (!is_array($result)) {
            return null;
        }
        if (isset($result['exception'])) {
            return null;
        }

        foreach ($result as $section) {
            if (!is_array($section) || empty($section['modules']) || !is_array($section['modules'])) {
                continue;
            }
            foreach ($section['modules'] as $module) {
                if (!is_array($module)) {
                    continue;
                }
                if (
                    isset($module['modname'], $module['instance'], $module['id']) &&
                    strtolower((string) $module['modname']) === $moduleName &&
                    (int) $module['instance'] === $instanceId &&
                    (int) $module['id'] > 0
                ) {
                    return (int) $module['id'];
                }
            }
        }

        return null;
    }

    public function deleteCourseModule(int $cmid): array
    {
        $primaryFunction = trim((string) config('services.moodle.module_delete_function', ''));
        if ($primaryFunction === '') {
            return ['ok' => false, 'skipped' => true, 'error' => 'module_delete_function_not_set'];
        }

        $cmid = max(1, $cmid);
        $primaryAttempt = $this->tryDeleteCourseModuleWithFunction($primaryFunction, $cmid);
        if (!empty($primaryAttempt['ok'])) {
            return [
                'ok' => true,
                'response' => $primaryAttempt['response'] ?? null,
                'function' => $primaryFunction,
                'param_style' => $primaryAttempt['param_style'] ?? null,
            ];
        }

        $fallbackFunction = trim((string) config('services.moodle.module_delete_fallback_function', ''));
        if ($fallbackFunction !== '' && $fallbackFunction !== $primaryFunction) {
            $fallbackAttempt = $this->tryDeleteCourseModuleWithFunction($fallbackFunction, $cmid);
            if (!empty($fallbackAttempt['ok'])) {
                return [
                    'ok' => true,
                    'response' => $fallbackAttempt['response'] ?? null,
                    'function' => $fallbackFunction,
                    'fallback' => true,
                    'fallback_from' => $primaryFunction,
                    'param_style' => $fallbackAttempt['param_style'] ?? null,
                    'primary_response' => $primaryAttempt['response'] ?? null,
                ];
            }

            $fallbackCode = (string) ($fallbackAttempt['errorcode'] ?? '');
            if ($fallbackCode === 'accessexception') {
                return [
                    'ok' => false,
                    'error' => 'Access control exception: Moodle token/service is missing delete-module permission.',
                    'response' => $fallbackAttempt['response'] ?? null,
                    'errorcode' => $fallbackCode,
                    'function' => $fallbackFunction,
                    'fallback' => true,
                    'fallback_from' => $primaryFunction,
                    'primary_response' => $primaryAttempt['response'] ?? null,
                ];
            }
            if ($fallbackCode === 'invalidparameter') {
                return [
                    'ok' => false,
                    'error' => 'Invalid parameter value detected while deleting Moodle module. Verify CMID and delete-function parameter format.',
                    'response' => $fallbackAttempt['response'] ?? null,
                    'errorcode' => $fallbackCode,
                    'function' => $fallbackFunction,
                    'fallback' => true,
                    'fallback_from' => $primaryFunction,
                    'param_style' => $fallbackAttempt['param_style'] ?? null,
                    'attempts' => $fallbackAttempt['attempts'] ?? null,
                    'primary_response' => $primaryAttempt['response'] ?? null,
                ];
            }

            return [
                'ok' => false,
                'error' => (string) ($fallbackAttempt['error'] ?? 'module_delete_failed'),
                'response' => $fallbackAttempt['response'] ?? null,
                'errorcode' => (string) ($fallbackAttempt['errorcode'] ?? ''),
                'function' => $fallbackFunction,
                'fallback' => true,
                'fallback_from' => $primaryFunction,
                'param_style' => $fallbackAttempt['param_style'] ?? null,
                'attempts' => $fallbackAttempt['attempts'] ?? null,
                'primary_response' => $primaryAttempt['response'] ?? null,
            ];
        }

        $primaryCode = (string) ($primaryAttempt['errorcode'] ?? '');
        if ($primaryCode === 'accessexception') {
            return [
                'ok' => false,
                'error' => 'Access control exception: Moodle token/service is missing delete-module permission.',
                'response' => $primaryAttempt['response'] ?? null,
                'errorcode' => $primaryCode,
                'function' => $primaryFunction,
            ];
        }
        if ($primaryCode === 'invalidparameter') {
            return [
                'ok' => false,
                'error' => 'Invalid parameter value detected while deleting Moodle module. Verify CMID and delete-function parameter format.',
                'response' => $primaryAttempt['response'] ?? null,
                'errorcode' => $primaryCode,
                'function' => $primaryFunction,
                'param_style' => $primaryAttempt['param_style'] ?? null,
                'attempts' => $primaryAttempt['attempts'] ?? null,
            ];
        }

        return [
            'ok' => false,
            'error' => (string) ($primaryAttempt['error'] ?? 'module_delete_failed'),
            'response' => $primaryAttempt['response'] ?? null,
            'errorcode' => $primaryCode,
            'function' => $primaryFunction,
            'param_style' => $primaryAttempt['param_style'] ?? null,
            'attempts' => $primaryAttempt['attempts'] ?? null,
        ];
    }

    private function tryDeleteCourseModuleWithFunction(string $function, int $cmid): array
    {
        $payloads = [
            // core_course_delete_modules expects cmids[]
            ['cmids[0]' => $cmid],
            ['cmids[0][id]' => $cmid],
            ['moduleids[0]' => $cmid],
            ['moduleids[0][id]' => $cmid],
            ['cmid' => $cmid],
            ['moduleid' => $cmid],
            ['id' => $cmid],
        ];

        $lastResult = null;
        $lastParamStyle = '';
        $attempts = [];
        foreach ($payloads as $payload) {
            $result = $this->call($function, $payload);
            $lastResult = $result;
            $lastParamStyle = (string) array_key_first($payload);

            $attempts[] = [
                'param_style' => $lastParamStyle,
                'errorcode' => is_array($result) ? (string) ($result['errorcode'] ?? '') : '',
                'message' => is_array($result) ? (string) ($result['message'] ?? '') : '',
            ];

            if (!is_array($result) || !isset($result['exception'])) {
                $paramStyle = $lastParamStyle;
                return ['ok' => true, 'response' => $result, 'param_style' => $paramStyle];
            }
        }

        return [
            'ok' => false,
            'response' => $lastResult,
            'error' => (string) ($lastResult['message'] ?? 'module_delete_failed'),
            'errorcode' => (string) ($lastResult['errorcode'] ?? ''),
            'param_style' => $lastParamStyle,
            'attempts' => $attempts,
        ];
    }

    private function uploadFileToDraft(string $absolutePath): array
    {
        $stream = @fopen($absolutePath, 'rb');
        if (!$stream) {
            \Log::error('Moodle draft upload failed: cannot open file', ['path' => $absolutePath]);
            return [];
        }

        try {
            $client = $this->applyTlsOptions(Http::timeout(120));

            $response = $client
                ->attach('file_1', $stream, basename($absolutePath))
                ->post($this->baseUrl . '/webservice/upload.php', [
                    'token' => $this->token,
                    'filepath' => '/',
                    'itemid' => 0,
                ]);
        } finally {
            fclose($stream);
        }

        $json = $response->json();
        if (
            !$response->successful() ||
            !is_array($json) ||
            empty($json[0]) ||
            !is_array($json[0]) ||
            empty($json[0]['itemid'])
        ) {
            \Log::error('Moodle draft upload failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return [];
        }

        return $json[0];
    }

    private function resolveFileSyncFunction(string $activityType): string
    {
        $type = strtolower(trim($activityType));

        if ($type === 'assignment') {
            return trim((string) config('services.moodle.assignment_sync_function', ''));
        }

        if ($type === 'quiz') {
            return trim((string) config('services.moodle.quiz_sync_function', ''));
        }

        return trim((string) config('services.moodle.file_sync_function', ''));
    }

    private function applyTlsOptions($client)
    {
        $sslVerify = (bool) config('services.moodle.ssl_verify', true);
        if (!$sslVerify) {
            $client = $client->withoutVerifying();
        }

        $sslVersion = strtolower(trim((string) config('services.moodle.ssl_version', '')));
        $sslCiphers = trim((string) config('services.moodle.ssl_ciphers', ''));
        $httpVersion = strtolower(trim((string) config('services.moodle.http_version', '')));
        $ipResolve = strtolower(trim((string) config('services.moodle.ip_resolve', '')));
        $curlOptions = [];

        $sslVersionOption = $this->resolveCurlSslVersion($sslVersion);
        if ($sslVersionOption !== null) {
            $curlOptions[CURLOPT_SSLVERSION] = $sslVersionOption;
        }
        if ($sslCiphers !== '') {
            $curlOptions[CURLOPT_SSL_CIPHER_LIST] = $sslCiphers;
        }
        $httpVersionOption = $this->resolveCurlHttpVersion($httpVersion);
        if ($httpVersionOption !== null) {
            $curlOptions[CURLOPT_HTTP_VERSION] = $httpVersionOption;
        }
        $ipResolveOption = $this->resolveCurlIpResolve($ipResolve);
        if ($ipResolveOption !== null) {
            $curlOptions[CURLOPT_IPRESOLVE] = $ipResolveOption;
        }

        if (!empty($curlOptions)) {
            $client = $client->withOptions(['curl' => $curlOptions]);
        }

        \Log::info('MoodleService TLS options', [
            'ssl_verify' => $sslVerify,
            'ssl_version' => $sslVersion !== '' ? $sslVersion : null,
            'ssl_ciphers' => $sslCiphers !== '' ? $sslCiphers : null,
            'http_version' => $httpVersion !== '' ? $httpVersion : null,
            'ip_resolve' => $ipResolve !== '' ? $ipResolve : null,
            'curl_ssl' => function_exists('curl_version') ? (curl_version()['ssl_version'] ?? null) : null,
        ]);

        return $client;
    }

    private function resolveCurlSslVersion(string $value): ?int
    {
        switch ($value) {
            case 'tlsv1':
            case 'tls1':
                return CURL_SSLVERSION_TLSv1;
            case 'tlsv1.1':
            case 'tls1.1':
            case 'tls11':
                return defined('CURL_SSLVERSION_TLSv1_1') ? CURL_SSLVERSION_TLSv1_1 : null;
            case 'tlsv1.2':
            case 'tls1.2':
            case 'tls12':
                return CURL_SSLVERSION_TLSv1_2;
            case 'tlsv1.3':
            case 'tls1.3':
            case 'tls13':
                return defined('CURL_SSLVERSION_TLSv1_3') ? CURL_SSLVERSION_TLSv1_3 : null;
            default:
                return null;
        }
    }

    private function resolveCurlHttpVersion(string $value): ?int
    {
        switch ($value) {
            case '1.0':
            case 'http1.0':
                return defined('CURL_HTTP_VERSION_1_0') ? CURL_HTTP_VERSION_1_0 : null;
            case '1.1':
            case 'http1.1':
            case 'http1':
                return CURL_HTTP_VERSION_1_1;
            case '2':
            case '2.0':
            case 'http2':
                return defined('CURL_HTTP_VERSION_2_0') ? CURL_HTTP_VERSION_2_0 : null;
            default:
                return null;
        }
    }

    private function resolveCurlIpResolve(string $value): ?int
    {
        switch ($value) {
            case 'v4':
            case 'ipv4':
                return defined('CURL_IPRESOLVE_V4') ? CURL_IPRESOLVE_V4 : null;
            case 'v6':
            case 'ipv6':
                return defined('CURL_IPRESOLVE_V6') ? CURL_IPRESOLVE_V6 : null;
            default:
                return null;
        }
    }

    private function shouldFallbackToResource(string $activityType): bool
    {
        $type = strtolower(trim($activityType));
        if ($type === 'resource') {
            return false;
        }

        // Never fallback assignment to resource; assignment must be explicit.
        if ($type === 'assignment') {
            return false;
        }

        return (bool) config('services.moodle.fallback_to_resource', true);
    }
}
