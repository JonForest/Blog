<?php
//require "assets/php/dbconnection.php";
require_once "app/repositories/articleRepository.php";
require_once "app/repositories/staticContentRepository.php";

$app->get(
    '/',
    function() use ($app) {
        $articleRepo = new ArticleRepository();
        $currentArticleId = $articleRepo->getLatestArticle();
        $data = getArticleInfo($currentArticleId);

        $app->render(
            'article.html.smarty',
            $data
        );
    }
);

$app->get(
    '/:id',
    function($id) use ($app) {
        $data = getArticleInfo($id);
        $app->render(
            'article.html.smarty',
            $data
        );
    }
)->conditions(array('id'=>'\d+'));

$app->get(
    '/about',
    function () use ($app) {
        $articleRepo = new ArticleRepository();
        $menu = $articleRepo->buildMenu(0);
        $staticRepo = new StaticContentRepository();
        $html = $staticRepo->getStaticArticle('about');
        $title = 'About Me';
        $app->render(
            'article.html.smarty',
            array(
                'menuHtml' => $menu,
                'title' => $title,
                'date' =>'',
                'html' => $html,
                'tldr' => '',
                'refHtml' =>''
            )
        );
    }
);


function getArticleInfo($articleId)
{
    $articleRepo = new ArticleRepository();
    $menu = $articleRepo->buildMenu($articleId);
    $article = $articleRepo->getArticleById($articleId);

    return  array(
        'menuHtml'=>$menu,
        'title'=> $article['title'],
        'date' => $article['date'],
        'html' => $article['html'],
        'tldr' => $article['tldr'],
        'refHtml' => $article['refHtml']
    );
}


