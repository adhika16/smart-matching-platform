import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    MapPin,
    Calendar,
    DollarSign,
    Building2,
    ExternalLink,
    CheckCircle,
    Clock,
    Users
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { FormEvent, useState } from 'react';
import { Textarea } from '@/components/ui/textarea';

interface Job {
    id: number;
    slug: string;
    title: string;
    summary?: string | null;
    description?: string | null;
    location?: string | null;
    is_remote: boolean;
    tags?: string[] | null;
    skills?: string[] | null;
    category?: string | null;
    published_at?: string | null;
    timeline_start?: string | null;
    timeline_end?: string | null;
    budget_min?: number | null;
    budget_max?: number | null;
    company?: {
        name?: string | null;
        industry?: string | null;
        size?: string | null;
        website?: string | null;
    };
}

interface JobShowProps {
    job: Job;
    hasApplied: boolean;
}

export default function JobShow({ job, hasApplied }: JobShowProps) {
    const [isApplying, setIsApplying] = useState(false);
    const [coverLetter, setCoverLetter] = useState('');
    const [showApplicationForm, setShowApplicationForm] = useState(false);

    const handleApply = async (event: FormEvent) => {
        event.preventDefault();
        setIsApplying(true);

        try {
            const formData = new FormData();
            formData.append('cover_letter', coverLetter);

            const response = await fetch(`/creative/jobs/${job.slug}/applications`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            if (response.ok) {
                // Refresh the page to show applied state
                router.reload();
            } else {
                throw new Error('Application failed');
            }
        } catch (error) {
            console.error('Application failed:', error);
            // You might want to show an error message here
        } finally {
            setIsApplying(false);
        }
    };

    const formatBudget = (min?: number | null, max?: number | null) => {
        if (!min && !max) return null;
        if (min && max) return `$${min.toLocaleString()} - $${max.toLocaleString()}`;
        if (min) return `From $${min.toLocaleString()}`;
        if (max) return `Up to $${max.toLocaleString()}`;
        return null;
    };

    const formatDate = (dateString?: string | null) => {
        if (!dateString) return null;
        return new Date(dateString).toLocaleDateString();
    };

    return (
        <AppLayout>
            <Head title={job.title} />

            <div className="container mx-auto py-8 space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href="/creative/jobs">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to jobs
                        </Link>
                    </Button>
                </div>

                <div className="grid gap-6 md:grid-cols-3">
                    {/* Main Content */}
                    <div className="md:col-span-2 space-y-6">
                        {/* Job Header */}
                        <Card>
                            <CardHeader>
                                <div className="space-y-4">
                                    <div>
                                        <CardTitle className="text-2xl mb-2">{job.title}</CardTitle>
                                        {job.company?.name && (
                                            <p className="text-lg text-muted-foreground flex items-center gap-2">
                                                <Building2 className="h-5 w-5" />
                                                {job.company.name}
                                            </p>
                                        )}
                                    </div>

                                    <div className="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                                        {(job.location || job.is_remote) && (
                                            <div className="flex items-center gap-2">
                                                <MapPin className="h-4 w-4" />
                                                {job.is_remote ? 'Remote' : job.location}
                                                {job.location && job.is_remote && ' (Remote)'}
                                            </div>
                                        )}
                                        {job.published_at && (
                                            <div className="flex items-center gap-2">
                                                <Clock className="h-4 w-4" />
                                                Posted {formatDate(job.published_at)}
                                            </div>
                                        )}
                                        {job.category && (
                                            <Badge variant="secondary">{job.category}</Badge>
                                        )}
                                    </div>

                                    {job.summary && (
                                        <p className="text-muted-foreground">{job.summary}</p>
                                    )}
                                </div>
                            </CardHeader>
                        </Card>

                        {/* Job Description */}
                        {job.description && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Job Description</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="prose prose-sm max-w-none">
                                        <p className="whitespace-pre-line">{job.description}</p>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Skills & Requirements */}
                        {job.skills && job.skills.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Required Skills</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex flex-wrap gap-2">
                                        {job.skills.map((skill) => (
                                            <Badge key={skill} variant="outline">
                                                {skill}
                                            </Badge>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Apply Section */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Apply for this job</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {hasApplied ? (
                                    <div className="text-center py-6">
                                        <CheckCircle className="h-12 w-12 text-green-600 mx-auto mb-4" />
                                        <h3 className="text-lg font-semibold mb-2">Application Submitted</h3>
                                        <p className="text-sm text-muted-foreground">
                                            You've already applied to this position.
                                            We'll notify you of any updates.
                                        </p>
                                    </div>
                                ) : showApplicationForm ? (
                                    <form onSubmit={handleApply} className="space-y-4">
                                        <div>
                                            <label className="text-sm font-medium">
                                                Cover Letter (Optional)
                                            </label>
                                            <Textarea
                                                value={coverLetter}
                                                onChange={(e) => setCoverLetter(e.target.value)}
                                                placeholder="Tell them why you're perfect for this role..."
                                                className="mt-1"
                                                rows={6}
                                            />
                                        </div>
                                        <div className="flex gap-2">
                                            <Button type="submit" disabled={isApplying} className="flex-1">
                                                {isApplying ? 'Submitting...' : 'Submit Application'}
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={() => setShowApplicationForm(false)}
                                            >
                                                Cancel
                                            </Button>
                                        </div>
                                    </form>
                                ) : (
                                    <Button
                                        onClick={() => setShowApplicationForm(true)}
                                        className="w-full"
                                    >
                                        Apply Now
                                    </Button>
                                )}
                            </CardContent>
                        </Card>

                        {/* Job Details */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Job Details</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {formatBudget(job.budget_min, job.budget_max) && (
                                    <div className="flex items-center gap-2">
                                        <DollarSign className="h-4 w-4 text-muted-foreground" />
                                        <span className="text-sm">
                                            {formatBudget(job.budget_min, job.budget_max)}
                                        </span>
                                    </div>
                                )}

                                {(job.timeline_start || job.timeline_end) && (
                                    <div className="flex items-center gap-2">
                                        <Calendar className="h-4 w-4 text-muted-foreground" />
                                        <span className="text-sm">
                                            {job.timeline_start && job.timeline_end
                                                ? `${formatDate(job.timeline_start)} - ${formatDate(job.timeline_end)}`
                                                : job.timeline_start
                                                ? `Starts ${formatDate(job.timeline_start)}`
                                                : `Ends ${formatDate(job.timeline_end)}`
                                            }
                                        </span>
                                    </div>
                                )}

                                {job.tags && job.tags.length > 0 && (
                                    <div>
                                        <p className="text-sm font-medium mb-2">Tags</p>
                                        <div className="flex flex-wrap gap-1">
                                            {job.tags.map((tag) => (
                                                <Badge key={tag} variant="outline" className="text-xs">
                                                    {tag}
                                                </Badge>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Company Info */}
                        {job.company && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>About the Company</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    {job.company.name && (
                                        <div>
                                            <p className="font-medium">{job.company.name}</p>
                                        </div>
                                    )}

                                    {job.company.industry && (
                                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                            <Building2 className="h-4 w-4" />
                                            {job.company.industry}
                                        </div>
                                    )}

                                    {job.company.size && (
                                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                            <Users className="h-4 w-4" />
                                            {job.company.size}
                                        </div>
                                    )}

                                    {job.company.website && (
                                        <Button variant="outline" size="sm" asChild className="w-full">
                                            <a
                                                href={job.company.website}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="flex items-center gap-2"
                                            >
                                                <ExternalLink className="h-4 w-4" />
                                                Visit Website
                                            </a>
                                        </Button>
                                    )}
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
