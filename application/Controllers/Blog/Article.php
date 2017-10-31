<?php namespace App\Controllers\Blog;

use App\Models\Blog\CommentsModel;
use App\Models\Blog\ArticleModel;

/**
 * Class Article
 *
 * @package App\Controllers\Blog
 */
class Article extends Application
{

    /**
     * @var \App\Models\Blog\ArticleModel
     */
    protected $article_model;

    /**
     * Article constructor.
     *
     * @param array ...$params
     */
    public function __construct(...$params)
    {
        parent::__construct(...$params);
        $this->stitle = 'Article';
        $this->article_model = new ArticleModel();
        $this->comments_model = new CommentsModel();
    }

    /**
     * @return string
     */
    public function index()
    {
        return redirect('404');
    }

    /**
     * @param string $link
     *
     * @return string
     */
    public function View(string $link): self
    {
        $info = $this->article_model->GetArticle('link', $link);

        if ($this->request->isValidIP($this->request->getIPAddress())) {
            $this->article_model->add_view($info->id, $this->request->getIPAddress());
        }

        $this->data['get_info_article'] = $info;
        $this->data['get_coms'] = $this->comments_model->GetComs($info->id);
        $this->data['PostView'] = $this->article_model->nb_PostView($info->id);
        $this->data['related_article'] = $this->article_model->GetRelated($info->keyword);

        return $this->render('article/view', $info->title, $info->keyword);
    }
}