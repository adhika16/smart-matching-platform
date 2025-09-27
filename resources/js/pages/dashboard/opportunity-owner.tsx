import { Head, Link } from '@inertiajs/react';
import { Building2, Users, CheckCircle2, Settings, Plus, AlertTriangle, ArrowUpRight, Mail } from 'lucide-react';
import opportunityOwnerRoutes from '@/routes/opportunity-owner';
import creativeRoutes from '@/routes/creative';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';

interface OpportunityOwnerProps {
    user: {
        name: string;
        email: string;
        user_type: 'opportunity_owner';
        profile_completion_score: number;
    };
    profile?: {
        company_name?: string;
        company_description?: string;
        company_website?: string;
        company_size?: string;
        industry?: string;
        is_verified?: boolean;
    };
    completionScore: number;
    profileComplete: boolean;
    isVerified: boolean;
    jobStats: {
        published: number;
        draft: number;
        archived: number;
    };
    applicationStats: {
        total: number;
        pending: number;
    };
    recentApplications?: {
        id: number;
        status: string;
        submitted_at?: string | null;
        applicant?: {
            name?: string | null;
            email?: string | null;
        } | null;
        job?: {
            id?: number | null;
            title?: string | null;
            slug?: string | null;
            status?: string | null;
        } | null;
    }[];
    jobApplicationOverview?: {
        id: number;
        title: string;
        slug?: string | null;
        status: string;
        published_at?: string | null;
        applications_count: number;
        pending_count: number;
        shortlisted_count: number;
    }[];
}

