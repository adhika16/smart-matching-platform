import { Head, Link } from '@inertiajs/react';
import { User, Settings, Upload, Search, Star } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';

interface CreativeProps {
    user: {
        name: string;
        email: string;
        user_type: 'creative';
        profile_completion_score: number;
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
}

export default function Creative({ user, profile, completionScore, profileComplete }: CreativeProps) {
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
                            <div className="text-2xl font-bold">0</div>
                            <p className="text-xs text-muted-foreground mt-2">
                                No active applications yet
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Profile Views
                            </CardTitle>
                            <Star className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">0</div>
                            <p className="text-xs text-muted-foreground mt-2">
                                Views this month
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

                            <Button variant="outline" className="w-full justify-start" disabled>
                                <Search className="mr-2 h-4 w-4" />
                                Browse Opportunities (Coming Soon)
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
                </div>
            </div>
        </AppLayout>
    );
}
