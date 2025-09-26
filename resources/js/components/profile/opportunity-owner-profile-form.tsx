import ProfileController from '@/actions/App/Http/Controllers/ProfileController';
import { Form } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface OpportunityOwnerProfileFormProps {
    profile?: {
        company_name?: string;
        company_description?: string;
        company_website?: string;
        company_size?: string;
        industry?: string;
    };
}

export default function OpportunityOwnerProfileForm({ profile }: OpportunityOwnerProfileFormProps) {
    return (
        <Form
            {...ProfileController.updateOpportunityOwner.form()}
            resetOnSuccess={[]}
            className="space-y-6"
        >
            {({ processing, errors }) => (
                <>
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

                        <div className="grid grid-cols-2 gap-4">
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

                    <Button type="submit" className="w-full" disabled={processing}>
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin mr-2" />}
                        Save Profile
                    </Button>
                </>
            )}
        </Form>
    );
}
