<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\Announcement;
use App\Repositories\UserRepo;

class HomeController extends Controller
{
    protected $user;
    public function __construct(UserRepo $user)
    {
        $this->user = $user;
    }


    public function index()
    {
        return redirect()->route('dashboard');
    }

    public function privacy_policy()
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Qs::getSetting('phone');
        return view('pages.other.privacy_policy', $data);
    }

    public function terms_of_use()
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Qs::getSetting('phone');
        return view('pages.other.terms_of_use', $data);
    }

    public function dashboard()
    {
        $user = auth()->user();
        
        // Redirect accountants to their dedicated dashboard
        if(Qs::userIsAccountant()){
            $announcements = Announcement::where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->with('createdBy')
                ->get();
            
            return view('pages.support_team.dashboard_accountant', [
                'announcements' => $announcements
            ]);
        }
        
        $d=[];
        
        if(Qs::userIsTeamSAT()){
            $d['users'] = $this->user->getAll();
        }

        // Get active announcements - show all active ones without strict date filtering
        $d['announcements'] = Announcement::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->with('createdBy')
            ->get();

        $d['guide'] = $this->resolveGuideForUserType((string) $user->user_type);

        return view('pages.support_team.dashboard', $d);
    }

    public function downloadGuide()
    {
        $guide = $this->resolveGuideForUserType((string) auth()->user()->user_type);

        if (!$guide) {
            return back()->with('flash_danger', 'No guide assigned for your account.');
        }

        $fullPath = public_path($guide['path']);

        if (!is_file($fullPath)) {
            return back()->with('flash_danger', 'Guide file not found. Please contact the administrator.');
        }

        return response()->download($fullPath, basename($fullPath));
    }

    private function resolveGuideForUserType(string $userType): ?array
    {
        $normalizedType = strtolower($userType);
        if ($normalizedType === 'super_admin') {
            $normalizedType = 'admin';
        }

        $guides = [
            'parent' => [
                'title' => 'Parent Guide',
                'path' => 'guides/parent-guide.pdf',
                'button_text' => 'Parent Guide to Monitor',
            ],
            'student' => [
                'title' => 'Student Guide',
                'path' => 'guides/student-guide.pdf',
                'button_text' => 'Student Guide to Learning',
            ],
            'teacher' => [
                'title' => 'Teacher Guide',
                'path' => 'guides/teacher-guide.pdf',
                'button_text' => 'Teacher Guide to Teaching',
            ],
            'admin' => [
                'title' => 'Admin Guide',
                'path' => 'guides/NextGene School.pdf',
                'button_text' => 'Admin Guide to Manage',
            ],
            'accountant' => [
                'title' => 'Accountant Guide',
                'path' => 'guides/accountant-guide.pdf',
                'button_text' => 'Accountant Guide to Finance',
            ],
        ];

        if (!isset($guides[$normalizedType])) {
            return null;
        }

        $guide = $guides[$normalizedType];
        $guide['exists'] = is_file(public_path($guide['path']));

        return $guide;
    }
}
