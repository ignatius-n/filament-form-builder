<?php

namespace Packstub\FormBuilder\Commands;

use Illuminate\Console\Command;
use Packstub\FormBuilder\FormBuilder;
use Packstub\FormBuilder\Models\Form;
use Packstub\FormBuilder\Models\FormSubmission;

/**
 * Apply the retention settings: delete submissions (and their files) older
 * than a form's "retention_days" (or config "submissions.retention_days"),
 * strip the IP address and user agent from those older than
 * "submissions.anonymize_after_days", and drop old webhook deliveries.
 * Schedule it daily.
 */
class PruneCommand extends Command
{
    protected $signature = 'form-builder:prune {--dry-run : Report what would go without deleting anything}';

    protected $description = 'Delete old submissions and anonymise older ones, per the retention settings.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $deleted = 0;
        $anonymized = 0;

        /** @var Form $form */
        foreach (FormBuilder::formModel()::query()->withoutGlobalScopes()->cursor() as $form) {
            $days = $form->retentionDays();

            if ($days === null) {
                continue;
            }

            $query = $form->submissions()->where('created_at', '<', now()->subDays($days));

            if ($dry) {
                $deleted += $query->count();

                continue;
            }

            /** @var FormSubmission $submission */
            foreach ($query->cursor() as $submission) {
                $submission->delete();
                $deleted++;
            }
        }

        $anonymizeAfter = (int) config('packstub-form-builder.submissions.anonymize_after_days', 0);

        if ($anonymizeAfter > 0) {
            $query = FormBuilder::submissionModel()::query()
                ->where('created_at', '<', now()->subDays($anonymizeAfter))
                ->where(fn ($query) => $query->whereNotNull('ip')->orWhereNotNull('user_agent'));

            $anonymized = $dry ? $query->count() : $query->update(['ip' => null, 'user_agent' => null]);
        }

        $keepDeliveries = (int) config('packstub-form-builder.webhooks.keep_days', 30);

        if ($keepDeliveries > 0 && ! $dry) {
            FormBuilder::webhookDeliveryModel()::query()->where('created_at', '<', now()->subDays($keepDeliveries))->delete();
        }

        $this->info(($dry ? 'Would delete ' : 'Deleted ')."{$deleted} submission(s), ".($dry ? 'would anonymise ' : 'anonymised ')."{$anonymized}.");

        return self::SUCCESS;
    }
}
