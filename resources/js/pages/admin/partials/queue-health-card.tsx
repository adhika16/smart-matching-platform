import { Activity, AlertTriangle, CloudCog, Database, Server } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

export interface QueueHealthSnapshot {
    bedrock: {
        enabled: boolean;
    };
    pinecone: {
        enabled: boolean;
        simulate: boolean;
    };
    queue: {
        driver?: string | null;
        connection?: string | null;
        supports_counts: boolean;
        pending_jobs: number | null;
        failed_jobs: number | null;
    };
    embeddings: {
        latest_job_generated_at: string | null;
        latest_profile_generated_at: string | null;
        total_cached_records: number;
    };
    recommendations: string[];
}

interface QueueHealthCardProps {
    snapshot: QueueHealthSnapshot;
}

const statusBadge = (label: string, variant: 'success' | 'warning' | 'default' = 'default') => (
    <Badge variant={variant === 'success' ? 'secondary' : variant === 'warning' ? 'destructive' : 'outline'}>{label}</Badge>
);

const formatRelativeTime = (timestamp: string | null): string => {
    if (!timestamp) {
        return 'Never';
    }

    const date = new Date(timestamp);
    const diff = Date.now() - date.getTime();

    if (Number.isNaN(diff)) {
        return 'Unknown';
    }

    const minutes = Math.floor(diff / 60000);

    if (minutes < 1) {
        return 'Just now';
    }

    if (minutes < 60) {
        return `${minutes} min${minutes === 1 ? '' : 's'} ago`;
    }

    const hours = Math.floor(minutes / 60);

    if (hours < 24) {
        return `${hours} hour${hours === 1 ? '' : 's'} ago`;
    }

    const days = Math.floor(hours / 24);
    if (days < 7) {
        return `${days} day${days === 1 ? '' : 's'} ago`;
    }

    return date.toLocaleDateString();
};

export function QueueHealthCard({ snapshot }: QueueHealthCardProps) {
    const queueDriver = snapshot.queue.driver ?? 'unknown';
    const supportsCounts = snapshot.queue.supports_counts;

    return (
        <Card>
            <CardHeader className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <CardTitle className="flex items-center gap-2">
                        <Server className="h-5 w-5 text-muted-foreground" />
                        Pipeline Health
                    </CardTitle>
                    <p className="text-sm text-muted-foreground">
                        Real-time insight into semantic search services, queues, and cached embeddings.
                    </p>
                </div>
                <div className="flex flex-wrap items-center gap-2">
                    {snapshot.bedrock.enabled
                        ? statusBadge('Bedrock enabled', 'success')
                        : statusBadge('Bedrock fallback', 'warning')}
                    {snapshot.pinecone.enabled
                        ? statusBadge(snapshot.pinecone.simulate ? 'Pinecone simulate' : 'Pinecone active', 'success')
                        : statusBadge('Pinecone disabled', 'warning')}
                </div>
            </CardHeader>
            <CardContent className="grid gap-6 md:grid-cols-2">
                <div className="space-y-4">
                    <div className="rounded-lg border p-4">
                        <div className="mb-3 flex items-center gap-2 text-sm font-medium">
                            <Database className="h-4 w-4 text-muted-foreground" />
                            Queue metrics
                        </div>
                        <dl className="space-y-2 text-sm">
                            <div className="flex items-center justify-between">
                                <dt className="text-muted-foreground">Driver</dt>
                                <dd className="font-medium uppercase">{queueDriver}</dd>
                            </div>
                            <div className="flex items-center justify-between">
                                <dt className="text-muted-foreground">Pending jobs</dt>
                                <dd className="font-medium">
                                    {supportsCounts && snapshot.queue.pending_jobs !== null
                                        ? snapshot.queue.pending_jobs
                                        : 'Unavailable'}
                                </dd>
                            </div>
                            <div className="flex items-center justify-between">
                                <dt className="text-muted-foreground">Failed jobs</dt>
                                <dd className="font-medium">
                                    {supportsCounts && snapshot.queue.failed_jobs !== null
                                        ? snapshot.queue.failed_jobs
                                        : 'Unavailable'}
                                </dd>
                            </div>
                        </dl>
                        {!supportsCounts && (
                            <p className="mt-3 flex items-start gap-2 text-xs text-muted-foreground">
                                <AlertTriangle className="mt-0.5 h-3.5 w-3.5" />
                                Configure a queue driver with metrics support (e.g. database) to monitor pending and failed jobs.
                            </p>
                        )}
                    </div>

                    <div className="rounded-lg border p-4">
                        <div className="mb-3 flex items-center gap-2 text-sm font-medium">
                            <Activity className="h-4 w-4 text-muted-foreground" />
                            Embedding cache
                        </div>
                        <dl className="space-y-2 text-sm">
                            <div className="flex items-center justify-between">
                                <dt className="text-muted-foreground">Cached vectors</dt>
                                <dd className="font-medium">{snapshot.embeddings.total_cached_records}</dd>
                            </div>
                            <div className="flex items-center justify-between">
                                <dt className="text-muted-foreground">Latest job</dt>
                                <dd className="font-medium" title={snapshot.embeddings.latest_job_generated_at ?? undefined}>
                                    {formatRelativeTime(snapshot.embeddings.latest_job_generated_at)}
                                </dd>
                            </div>
                            <div className="flex items-center justify-between">
                                <dt className="text-muted-foreground">Latest creative profile</dt>
                                <dd className="font-medium" title={snapshot.embeddings.latest_profile_generated_at ?? undefined}>
                                    {formatRelativeTime(snapshot.embeddings.latest_profile_generated_at)}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div className="space-y-4">
                    <div className="rounded-lg border p-4">
                        <div className="mb-3 flex items-center gap-2 text-sm font-medium">
                            <CloudCog className="h-4 w-4 text-muted-foreground" />
                            Recommended actions
                        </div>
                        {snapshot.recommendations.length === 0 ? (
                            <p className="text-sm text-muted-foreground">All systems look healthy. Keep an eye on queue depth in busy periods.</p>
                        ) : (
                            <ul className="space-y-2 text-sm text-muted-foreground">
                                {snapshot.recommendations.map((note, index) => (
                                    <li key={`${note}-${index}`} className="flex items-start gap-2">
                                        <span className="mt-1.5 h-1.5 w-1.5 rounded-full bg-muted-foreground/70" />
                                        <span>{note}</span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
