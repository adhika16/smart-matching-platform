import { Head, Link, router } from '@inertiajs/react';
import { Search, MapPin, BadgeCheck, ArrowRight, Sparkles } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';
import { FormEvent, useState } from 'react';

interface Job {
    id: number;
    slug: string;
    title: string;
    summary?: string | null;
    location?: string | null;
    is_remote: boolean;
    tags?: string[] | null;
    published_at?: string | null;
    company?: string | null;
    scores?: {
        final: number;
        semantic: number;
        keyword: number;
    };
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Filters {
    search?: string | null;
    location?: string | null;
    remote?: boolean;
    tag?: string | null;
}

interface PageProps {
    jobs: {
        data: Job[];
        links: PaginationLink[];
    };
    filters: Filters;
    auth: {
        user: {
            id: number;
            name: string;
            user_type: 'creative';
            creativeProfile?: {
                id: number;
                skills?: string[];
                bio?: string;
            };
        };
    };
}

interface SmartSearchResult {
    data: Job[];
    meta: {
        source: string;
        semantic_limit: number;
        keyword_count: number;
        semantic_count: number;
    };
}

export default function BrowseJobs({ jobs: initialJobs, filters, auth }: PageProps) {
    const [jobs, setJobs] = useState(initialJobs);
    const [formData, setFormData] = useState({
        search: filters.search ?? '',
        location: filters.location ?? '',
        tag: filters.tag ?? '',
        remote: Boolean(filters.remote),
    });
    const [isSmartSearch, setIsSmartSearch] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [searchMeta, setSearchMeta] = useState<SmartSearchResult['meta'] | null>(null);

    const hasProfile = Boolean(auth.user.creativeProfile);

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();

        if (!formData.search.trim()) {
            router.get('/creative/jobs', formData);
            return;
        }

        setIsLoading(true);

        try {
            const params = new URLSearchParams({
                q: formData.search,
                ...(formData.location && { 'filters[location]': formData.location }),
                ...(formData.tag && { 'filters[category]': formData.tag }),
                ...(formData.remote && { 'filters[remote]': '1' }),
                limit: '20',
            });

            const response = await fetch(`/api/search/personalized?${params}`, {
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

            setJobs({
                data: result.data,
                links: [],
            });
            setSearchMeta(result.meta);
            setIsSmartSearch(true);

        } catch (error) {
            console.error('Smart search failed, falling back to traditional search:', error);
            router.get('/creative/jobs', formData);
        } finally {
            setIsLoading(false);
        }
    };

    const clearFilters = () => {
        setFormData({
            search: '',
            location: '',
            tag: '',
            remote: false,
        });
        setIsSmartSearch(false);
        setSearchMeta(null);
        router.get('/creative/jobs');
    };

    const formatMatchScore = (score: number): string => {
        return Math.round(score * 100).toString();
    };

    const getMatchScoreVariant = (score: number): "default" | "secondary" | "outline" => {
        if (score >= 0.8) return "default";
        if (score >= 0.6) return "secondary";
        return "outline";
    };

    return (
        <AppLayout>
            <Head title="Discover Jobs" />

            <div className="container mx-auto space-y-6 py-8">
                <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-semibold">Discover opportunities</h1>
                        <p className="text-muted-foreground">
                            {isSmartSearch
                                ? 'AI-powered job recommendations tailored to your profile'
                                : 'Explore published roles from verified opportunity owners'}
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Badge variant="secondary">{jobs.data.length} jobs</Badge>
                        {isSmartSearch && (
                            <Badge variant="default" className="bg-primary/10 text-primary border-primary/20">
                                <Sparkles className="h-3 w-3 mr-1" />
                                Smart Search
                            </Badge>
                        )}
                    </div>
                </div>

                <Card>
                    <form className="grid gap-4 border-b p-6 md:grid-cols-4" onSubmit={handleSubmit}>
                        <div className="md:col-span-2">
                            <label className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                Keywords
                            </label>
                            <div className="relative mt-1">
                                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    name="search"
                                    value={formData.search}
                                    onChange={(event) => setFormData((prev) => ({ ...prev, search: event.target.value }))}
                                    placeholder={hasProfile ? "Search jobs that match your skills..." : "Search title, summary or description"}
                                    className="pl-9"
                                    disabled={isLoading}
                                />
                            </div>
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
                                Category
                            </label>
                            <Input
                                name="tag"
                                value={formData.tag}
                                onChange={(event) => setFormData((prev) => ({ ...prev, tag: event.target.value }))}
                                placeholder="e.g. design, development"
                                className="mt-1"
                                disabled={isLoading}
                            />
                        </div>
                        <div className="flex items-center gap-2">
                            <Checkbox
                                id="remote"
                                name="remote"
                                checked={formData.remote}
                                onCheckedChange={(checked) =>
                                    setFormData((prev) => ({ ...prev, remote: checked === true }))
                                }
                                disabled={isLoading}
                            />
                            <label htmlFor="remote" className="text-sm text-muted-foreground">
                                Remote only
                            </label>
                        </div>
                        <div className="md:col-span-4 flex flex-wrap gap-2">
                            <Button type="submit" disabled={isLoading}>
                                {isLoading ? (
                                    <>
                                        <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
                                        Searching...
                                    </>
                                ) : (
                                    <>
                                        <Search className="mr-2 h-4 w-4" />
                                        {formData.search ? 'Smart Search' : 'Search'}
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
                                    Smart matching active • Found {jobs.data.length} jobs
                                    <span className="ml-1">
                                        ({searchMeta.keyword_count} keyword + {searchMeta.semantic_count} semantic matches)
                                    </span>
                                </span>
                            </div>
                        </div>
                    )}
                </Card>

                {jobs.data.length === 0 ? (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-12">
                            <Search className="h-12 w-12 text-muted-foreground mb-4" />
                            <h3 className="text-lg font-medium mb-2">No jobs found</h3>
                            <p className="text-sm text-muted-foreground text-center mb-4">
                                {isSmartSearch
                                    ? "Try adjusting your search terms or complete your profile for better AI matching."
                                    : "Try different search terms or check back later for new opportunities."}
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {jobs.data.map((job) => (
                            <Card key={job.id} className="group hover:shadow-lg transition-all duration-200">
                                <CardHeader className="pb-3">
                                    <div className="flex items-start justify-between gap-2">
                                        <CardTitle className="text-lg line-clamp-2 group-hover:text-primary transition-colors">
                                            {job.title}
                                        </CardTitle>
                                        {job.scores && (
                                            <Badge
                                                variant={getMatchScoreVariant(job.scores.final)}
                                                className="shrink-0 text-xs"
                                            >
                                                {formatMatchScore(job.scores.final)}% match
                                            </Badge>
                                        )}
                                    </div>
                                </CardHeader>

                                <CardContent className="pt-0">
                                    <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground mb-3">
                                        {job.company && (
                                            <span className="flex items-center gap-1">
                                                <BadgeCheck className="h-3 w-3" /> {job.company}
                                            </span>
                                        )}
                                        {job.is_remote && <Badge variant="secondary" className="text-xs">Remote</Badge>}
                                        {job.location && (
                                            <span className="flex items-center gap-1">
                                                <MapPin className="h-3 w-3" /> {job.location}
                                            </span>
                                        )}
                                    </div>

                                    {job.summary && (
                                        <p className="text-sm text-muted-foreground line-clamp-3 mb-3">
                                            {job.summary}
                                        </p>
                                    )}

                                    {job.tags && job.tags.length > 0 && (
                                        <div className="flex flex-wrap gap-1">
                                            {job.tags.slice(0, 3).map((tag) => (
                                                <Badge key={tag} variant="outline" className="text-xs lowercase">
                                                    {tag}
                                                </Badge>
                                            ))}
                                            {job.tags.length > 3 && (
                                                <Badge variant="outline" className="text-xs">
                                                    +{job.tags.length - 3} more
                                                </Badge>
                                            )}
                                        </div>
                                    )}
                                </CardContent>

                                <CardFooter className="pt-0">
                                    <Button asChild className="w-full group/button">
                                        <Link href={`/creative/jobs/${job.slug}`}>
                                            View Details
                                            <ArrowRight className="ml-2 h-4 w-4 transition-transform group-hover/button:translate-x-1" />
                                        </Link>
                                    </Button>
                                </CardFooter>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
