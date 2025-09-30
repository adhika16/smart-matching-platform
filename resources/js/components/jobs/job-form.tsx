import InputError from '@/components/input-error';
import SkillSelector from '@/components/jobs/skill-selector';
import RichTextEditor from '@/components/rich-text-editor';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Form } from '@inertiajs/react';
import { LoaderCircle, Sparkles } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

interface CompensationType {
    value: string;
    label: string;
}

export interface JobFormValues {
    title?: string;
    summary?: string | null;
    description?: string;
    location?: string | null;
    is_remote?: boolean;
    status?: string;
    compensation_type?: string | null;
    skills?: string[] | null;
    category?: string | null;
    timeline_start?: string | null;
    timeline_end?: string | null;
    budget_min?: string | number | null;
    budget_max?: string | number | null;
    tags?: string[] | null;
}

interface JobFormProps {
    action: string;
    method: 'post' | 'put';
    job?: JobFormValues;
    compensationTypes: CompensationType[];
    taxonomy: {
        skills: Array<{ value: string; label: string }>;
        categories: Array<{ value: string; label: string }>;
    };
    submitLabels?: {
        draft: string;
        publish: string;
    };
}

type GenerationState = {
    loading: boolean;
    error: string | null;
    updated: boolean;
};

export default function JobForm({
    action,
    method,
    job,
    compensationTypes,
    taxonomy,
    submitLabels = {
        draft: 'Save draft',
        publish: 'Save & publish',
    },
}: JobFormProps) {
    const skillOptions = useMemo(() => taxonomy.skills ?? [], [taxonomy.skills]);
    const categoryOptions = useMemo(() => taxonomy.categories ?? [], [taxonomy.categories]);

    const [summary, setSummary] = useState(job?.summary ?? '');
    const [description, setDescription] = useState(job?.description ?? '');
    const [selectedCategory, setSelectedCategory] = useState(job?.category ?? '');
    const [selectedSkills, setSelectedSkills] = useState<string[]>(() => {
        if (job?.skills?.length) {
            return job.skills;
        }

        if (job?.tags?.length) {
            return job.tags;
        }

        return [];
    });
    const [timelineStart, setTimelineStart] = useState(job?.timeline_start ?? '');
    const [timelineEnd, setTimelineEnd] = useState(job?.timeline_end ?? '');
    const [budgetMin, setBudgetMin] = useState(job?.budget_min ? String(job.budget_min) : '');
    const [budgetMax, setBudgetMax] = useState(job?.budget_max ? String(job.budget_max) : '');
    const [isRemote, setIsRemote] = useState(job?.is_remote ?? false);
    const [generationState, setGenerationState] = useState<GenerationState>({
        loading: false,
        error: null,
        updated: false,
    });

    useEffect(() => {
        setIsRemote(job?.is_remote ?? false);
    }, [job?.is_remote]);

    const csrfToken = useMemo(() => {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        return token ?? '';
    }, []);

    const handleGenerateDescription = async () => {
        if (generationState.loading) {
            return;
        }

        const titleInput = document.getElementById('title') as HTMLInputElement | null;
        const title = titleInput?.value?.trim() ?? job?.title ?? '';

        setGenerationState({ loading: true, error: null, updated: false });

        try {
            const response = await fetch('/opportunity-owner/jobs/description-helper', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    title,
                    summary,
                    skills: selectedSkills,
                    category: selectedCategory,
                    timeline_start: timelineStart || null,
                    timeline_end: timelineEnd || null,
                    budget_min: budgetMin || null,
                    budget_max: budgetMax || null,
                }),
            });

            const payload = await response.json().catch(() => null);

            if (!response.ok || !payload) {
                throw new Error(payload?.message ?? 'AI helper could not generate copy.');
            }

            const generated = payload.description as string | undefined;
            if (generated) {
                setDescription(generated);

                if (!summary) {
                    const plain = generated.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                    setSummary(plain.slice(0, 240));
                }
            }

            setGenerationState({ loading: false, error: null, updated: true });
        } catch (error) {
            setGenerationState({
                loading: false,
                error: error instanceof Error ? error.message : 'Unexpected error while generating description.',
                updated: false,
            });
        }
    };

    return (
        <Form action={action} method={method} className="space-y-8">
            {({ processing, errors }) => (
                <div className="space-y-8">
                    <section className="grid gap-6">
                        <div className="grid gap-2">
                            <Label htmlFor="title">Job title *</Label>
                            <Input
                                id="title"
                                name="title"
                                defaultValue={job?.title ?? ''}
                                placeholder="e.g., Senior Product Designer"
                                required
                            />
                            <InputError message={errors.title} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="summary">Summary</Label>
                            <Textarea
                                id="summary"
                                name="summary"
                                value={summary}
                                onChange={(event) => setSummary(event.target.value)}
                                placeholder="Provide a short overview of the role"
                                rows={3}
                            />
                            <InputError message={errors.summary} />
                        </div>

                        <div className="grid gap-2">
                            <div className="flex items-center justify-between">
                                <Label htmlFor="description">Full description *</Label>
                                <Button
                                    type="button"
                                    variant="secondary"
                                    size="sm"
                                    className="gap-2"
                                    onClick={handleGenerateDescription}
                                    disabled={generationState.loading}
                                >
                                    {generationState.loading ? (
                                        <LoaderCircle className="h-4 w-4 animate-spin" />
                                    ) : (
                                        <Sparkles className="h-4 w-4" />
                                    )}
                                    {generationState.loading ? 'Generating…' : 'Generate with AI'}
                                </Button>
                            </div>
                            <RichTextEditor
                                name="description"
                                value={description}
                                onChange={setDescription}
                            />
                            {generationState.error && (
                                <p className="text-xs text-destructive">{generationState.error}</p>
                            )}
                            {generationState.updated && !generationState.error && (
                                <p className="text-xs text-emerald-600">
                                    AI suggestion added. Review and tweak before publishing.
                                </p>
                            )}
                            <InputError message={errors.description} />
                        </div>
                    </section>

                    <section className="grid gap-6 md:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="location">Location</Label>
                            <Input
                                id="location"
                                name="location"
                                defaultValue={job?.location ?? ''}
                                placeholder="e.g., New York, NY"
                            />
                            <InputError message={errors.location} />
                        </div>

                        <div className="flex items-center gap-2 pt-2">
                            <input type="hidden" name="is_remote" value="0" />
                            <input
                                id="is_remote"
                                name="is_remote"
                                type="checkbox"
                                checked={isRemote}
                                onChange={(event) => setIsRemote(event.target.checked)}
                                value="1"
                                className="h-4 w-4 rounded border border-input"
                            />
                            <Label htmlFor="is_remote" className="text-sm font-medium">
                                Remote friendly
                            </Label>
                        </div>
                    </section>

                    <section className="grid gap-6 md:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="compensation_type">Compensation type</Label>
                            <select
                                id="compensation_type"
                                name="compensation_type"
                                defaultValue={job?.compensation_type ?? ''}
                                className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            >
                                <option value="">Select compensation type</option>
                                {compensationTypes.map((option) => (
                                    <option key={option.value} value={option.value}>
                                        {option.label}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.compensation_type} />
                        </div>

                        <div className="grid gap-2">
                            {/* Empty grid item for layout balance */}
                        </div>
                    </section>

                    <section className="grid gap-6 md:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="timeline_start">Kick-off date</Label>
                            <Input
                                id="timeline_start"
                                name="timeline_start"
                                type="date"
                                value={timelineStart}
                                onChange={(event) => setTimelineStart(event.target.value)}
                            />
                            <InputError message={errors.timeline_start} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="timeline_end">Wrap-up date</Label>
                            <Input
                                id="timeline_end"
                                name="timeline_end"
                                type="date"
                                value={timelineEnd}
                                onChange={(event) => setTimelineEnd(event.target.value)}
                            />
                            <InputError message={errors.timeline_end} />
                        </div>
                    </section>

                    <section className="grid gap-6 md:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="budget_min">Compensation min (Rp)</Label>
                            <Input
                                id="budget_min"
                                name="budget_min"
                                type="number"
                                step="1000"
                                min="0"
                                value={budgetMin}
                                onChange={(event) => setBudgetMin(event.target.value)}
                                placeholder="5000000"
                            />
                            <InputError message={errors.budget_min} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="budget_max">Compensation max (Rp)</Label>
                            <Input
                                id="budget_max"
                                name="budget_max"
                                type="number"
                                step="1000"
                                min="0"
                                value={budgetMax}
                                onChange={(event) => setBudgetMax(event.target.value)}
                                placeholder="15000000"
                            />
                            <InputError message={errors.budget_max} />
                        </div>
                    </section>

                    <section className="grid gap-6">
                        <div className="grid gap-2">
                            <Label htmlFor="category">Category *</Label>
                            <select
                                id="category"
                                name="category"
                                value={selectedCategory}
                                onChange={(event) => setSelectedCategory(event.target.value)}
                                className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                required={categoryOptions.length > 0}
                            >
                                <option value="">Select a category</option>
                                {categoryOptions.map((option) => (
                                    <option key={option.value} value={option.value}>
                                        {option.label}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.category} />
                        </div>

                        <div className="grid gap-2">
                            <Label>Core skills *</Label>
                            <SkillSelector options={skillOptions} value={selectedSkills} onChange={setSelectedSkills} />
                            <InputError message={errors.skills} />
                            {selectedSkills.map((skill) => (
                                <input key={skill} type="hidden" name="skills[]" value={skill} />
                            ))}
                        </div>
                    </section>

                    <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div className="text-sm text-muted-foreground">
                            You can publish now or keep editing later.
                        </div>
                        <div className="flex gap-3">
                            <Button
                                type="submit"
                                variant="outline"
                                name="status"
                                value="draft"
                                disabled={processing}
                            >
                                {processing ? 'Saving…' : submitLabels.draft}
                            </Button>
                            <Button
                                type="submit"
                                name="status"
                                value="published"
                                disabled={processing}
                            >
                                {processing ? 'Publishing…' : submitLabels.publish}
                            </Button>
                        </div>
                    </div>
                </div>
            )}
        </Form>
    );
}
