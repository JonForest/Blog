<?php
//require "assets/php/dbconnection.php";
require_once "app/repositories/articleRepository.php";
require_once "app/repositories/staticContentRepository.php";

$app->get(
    '/admin',
    function() use ($app) {
        $articleRepo = new ArticleRepository();
        $results = $articleRepo->getAllArticles();
        $app->render(
            'allarticles.html.smarty',
            array ('results' => $results)
        );
    }
);

$app->get(
    '/admin/article/delete/:id',
    /**
     * @param {integer} $id
     * @returns {string}
     */
    function($id) use ($app) {
        $articleRepo = new ArticleRepository();
        $result =
            '{"result": ' .
            ($articleRepo->deleteArticle($id) ? 'true' : 'false') .
            '}';
        header("Content-Type: application/json");
        echo $result;
        exit;
    }
)->conditions(array('id'=>'\d+'));

$app->get(
    '/admin/article/edit/:id',
    /**
     * @param {integer} $id
     * @returns {string}
     */
    function($id) use ($app) {
        $articleRepo = new ArticleRepository();
        $article = $articleRepo->getArticleByPK($id);
        $app->render(
            'editarticle.html.smarty',
            $article
        );
        exit;
    }
)->conditions(array('id'=>'\d+'));

$app->post(
    '/admin/article/save/:id',
    function($id) use ($app) {
        $article = new stdClass();


        $article->html = $app->request->params("text");
        $article->title = $app->request->params("title");
        $article->tldr = $app->request->params("tldr");
        $article->articleId = $app->request->params("articleId");
        $article->refs = json_decode($app->request->params("references"));
        $article->tags = json_decode($app->request->params("tags"));

        $articleRepo = new ArticleRepository();
        echo $articleRepo->saveArticle($article);
        exit;
    }
)->conditions(array('id'=>'\d+'));


$app->get(
    '/admin/article/preview/:id',
    function($id) use ($app) {
        $data = getArticleInfoByPK($id);
        $data['preview'] = true;
        $data['articlePK'] = $id;
        $app->render(
            'article.html.smarty',
            $data
        );
    }
)->conditions(array('id'=>'\d+'));

$app->get(
    '/admin/article/publish/:id',
    function($id) use ($app) {
        $articleRepo = new ArticleRepository();
        $articleRepo->publishArticle($id);
        $app->response->headers->set('Location', '../../../admin');
    }
)->conditions(array('id'=>'\d+'));

$app->get(
    '/admin/article/edit/new',
    function() use ($app) {
        $articleRepo = new ArticleRepository();
        $data = $articleRepo->getNewArticle();
        $app->render(
            'editarticle.html.smarty',
            $data
        );
    }
);


function getArticleInfoByPK($articlePK)
{
    $articleRepo = new ArticleRepository();
    $menu = $articleRepo->buildMenu(0);
    $article = $articleRepo->getArticleByPK($articlePK);

    return  array(
        'menuHtml'=>$menu,
        'title'=> $article['title'],
        'date' => $article['date'],
        'html' => $article['html'],
        'tldr' => $article['tldr'],
        'refHtml' => $article['refHtml']
    );
}