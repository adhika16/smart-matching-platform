import JobController from '@/actions/App/Http/Controllers/JobController';
import { index as jobsIndex } from '@/routes/opportunity-owner/jobs';
import { Head, Link } from '@inertiajs/react';

import JobForm from '@/components/jobs/job-form';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';

interface CreateJobProps {
    compensationTypes: Array<{ value: string; label: string }>;
    taxonomy: {
        skills: Array<{ value: string; label: string }>;
        categories: Array<{ value: string; label: string }>;
    };
}

export default function Create({ compensationTypes, taxonomy }: CreateJobProps) {
    return (
        <AppLayout>
            <Head title="Post a job" />

            <div className="container mx-auto max-w-4xl space-y-8 py-8">
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-semibold">Post a new opportunity</h1>
                        <p className="text-muted-foreground">
                            Share your opportunity with creatives across the community.
                        </p>
                    </div>
                    <Button asChild variant="outline">
                        <Link href={jobsIndex()}>Back to jobs</Link>
                    </Button>
                </div>

                <JobForm
                    action={JobController.store().url}
                    method="post"
                    compensationTypes={compensationTypes}
                    taxonomy={taxonomy}
                    submitLabels={{
                        draft: 'Save draft',
                        publish: 'Publish job',
                    }}
                />
            </div>
        </AppLayout>
    );
}
