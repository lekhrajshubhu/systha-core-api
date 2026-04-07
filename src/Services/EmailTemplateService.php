<?php

namespace Systha\Core\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Systha\Core\Models\EmailTemplate;


class EmailTemplateService
{
    protected EmailTemplate $template;
    protected array $data;

    /**
     * Load the template and dynamic data
     */
    public function load(EmailTemplate $template, array $data): static
    {
        $this->template = $template;
        $this->data = $data;
        return $this;
    }

    /**
     * Render and return the parsed subject/content
     */
    public function render(): array
    {
        // header('Access-Control-Allow-Origin:*');
        try {
            $content = $this->template->temp_html;
            $subject = $this->template->subject;

            $vendorContent = $this->template->temp_vendor;
            $vendorSubject = $this->template->subject_vendor;

            $msgContent = $this->template->temp_msg;


            // Parse dynamic fields from text
            $dynamicFields = array_filter(array_map('trim', explode(',', $this->template->temp_json)));

            $this->validateFields($dynamicFields);

            // Replace placeholders with actual values
            foreach ($dynamicFields as $field) {
                $value = $this->data[$field] ?? '';
                $placeholders = ['{{ ' . $field . ' }}', '{{' . $field . '}}'];

                $content = str_replace($placeholders, $value, $content);
                $subject = str_replace($placeholders, $value, $subject);

                $vendorContent = str_replace($placeholders, $value, $vendorContent);
                $vendorSubject = str_replace($placeholders, $value, $vendorSubject);
                $msgContent = str_replace($placeholders, $value, $msgContent);
            }

            // dd($content);
            return [
                'subject' => $subject,
                'content' => $content,

                'vendor_subject' => $vendorSubject,
                'vendor_content' => $vendorContent,
                'msgContent' => $msgContent,
            ];
        } catch (\Throwable $th) {
            // Log the error and throw a clean exception
            Log::error('Email template rendering failed', [
                'error' => $th->getMessage(),
                'template_id' => $this->template->id ?? null,
                'data' => $this->data,
            ]);

            throw new Exception('Failed to render email template: ' . $th->getMessage());
        }
    }



    /**
     * Ensure all required dynamic fields are present in the data
     */
    protected function validateFields(array $requiredFields): void
    {
        $missing = [];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $this->data)) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new Exception('Missing dynamic fields: ' . implode(', ', $missing));
        }
    }



   
}
