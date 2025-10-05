import { Head, Link } from '@inertiajs/react';
import { User, Settings, Upload, Search, Star } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import creativeRoutes from '@/routes/creative';
import EmailVerificationNotificationController from '@/actions/App/Http/Controllers/Auth/EmailVerificationNotificationController';
import { Form } from '@inertiajs/react';

interface CreativeProps {
    user: {
        name: string;
        email: string;
        user_type: 'creative';
        profile_completion_score: number;
        email_verified_at?: string | null;
    };
    profile?: {
        bio?: string;
        skills?: string[];
        location?: string;
        experience_level?: string;
        available_for_work?: boolean;
    };
    completionScore: number;
    profileComplete: boolean;
    stats?: {
        activeApplications: number;
        shortlistedApplications: number;
    };
    recentApplications?: {
        id: number;
        status: string;
        applied_at?: string | null;
        job?: {
            title?: string | null;
            slug?: string | null;
        } | null;
    }[];
}

export default function Creative({
    user,
    profile,
    completionScore,
    profileComplete,
    stats = { activeApplications: 0, shortlistedApplications: 0 },
    recentApplications = [],
}: CreativeProps) {
    const statusLabel: Record<string, string> = {
        pending: 'Pending review',
        shortlisted: 'Shortlisted',
        rejected: 'Not selected',
    };

    const statusVariant = (status: string) => {
        switch (status) {
            case 'shortlisted':
                return 'default' as const;
            case 'rejected':
                return 'outline' as const;
            default:
                return 'secondary' as const;
        }
    };

    return (
        <AppLayout>
            <Head title="Creative Dashboard" />

            <div className="container mx-auto py-8">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold mb-2">Welcome back, {user.name}!</h1>
                    <p className="text-muted-foreground">
                        Manage your creative profile and find new opportunities
                    </p>
                </div>

                {!user.email_verified_at && (
                    <Card className="mb-6 border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950">
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center space-x-3">
                                    <div className="flex-shrink-0">
                                        <svg className="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 className="text-sm font-medium text-amber-800 dark:text-amber-200">
                                            Email Verification Required
                                        </h3>
                                        <p className="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                            Please verify your email address to unlock full access to semantic search and smart matching features.
                                        </p>
                                    </div>
                                </div>
                                <Form {...EmailVerificationNotificationController.store.form()}>
                                    {({ processing }) => (
                                        <Button type="submit" disabled={processing} size="sm" variant="outline" className="border-amber-300 text-amber-700 hover:bg-amber-100 dark:border-amber-600 dark:text-amber-300 dark:hover:bg-amber-900">
                                            Resend Verification Email
                                        </Button>
                                    )}
                                </Form>
                            </div>
                        </CardContent>
                    </Card>
                )}

                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mb-8">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Profile Completion
                            </CardTitle>
                            <User className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{completionScore}%</div>
                            <Progress value={completionScore} className="mt-2" />
                            {!profileComplete && (
                                <p className="text-xs text-muted-foreground mt-2">
                                    Complete your profile to attract more opportunities
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Active Applications
                            </CardTitle>
                            <Search className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.activeApplications}</div>
                            <p className="text-xs text-muted-foreground mt-2">
                                {stats.activeApplications === 0
                                    ? 'No active applications yet'
                                    : 'Pending or shortlisted opportunities in progress'}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Shortlisted Applications
                            </CardTitle>
                            <Star className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.shortlistedApplications}</div>
                            <p className="text-xs text-muted-foreground mt-2">
                                {stats.shortlistedApplications === 0
                                    ? 'Waiting for responses from opportunity owners'
                                    : 'Great news! You have shortlisted applications to review'}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                            <CardDescription>
                                Get started with your creative profile
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <Button asChild className="w-full justify-start">
                                <Link href="/profile/setup">
                                    <Settings className="mr-2 h-4 w-4" />
                                    {profileComplete ? 'Update Profile' : 'Complete Profile'}
                                </Link>
                            </Button>

                            <Button variant="outline" className="w-full justify-start" disabled>
                                <Upload className="mr-2 h-4 w-4" />
                                Upload Portfolio (Coming Soon)
                            </Button>

                            <Button variant="outline" className="w-full justify-start" asChild>
                                <Link href={creativeRoutes.jobs.index.url()}>
                                    <Search className="mr-2 h-4 w-4" />
                                    Browse Opportunities
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Your Profile Summary</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {profile?.bio ? (
                                <div className="space-y-4">
                                    <div>
                                        <h4 className="font-medium">Bio</h4>
                                        <p className="text-sm text-muted-foreground mt-1">
                                            {profile.bio}
                                        </p>
                                    </div>

                                    {profile.location && (
                                        <div>
                                            <h4 className="font-medium">Location</h4>
                                            <p className="text-sm text-muted-foreground mt-1">
                                                {profile.location}
                                            </p>
                                        </div>
                                    )}

                                    {profile.experience_level && (
                                        <div>
                                            <h4 className="font-medium">Experience Level</h4>
                                            <Badge variant="secondary" className="mt-1">
                                                {profile.experience_level}
                                            </Badge>
                                        </div>
                                    )}

                                    <div>
                                        <h4 className="font-medium">Status</h4>
                                        <Badge variant={profile.available_for_work ? "default" : "secondary"} className="mt-1">
                                            {profile.available_for_work ? "Available for work" : "Not available"}
                                        </Badge>
                                    </div>
                                </div>
                            ) : (
                                <div className="text-center py-8">
                                    <User className="mx-auto h-8 w-8 text-muted-foreground mb-4" />
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

                    {recentApplications.length > 0 && (
                        <Card className="md:col-span-2">
                            <CardHeader>
                                <CardTitle>Recent Applications</CardTitle>
                                <CardDescription>
                                    Track the latest roles you've applied to
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {recentApplications.map((application) => {
                                    const appliedDate = application.applied_at
                                        ? new Date(application.applied_at)
                                        : null;

                                    return (
                                        <div
                                            key={application.id}
                                            className="flex flex-col gap-2 rounded-lg border p-4 md:flex-row md:items-center md:justify-between"
                                        >
                                            <div>
                                                <p className="font-medium">
                                                    {application.job?.title ?? 'Opportunity'}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {appliedDate
                                                        ? `Applied ${appliedDate.toLocaleDateString()}`
                                                        : 'Applied recently'}
                                                </p>
                                            </div>
                                            <div className="flex items-center gap-3">
                                                <Badge variant={statusVariant(application.status)}>
                                                    {statusLabel[application.status] ?? application.status}
                                                </Badge>
                                                {application.job?.slug && (
                                                    <Button variant="ghost" size="sm" asChild>
                                                        <Link href={`/creative/jobs/${application.job.slug}`}>
                                                            View job
                                                        </Link>
                                                    </Button>
                                                )}
                                            </div>
                                        </div>
                                    );
                                })}
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
