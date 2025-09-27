import JobController from '@/actions/App/Http/Controllers/JobController';
import { archive as archiveRoute, index as jobsIndex, publish as publishRoute } from '@/routes/opportunity-owner/jobs';
import { Form, Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';

import JobForm, { type JobFormValues } from '@/components/jobs/job-form';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';

interface ApplicationListItem {
    id: number;
    status: 'pending' | 'shortlisted' | 'rejected';
    cover_letter?: string | null;
    submitted_at?: string | null;
    applicant: {
        id: number;
        name: string;
        email: string;
    };
}

interface StatusOption {
    value: ApplicationListItem['status'];
    label: string;
}

interface EditJobProps {
    job: JobFormValues & {
        id: number;
        status: 'draft' | 'published' | 'archived';
        published_at?: string | null;
    };
    compensationTypes: Array<{ value: string; label: string }>;
    applications: ApplicationListItem[];
    applicationStatuses: StatusOption[];
}

const statusCopy: Record<'draft' | 'published' | 'archived', { label: string; description: string; badge: 'outline' | 'default' | 'secondary' }> = {
    draft: {
        label: 'Draft',
        description: 'Your job is not visible to creatives yet. Publish when you are ready.',
        badge: 'outline',
    },
    published: {
        label: 'Published',
        description: 'Creatives can view and apply to this job.',
        badge: 'default',
    },
    archived: {
        label: 'Archived',
        description: 'This job is hidden from creatives but remains in your records.',
        badge: 'secondary',
    },
};

export default function Edit({ job, compensationTypes, applications, applicationStatuses }: EditJobProps) {
    const statusBadgeVariant: Record<ApplicationListItem['status'], 'outline' | 'default' | 'secondary'> = {
        pending: 'outline',
        shortlisted: 'default',
        rejected: 'secondary',
    };

    return (
        <AppLayout>
            <Head title={`Edit job · ${job.title}`} />

            <div className="container mx-auto max-w-4xl space-y-8 py-8">
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-semibold">Edit job</h1>
                        <p className="text-muted-foreground">
                            Update details, publish changes, or archive this opportunity.
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button asChild variant="outline">
                            <Link href={jobsIndex().url}>Back to jobs</Link>
                        </Button>
                        <DangerButton
                            route={JobController.destroy(job.id).url}
                            method="delete"
                            confirm
                        >
                            Delete job
                        </DangerButton>
                    </div>
                </div>

                <Card>
                    <CardHeader className="flex flex-col gap-2">
                        <CardTitle className="flex items-center gap-3">
                            <span>Job status</span>
                            <Badge variant={statusCopy[job.status].badge}>{statusCopy[job.status].label}</Badge>
                        </CardTitle>
                        <p className="text-sm text-muted-foreground">
                            {statusCopy[job.status].description}
                        </p>
                        {job.published_at && (
                            <p className="text-xs text-muted-foreground">
                                Published {new Date(job.published_at).toLocaleString()}
                            </p>
                        )}
                    </CardHeader>
                </Card>

                {job.status !== 'published' ? (
                    <QuickActionCard
                        title="Ready to go live?"
                        description="Publish this opportunity so creatives can discover it."
                        actionLabel="Publish now"
                        actionRoute={publishRoute(job.id).url}
                        method="patch"
                    />
                ) : (
                    <QuickActionCard
                        title="Need to pause applications?"
                        description="Archive the job to hide it from creatives while keeping it for reference."
                        actionLabel="Archive job"
                        actionRoute={archiveRoute(job.id).url}
                        method="patch"
                        variant="outline"
                    />
                )}

                <JobForm
                    action={JobController.update(job.id).url}
                    method="put"
                    job={job}
                    compensationTypes={compensationTypes}
                    submitLabels={{
                        draft: job.status === 'published' ? 'Save as draft' : 'Save draft',
                        publish: job.status === 'published' ? 'Update & republish' : 'Save & publish',
                    }}
                />

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center justify-between gap-4">
                            <span>Applications</span>
                            <Badge variant="outline">{applications.length}</Badge>
                        </CardTitle>
                        <p className="text-sm text-muted-foreground">
                            Review and update the status of creatives who have applied to this opportunity.
                        </p>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {applications.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                You haven’t received any applications yet. Once creatives apply, they’ll appear here.
                            </p>
                        ) : (
                            applications.map((application) => (
                                <div
                                    key={application.id}
                                    className="rounded-md border p-4 md:flex md:items-start md:justify-between md:gap-6"
                                >
                                    <div className="space-y-2">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <h3 className="text-lg font-semibold">{application.applicant.name}</h3>
                                            <Badge variant={statusBadgeVariant[application.status]} className="capitalize">
                                                {application.status}
                                            </Badge>
                                        </div>
                                        <p className="text-sm text-muted-foreground">{application.applicant.email}</p>
                                        {application.submitted_at && (
                                            <p className="text-xs text-muted-foreground">
                                                Applied {new Date(application.submitted_at).toLocaleString()}
                                            </p>
                                        )}
                                        {application.cover_letter && (
                                            <div className="rounded-md bg-muted p-3 text-sm">
                                                <p className="whitespace-pre-line">{application.cover_letter}</p>
                                            </div>
                                        )}
                                    </div>

                                    <Form
                                        method="patch"
                                        action={`/opportunity-owner/jobs/${job.id}/applications/${application.id}`}
                                        className="mt-4 flex flex-col gap-2 md:mt-0 md:w-56"
                                    >
                                        {({ processing }) => (
                                            <>
                                                <label className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                                    Update status
                                                </label>
                                                <select
                                                    name="status"
                                                    defaultValue={application.status}
                                                    className="rounded-md border border-input bg-background px-3 py-2 text-sm"
                                                >
                                                    {applicationStatuses.map((option) => (
                                                        <option key={option.value} value={option.value}>
                                                            {option.label}
                                                        </option>
                                                    ))}
                                                </select>
                                                <Button type="submit" size="sm" disabled={processing}>
                                                    {processing ? 'Saving…' : 'Save' }
                                                </Button>
                                            </>
                                        )}
                                    </Form>
                                </div>
                            ))
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

interface QuickActionCardProps {
    title: string;
    description: string;
    actionLabel: string;
    actionRoute: string;
    method: 'patch';
    variant?: 'default' | 'outline';
}

function QuickActionCard({ title, description, actionLabel, actionRoute, method, variant = 'default' }: QuickActionCardProps) {
    return (
        <Card>
            <CardContent className="flex flex-col gap-4 py-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 className="text-lg font-semibold">{title}</h2>
                    <p className="text-sm text-muted-foreground">{description}</p>
                </div>
                <Form action={actionRoute} method={method} className="md:w-auto">
                    {({ processing }) => (
                        <Button type="submit" variant={variant} disabled={processing}>
                            {processing ? 'Processing…' : actionLabel}
                        </Button>
                    )}
                </Form>
            </CardContent>
        </Card>
    );
}

interface DangerButtonProps {
    route: string;
    method: 'delete';
    children: ReactNode;
    confirm?: boolean;
}

function DangerButton({ route, method, children, confirm = false }: DangerButtonProps) {
    const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        if (confirm && !window.confirm('This will permanently delete the job. Continue?')) {
            event.preventDefault();
        }
    };

    return (
        <Form action={route} method={method} onSubmit={handleSubmit}>
            {({ processing }) => (
                <Button type="submit" variant="destructive" disabled={processing}>
                    {processing ? 'Deleting…' : children}
                </Button>
            )}
        </Form>
    );
}
