import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { useMemo } from 'react';

type SkillOption = {
    value: string;
    label: string;
};

interface SkillSelectorProps {
    options: SkillOption[];
    value: string[];
    onChange: (next: string[]) => void;
}

export default function SkillSelector({ options, value, onChange }: SkillSelectorProps) {
    const selected = useMemo(() => new Set(value), [value]);

    const toggleSkill = (option: SkillOption) => {
        const next = new Set(selected);
        if (next.has(option.value)) {
            next.delete(option.value);
        } else {
            next.add(option.value);
        }

        onChange(Array.from(next));
    };

    return (
        <div className="space-y-3">
            <div className="flex flex-wrap gap-2">
                {options.map((option) => {
                    const isSelected = selected.has(option.value);

                    return (
                        <Button
                            key={option.value}
                            type="button"
                            variant={isSelected ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => toggleSkill(option)}
                            className={cn('rounded-full px-3 py-1 text-sm')}
                        >
                            {option.label}
                        </Button>
                    );
                })}
            </div>

            {selected.size > 0 ? (
                <div className="flex flex-wrap gap-2">
                    {value.map((skill) => {
                        const label = options.find((option) => option.value === skill)?.label ?? skill;
                        return (
                            <Badge key={skill} variant="secondary" className="lowercase">
                                {label}
                            </Badge>
                        );
                    })}
                </div>
            ) : (
                <p className="text-sm text-muted-foreground">
                    Select at least one core skill so creatives understand the focus of this opportunity.
                </p>
            )}
        </div>
    );
}
