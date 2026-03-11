<?php

namespace Modules\FocusCmsFrontModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\PostTerm;
use App\Models\PostTermMeta;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxonomyController extends Controller
{
    protected $currentThemeName;

    public function __construct()
    {
        $this->currentThemeName = Option::find('currentThemeName')->value;
    }

    public function index(Request $request): View
    {
        $taxonomy = $request->route('taxonomy');

        $config = config("taxonomies.$taxonomy");

        $terms = PostTerm::where(
            'post_taxonomy_name',
            $taxonomy
        )->get();

        return view(
            "theme::".$config['views']['index'],
            [
                'terms'=>$terms,
                'taxonomy'=>$taxonomy,
                'config'=>$config,
                'currentTheme'=>$this->currentThemeName
            ]
        );
    }

    public function show(Request $request,$term): View
    {
        $taxonomy = $request->route('taxonomy');

        $config = config("taxonomies.$taxonomy");

        $term = PostTerm::where(
            'post_taxonomy_name',
            $taxonomy
        )
        ->where(
            'name',
            $term
        )
        ->firstOrFail();

        $meta = PostTermMeta::where(
            'post_term_id',
            $term->id
        )
        ->pluck(
            'value',
            'name'
        );

        $postsPerPage = Option::getValue(
            'website_setting_posts_per_page'
        );

        $query = $term->posts()
            ->where(
                'status',
                'published'
            );

        if($config['hierarchical']){
            $posts = $query
                ->paginate($postsPerPage)
                ->withQueryString();
        }
        else{
            $posts = $query->get();
        }

        return view(
            "theme::".$config['views']['show'],
            [
                'term'=>$term,
                'taxonomy'=>$taxonomy,
                'config'=>$config,
                'posts'=>$posts,
                'meta'=>$meta,
                'website_setting_posts_per_page'=>$postsPerPage,
                'currentTheme'=>$this->currentThemeName
            ]
        );
    }
}