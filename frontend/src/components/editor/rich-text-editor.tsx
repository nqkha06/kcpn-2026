"use client";

import { cn } from "@/lib/utils";
import {
    Bold,
    Code,
    Heading2,
    Heading3,
    Italic,
    Link2,
    List,
    ListOrdered,
    Quote,
    Redo2,
    Strikethrough,
    Undo2,
} from "lucide-react";
import { useEffect, useRef, type ReactNode } from "react";

export function RichTextEditor({
    value,
    onChange,
    placeholder = "Start writing...",
    className,
}: {
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    className?: string;
}) {
    const editorRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (editorRef.current && editorRef.current.innerHTML !== value)
            editorRef.current.innerHTML = value;
    }, [value]);

    function command(name: string, argument?: string): void {
        editorRef.current?.focus();
        document.execCommand(name, false, argument);
        onChange(editorRef.current?.innerHTML ?? "");
    }

    function link(): void {
        const url = window.prompt("Enter URL", "https://");
        if (url?.trim()) command("createLink", url.trim());
    }

    return (
        <div className={cn("overflow-hidden rounded-md border", className)}>
            <div className="flex flex-wrap gap-1 border-b bg-muted/30 p-2">
                <EditorButton label="Bold" onClick={() => command("bold")}>
                    <Bold />
                </EditorButton>
                <EditorButton label="Italic" onClick={() => command("italic")}>
                    <Italic />
                </EditorButton>
                <EditorButton
                    label="Strike"
                    onClick={() => command("strikeThrough")}
                >
                    <Strikethrough />
                </EditorButton>
                <EditorButton
                    label="Code"
                    onClick={() => command("formatBlock", "pre")}
                >
                    <Code />
                </EditorButton>
                <EditorButton
                    label="Heading 2"
                    onClick={() => command("formatBlock", "h2")}
                >
                    <Heading2 />
                </EditorButton>
                <EditorButton
                    label="Heading 3"
                    onClick={() => command("formatBlock", "h3")}
                >
                    <Heading3 />
                </EditorButton>
                <EditorButton
                    label="Bullet list"
                    onClick={() => command("insertUnorderedList")}
                >
                    <List />
                </EditorButton>
                <EditorButton
                    label="Ordered list"
                    onClick={() => command("insertOrderedList")}
                >
                    <ListOrdered />
                </EditorButton>
                <EditorButton
                    label="Quote"
                    onClick={() => command("formatBlock", "blockquote")}
                >
                    <Quote />
                </EditorButton>
                <EditorButton label="Link" onClick={link}>
                    <Link2 />
                </EditorButton>
                <EditorButton label="Undo" onClick={() => command("undo")}>
                    <Undo2 />
                </EditorButton>
                <EditorButton label="Redo" onClick={() => command("redo")}>
                    <Redo2 />
                </EditorButton>
            </div>
            <div
                ref={editorRef}
                role="textbox"
                aria-multiline="true"
                data-placeholder={placeholder}
                contentEditable
                suppressContentEditableWarning
                onInput={(event) => onChange(event.currentTarget.innerHTML)}
                className="min-h-[360px] px-4 py-3 text-sm leading-7 outline-none empty:before:text-muted-foreground empty:before:content-[attr(data-placeholder)] [&_h2]:text-xl [&_h2]:font-semibold [&_h3]:text-lg [&_h3]:font-semibold"
            />
        </div>
    );
}

function EditorButton({
    label,
    onClick,
    children,
}: {
    label: string;
    onClick: () => void;
    children: ReactNode;
}) {
    return (
        <button
            type="button"
            aria-label={label}
            title={label}
            onMouseDown={(event) => event.preventDefault()}
            onClick={onClick}
            className="inline-flex size-8 items-center justify-center rounded-md hover:bg-accent [&_svg]:size-4"
        >
            {children}
        </button>
    );
}
