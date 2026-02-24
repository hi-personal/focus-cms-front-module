<?php

namespace Modules\FocusCmsFrontModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\PostTerm;
use App\Models\PostTermMeta;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    protected $currentThemeName;

    public function __construct()
    {
        $this->currentThemeName = Option::find('currentThemeName')->value;
    }

    public function index(Request $request): View
    {
        $categories = PostTerm::where('post_taxonomy_name', 'categories')->get();

        return view("theme::categories", [
            'categories'   => $categories,
            'currentTheme' => $this->currentThemeName
        ]);
    }

    public function show(Request $request, $category): View
    {
        $category = PostTerm::where('post_taxonomy_name', 'categories')
            ->where('name', $category)
            ->firstOrFail();

        $head_image_url = PostTermMeta::where('post_term_id', $category->id)
            ->where('name', 'head_image_url')
            ->value('value');

        $description = PostTermMeta::where('post_term_id', $category->id)
            ->where('name', 'description')
            ->value('value');

        $website_setting_posts_per_page = Option::getValue('website_setting_posts_per_page');

        $posts = $category->posts()
            ->where('status', 'published')
            ->paginate($website_setting_posts_per_page)
            ->withQueryString();

        return view("theme::category", [
            'category'                       => $category,
            'posts'                          => $posts,
            'website_setting_posts_per_page' => $website_setting_posts_per_page,
            'head_image_url'                 => $head_image_url,
            'description'                    => $description,
            'currentTheme'                   => $this->currentThemeName,
        ]);
    }
}