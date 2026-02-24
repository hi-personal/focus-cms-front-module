<?php

namespace Modules\FocusCmsFrontModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\Post;
use App\Models\PostMeta;
use Illuminate\View\View;

class PostController extends Controller
{
    protected $currentThemeName;
    protected int $homePageId;

    public function __construct()
    {
        $this->currentThemeName = Option::find('currentThemeName')->value;
        $this->homePageId = Option::find('website_setting_start_page_id')->value;
    }

    public function home(): View
    {
        $page = Post::findOrFail($this->homePageId);

        if (
            auth()->check() === false
            && in_array($page->post_type_name, ['post', 'page'])
            && $page->status !== 'published'
        ) {
            abort(404);
        }

        $rawContent = $page->content;
        $page->content = markdownToHtml($rawContent);

        $features = $this->detectContentFeatures($rawContent);

        $meta_title = PostMeta::where('post_id', $page->id)->where('name', 'meta_title')->value('value');
        $meta_description = PostMeta::where('post_id', $page->id)->where('name', 'meta_description')->value('value');

        $isHome = $this->homePageId == $page->id;

        $headImageUrl =
            PostMeta::where('post_id', $page->id)->where('name', 'head_image_url')->value('value')
            ?? PostMeta::where('post_id', $this->homePageId)->where('name', 'head_image_url')->value('value');

        $category = $page->terms->where('post_taxonomy_name', 'categories')->first();
        $tags = $page->terms->where('post_taxonomy_name', 'tags');

        return view("theme::page", [
            'post'             => $page,
            'category'         => $category,
            'tags'             => $tags,
            'meta_title'       => $meta_title,
            'meta_description' => $meta_description,
            'currentTheme'     => $this->currentThemeName,
            'features'         => $features,
            'is_home'          => $isHome,
            'homePageId'       => $this->homePageId,
            'headImageUrl'     => $headImageUrl,
        ]);
    }

    public function show($slug)
    {
        $post = Post::where('name', $slug)->firstOrFail();

        if (
            auth()->check() === false
            && in_array($post->post_type_name, ['post', 'page'])
            && $post->status !== 'published'
        ) {
            abort(404);
        }

        $rawContent = $post->content;
        $post->content = markdownToHtml($rawContent);

        $features = $this->detectContentFeatures($rawContent);

        $meta_title = PostMeta::where('post_id', $post->id)->where('name', 'meta_title')->value('value');
        $meta_description = PostMeta::where('post_id', $post->id)->where('name', 'meta_description')->value('value');

        $isHome = $this->homePageId == $post->id;

        $headImageUrl =
            PostMeta::where('post_id', $post->id)->where('name', 'head_image_url')->value('value')
            ?? PostMeta::where('post_id', $this->homePageId)->where('name', 'head_image_url')->value('value');

        $category = $post->terms->where('post_taxonomy_name', 'categories')->first();
        $tags = $post->terms->where('post_taxonomy_name', 'tags');

        $nextPost = $post->nextPost();
        $prevPost = $post->prevPost();

        $prevPostInTerm = empty($category) ? null : $category->prevPostInTerm($post, $category);
        $nextPostInTerm = empty($category) ? null : $category->nextPostInTerm($post, $category);

        $viewName = match ($post->post_type_name) {
            'post'  => 'theme::post',
            default => 'theme::page'
        };

        return view($viewName, [
            'post'             => $post,
            'headImageUrl'     => $headImageUrl,
            'meta_title'       => $meta_title,
            'meta_description' => $meta_description,
            'category'         => $category,
            'tags'             => $tags,
            'nextPost'         => $nextPost,
            'prevPost'         => $prevPost,
            'nextPostInTerm'   => $nextPostInTerm,
            'prevPostInTerm'   => $prevPostInTerm,
            'currentTheme'     => $this->currentThemeName,
            'features'         => $features,
            'is_home'          => $isHome,
            'homePageId'       => $this->homePageId,
        ]);
    }

    private function detectContentFeatures(?string $content): array
    {
        if (empty($content)) {
            return [
                'code'    => false,
                'image'   => false,
                'gallery' => false,
                'file'    => false,
            ];
        }

        return [
            'code' =>
                str_contains($content, '```')
                || preg_match('/\{\{code(?::[a-z0-9_-]+)?\}\}/i', $content)
                || str_contains($content, '<pre')
                || str_contains($content, '<code'),

            'image' =>
                preg_match('/\[image\s+id\(/i', $content),

            'gallery' =>
                preg_match('/\[gallery\s+ids\(/i', $content),

            'file' =>
                preg_match('/\[file\s+id\(/i', $content),
        ];
    }
}