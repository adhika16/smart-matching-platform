import JobController from '@/actions/App/Http/Controllers/JobController';
import { archive as archiveRoute, index as jobsIndex, publish as publishRoute } from '@/routes/opportunity-owner/jobs';
import { Form, Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';

import JobForm, { type JobFormValues } from '@/components/jobs/job-form';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
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
    ai_match?: {
        score: number;
        breakdown: {
            profile_match: number;
            skills_match: number;
            experience_match: number;
        };
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
    hasSmartRanking: boolean;
    applicationStatuses: StatusOption[];
    taxonomy: {
        skills: Array<{ value: string; label: string }>;
        categories: Array<{ value: string; label: string }>;
    };
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

export default function EditJob({ job, compensationTypes, applications, hasSmartRanking, applicationStatuses, taxonomy }: EditJobProps): ReactNode {
    const statusBadgeVariant: Record<ApplicationListItem['status'], 'outline' | 'default' | 'secondary'> = {
        pending: 'outline',
        shortlisted: 'default',
        rejected: 'secondary',
    };

    // Ensure applications is always an array
    const safeApplications = Array.isArray(applications) ? applications : [];

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
                    taxonomy={taxonomy}
                    submitLabels={{
                        draft: job.status === 'published' ? 'Save as draft' : 'Save draft',
                        publish: job.status === 'published' ? 'Update & republish' : 'Save & publish',
                    }}
                />

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center justify-between gap-4">
                            <div className="flex items-center gap-2">
                                <span>Applications</span>
                                {hasSmartRanking && safeApplications.length > 0 && (
                                    <Badge variant="default" className="bg-primary/10 text-primary border-primary/20 text-xs">
                                        AI Ranked
                                    </Badge>
                                )}
                            </div>
                            <Badge variant="outline">{safeApplications.length}</Badge>
                        </CardTitle>
                        <p className="text-sm text-muted-foreground">
                            Review and update the status of creatives who have applied to this opportunity.
                            {hasSmartRanking && safeApplications.length > 0 && (
                                <span className="block mt-1 text-primary">
                                    Applications are ranked by AI matching score for better decision making.
                                </span>
                            )}
                        </p>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {safeApplications.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                You haven't received any applications yet. Once creatives apply, they'll appear here.
                            </p>
                        ) : (
                            safeApplications.map((application) => (
                                <div
                                    key={application.id}
                                    className="rounded-md border p-4"
                                >
                                    <div className="md:flex md:items-start md:justify-between md:gap-6">
                                        <div className="space-y-2 flex-1">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <h3 className="text-lg font-semibold">{application.applicant.name}</h3>
                                                <Badge variant={statusBadgeVariant[application.status]} className="capitalize">
                                                    {application.status}
                                                </Badge>
                                                {application.ai_match && (
                                                    <Badge variant="outline" className="text-xs bg-green-50 border-green-200 text-green-700">
                                                        {Math.round(application.ai_match.score * 100)}% AI Match
                                                    </Badge>
                                                )}
                                            </div>
                                            <p className="text-sm text-muted-foreground">{application.applicant.email}</p>
                                            {application.submitted_at && (
                                                <p className="text-xs text-muted-foreground">
                                                    Applied {new Date(application.submitted_at).toLocaleString()}
                                                </p>
                                            )}

                                            {/* AI Match Breakdown */}
                                            {application.ai_match && (
                                                <div className="mt-3 p-3 bg-muted/50 rounded-md">
                                                    <p className="text-xs font-medium text-muted-foreground mb-2">AI Matching Breakdown</p>
                                                    <div className="space-y-1">
                                                        <div className="flex items-center justify-between text-xs">
                                                            <span>Profile Match</span>
                                                            <span>{Math.round(application.ai_match.breakdown.profile_match * 100)}%</span>
                                                        </div>
                                                        <Progress
                                                            value={application.ai_match.breakdown.profile_match * 100}
                                                            className="h-1"
                                                        />
                                                        <div className="flex items-center justify-between text-xs">
                                                            <span>Skills Match</span>
                                                            <span>{Math.round(application.ai_match.breakdown.skills_match * 100)}%</span>
                                                        </div>
                                                        <Progress
                                                            value={application.ai_match.breakdown.skills_match * 100}
                                                            className="h-1"
                                                        />
                                                        <div className="flex items-center justify-between text-xs">
                                                            <span>Experience Level</span>
                                                            <span>{Math.round(application.ai_match.breakdown.experience_match * 100)}%</span>
                                                        </div>
                                                        <Progress
                                                            value={application.ai_match.breakdown.experience_match * 100}
                                                            className="h-1"
                                                        />
                                                    </div>
                                                </div>
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
