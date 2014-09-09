<?php

class staticContentRepository
{
    public function getStaticArticle($articleName)
    {
        return file_get_contents('app/static/' . $articleName . '.html');
    }
}