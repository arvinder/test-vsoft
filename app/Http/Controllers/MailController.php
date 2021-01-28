<?php

namespace App\Http\Controllers;

use App\Http\Resources\MailResource;
use App\Jobs\SendEmail;
use App\Models\Mail;
use Illuminate\Http\Request;

class MailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return MailResource
     */
    public function index()
    {
        $emails = Mail::with('attachments')->paginate(5);
        return new MailResource($emails);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function send(Request $request) {
        $rules = [
            'emails' => 'required|array',
            'emails.*.mail' => 'required|email:rfc,dns',
            'emails.*.subject' => 'required|string',
            'emails.*.body' => 'required|string',
            'emails.*.attachments' => 'sometimes|array',
            'emails.*.attachments.*.name' => 'required_if:email.*.attachments,*|string',
            'emails.*.attachments.*.content' => 'required_if:email.*.attachments,*|string',
        ];
        $validated = $request->validate($rules);
        foreach($validated['emails'] as $email) {
            // Queue the Job to process and send email
            SendEmail::dispatch($email);
        }
        return ["message" => "success", "errors" => null];
    }
}