export default function OpportunityOwner({
    user,
    profile,
    completionScore,
    profileComplete,
    isVerified,
    jobStats,
    applicationStats,
    recentApplications = [],
    jobApplicationOverview = [],
}: OpportunityOwnerProps) {
    const statusVariant = (status: string) => {
        switch (status) {
            case 'shortlisted':
                return 'default' as const;
            case 'rejected':
                return 'destructive' as const;
            default:
                return 'secondary' as const;
        }
    };

    const statusLabel: Record<string, string> = {
        pending: 'Pending review',
        shortlisted: 'Shortlisted',
        rejected: 'Rejected',
    };

    return (
        <AppLayout>
            <Head title="Opportunity Owner Dashboard" />

            <div className="container mx-auto py-8">
                <div className="mb-8">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-bold mb-2">Welcome back, {user.name}!</h1>
                            <p className="text-muted-foreground">
                                Manage your company profile and find amazing talent
                            </p>
                        </div>
                        <div className="text-right">
                            {profile?.company_name && (
                                <p className="font-medium">{profile.company_name}</p>
                            )}
                            <Badge variant={isVerified ? "default" : "secondary"} className="mt-1">
                                {isVerified ? (
                                    <>
                                        <CheckCircle2 className="mr-1 h-3 w-3" />
                                        Verified
                                    </>
                                ) : (
                                    "Unverified"
                                )}
                            </Badge>
                        </div>
                    </div>
                </div>

                {!isVerified && (
                    <div className="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-900">
                        <div className="flex items-start gap-3">
                            <AlertTriangle className="mt-1 h-5 w-5" />
                            <div>
                                <p className="font-semibold">Verification pending</p>
                                <p className="text-sm">
                                    Thanks for completing your company profile. An administrator is reviewing your details now. You’ll receive access to post opportunities once your account is approved.
                                </p>
                            </div>
                        </div>
                    </div>
                )}

                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mb-8">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Profile Completion
                            </CardTitle>
                            <Building2 className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{completionScore}%</div>
                            <Progress value={completionScore} className="mt-2" />
                            {!profileComplete && (
                                <p className="text-xs text-muted-foreground mt-2">
                                    Complete your profile to start posting opportunities
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Active Opportunities
                            </CardTitle>
                            <Plus className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{jobStats.published}</div>
                            <p className="text-xs text-muted-foreground mt-2">
                                {jobStats.published === 0
                                    ? 'No active opportunities yet'
                                    : `${jobStats.published} currently live`}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Applications Received
                            </CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{applicationStats.total}</div>
                            <p className="text-xs text-muted-foreground mt-2">
                                {applicationStats.pending} waiting for review
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                            <CardDescription>
                                Get started with your company profile
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <Button asChild className="w-full justify-start">
                                <Link href="/profile/setup">
                                    <Settings className="mr-2 h-4 w-4" />
                                    {profileComplete ? 'Update Profile' : 'Complete Profile'}
                                </Link>
                            </Button>

                            {profileComplete ? (
                                <Button asChild className="w-full justify-start">
                                    <Link href={opportunityOwnerRoutes.jobs.create.url()}>
                                        <Plus className="mr-2 h-4 w-4" />
                                        Post a job
                                    </Link>
                                </Button>
                            ) : (
                                <Button
                                    variant="outline"
                                    className="w-full justify-start"
                                    disabled
                                >
                                    <Plus className="mr-2 h-4 w-4" />
                                    Complete profile to post jobs
                                </Button>
                            )}

                            <Button asChild variant="outline" className="w-full justify-start">
                                <Link href={opportunityOwnerRoutes.jobs.index.url()}>
                                    <Users className="mr-2 h-4 w-4" />
                                    Manage jobs
                                </Link>
                            </Button>

                            <Button variant="outline" className="w-full justify-start" disabled>
                                <Users className="mr-2 h-4 w-4" />
                                Browse Talent (Coming Soon)
                            </Button>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Company Summary</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {profile?.company_name ? (
                                <div className="space-y-4">
                                    <div>
                                        <h4 className="font-medium">Company</h4>
                                        <p className="text-sm text-muted-foreground mt-1">
                                            {profile.company_name}
                                        </p>
                                    </div>

                                    {profile.company_description && (
                                        <div>
                                            <h4 className="font-medium">Description</h4>
                                            <p className="text-sm text-muted-foreground mt-1">
                                                {profile.company_description}
                                            </p>
                                        </div>
                                    )}

                                    {profile.industry && (
                                        <div>
                                            <h4 className="font-medium">Industry</h4>
                                            <Badge variant="secondary" className="mt-1">
                                                {profile.industry}
                                            </Badge>
                                        </div>
                                    )}

                                    {profile.company_size && (
                                        <div>
                                            <h4 className="font-medium">Company Size</h4>
                                            <Badge variant="outline" className="mt-1">
                                                {profile.company_size}
                                            </Badge>
                                        </div>
                                    )}

                                    {profile.company_website && (
                                        <div>
                                            <h4 className="font-medium">Website</h4>
                                            <a
                                                href={profile.company_website}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-sm text-primary hover:underline"
                                            >
                                                {profile.company_website}
                                            </a>
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <div className="text-center py-8">
                                    <Building2 className="mx-auto h-8 w-8 text-muted-foreground mb-4" />
                                    <p className="text-muted-foreground">
                                        Complete your profile to see a summary here
                                    </p>
                                    <Button asChild className="mt-4">
                                        <Link href="/profile/setup">
                                            Get Started
                                        </Link>
                                    </Button>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card className="md:col-span-2">
                        <CardHeader>
                            <CardTitle>Recent Applications</CardTitle>
                            <CardDescription>
                                Stay on top of new creatives reaching out
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {recentApplications.length === 0 ? (
                                <div className="rounded-lg border border-dashed p-6 text-center text-sm text-muted-foreground">
                                    You haven&apos;t received any applications yet. Once creatives apply to your live jobs,
                                    you&apos;ll see them here.
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    {recentApplications.map((application) => {
                                        const submittedDate = application.submitted_at
                                            ? new Date(application.submitted_at)
                                            : null;

                                        return (
                                            <div
                                                key={application.id}
                                                className="rounded-lg border bg-card p-4 shadow-sm"
                                            >
                                                <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                                    <div className="space-y-1">
                                                        <div className="flex items-center gap-2">
                                                            <h3 className="text-base font-semibold">
                                                                {application.job?.title ?? 'Opportunity'}
                                                            </h3>
                                                            {application.job?.status === 'published' && (
                                                                <Badge variant="outline" className="text-xs">
                                                                    Live
                                                                </Badge>
                                                            )}
                                                            {application.job?.status === 'draft' && (
                                                                <Badge variant="secondary" className="text-xs">
                                                                    Draft
                                                                </Badge>
                                                            )}
                                                        </div>
                                                        <p className="text-sm text-muted-foreground">
                                                            {application.applicant?.name ?? 'Creative applicant'}
                                                            {application.applicant?.email && (
                                                                <span className="flex items-center gap-1 text-xs">
                                                                    <Mail className="h-3 w-3" />
                                                                    {application.applicant.email}
                                                                </span>
                                                            )}
                                                        </p>
                                                        <p className="text-xs text-muted-foreground">
                                                            {submittedDate
                                                                ? `Submitted ${submittedDate.toLocaleString()}`
                                                                : 'Submitted recently'}
                                                        </p>
                                                    </div>

                                                    <div className="flex flex-wrap items-center gap-2">
                                                        <Badge variant={statusVariant(application.status)}>
                                                            {statusLabel[application.status] ?? application.status}
                                                        </Badge>

                                                        {application.job?.id && (
                                                            <Button variant="outline" size="sm" asChild>
                                                                <Link href={opportunityOwnerRoutes.jobs.edit.url({ job: application.job.id })}>
                                                                    Manage job
                                                                    <ArrowUpRight className="ml-1 h-4 w-4" />
                                                                </Link>
                                                            </Button>
                                                        )}

                                                        {application.job?.slug && (
                                                            <Button variant="secondary" size="sm" asChild>
                                                                <Link href={creativeRoutes.jobs.show.url({ job: application.job.slug })}>
                                                                    View posting
                                                                </Link>
                                                            </Button>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card className="md:col-span-2">
                        <CardHeader>
                            <CardTitle>Job Application Overview</CardTitle>
                            <CardDescription>
                                Review applicant momentum across your recent postings
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {jobApplicationOverview.length === 0 ? (
                                <div className="rounded-lg border border-dashed p-6 text-center text-sm text-muted-foreground">
                                    Publish a job to start receiving applications.
                                </div>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="w-full min-w-[600px] text-sm">
                                        <thead>
                                            <tr className="text-left text-xs uppercase text-muted-foreground">
                                                <th className="py-2 pr-4">Job</th>
                                                <th className="py-2 pr-4">Status</th>
                                                <th className="py-2 pr-4 text-center">Applicants</th>
                                                <th className="py-2 pr-4 text-center">Pending</th>
                                                <th className="py-2 pr-4 text-center">Shortlisted</th>
                                                <th className="py-2 text-right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y">
                                            {jobApplicationOverview.map((job) => (
                                                <tr key={job.id} className="align-middle">
                                                    <td className="py-3 pr-4">
                                                        <div className="font-medium">{job.title}</div>
                                                        {job.published_at && (
                                                            <div className="text-xs text-muted-foreground">
                                                                Published {new Date(job.published_at).toLocaleDateString()}
                                                            </div>
                                                        )}
                                                    </td>
                                                    <td className="py-3 pr-4">
                                                        <Badge variant={job.status === 'published' ? 'default' : job.status === 'draft' ? 'secondary' : 'outline'}>
                                                            {job.status}
                                                        </Badge>
                                                    </td>
                                                    <td className="py-3 pr-4 text-center font-semibold">{job.applications_count}</td>
                                                    <td className="py-3 pr-4 text-center">{job.pending_count}</td>
                                                    <td className="py-3 pr-4 text-center">{job.shortlisted_count}</td>
                                                    <td className="py-3 text-right">
                                                        <div className="flex justify-end gap-2">
                                                            <Button variant="outline" size="sm" asChild>
                                                                <Link href={opportunityOwnerRoutes.jobs.edit.url({ job: job.id })}>
                                                                    Manage
                                                                </Link>
                                                            </Button>
                                                            {job.slug && (
                                                                <Button variant="ghost" size="sm" asChild>
                                                                    <Link href={creativeRoutes.jobs.show.url({ job: job.slug })}>
                                                                        View
                                                                    </Link>
                                                                </Button>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
