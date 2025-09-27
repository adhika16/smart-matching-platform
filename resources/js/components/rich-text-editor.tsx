import { useEffect, useMemo, useState } from 'react';
import type { Editor } from '@tiptap/core';
import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import { Bold, Italic, List, ListOrdered, Redo2, Undo2 } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

interface RichTextEditorProps {
    name: string;
    value: string;
    onChange: (html: string) => void;
    placeholder?: string;
    disabled?: boolean;
}

export default function RichTextEditor({ name, value, onChange, placeholder, disabled }: RichTextEditorProps) {
    const [isClient, setIsClient] = useState(false);

    useEffect(() => {
        setIsClient(true);
    }, []);

    const editor = useEditor(
        {
            editable: !disabled,
            extensions: [
                StarterKit.configure({
                    bulletList: { keepMarks: true },
                    orderedList: { keepMarks: true },
                }),
                Placeholder.configure({
                    placeholder: placeholder ?? 'Write a compelling description that highlights deliverables, expectations, and timelines…',
                }),
            ],
            content: value,
            editorProps: {
                attributes: {
                    class: 'prose prose-sm dark:prose-invert focus:outline-none min-h-[200px]',
                },
            },
            onUpdate({ editor: currentEditor }: { editor: Editor }) {
                onChange(currentEditor.getHTML());
            },
        },
        [placeholder, disabled]
    );

    useEffect(() => {
        if (!editor) {
            return;
        }

        const current = editor.getHTML();
        if (value !== current) {
            editor.commands.setContent(value, false);
        }
    }, [value, editor]);

    const controls = useMemo(() => (
        <div className="flex flex-wrap gap-2 border-b bg-muted/40 px-3 py-2">
            <Button
                type="button"
                variant={editor?.isActive('bold') ? 'default' : 'ghost'}
                size="icon"
                title="Bold"
                onClick={() => editor?.chain().focus().toggleBold().run()}
                disabled={!editor}
            >
                <Bold className="h-4 w-4" />
            </Button>
            <Button
                type="button"
                variant={editor?.isActive('italic') ? 'default' : 'ghost'}
                size="icon"
                title="Italic"
                onClick={() => editor?.chain().focus().toggleItalic().run()}
                disabled={!editor}
            >
                <Italic className="h-4 w-4" />
            </Button>
            <Button
                type="button"
                variant={editor?.isActive('bulletList') ? 'default' : 'ghost'}
                size="icon"
                title="Bulleted list"
                onClick={() => editor?.chain().focus().toggleBulletList().run()}
                disabled={!editor}
            >
                <List className="h-4 w-4" />
            </Button>
            <Button
                type="button"
                variant={editor?.isActive('orderedList') ? 'default' : 'ghost'}
                size="icon"
                title="Numbered list"
                onClick={() => editor?.chain().focus().toggleOrderedList().run()}
                disabled={!editor}
            >
                <ListOrdered className="h-4 w-4" />
            </Button>
            <Button
                type="button"
                variant="ghost"
                size="icon"
                title="Undo"
                onClick={() => editor?.chain().focus().undo().run()}
                disabled={!editor || !editor.can().undo()}
            >
                <Undo2 className="h-4 w-4" />
            </Button>
            <Button
                type="button"
                variant="ghost"
                size="icon"
                title="Redo"
                onClick={() => editor?.chain().focus().redo().run()}
                disabled={!editor || !editor.can().redo()}
            >
                <Redo2 className="h-4 w-4" />
            </Button>
        </div>
    ), [editor]);

    if (!isClient) {
        return (
            <div className="rounded-md border bg-background">
                <textarea
                    name={name}
                    defaultValue={value}
                    className="h-52 w-full rounded-md border-0 bg-transparent p-4 text-sm focus-visible:outline-none"
                    placeholder={placeholder}
                    disabled
                />
            </div>
        );
    }

    return (
        <div className={cn('rounded-md border', disabled && 'opacity-60')}>
            {controls}
            <div className="px-3 py-2">
                <EditorContent editor={editor} />
            </div>
            <input type="hidden" name={name} value={value} />
        </div>
    );
}
