<?php

namespace App\Http\Controllers;

use App\Enums\BaseStatusEnum;
use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;

class PublicPageShowController extends Controller
{
    public function __invoke(string $slug): Response
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('status', BaseStatusEnum::PUBLISHED->value)
            ->firstOrFail();

        return Inertia::render('Page/Show', [
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'image' => $page->image,
                'content' => $page->content,
                'meta_title' => $page->meta_title,
                'meta_description' => $page->meta_description,
                'meta_keywords' => $page->meta_keywords,
            ],
        ]);
    }
}
