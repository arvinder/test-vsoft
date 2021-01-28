<?php

namespace App\Jobs;

use App\Models\Attachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

const PATH_PREFIX = "/mails/attachments/";
const DEFAULT_FILENAME_EXTENSTION = ".png";
const NO_OF_EMAILS_PER_FREQ = "1";
const DECAY_FREQUENCY = "2";

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $email;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Array $email)
    {
        $this->email = $email;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        $mail = new \App\Models\Mail();
        $mail->email = $this->email['mail'];
        $mail->subject = $this->email['subject'];
        $mail->body = $this->email['body'];
        $mail->save();

        $attachments = $this->email['attachments'] ?? array();
        $attachments_ = array();

        foreach($attachments as $attach) {
            $attachments_[] = $this->saveAttachment($attach);
        }

        $mail->attachments()->saveMany($attachments_);

        Redis::throttle('SendEmail')
            ->allow(NO_OF_EMAILS_PER_FREQ)  // No of emails to send
            ->every(DECAY_FREQUENCY)  // every 2 seconds
            ->then(function () use($mail) {
                Mail::send('emails.generic', ['subject'=> $mail->subject, 'body' => $mail->body], function ($message) use($mail){
                    $message->to($mail->email)->subject($mail->subject);
                    $files  = $mail->attachments()->get();
                    if(count($files)) {
                        foreach($files as $file) {
                            $message->attach($file->content, array(
                                'as' => $file->name.DEFAULT_FILENAME_EXTENSTION, // TODO: Attachment extenison details not available.
                            ));
                        }
                    }
                });
            }, function () {
                return $this->release(DECAY_FREQUENCY);
            });
//        Log::info('Email Sent ' . $mail->id);
    }

    private function saveAttachment($attach): Attachment {
        $attachment = new Attachment();

        $attachment->name = $attach['name'];  // TODO: Will the name include extension. Details not available
        $extension = DEFAULT_FILENAME_EXTENSTION;  // TODO: As a workaround for sake of completing the test
        $filename = PATH_PREFIX.$attachment->name.uniqid().$extension;

        if(Storage::disk('local')->put($filename, base64_decode($attach['content']))) {
            $attachment->content = storage_path('app').$filename;
        }

        return $attachment;
    }
}
