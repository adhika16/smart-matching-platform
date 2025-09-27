import DOMPurify from 'dompurify';
import { useMemo } from 'react';
import { cn } from '@/lib/utils';

interface RichTextContentProps {
    html?: string | null;
    className?: string;
}

export default function RichTextContent({ html, className }: RichTextContentProps) {
    const sanitized = useMemo(() => {
        if (!html) {
            return '';
        }

        return DOMPurify.sanitize(html, {
            USE_PROFILES: { html: true },
            ADD_ATTR: ['target', 'rel'],
        });
    }, [html]);

    if (!sanitized) {
        return null;
    }

    return (
        <div
            className={cn('prose prose-sm dark:prose-invert space-y-4', className)}
            dangerouslySetInnerHTML={{ __html: sanitized }}
        />
    );
}
