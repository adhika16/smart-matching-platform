import { Head, Link } from '@inertiajs/react';
import { Search, MapPin, User, ExternalLink, Sparkles } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FormEvent, useState } from 'react';

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
    scores?: {
        final: number;
        semantic: number;
        keyword: number;
    };
}

interface Job {
    id: number;
    title: string;
}

interface PageProps {
    jobs: Job[];
    auth: {
        user: {
            id: number;
            name: string;
            user_type: 'opportunity_owner';
        };
    };
}

interface SmartSearchResult {
    data: Creative[];
    meta: {
        source: string;
        semantic_limit: number;
        keyword_count: number;
        semantic_count: number;
        job_context?: string | null;
    };
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
export default function SearchCreatives({ jobs, auth }: PageProps) {
    const [creatives, setCreatives] = useState<Creative[]>([]);
    const [formData, setFormData] = useState({
        search: '',
        location: '',
        experience_level: '',
        skills: '',
        job_id: '',
    });

    // Helper function to get display values for selects
    const getSelectValue = (field: 'job_id' | 'experience_level', value: string) => {
        if (field === 'job_id') {
            return value === '' ? 'none' : value;
        }
        if (field === 'experience_level') {
            return value === '' ? 'any' : value;
        }
        return value;
    };
    const [isSmartSearch, setIsSmartSearch] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [searchMeta, setSearchMeta] = useState<SmartSearchResult['meta'] | null>(null);

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();

        if (!formData.search.trim()) {
            return;
        }

        setIsLoading(true);

        try {
            const params = new URLSearchParams({
                q: formData.search,
                limit: '20',
            });

            if (formData.location) {
                params.append('filters[location]', formData.location);
            }
            if (formData.experience_level) {
                params.append('filters[experience_level]', formData.experience_level);
            }
            if (formData.skills) {
                // Split skills by comma and add each as separate array element
                const skillsArray = formData.skills.split(',').map(s => s.trim()).filter(s => s);
                skillsArray.forEach(skill => {
                    params.append('filters[skills][]', skill);
                });
            }
            if (formData.job_id) {
                params.append('job_id', formData.job_id);
            }

            const response = await fetch(`/api/search/creatives?${params}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
                },
                credentials: 'same-origin', // Include cookies for session auth
            });

            if (!response.ok) {
                throw new Error('Search failed');
            }

            const result: SmartSearchResult = await response.json();

            setCreatives(result.data);
            setSearchMeta(result.meta);
            setIsSmartSearch(true);

        } catch (error) {
            console.error('Creative search failed:', error);
        } finally {
            setIsLoading(false);
        }
    };

    const clearFilters = () => {
        setFormData({
            search: '',
            location: '',
            experience_level: '',
            skills: '',
            job_id: '',
        });
        setIsSmartSearch(false);
        setSearchMeta(null);
        setCreatives([]);
    };

    const formatMatchScore = (score: number): string => {
        return Math.round(score * 100).toString();
    };

    const getMatchScoreVariant = (score: number): "default" | "secondary" | "outline" => {
        if (score >= 0.8) return "default";
        if (score >= 0.6) return "secondary";
        return "outline";
    };

    const formatExperienceLevel = (level: string): string => {
        const levels: Record<string, string> = {
            'entry': 'Entry Level',
            'mid': 'Mid Level',
            'senior': 'Senior',
            'lead': 'Lead/Principal'
        };
        return levels[level] || level;
    };

    return (
        <AppLayout>
            <Head title="Search Creatives" />

            <div className="container mx-auto space-y-6 py-8">
                <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-semibold">Find talented creatives</h1>
                        <p className="text-muted-foreground">
                            {isSmartSearch
                                ? 'AI-powered talent discovery tailored to your needs'
                                : 'Search through our network of creative professionals'}
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Badge variant="secondary">{creatives.length} creatives</Badge>
                        {isSmartSearch && (
                            <Badge variant="default" className="bg-primary/10 text-primary border-primary/20">
                                <Sparkles className="h-3 w-3 mr-1" />
                                Smart Search
                            </Badge>
                        )}
                    </div>
                </div>

                <Card>
                    <form className="grid gap-4 border-b p-6 md:grid-cols-3" onSubmit={handleSubmit}>
                        <div className="md:col-span-2">
                            <label className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                Search Keywords
                            </label>
                            <div className="relative mt-1">
                                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    name="search"
                                    value={formData.search}
                                    onChange={(event) => setFormData((prev) => ({ ...prev, search: event.target.value }))}
                                    placeholder="Search by skills, bio, or expertise..."
                                    className="pl-9"
                                    disabled={isLoading}
                                />
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                <Sparkles className="h-3 w-3 inline mr-1" />
                                AI will find creatives that match your requirements
                            </p>
                        </div>
                        <div>
                            <label className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                Context Job (Optional)
                            </label>
                            <Select
                                value={getSelectValue('job_id', formData.job_id)}
                                onValueChange={(value) => setFormData((prev) => ({ ...prev, job_id: value === 'none' ? '' : value }))}
                                disabled={isLoading}
                            >
                                <SelectTrigger className="mt-1">
                                    <SelectValue placeholder="Select a job for context" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">No specific job</SelectItem>
                                    {jobs.map((job) => (
                                        <SelectItem key={job.id} value={job.id.toString()}>
                                            {job.title}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <label className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                Location
                            </label>
                            <div className="relative mt-1">
                                <MapPin className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    name="location"
                                    value={formData.location}
                                    onChange={(event) => setFormData((prev) => ({ ...prev, location: event.target.value }))}
                                    placeholder="City or region"
                                    className="pl-9"
                                    disabled={isLoading}
                                />
                            </div>
                        </div>
                        <div>
                            <label className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                Experience Level
                            </label>
                            <Select
                                value={getSelectValue('experience_level', formData.experience_level)}
                                onValueChange={(value) => setFormData((prev) => ({ ...prev, experience_level: value === 'any' ? '' : value }))}
                                disabled={isLoading}
                            >
                                <SelectTrigger className="mt-1">
                                    <SelectValue placeholder="Any level" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="any">Any level</SelectItem>
                                    <SelectItem value="entry">Entry Level</SelectItem>
                                    <SelectItem value="mid">Mid Level</SelectItem>
                                    <SelectItem value="senior">Senior</SelectItem>
                                    <SelectItem value="lead">Lead/Principal</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <label className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                Specific Skills
                            </label>
                            <Input
                                name="skills"
                                value={formData.skills}
                                onChange={(event) => setFormData((prev) => ({ ...prev, skills: event.target.value }))}
                                placeholder="e.g. React, Design, Photography"
                                className="mt-1"
                                disabled={isLoading}
                            />
                        </div>
                        <div className="md:col-span-3 flex flex-wrap gap-2">
                            <Button type="submit" disabled={isLoading || !formData.search.trim()}>
                                {isLoading ? (
                                    <>
                                        <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
                                        Searching...
                                    </>
                                ) : (
                                    <>
                                        <Search className="mr-2 h-4 w-4" />
                                        Find Creatives
                                    </>
                                )}
                            </Button>
                            <Button type="button" variant="ghost" onClick={clearFilters} disabled={isLoading}>
                                Clear filters
                            </Button>
                        </div>
                    </form>

                    {isSmartSearch && searchMeta && (
                        <div className="px-6 py-3 border-b bg-muted/20">
                            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                <Sparkles className="h-4 w-4 text-primary" />
                                <span>
                                    Smart matching active • Found {creatives.length} creatives
                                    <span className="ml-1">
                                        ({searchMeta.keyword_count} keyword + {searchMeta.semantic_count} semantic matches)
                                    </span>
                                </span>
                                {searchMeta.job_context && (
                                    <Badge variant="outline" className="ml-2">
                                        Context: {searchMeta.job_context}
                                    </Badge>
                                )}
                            </div>
                        </div>
                    )}
                </Card>

                {creatives.length === 0 ? (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-12">
                            <Search className="h-12 w-12 text-muted-foreground mb-4" />
                            <h3 className="text-lg font-medium mb-2">No creatives found</h3>
                            <p className="text-sm text-muted-foreground text-center mb-4">
                                {!formData.search.trim()
                                    ? "Enter search keywords to find talented creatives using AI-powered matching."
                                    : "Try different search terms or adjust your filters to find more creatives."}
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {creatives.map((creative) => (
                            <Card key={creative.id} className="group hover:shadow-lg transition-all duration-200">
                                <CardHeader className="pb-3">
                                    <div className="flex items-start justify-between gap-2">
                                        <div>
                                            <CardTitle className="text-lg group-hover:text-primary transition-colors">
                                                {creative.name}
                                            </CardTitle>
                                            {creative.experience_level && (
                                                <p className="text-sm text-muted-foreground">
                                                    {formatExperienceLevel(creative.experience_level)}
                                                </p>
                                            )}
                                        </div>
                                        {creative.scores && (
                                            <Badge
                                                variant={getMatchScoreVariant(creative.scores.final)}
                                                className="shrink-0 text-xs"
                                            >
                                                {formatMatchScore(creative.scores.final)}% match
                                            </Badge>
                                        )}
                                    </div>
                                </CardHeader>

                                <CardContent className="pt-0">
                                    <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground mb-3">
                                        {creative.location && (
                                            <span className="flex items-center gap-1">
                                                <MapPin className="h-3 w-3" /> {creative.location}
                                            </span>
                                        )}
                                        <span className="flex items-center gap-1">
                                            <User className="h-3 w-3" /> Creative Professional
                                        </span>
                                    </div>

                                    {creative.bio && (
                                        <p className="text-sm text-muted-foreground line-clamp-3 mb-3">
                                            {creative.bio}
                                        </p>
                                    )}

                                    {creative.skills && creative.skills.length > 0 && (
                                        <div className="flex flex-wrap gap-1">
                                            {creative.skills.slice(0, 4).map((skill) => (
                                                <Badge key={skill} variant="outline" className="text-xs">
                                                    {skill}
                                                </Badge>
                                            ))}
                                            {creative.skills.length > 4 && (
                                                <Badge variant="outline" className="text-xs">
                                                    +{creative.skills.length - 4} more
                                                </Badge>
                                            )}
                                        </div>
                                    )}
                                </CardContent>

                                <CardFooter className="pt-0 space-y-2">
                                    <div className="w-full space-y-2">
                                        <div className="flex items-center gap-2">
                                            <Button size="sm" variant="outline" className="flex-1" asChild>
                                                <Link href={`/opportunity-owner/creatives/${creative.id}`}>
                                                    <User className="mr-2 h-4 w-4" />
                                                    View Profile
                                                </Link>
                                            </Button>
                                            {creative.portfolio_url && (
                                                <Button size="sm" variant="outline" asChild>
                                                    <a
                                                        href={creative.portfolio_url}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="flex items-center gap-2"
                                                    >
                                                        <ExternalLink className="h-4 w-4" />
                                                    </a>
                                                </Button>
                                            )}
                                        </div>
                                        <p className="text-xs text-muted-foreground text-center">
                                            {creative.email}
                                        </p>
                                    </div>
                                </CardFooter>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
