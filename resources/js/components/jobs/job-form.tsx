import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Form } from '@inertiajs/react';
import { useMemo } from 'react';

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
    compensation_min?: string | number | null;
    compensation_max?: string | number | null;
    tags?: string[] | null;
}

interface JobFormProps {
    action: string;
    method: 'post' | 'put';
    job?: JobFormValues;
    compensationTypes: CompensationType[];
    submitLabels?: {
        draft: string;
        publish: string;
    };
}

export default function JobForm({
    action,
    method,
    job,
    compensationTypes,
    submitLabels = {
        draft: 'Save draft',
        publish: 'Save & publish',
    },
}: JobFormProps) {
    const tagsAsString = useMemo(() => {
        if (!job?.tags?.length) {
            return '';
        }

        return job.tags.join(', ');
    }, [job?.tags]);

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
                                defaultValue={job?.summary ?? ''}
                                placeholder="Provide a short overview of the role"
                                rows={3}
                            />
                            <InputError message={errors.summary} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="description">Full description *</Label>
                            <Textarea
                                id="description"
                                name="description"
                                defaultValue={job?.description ?? ''}
                                placeholder="Describe responsibilities, requirements, and benefits"
                                rows={8}
                                required
                            />
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
                            <input
                                id="is_remote"
                                name="is_remote"
                                type="checkbox"
                                defaultChecked={job?.is_remote ?? false}
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

                        <div className="grid grid-cols-2 gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="compensation_min">Min compensation</Label>
                                <Input
                                    id="compensation_min"
                                    name="compensation_min"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    defaultValue={job?.compensation_min ?? ''}
                                    placeholder="e.g., 75000"
                                />
                                <InputError message={errors.compensation_min} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="compensation_max">Max compensation</Label>
                                <Input
                                    id="compensation_max"
                                    name="compensation_max"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    defaultValue={job?.compensation_max ?? ''}
                                    placeholder="e.g., 95000"
                                />
                                <InputError message={errors.compensation_max} />
                            </div>
                        </div>
                    </section>

                    <section className="grid gap-2">
                        <Label htmlFor="tags">Tags</Label>
                        <Input
                            id="tags"
                            name="tags"
                            defaultValue={tagsAsString}
                            placeholder="Comma-separated skills or keywords"
                        />
                        <InputError message={errors.tags} />
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
