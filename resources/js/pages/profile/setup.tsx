/* eslint-disable @typescript-eslint/no-unused-vars */
/* eslint-disable @typescript-eslint/no-explicit-any */
import { Head } from '@inertiajs/react';
import { User } from '@/types';

import CreativeProfileForm from '@/components/profile/creative-profile-form';
import OpportunityOwnerProfileForm from '@/components/profile/opportunity-owner-profile-form';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import AppLayout from '@/layouts/app-layout';

interface SetupProps {
    user: User & { user_type: 'creative' | 'opportunity_owner' };
    profile: any;
    userType: 'creative' | 'opportunity_owner';
    completionScore: number;
}

export default function Setup({ user, profile, userType, completionScore }: SetupProps) {
    return (
        <AppLayout>
            <Head title="Profile Setup" />

            <div className="container mx-auto py-8 max-w-2xl">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold mb-2">Complete Your Profile</h1>
                    <p className="text-muted-foreground">
                        Let's set up your profile to get the most out of the platform.
                    </p>

                    <div className="mt-4">
                        <div className="flex items-center justify-between mb-2">
                            <span className="text-sm font-medium">Profile Completion</span>
                            <span className="text-sm text-muted-foreground">{completionScore}%</span>
                        </div>
                        <Progress value={completionScore} className="w-full" />
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>
                            {userType === 'creative' ? 'Creative Profile' : 'Company Profile'}
                        </CardTitle>
                        <CardDescription>
                            {userType === 'creative'
                                ? 'Tell us about your skills and experience'
                                : 'Tell us about your company and what you\'re looking for'
                            }
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {userType === 'creative' ? (
                            <CreativeProfileForm profile={profile} />
                        ) : (
                            <OpportunityOwnerProfileForm profile={profile} />
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
