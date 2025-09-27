import { Head, Link } from '@inertiajs/react';
import { Building2, Users, CheckCircle2, Settings, Plus, AlertTriangle } from 'lucide-react';
import { create as jobsCreateRoute, index as jobsIndexRoute } from '@/routes/opportunity-owner/jobs';

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
}

export default function OpportunityOwner({
    user,
    profile,
    completionScore,
    profileComplete,
    isVerified,
    jobStats
}: OpportunityOwnerProps) {
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
                            <div className="text-2xl font-bold">0</div>
                            <p className="text-xs text-muted-foreground mt-2">
                                Applications this month
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
                                    <Link href={jobsCreateRoute().url}>
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
                                <Link href={jobsIndexRoute().url}>
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
                </div>
            </div>
        </AppLayout>
    );
}
