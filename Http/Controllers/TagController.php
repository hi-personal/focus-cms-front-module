<?php

namespace Modules\FocusCmsFrontModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\PostTerm;
use App\Models\PostTermMeta;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    protected $currentThemeName;

    public function __construct()
    {
        $this->currentThemeName = Option::find('currentThemeName')->value;
    }

    public function index(Request $request): View
    {
        $tags = PostTerm::where('post_taxonomy_name', 'tags')->get();

        return view("theme::tags", [
            'tags'         => $tags,
            'currentTheme' => $this->currentThemeName
        ]);
    }

    public function show(Request $request, $tag): View
    {
        $tag = PostTerm::findTerm(
            'tags',
            $tag
        );

        abort_if(
            empty($tag),
            404
        );

        $description = PostTermMeta::where(
                'post_term_id',
                $tag->id
            )
            ->where(
                'name',
                'description'
            )
            ->where(
                'locale',
                cms_locale()
            )
            ->value('value');

        return view(
            "theme::tag",
            [
                'tag'          => $tag,
                'description'  => $description,
                'currentTheme' => $this->currentThemeName
            ]
        );
    }
}