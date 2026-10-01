<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BigBlueButtonService
{
    private $serverUrl;
    private $secretKey;

    public function __construct()
    {
        $this->serverUrl = config('bigbluebutton.server_url');
        $this->secretKey = config('bigbluebutton.secret_key');
        \Log::info('BBB Service - Constructor - Server URL: ' . $this->serverUrl);
        \Log::info('BBB Service - Constructor - Secret Key: ' . (strlen($this->secretKey) > 0 ? '***' : 'EMPTY'));
    }

    /**
     * Create BBB meeting
     */
    public function createMeeting($meetingId, $meetingName, $description = '', $duration = 0)
    {
        $params = [
            'meetingID' => $meetingId,
            'name' => $meetingName,
            'moderatorPW' => $this->generatePassword(),
            'attendeePW' => $this->generatePassword(),
            'logoutURL' => route('dashboard'),
            'record' => 'true',
            'duration' => $duration,
        ];

        if ($description) {
            $params['meta_description'] = $description;
        }

        $queryString = $this->buildQueryString($params);
        
        // Calculate checksum
        $checksumString = 'create' . $queryString . $this->secretKey;
        \Log::info('BBB Service - Checksum String: ' . $checksumString);
        
        $checksum = hash('sha1', $checksumString);
        
        \Log::info('BBB Service - Calculated Checksum: ' . $checksum);

        $url = $this->serverUrl . 'create?' . $queryString . '&checksum=' . $checksum;

        \Log::info('BBB Service - Create URL: ' . $url);

        try {
            $response = Http::timeout(10)->get($url);
            \Log::info('BBB Service - Response Status: ' . $response->status());
            \Log::info('BBB Service - Response Body: ' . $response->body());
            
            // Check if response status is not 200
            if ($response->status() !== 200) {
                $errorMsg = 'Server returned status: ' . $response->status();
                \Log::error('BBB Service - HTTP Error: ' . $errorMsg);
                return [
                    'success' => false,
                    'error' => $errorMsg,
                ];
            }
            
            $xml = simplexml_load_string($response);

            if ($xml === false) {
                $errors = libxml_get_errors();
                $errorMsg = !empty($errors) ? $errors[0]->message : 'XML parsing failed';
                \Log::error('BBB Service - XML Parse Error: ' . $errorMsg);
                return [
                    'success' => false,
                    'error' => 'XML Parse Error: ' . $errorMsg,
                ];
            }

            if ($xml && $xml->returncode === 'SUCCESS') {
                return [
                    'success' => true,
                    'meeting_id' => (string)$xml->meetingID,
                    'moderator_pw' => $params['moderatorPW'],
                    'attendee_pw' => $params['attendeePW'],
                ];
            }

            // Try to get error message from various possible fields
            $error = 'Unknown error';
            if ($xml) {
                $error = (string)$xml->message ?: ((string)$xml->error ?: 'Unknown error');
            }
            \Log::error('BBB Service - Create Failed: ' . $error);
            return [
                'success' => false,
                'error' => $error,
            ];
        } catch (\Exception $e) {
            \Log::error('BBB Service - Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get join URL for user
     */
    public function getJoinUrl($meetingId, $userName, $isAdmin = false)
    {
        $password = $isAdmin ? $this->getMeetingPassword($meetingId, 'moderator') : $this->getMeetingPassword($meetingId, 'attendee');

        if (!$password) {
            return null;
        }

        $params = [
            'meetingID' => $meetingId,
            'fullName' => $userName,
            'password' => $password,
            'userID' => auth()->id(),
        ];

        $queryString = $this->buildQueryString($params);
        $checksum = hash('sha1', 'join' . $queryString . $this->secretKey);

        return $this->serverUrl . 'join?' . $queryString . '&checksum=' . $checksum;
    }

    /**
     * Get meetings info
     */
    public function getMeetingInfo($meetingId)
    {
        $params = [
            'meetingID' => $meetingId,
        ];

        $queryString = $this->buildQueryString($params);
        $checksum = hash('sha1', 'getMeetingInfo' . $queryString . $this->secretKey);

        $url = $this->serverUrl . 'getMeetingInfo?' . $queryString . '&checksum=' . $checksum;

        $response = Http::get($url);
        $xml = simplexml_load_string($response);

        if ($xml && $xml->returncode === 'SUCCESS') {
            return [
                'success' => true,
                'data' => $xml,
            ];
        }

        return [
            'success' => false,
        ];
    }

    /**
     * End meeting
     */
    public function endMeeting($meetingId, $password = '')
    {
        $params = [
            'meetingID' => $meetingId,
            'password' => $password,
        ];

        $queryString = $this->buildQueryString($params);
        $checksum = hash('sha1', 'end' . $queryString . $this->secretKey);

        $url = $this->serverUrl . 'end?' . $queryString . '&checksum=' . $checksum;

        $response = Http::get($url);
        $xml = simplexml_load_string($response);

        return $xml && $xml->returncode === 'SUCCESS';
    }

    /**
     * Get recordings
     */
    public function getRecordings($meetingId = null)
    {
        $params = [];
        if ($meetingId) {
            $params['meetingID'] = $meetingId;
        }

        $queryString = $this->buildQueryString($params);
        $checksum = hash('sha1', 'getRecordings' . $queryString . $this->secretKey);

        $url = $this->serverUrl . 'getRecordings?' . $queryString . '&checksum=' . $checksum;

        $response = Http::get($url);
        $xml = simplexml_load_string($response);

        if ($xml && $xml->returncode === 'SUCCESS') {
            $recordings = [];
            if (isset($xml->recordings->recording)) {
                foreach ($xml->recordings->recording as $rec) {
                    $recordings[] = [
                        'recordID' => (string)$rec->recordID,
                        'meetingID' => (string)$rec->meetingID,
                        'name' => (string)$rec->name,
                        'published' => (string)$rec->published,
                        'startTime' => (int)$rec->startTime,
                        'endTime' => (int)$rec->endTime,
                        'playbacks' => $rec->playback,
                    ];
                }
            }
            return [
                'success' => true,
                'recordings' => $recordings,
            ];
        }

        return [
            'success' => false,
        ];
    }

    /**
     * Delete recording
     */
    public function deleteRecording($recordingId)
    {
        $params = [
            'recordID' => $recordingId,
        ];

        $queryString = $this->buildQueryString($params);
        $checksum = hash('sha1', 'deleteRecordings' . $queryString . $this->secretKey);

        $url = $this->serverUrl . 'deleteRecordings?' . $queryString . '&checksum=' . $checksum;

        $response = Http::get($url);
        $xml = simplexml_load_string($response);

        return $xml && $xml->returncode === 'SUCCESS';
    }

    /**
     * Publish/unpublish recording
     */
    public function publishRecording($recordingId, $publish = true)
    {
        $params = [
            'recordID' => $recordingId,
            'publish' => $publish ? 'true' : 'false',
        ];

        $queryString = $this->buildQueryString($params);
        $checksum = hash('sha1', 'publishRecordings' . $queryString . $this->secretKey);

        $url = $this->serverUrl . 'publishRecordings?' . $queryString . '&checksum=' . $checksum;

        $response = Http::get($url);
        $xml = simplexml_load_string($response);

        return $xml && $xml->returncode === 'SUCCESS';
    }

    /**
     * Build query string from parameters
     */
    private function buildQueryString($params)
    {
        // Sort parameters alphabetically for checksum calculation
        ksort($params);
        
        $queryString = '';
        foreach ($params as $key => $value) {
            $queryString .= $key . '=' . urlencode($value) . '&';
        }
        return rtrim($queryString, '&');
    }

    /**
     * Generate secure password
     */
    private function generatePassword($length = 16)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $password;
    }

    /**
     * Get meeting password from database
     */
    private function getMeetingPassword($meetingId, $type = 'attendee')
    {
        $meeting = \App\Models\BbgMeeting::where('meeting_id', $meetingId)->first();
        if (!$meeting) {
            return null;
        }
        return $type === 'moderator' ? $meeting->moderator_password : $meeting->attendee_password;
    }
}
