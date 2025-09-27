import { Head, router } from '@inertiajs/react';
import { CheckCircle2, XCircle, Building2 } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface PendingProfile {
    id: number;
    company_name: string;
    industry?: string | null;
    company_size?: string | null;
    submitted_at?: string | null;
    user: {
        id: number;
        name: string;
        email: string;
    };
}

interface VerifiedProfile {
    id: number;
    company_name: string;
    verified_at?: string | null;
    user: {
        name: string;
    };
}

interface OpportunityOwnerAdminProps {
    pendingProfiles: PendingProfile[];
    recentlyVerified: VerifiedProfile[];
}

export default function OpportunityOwnerAdminIndex({ pendingProfiles, recentlyVerified }: OpportunityOwnerAdminProps) {
    const approve = (id: number) => {
        router.post(`/admin/opportunity-owners/${id}/approve`);
    };

    const reject = (id: number) => {
        router.post(`/admin/opportunity-owners/${id}/reject`);
    };

    return (
        <AppLayout>
            <Head title="Opportunity Owner Verification" />

            <div className="container mx-auto py-8 space-y-8">
                <div>
                    <h1 className="text-3xl font-bold mb-2">Opportunity Owner Verification</h1>
                    <p className="text-muted-foreground">
                        Review pending company profiles and manually approve verified businesses.
                    </p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Pending Reviews</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {pendingProfiles.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center text-muted-foreground">
                                <Building2 className="h-12 w-12 mb-4" />
                                <p>No pending opportunity owners at the moment.</p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {pendingProfiles.map((profile) => (
                                    <div
                                        key={profile.id}
                                        className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border rounded-lg p-4"
                                    >
                                        <div>
                                            <div className="flex items-center gap-2">
                                                <h2 className="text-lg font-semibold">{profile.company_name}</h2>
                                                {profile.industry && (
                                                    <Badge variant="secondary">{profile.industry}</Badge>
                                                )}
                                            </div>
                                            <p className="text-sm text-muted-foreground">
                                                Submitted by {profile.user.name} ({profile.user.email})
                                            </p>
                                            <div className="flex gap-3 mt-2 text-sm text-muted-foreground">
                                                {profile.company_size && (
                                                    <span>
                                                        Size: <strong>{profile.company_size}</strong>
                                                    </span>
                                                )}
                                                {profile.submitted_at && (
                                                    <span>
                                                        Submitted {new Date(profile.submitted_at).toLocaleDateString()}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                        <div className="flex gap-2">
                                            <Button onClick={() => reject(profile.id)} variant="outline" className="gap-2">
                                                <XCircle className="h-4 w-4" />
                                                Reject
                                            </Button>
                                            <Button onClick={() => approve(profile.id)} className="gap-2">
                                                <CheckCircle2 className="h-4 w-4" />
                                                Approve
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Recently Verified</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {recentlyVerified.length === 0 ? (
                            <p className="text-muted-foreground text-sm">No companies verified yet.</p>
                        ) : (
                            <div className="space-y-3">
                                {recentlyVerified.map((profile) => (
                                    <div key={profile.id} className="flex items-center justify-between">
                                        <div>
                                            <p className="font-medium">{profile.company_name}</p>
                                            <p className="text-xs text-muted-foreground">
                                                Approved for {profile.user.name}
                                            </p>
                                        </div>
                                        {profile.verified_at && (
                                            <p className="text-xs text-muted-foreground">
                                                {new Date(profile.verified_at).toLocaleString()}
                                            </p>
                                        )}
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
