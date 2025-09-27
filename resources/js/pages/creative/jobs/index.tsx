import { Head, Link, router } from '@inertiajs/react';
import { Search, MapPin, BadgeCheck, ArrowRight } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';
import { FormEvent, useState } from 'react';

interface JobCard {
    id: number;
    slug: string;
    title: string;
    summary?: string | null;
    location?: string | null;
    is_remote: boolean;
    tags?: string[] | null;
    published_at?: string | null;
    company?: string | null;
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

interface BrowsePageProps {
    jobs: {
        data: JobCard[];
        links: PaginationLink[];
    };
    filters: Filters;
}

export default function BrowseJobs({ jobs, filters }: BrowsePageProps) {
    const [formData, setFormData] = useState({
        search: filters.search ?? '',
        location: filters.location ?? '',
        tag: filters.tag ?? '',
        remote: Boolean(filters.remote),
    });

    const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        router.get(
            '/creative/jobs',
            {
                search: formData.search || undefined,
                location: formData.location || undefined,
                tag: formData.tag || undefined,
                remote: formData.remote ? '1' : undefined,
            },
            {
                preserveState: true,
                replace: true,
            }
        );
    };

    const clearFilters = () => {
        setFormData({ search: '', location: '', tag: '', remote: false });
        router.get('/creative/jobs', {}, { replace: true });
    };

    return (
        <AppLayout>
            <Head title="Browse jobs" />

            <div className="container mx-auto space-y-8 py-8">
                <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-semibold">Discover opportunities</h1>
                        <p className="text-muted-foreground">
                            Explore published roles from verified opportunity owners and apply in a few clicks.
                        </p>
                    </div>
                    <Badge variant="secondary">{jobs.data.length} jobs</Badge>
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
                                    placeholder="Search title, summary or description"
                                    className="pl-9"
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
                                />
                            </div>
                        </div>
                        <div>
                            <label className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                Tag
                            </label>
                            <Input
                                name="tag"
                                value={formData.tag}
                                onChange={(event) => setFormData((prev) => ({ ...prev, tag: event.target.value }))}
                                placeholder="e.g. design"
                                className="mt-1"
                            />
                        </div>
                        <div className="flex items-center gap-2 md:col-span-1">
                            <Checkbox
                                id="remote"
                                name="remote"
                                checked={formData.remote}
                                onCheckedChange={(checked) =>
                                    setFormData((prev) => ({ ...prev, remote: checked === true }))
                                }
                            />
                            <label htmlFor="remote" className="text-sm text-muted-foreground">
                                Remote only
                            </label>
                        </div>
                        <div className="md:col-span-4 flex flex-wrap gap-2">
                            <Button type="submit">Search</Button>
                            <Button type="button" variant="ghost" onClick={clearFilters}>
                                Clear filters
                            </Button>
                        </div>
                    </form>

                    <CardContent className="space-y-4">
                        {jobs.data.length === 0 ? (
                            <p className="text-muted-foreground text-sm">
                                No jobs match your filters just yet. Try adjusting your search or check back soon.
                            </p>
                        ) : (
                            jobs.data.map((job) => (
                                <Card key={job.id} className="border shadow-sm">
                                    <CardHeader>
                                        <CardTitle className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                            <span className="text-xl font-semibold">{job.title}</span>
                                            <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                                {job.company && (
                                                    <span className="flex items-center gap-1">
                                                        <BadgeCheck className="h-3 w-3" /> {job.company}
                                                    </span>
                                                )}
                                                {job.is_remote && <span className="rounded-full bg-emerald-100 px-3 py-1 text-emerald-700">Remote</span>}
                                                {job.location && <span>{job.location}</span>}
                                                {job.published_at && (
                                                    <span>
                                                        Posted {new Date(job.published_at).toLocaleDateString()}
                                                    </span>
                                                )}
                                            </div>
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-3">
                                        {job.summary && <p className="text-sm text-muted-foreground">{job.summary}</p>}
                                        {job.tags && job.tags.length > 0 && (
                                            <div className="flex flex-wrap gap-2">
                                                {job.tags.map((tag) => (
                                                    <Badge key={tag} variant="outline" className="lowercase">
                                                        {tag}
                                                    </Badge>
                                                ))}
                                            </div>
                                        )}
                                    </CardContent>
                                    <CardFooter className="flex items-center justify-between border-t bg-muted/40 px-6 py-4">
                                        <span className="text-xs text-muted-foreground">Tap to read more and apply</span>
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={`/creative/jobs/${job.slug}`} className="flex items-center gap-2">
                                                View job
                                                <ArrowRight className="h-4 w-4" />
                                            </Link>
                                        </Button>
                                    </CardFooter>
                                </Card>
                            ))
                        )}
                    </CardContent>

                    <CardFooter className="flex flex-wrap items-center gap-2 border-t bg-muted/40">
                        {jobs.links.map((link, index) => (
                            <Button
                                key={`${link.label}-${index}`}
                                variant={link.active ? 'default' : 'ghost'}
                                size="sm"
                                disabled={link.url === null}
                                asChild
                            >
                                <Link href={link.url ?? '#'} dangerouslySetInnerHTML={{ __html: link.label }} />
                            </Button>
                        ))}
                    </CardFooter>
                </Card>
            </div>
        </AppLayout>
    );
}
