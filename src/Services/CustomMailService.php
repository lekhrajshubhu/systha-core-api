<?php

namespace Systha\Core\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Systha\Core\Models\DefaultCompany;
use Systha\Core\Models\VendorDefault;
use Systha\Core\Models\EmailLog;

class CustomMailService
{
    protected array $smtpConfig = [];

    /**
     * Load SMTP config from VendorDefault if vendor is given, else from DefaultCompany
     */
    protected function loadSmtpConfig($vendor = null): void
    {
        if ($vendor) {
            $emailConfig = VendorDefault::where('vendor_id', $vendor->id ?? $vendor)
                ->where('property', 'email_credential')
                ->first();
        } else {
            $emailConfig = DefaultCompany::where('is_deleted', 0)
                ->where('property', 'email_credential')
                ->first();
        }

        if (!$emailConfig || empty($emailConfig->value)) {
            throw new \Exception('Email setup not configured in vendor or default_companies.');
        }

        $this->smtpConfig = $this->parseEmailSetup($emailConfig->value);
    }

    /**
     * Parse SMTP config (JSON or colon format)
     */
    protected function parseEmailSetup(string $raw): array
    {
        $json = json_decode($raw, true);

        if (is_array($json)) {
            $host = $json['host'] ?? null;
            $port = $json['port'] ?? null;
            $username = $json['username'] ?? null;
            $password = $json['password'] ?? null;
            $encryption = $json['encryption'] ?? 'tls';
            $fromAddress = $json['from_address'] ?? $username;
            $fromName = $json['from_name'] ?? 'Support';
        } else {
            [$host, $port, $username, $password, $encryption] = array_pad(
                explode(':', $raw, 5),
                5,
                null
            );
            $fromAddress = $username;
            $fromName = 'Support';
            $encryption = $encryption ?: 'tls';
        }

        if (!$host || !$port || !$username || !$password) {
            throw new \Exception('Invalid email setup format.');
        }

        return [
            'host' => $host,
            'port' => (int) $port,
            'username' => $username,
            'password' => $password,
            'encryption' => $encryption,
            'from_address' => $fromAddress,
            'from_name' => $fromName,
        ];
    }

    /**
     * Send Email
     */
    public function send(array $mailData, $vendor = null): array
    {
        $logConfig = [];
        try {
            // Load latest SMTP config (vendor or default)
            $this->loadSmtpConfig($vendor);
            $mailer = Mail::build([
                'transport' => 'smtp',
                'host' => $this->smtpConfig['host'],
                'port' => $this->smtpConfig['port'],
                'username' => $this->smtpConfig['username'],
                'password' => $this->smtpConfig['password'],
                'encryption' => $this->smtpConfig['encryption'],
            ]);


            $fromEmail = $mailData['from_email'] ?? $this->smtpConfig['from_address'];
            $fromName = $mailData['from_name'] ?? $this->smtpConfig['from_name'];

            $mailer->send([], [], function ($message) use ($mailData, $fromEmail, $fromName) {
                $message->to($mailData['to_email'], $mailData['to_name'] ?? null);

                if (!empty($mailData['cc'])) {
                    $message->cc($mailData['cc']);
                }
                if (!empty($mailData['bcc'])) {
                    $message->bcc($mailData['bcc']);
                }

                $message->subject($mailData['subject']);
                $message->from($fromEmail, $fromName);
                $message->html($mailData['message']);

                if (!empty($mailData['attachments'])) {
                    foreach ($mailData['attachments'] as $attachment) {
                        if (is_string($attachment)) {
                            $message->attach($attachment);
                        } elseif (is_array($attachment) && !empty($attachment['path'])) {
                            $message->attach(
                                $attachment['path'],
                                [
                                    'as' => $attachment['name'] ?? null,
                                    'mime' => $attachment['mime'] ?? null,
                                ]
                            );
                        }
                    }
                }
            });

            $logConfig = array_merge($mailData, [
                'from_email' => $fromEmail,
                'from_name' => $fromName,
                'status' => 'success',
                'error_msg' => null,
            ]);
            $this->mailLog($logConfig);

            return [
                'success' => true,
                'message' => 'Email sent successfully.',
            ];
        } catch (\Throwable $e) {
            Log::error('CustomMailService Error', [
                'error' => $e->getMessage(),
            ]);
            $logConfig = array_merge($mailData, [
                'from_email' => $fromEmail ?? null,
                'from_name' => $fromName ?? null,
                'status' => 'failed',
                'error_msg' => $e->getMessage(),
            ]);
            $this->mailLog($logConfig);

            return [
                'success' => false,
                'message' => 'Failed to send email.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function mailLog(array $config)
    {
        if (empty($config['table_name']) || empty($config['table_id'])) {
            throw new \InvalidArgumentException('table_name and table_id are required for email logging.');
        }

        $data = [
            'table_name' => $config['table_name'],
            'table_id' => $config['table_id'],
            'from' => $config['from_email'] ?? null,
            'to' => $config['to_email'] ?? null,
            'cc' => json_encode($config['cc'] ?? []),
            'subject' => $config['subject'] ?? null,
            'message' => $config['message'] ?? null,
            'sent_status' => $config['status'] ?? null,
            'sent_date' => now(),
            'error_msg' => $config['error_msg'] ?? null,
        ];

        return EmailLog::create($data);
    }
}
