/* eslint-disable @typescript-eslint/no-explicit-any */
import ProfileController from '@/actions/App/Http/Controllers/ProfileController';
import { Form } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface CreativeProfileFormProps {
    profile?: any;
}

export default function CreativeProfileForm({ profile }: CreativeProfileFormProps) {
    return (
        <Form
            {...ProfileController.updateCreative.form()}
            resetOnSuccess={[]}
            className="space-y-6"
        >
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="bio">Bio</Label>
                            <Textarea
                                id="bio"
                                name="bio"
                                placeholder="Tell us about yourself and your creative work..."
                                defaultValue={profile?.bio || ''}
                                rows={4}
                            />
                            <InputError message={errors.bio} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="location">Location</Label>
                            <Input
                                id="location"
                                name="location"
                                placeholder="e.g., New York, NY"
                                defaultValue={profile?.location || ''}
                            />
                            <InputError message={errors.location} />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="hourly_rate">Hourly Rate ($)</Label>
                                <Input
                                    id="hourly_rate"
                                    name="hourly_rate"
                                    type="number"
                                    placeholder="50"
                                    defaultValue={profile?.hourly_rate || ''}
                                />
                                <InputError message={errors.hourly_rate} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="experience_level">Experience Level</Label>
                                <select
                                    name="experience_level"
                                    defaultValue={profile?.experience_level || ''}
                                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                >
                                    <option value="">Select level</option>
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="expert">Expert</option>
                                </select>
                                <InputError message={errors.experience_level} />
                            </div>
                        </div>

                        <div className="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                id="available_for_work"
                                name="available_for_work"
                                defaultChecked={profile?.available_for_work ?? true}
                                className="h-4 w-4 rounded border border-primary"
                            />
                            <Label htmlFor="available_for_work">Available for work</Label>
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
