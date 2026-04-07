<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Systha\Core\Models\InquiryModel;

class BackfillInquiryNumbers extends Command
{
    protected $signature = 'inquiry:backfill-enq-no {--force : Overwrite existing inquiry numbers}';

    protected $description = 'Generate inquiry numbers in INQ-ymd-XXXX format using each record created_at date';

    public function handle(): int
    {
        $query = InquiryModel::query()->orderBy('id');

        if (! $this->option('force')) {
            $query->where(function ($builder) {
                $builder->whereNull('enq_no')
                    ->orWhere('enq_no', '');
            });
        }

        $updated = 0;

        $query->chunkById(100, function ($inquiries) use (&$updated) {
            foreach ($inquiries as $inquiry) {
                $date = $inquiry->created_at;
                $enqNo = $this->generateUniqueEnqNo($date?->copy());

                $inquiry->forceFill([
                    'enq_no' => $enqNo,
                ])->save();

                $updated++;
            }
        });

        $this->info("Updated {$updated} inquiry record(s).");

        return self::SUCCESS;
    }

    protected function generateUniqueEnqNo($date): string
    {
        do {
            $enqNo = InquiryModel::generateEnqNo($date);
        } while (InquiryModel::query()->where('enq_no', $enqNo)->exists());

        return $enqNo;
    }
}
