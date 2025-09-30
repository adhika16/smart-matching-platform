import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, MapPin, User, ExternalLink, Mail, Calendar } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Creative {
    id: number;
    user_id: number;
    name: string;
    email: string;
    bio?: string | null;
    skills?: string[] | null;
    experience_level?: string | null;
    location?: string | null;
    portfolio_url?: string | null;
    created_at?: string | null;
    updated_at?: string | null;
}

interface PageProps {
    creative: Creative;
}

export default function ShowCreative({ creative }: PageProps) {
    const formatExperienceLevel = (level: string): string => {
        const levels: Record<string, string> = {
            'entry': 'Entry Level',
            'mid': 'Mid Level',
            'senior': 'Senior',
            'lead': 'Lead/Principal'
        };
        return levels[level] || level;
    };

    const formatDate = (dateString?: string | null): string => {
        if (!dateString) return 'Not specified';
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    return (
        <AppLayout>
            <Head title={`${creative.name} - Creative Profile`} />

            <div className="container mx-auto max-w-4xl space-y-6 py-8">
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="outline" size="sm" asChild>
                            <Link href="/opportunity-owner/creatives">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to Search
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-3xl font-semibold">{creative.name}</h1>
                            <p className="text-muted-foreground">Creative Professional</p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <a href={`mailto:${creative.email}`}>
                                <Mail className="mr-2 h-4 w-4" />
                                Contact
                            </a>
                        </Button>
                        {creative.portfolio_url && (
                            <Button asChild>
                                <a
                                    href={creative.portfolio_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <ExternalLink className="mr-2 h-4 w-4" />
                                    View Portfolio
                                </a>
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-3">
                    <div className="md:col-span-2 space-y-6">
                        {/* About Section */}
                        <Card>
                            <CardHeader>
                                <CardTitle>About</CardTitle>
                            </CardHeader>
                            <CardContent>
                                {creative.bio ? (
                                    <p className="text-muted-foreground whitespace-pre-line">
                                        {creative.bio}
                                    </p>
                                ) : (
                                    <p className="text-muted-foreground italic">
                                        No bio provided.
                                    </p>
                                )}
                            </CardContent>
                        </Card>

                        {/* Skills Section */}
                        {creative.skills && creative.skills.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Skills & Expertise</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex flex-wrap gap-2">
                                        {creative.skills.map((skill) => (
                                            <Badge key={skill} variant="secondary">
                                                {skill}
                                            </Badge>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    <div className="space-y-6">
                        {/* Profile Info */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Profile Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex items-center gap-2 text-sm">
                                    <Mail className="h-4 w-4 text-muted-foreground" />
                                    <span>{creative.email}</span>
                                </div>

                                {creative.location && (
                                    <div className="flex items-center gap-2 text-sm">
                                        <MapPin className="h-4 w-4 text-muted-foreground" />
                                        <span>{creative.location}</span>
                                    </div>
                                )}

                                {creative.experience_level && (
                                    <div className="flex items-center gap-2 text-sm">
                                        <User className="h-4 w-4 text-muted-foreground" />
                                        <span>{formatExperienceLevel(creative.experience_level)}</span>
                                    </div>
                                )}

                                <div className="flex items-center gap-2 text-sm">
                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                    <span>Joined {formatDate(creative.created_at)}</span>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Actions */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Actions</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <Button className="w-full" asChild>
                                    <a href={`mailto:${creative.email}`}>
                                        <Mail className="mr-2 h-4 w-4" />
                                        Send Email
                                    </a>
                                </Button>

                                {creative.portfolio_url && (
                                    <Button variant="outline" className="w-full" asChild>
                                        <a
                                            href={creative.portfolio_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <ExternalLink className="mr-2 h-4 w-4" />
                                            View Portfolio
                                        </a>
                                    </Button>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
