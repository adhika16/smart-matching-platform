import ProfileController from '@/actions/App/Http/Controllers/ProfileController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Form } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';

interface VerificationDocument {
    original_name: string;
    uploaded_at?: string | null;
    url?: string | null;
}

interface OpportunityOwnerProfileFormProps {
    profile?: {
        company_name?: string;
        company_description?: string;
        company_website?: string;
        company_size?: string;
        industry?: string;
        verification_documents?: VerificationDocument[];
    };
}

export default function OpportunityOwnerProfileForm({ profile }: OpportunityOwnerProfileFormProps) {
    return (
        <Form
            {...ProfileController.updateOpportunityOwner.form()}
            resetOnSuccess={[]}
            className="space-y-6"
            encType="multipart/form-data"
        >
            {({ processing, errors }) => {
                const documentError = Object.entries(errors ?? {}).find(([key]) =>
                    key.startsWith('verification_documents')
                )?.[1];

                return (
                    <>
                        {profile?.verification_documents && profile.verification_documents.length > 0 && (
                            <div className="rounded-lg border border-dashed p-4">
                                <div className="mb-2 flex items-center justify-between">
                                    <span className="text-sm font-medium">Uploaded evidence</span>
                                    <span className="text-xs text-muted-foreground">
                                        {profile.verification_documents.length} file
                                        {profile.verification_documents.length > 1 ? 's' : ''}
                                    </span>
                                </div>
                                <ul className="space-y-2 text-sm">
                                    {profile.verification_documents.map((document, index) => (
                                        <li
                                            key={`${document.original_name}-${index}`}
                                            className="flex flex-col gap-1 md:flex-row md:items-center md:justify-between"
                                        >
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

                        <div className="grid gap-2">
                            <Label htmlFor="verification_documents">Verification documents</Label>
                            <input
                                id="verification_documents"
                                name="verification_documents[]"
                                type="file"
                                multiple
                                accept=".pdf,.png,.jpg,.jpeg,.webp"
                                className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-muted file:px-3 file:py-2 file:text-sm"
                            />
                            <p className="text-xs text-muted-foreground">
                                Upload supporting evidence (business registration, tax document, portfolio PDF, etc.). Max 10&nbsp;MB per file.
                            </p>
                            <InputError message={documentError} />
                        </div>

                        <div className="grid gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="company_name">Company Name *</Label>
                                <Input
                                    id="company_name"
                                    name="company_name"
                                    placeholder="Your company name"
                                    defaultValue={profile?.company_name || ''}
                                    required
                                />
                                <InputError message={errors.company_name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="company_description">Company Description</Label>
                                <Textarea
                                    id="company_description"
                                    name="company_description"
                                    placeholder="Tell us about your company, what you do, and your mission..."
                                    defaultValue={profile?.company_description || ''}
                                    rows={4}
                                />
                                <InputError message={errors.company_description} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="company_website">Company Website</Label>
                                <Input
                                    id="company_website"
                                    name="company_website"
                                    type="url"
                                    placeholder="https://yourcompany.com"
                                    defaultValue={profile?.company_website || ''}
                                />
                                <InputError message={errors.company_website} />
                            </div>

                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="company_size">Company Size</Label>
                                    <select
                                        name="company_size"
                                        defaultValue={profile?.company_size || ''}
                                        className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    >
                                        <option value="">Select size</option>
                                        <option value="1-10">1-10 employees</option>
                                        <option value="11-50">11-50 employees</option>
                                        <option value="51-200">51-200 employees</option>
                                        <option value="201-500">201-500 employees</option>
                                        <option value="500+">500+ employees</option>
                                    </select>
                                    <InputError message={errors.company_size} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="industry">Industry</Label>
                                    <Input
                                        id="industry"
                                        name="industry"
                                        placeholder="e.g., Technology, Healthcare, Finance"
                                        defaultValue={profile?.industry || ''}
                                    />
                                    <InputError message={errors.industry} />
                                </div>
                            </div>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="verification_note">Context for reviewers</Label>
                            <Textarea
                                id="verification_note"
                                name="verification_note"
                                placeholder="Add any helpful details for our verification team."
                                rows={3}
                            />
                            <InputError message={errors.verification_note} />
                            <p className="text-xs text-muted-foreground">
                                Optional: share extra context about the documents you uploaded.
                            </p>
                        </div>

                        <Button type="submit" className="w-full" disabled={processing}>
                            {processing && <LoaderCircle className="mr-2 h-4 w-4 animate-spin" />}
                            Save Profile
                        </Button>
                    </>
                );
            }}
        </Form>
    );
}
