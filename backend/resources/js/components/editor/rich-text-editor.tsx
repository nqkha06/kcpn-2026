import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import { Bold, Code, Heading2, Heading3, Italic, Link2, List, ListOrdered, Quote, Redo2, Strikethrough, Undo2 } from 'lucide-react';
import { useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

interface RichTextEditorProps {
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    className?: string;
}

export default function RichTextEditor({
    value,
    onChange,
    placeholder = 'Start writing...',
    className,
}: RichTextEditorProps) {
    const editor = useEditor({
        immediatelyRender: false,
        extensions: [
            StarterKit,
            Link.configure({
                openOnClick: false,
                autolink: true,
                defaultProtocol: 'https',
                HTMLAttributes: {
                    class: 'text-primary underline underline-offset-2',
                    rel: 'noopener noreferrer nofollow',
                    target: '_blank',
                },
            }),
            Placeholder.configure({
                placeholder,
            }),
        ],
        content: value || '',
        onUpdate: ({ editor: currentEditor }) => {
            onChange(currentEditor.getHTML());
        },
        editorProps: {
            attributes: {
                class:
                    'prose prose-slate dark:prose-invert max-w-none min-h-[360px] px-4 py-3 focus:outline-none [&_h2]:text-xl [&_h2]:font-semibold [&_h3]:text-lg [&_h3]:font-semibold [&_p]:leading-7',
            },
        },
    });

    useEffect(() => {
        if (!editor) {
            return;
        }

        const current = editor.getHTML();
        const next = value || '';

        if (current !== next) {
            editor.commands.setContent(next, { emitUpdate: false });
        }
    }, [editor, value]);

    const toggleLink = () => {
        if (!editor) {
            return;
        }

        const previousUrl = editor.getAttributes('link').href as string | undefined;
        const url = window.prompt('Enter URL', previousUrl || '');

        if (url === null) {
            return;
        }

        const normalizedUrl = url.trim();

        if (normalizedUrl === '') {
            editor.chain().focus().extendMarkRange('link').unsetLink().run();
            return;
        }

        editor.chain().focus().extendMarkRange('link').setLink({ href: normalizedUrl }).run();
    };

    if (!editor) {
        return null;
    }

    return (
        <div className={cn('overflow-hidden rounded-md border', className)}>
            <div className="flex flex-wrap gap-1 border-b bg-muted/30 p-2">
                <ToolbarButton onClick={() => editor.chain().focus().toggleBold().run()} active={editor.isActive('bold')}>
                    <Bold className="h-4 w-4" />
                </ToolbarButton>
                <ToolbarButton onClick={() => editor.chain().focus().toggleItalic().run()} active={editor.isActive('italic')}>
                    <Italic className="h-4 w-4" />
                </ToolbarButton>
                <ToolbarButton onClick={() => editor.chain().focus().toggleStrike().run()} active={editor.isActive('strike')}>
                    <Strikethrough className="h-4 w-4" />
                </ToolbarButton>
                <ToolbarButton onClick={() => editor.chain().focus().toggleCode().run()} active={editor.isActive('code')}>
                    <Code className="h-4 w-4" />
                </ToolbarButton>
                <ToolbarButton onClick={() => editor.chain().focus().toggleHeading({ level: 2 }).run()} active={editor.isActive('heading', { level: 2 })}>
                    <Heading2 className="h-4 w-4" />
                </ToolbarButton>
                <ToolbarButton onClick={() => editor.chain().focus().toggleHeading({ level: 3 }).run()} active={editor.isActive('heading', { level: 3 })}>
                    <Heading3 className="h-4 w-4" />
                </ToolbarButton>
                <ToolbarButton onClick={() => editor.chain().focus().toggleBulletList().run()} active={editor.isActive('bulletList')}>
                    <List className="h-4 w-4" />
                </ToolbarButton>
                <ToolbarButton onClick={() => editor.chain().focus().toggleOrderedList().run()} active={editor.isActive('orderedList')}>
                    <ListOrdered className="h-4 w-4" />
                </ToolbarButton>
                <ToolbarButton onClick={() => editor.chain().focus().toggleBlockquote().run()} active={editor.isActive('blockquote')}>
                    <Quote className="h-4 w-4" />
                </ToolbarButton>
                <ToolbarButton onClick={toggleLink} active={editor.isActive('link')}>
                    <Link2 className="h-4 w-4" />
                </ToolbarButton>
                <ToolbarButton onClick={() => editor.chain().focus().undo().run()}>
                    <Undo2 className="h-4 w-4" />
                </ToolbarButton>
                <ToolbarButton onClick={() => editor.chain().focus().redo().run()}>
                    <Redo2 className="h-4 w-4" />
                </ToolbarButton>
            </div>

            <EditorContent editor={editor} />
        </div>
    );
}

interface ToolbarButtonProps {
    active?: boolean;
    onClick: () => void;
    children: React.ReactNode;
}

function ToolbarButton({ active = false, onClick, children }: ToolbarButtonProps) {
    return (
        <Button
            type="button"
            size="icon"
            variant={active ? 'default' : 'ghost'}
            className="h-8 w-8"
            onClick={onClick}
        >
            {children}
        </Button>
    );
}
