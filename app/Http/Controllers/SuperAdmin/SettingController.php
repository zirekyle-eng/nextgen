<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettingUpdate;
use App\Models\AcademicSession;
use App\Repositories\MyClassRepo;
use App\Repositories\SettingRepo;
use App\Services\WhatsappGateway;
use App\Services\WhatsappSettings;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected $setting, $my_class;

    public function __construct(SettingRepo $setting, MyClassRepo $my_class)
    {
        $this->setting = $setting;
        $this->my_class = $my_class;
    }

    public function index()
    {
         $s = $this->setting->all();
         $d['class_types'] = $this->my_class->getTypes();
         $d['academic_sessions'] = AcademicSession::query()
             ->orderByDesc('start_date')
             ->get(['id', 'name']);
         $d['s'] = $s->flatMap(function($s){
            return [$s->type => $s->description];
        });
        return view('pages.super_admin.settings', $d);
    }

    public function update(SettingUpdate $req)
    {
        $sets = $req->except('_token', '_method', 'logo');
        $sets['lock_exam'] = $sets['lock_exam'] == 1 ? 1 : 0;

        // Map fee inputs (nt_fee_*) to persisted setting keys (next_term_fees_*)
        foreach ($sets as $key => $value) {
            if (strpos($key, 'nt_fee_') === 0) {
                $suffix = substr($key, strlen('nt_fee_'));
                $sets['next_term_fees_' . $suffix] = $value;
                unset($sets[$key]);
            }
        }

        $keys = array_keys($sets);
        $values = array_values($sets);
        for($i=0; $i<count($sets); $i++){
            $this->setting->update($keys[$i], $values[$i]);
        }

        if($req->hasFile('logo')) {
            $logo = $req->file('logo');
            $f = Qs::getFileMetaData($logo);
            $f['name'] = 'logo.' . $f['ext'];
            $f['path'] = $logo->storeAs(Qs::getPublicUploadPath(), $f['name']);
            $logo_path = asset('storage/' . $f['path']);
            $this->setting->update('logo', $logo_path);
        }

        return back()->with('flash_success', __('msg.update_ok'));

    }

    public function whatsappTest(
        Request $request,
        WhatsappGateway $whatsappGateway,
        WhatsappSettings $whatsappSettings
    ) {
        $request->validate([
            'whatsapp_test_number' => 'required|string|min:6',
            'whatsapp_test_message' => 'nullable|string|max:500',
        ]);

        if (!$whatsappSettings->enabled()) {
            return back()->with('flash_danger', 'WhatsApp notifications are disabled.');
        }

        $number = trim((string) $request->input('whatsapp_test_number'));
        $message = trim((string) $request->input('whatsapp_test_message'));
        if ($message === '') {
            $message = 'Test WhatsApp message from the system.';
        }

        $sent = $whatsappGateway->sendMessage($number, $message, ['type' => 'test']);

        if ($sent) {
            return back()->with('flash_success', 'WhatsApp test message sent.');
        }

        return back()->with('flash_danger', 'WhatsApp test failed. Check gateway and number.');
    }
}
