import JobController from '@/actions/App/Http/Controllers/JobController';
import { archive as archiveRoute, create as jobsCreate, edit as jobsEdit, publish as publishRoute } from '@/routes/opportunity-owner/jobs';
import { Form, Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';

interface JobListItem {
    id: number;
    title: string;
    status: 'draft' | 'published' | 'archived';
    published_at?: string | null;
    created_at?: string | null;
    updated_at?: string | null;
}

interface JobsIndexProps {
    jobs: {
        data: JobListItem[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}

const statusVariantMap: Record<JobListItem['status'], 'outline' | 'default' | 'secondary'> = {
    draft: 'outline',
    published: 'default',
    archived: 'secondary',
};

export default function Index({ jobs }: JobsIndexProps) {
    const formatDate = (value?: string | null) => {
        if (!value) return '—';
        return new Date(value).toLocaleDateString();
    };

    return (
        <AppLayout>
            <Head title="Jobs" />

            <div className="container mx-auto space-y-8 py-8">
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-semibold">Your job postings</h1>
                        <p className="text-muted-foreground">
                            Manage the opportunities you share with the creative community.
                        </p>
                    </div>
                    <Button asChild>
                        <Link href={jobsCreate().url}>Post a job</Link>
                    </Button>
                </div>

                {jobs.data.length === 0 ? (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center justify-between">
                                <span>No job posts yet</span>
                                <Button asChild size="sm">
                                    <Link href={jobsCreate().url}>Create your first job</Link>
                                </Button>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-muted-foreground">
                                When you share an opportunity, it will appear here with quick actions to manage it.
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="space-y-4">
                        {jobs.data.map((job) => (
                            <Card key={job.id}>
                                <CardContent className="flex flex-col gap-4 py-6 md:flex-row md:items-center md:justify-between">
                                    <div className="space-y-2">
                                        <div className="flex items-center gap-3">
                                            <h2 className="text-xl font-semibold">{job.title}</h2>
                                            <Badge variant={statusVariantMap[job.status]} className="capitalize">
                                                {job.status}
                                            </Badge>
                                        </div>
                                        <div className="text-sm text-muted-foreground space-x-4">
                                            <span>Created {formatDate(job.created_at)}</span>
                                            <span>Updated {formatDate(job.updated_at)}</span>
                                            <span>Published {formatDate(job.published_at)}</span>
                                        </div>
                                    </div>

                                    <div className="flex flex-wrap items-center gap-2">
                                        <Button asChild variant="outline" size="sm">
                                            <Link href={jobsEdit(job.id).url}>Edit</Link>
                                        </Button>

                                        {job.status !== 'published' && (
                                            <FormButton
                                                route={publishRoute(job.id).url}
                                                method="patch"
                                                variant="default"
                                            >
                                                Publish
                                            </FormButton>
                                        )}

                                        {job.status === 'published' && (
                                            <FormButton
                                                route={archiveRoute(job.id).url}
                                                method="patch"
                                                variant="outline"
                                            >
                                                Archive
                                            </FormButton>
                                        )}

                                        <FormButton
                                            route={JobController.destroy(job.id).url}
                                            method="delete"
                                            variant="ghost"
                                            confirm
                                        >
                                            Delete
                                        </FormButton>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

interface FormButtonProps {
    route: string;
    method: 'patch' | 'delete';
    variant?: 'default' | 'outline' | 'ghost';
    children: ReactNode;
    confirm?: boolean;
}

function FormButton({ route, method, variant = 'default', children, confirm = false }: FormButtonProps) {
    const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        if (confirm && !window.confirm('Are you sure you want to continue?')) {
            event.preventDefault();
        }
    };

    return (
        <Form action={route} method={method} onSubmit={handleSubmit} className="inline">
            {({ processing }) => (
                <Button type="submit" size="sm" variant={variant} disabled={processing}>
                    {processing ? 'Processing…' : children}
                </Button>
            )}
        </Form>
    );
}
