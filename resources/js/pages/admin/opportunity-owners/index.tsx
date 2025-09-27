import { Head, router } from '@inertiajs/react';
import { CheckCircle2, XCircle, Building2, FileText, Clock } from 'lucide-react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';

interface VerificationDocument {
    original_name: string;
    uploaded_at?: string | null;
    url?: string | null;
}

interface VerificationLog {
    id: number;
    action: string;
    actor_role: string;
    actor_name?: string | null;
    notes?: string | null;
    created_at?: string | null;
}

interface PendingProfile {
    id: number;
    company_name: string;
    industry?: string | null;
    company_size?: string | null;
    submitted_at?: string | null;
    verification_documents: VerificationDocument[];
    logs: VerificationLog[];
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
    last_action?: {
        action?: string;
        notes?: string | null;
    } | null;
    user: {
        name: string;
    };
}

interface OpportunityOwnerAdminProps {
    pendingProfiles: PendingProfile[];
    recentlyVerified: VerifiedProfile[];
}

export default function OpportunityOwnerAdminIndex({ pendingProfiles, recentlyVerified }: OpportunityOwnerAdminProps) {
    const [notes, setNotes] = useState<Record<number, string>>({});
    const [submittingId, setSubmittingId] = useState<number | null>(null);
    const [submittingAction, setSubmittingAction] = useState<'approve' | 'reject' | null>(null);

    const submitDecision = (id: number, action: 'approve' | 'reject') => {
        setSubmittingId(id);
        setSubmittingAction(action);
        router.post(
            `/admin/opportunity-owners/${id}/${action}`,
            {
                notes: notes[id] ?? '',
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setNotes((previous) => ({ ...previous, [id]: '' }));
                },
                onFinish: () => {
                    setSubmittingId((current) => (current === id ? null : current));
                    setSubmittingAction((current) => (current === action ? null : current));
                },
            }
        );
    };

    return (
        <AppLayout>
            <Head title="Opportunity Owner Verification" />

            <div className="container mx-auto space-y-8 py-8">
                <div>
                    <h1 className="mb-2 text-3xl font-bold">Opportunity Owner Verification</h1>
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
                                        className="flex flex-col gap-4 rounded-lg border p-4"
                                    >
                                        <div className="flex flex-col gap-2">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <h2 className="text-lg font-semibold">{profile.company_name}</h2>
                                                {profile.industry && <Badge variant="secondary">{profile.industry}</Badge>}
                                            </div>
                                            <p className="text-sm text-muted-foreground">
                                                Submitted by {profile.user.name} ({profile.user.email})
                                            </p>
                                            <div className="flex flex-wrap gap-3 text-sm text-muted-foreground">
                                                {profile.company_size && (
                                                    <span>
                                                        Size: <strong>{profile.company_size}</strong>
                                                    </span>
                                                )}
                                                {profile.submitted_at && (
                                                    <span className="flex items-center gap-1">
                                                        <Clock className="h-3 w-3" />
                                                        Submitted {new Date(profile.submitted_at).toLocaleDateString()}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                        {profile.verification_documents.length > 0 && (
                                            <div className="rounded-md bg-muted/40 p-3">
                                                <p className="mb-2 text-sm font-medium">Evidence</p>
                                                <ul className="space-y-2 text-sm">
                                                    {profile.verification_documents.map((document, index) => (
                                                        <li key={`${document.original_name}-${index}`} className="flex flex-col gap-1">
                                                            <span className="flex items-center gap-2">
                                                                <FileText className="h-4 w-4 text-muted-foreground" />
                                                                {document.url ? (
                                                                    <a
                                                                        href={document.url}
                                                                        target="_blank"
                                                                        rel="noopener noreferrer"
                                                                        className="text-primary underline"
                                                                    >
                                                                        {document.original_name}
                                                                    </a>
                                                                ) : (
                                                                    <span>{document.original_name}</span>
                                                                )}
                                                            </span>
                                                            {document.uploaded_at && (
                                                                <span className="text-xs text-muted-foreground">
                                                                    Uploaded {new Date(document.uploaded_at).toLocaleString()}
                                                                </span>
                                                            )}
                                                        </li>
                                                    ))}
                                                </ul>
                                            </div>
                                        )}

                                        {profile.logs.length > 0 && (
                                            <div className="rounded-md border border-dashed p-3 text-sm">
                                                <p className="mb-2 font-medium">Recent activity</p>
                                                <ul className="space-y-2">
                                                    {profile.logs.map((log) => (
                                                        <li key={log.id} className="flex flex-col">
                                                            <span className="font-medium capitalize">{log.action.replace('_', ' ')}</span>
                                                            <span className="text-xs text-muted-foreground">
                                                                {log.actor_role} {log.actor_name ? `• ${log.actor_name}` : ''}
                                                                {log.created_at
                                                                    ? ` • ${new Date(log.created_at).toLocaleString()}`
                                                                    : ''}
                                                            </span>
                                                            {log.notes && <span className="text-xs text-muted-foreground">“{log.notes}”</span>}
                                                        </li>
                                                    ))}
                                                </ul>
                                            </div>
                                        )}

                                        <div className="flex flex-col gap-3">
                                            <Textarea
                                                placeholder="Add review notes (optional)"
                                                value={notes[profile.id] ?? ''}
                                                onChange={(event) =>
                                                    setNotes((previous) => ({
                                                        ...previous,
                                                        [profile.id]: event.target.value,
                                                    }))
                                                }
                                                rows={3}
                                            />
                                            <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-end">
                                                <Button
                                                    onClick={() => submitDecision(profile.id, 'reject')}
                                                    variant="outline"
                                                    className="gap-2"
                                                    disabled={submittingId === profile.id}
                                                >
                                                    <XCircle className="h-4 w-4" />
                                                     {submittingId === profile.id && submittingAction === 'reject'
                                                         ? 'Rejecting…'
                                                         : 'Reject'}
                                                </Button>
                                                <Button
                                                    onClick={() => submitDecision(profile.id, 'approve')}
                                                    className="gap-2"
                                                    disabled={submittingId === profile.id}
                                                >
                                                    <CheckCircle2 className="h-4 w-4" />
                                                     {submittingId === profile.id && submittingAction === 'approve'
                                                         ? 'Approving…'
                                                         : 'Approve'}
                                                </Button>
                                            </div>
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
                                            {profile.last_action?.action && (
                                                <p className="text-xs text-muted-foreground italic">
                                                    {profile.last_action.action.replace('_', ' ')}
                                                    {profile.last_action.notes ? ` • "${profile.last_action.notes}"` : ''}
                                                </p>
                                            )}
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
