import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft, Building2, MapPin, Globe, ArrowRightCircle } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';

interface JobDetailProps {
    job: {
        id: number;
        slug: string;
        title: string;
        summary?: string | null;
        description: string;
        location?: string | null;
        is_remote: boolean;
        tags?: string[] | null;
        published_at?: string | null;
        company?: {
            name?: string | null;
            industry?: string | null;
            size?: string | null;
            website?: string | null;
        } | null;
    };
    hasApplied: boolean;
}

export default function ShowJob({ job, hasApplied }: JobDetailProps) {
    return (
        <AppLayout>
            <Head title={job.title} />

            <div className="container mx-auto space-y-8 py-8">
                <Button asChild variant="ghost" className="px-0" size="sm">
                    <Link href="/creative/jobs" className="flex items-center gap-2">
                        <ArrowLeft className="h-4 w-4" /> Back to jobs
                    </Link>
                </Button>

                <div className="grid gap-8 lg:grid-cols-[2fr,1fr]">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-3xl font-semibold">{job.title}</CardTitle>
                            <div className="flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                                {job.is_remote && <Badge variant="secondary">Remote friendly</Badge>}
                                {job.location && (
                                    <span className="flex items-center gap-1">
                                        <MapPin className="h-4 w-4" /> {job.location}
                                    </span>
                                )}
                                {job.published_at && (
                                    <span>Published {new Date(job.published_at).toLocaleDateString()}</span>
                                )}
                            </div>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            {job.summary && <p className="text-lg text-muted-foreground">{job.summary}</p>}

                            {job.tags && job.tags.length > 0 && (
                                <div className="flex flex-wrap gap-2">
                                    {job.tags.map((tag) => (
                                        <Badge key={tag} variant="outline" className="lowercase">
                                            {tag}
                                        </Badge>
                                    ))}
                                </div>
                            )}

                            <div className="space-y-3">
                                <h2 className="text-lg font-semibold">About the opportunity</h2>
                                <p className="whitespace-pre-line text-muted-foreground">{job.description}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="h-fit">
                        <CardHeader>
                            <CardTitle>Application</CardTitle>
                            <p className="text-sm text-muted-foreground">
                                Share a short note. The opportunity owner will contact shortlisted creatives.
                            </p>
                        </CardHeader>
                        <Form
                            method="post"
                            action={`/creative/jobs/${job.slug}/applications`}
                            className="flex flex-col gap-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <CardContent className="space-y-4">
                                        <div className="space-y-2">
                                            <label className="text-sm font-medium" htmlFor="cover_letter">
                                                Cover letter
                                            </label>
                                            <Textarea
                                                id="cover_letter"
                                                name="cover_letter"
                                                maxLength={5000}
                                                placeholder="Highlight relevant experience, availability, or portfolio links."
                                                disabled={hasApplied}
                                                className="min-h-32"
                                            />
                                            {errors.cover_letter && (
                                                <p className="text-xs text-destructive">{errors.cover_letter}</p>
                                            )}
                                        </div>
                                    </CardContent>

                                    <CardFooter className="flex justify-end gap-2 border-t bg-muted/40 py-4">
                                        {hasApplied ? (
                                            <Badge variant="secondary">Application submitted</Badge>
                                        ) : (
                                            <Button type="submit" disabled={processing} className="gap-2">
                                                {processing ? 'Submitting…' : 'Submit application'}
                                                <ArrowRightCircle className="h-4 w-4" />
                                            </Button>
                                        )}
                                    </CardFooter>
                                </>
                            )}
                        </Form>
                    </Card>

                    {job.company && (
                        <Card className="lg:col-span-2">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Building2 className="h-5 w-5" /> Company details
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4 md:grid-cols-2">
                                {job.company.name && (
                                    <div>
                                        <p className="text-xs font-semibold uppercase text-muted-foreground">Company</p>
                                        <p className="text-sm">{job.company.name}</p>
                                    </div>
                                )}
                                {job.company.industry && (
                                    <div>
                                        <p className="text-xs font-semibold uppercase text-muted-foreground">Industry</p>
                                        <p className="text-sm">{job.company.industry}</p>
                                    </div>
                                )}
                                {job.company.size && (
                                    <div>
                                        <p className="text-xs font-semibold uppercase text-muted-foreground">Team size</p>
                                        <p className="text-sm">{job.company.size}</p>
                                    </div>
                                )}
                                {job.company.website && (
                                    <div>
                                        <p className="text-xs font-semibold uppercase text-muted-foreground">Website</p>
                                        <a
                                            href={job.company.website}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="flex items-center gap-2 text-sm text-primary hover:underline"
                                        >
                                            <Globe className="h-4 w-4" /> {job.company.website}
                                        </a>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
