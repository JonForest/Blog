<?php

require 'app/common/dbconnection.php';

class ArticleRepository
{
    function getLatestArticle()
    {
        $con = getConnection();
        //Send back date and lastupdated in JSON format
        $sql = "SELECT articleId FROM Articles WHERE status='published' ORDER BY createdDate DESC, articleId DESC LIMIT 1";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $stmt->bind_result($articleId);
        $stmt->fetch(); //only need to worry about one row
        $stmt->close();

        return $articleId;
    }

    function buildMenu($articleId)
    {

        $con = getConnection();
        $menuDate = '';
        $menuHtml = '';

        $sql = "SELECT articleId, title, createdDate FROM   Articles t1  WHERE  status = 'Published' AND NOT EXISTS (SELECT 1 FROM   Articles t2 WHERE  t1.articleId = t2.articleId AND  t2.status = 'Deleted' AND  t2.lastUpdate > t1.lastUpdate) ORDER BY createdDate DESC, articleId DESC";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $stmt->bind_result($menuArticleId, $menuTitle, $menuDate);
        while ($stmt->fetch())
        {
            if ($articleId == $menuArticleId) {
                $menuHtml.=$menuDate.' - '.$menuTitle.'<br>';
            } else {
                $menuHtml.='<a href="'.$menuArticleId.'">'.$menuDate.' - '.$menuTitle.'</a><br>';
            }

        }

        return $menuHtml;

    }

    /**
     * Gets all articles for use in the admin section of the site
     * @return array
     */
    public function getAllArticles()
    {
        $results = array();
        $sql = "select articlePK, articleId, title, status, lastUpdate from Articles order by articleId desc";
        $con = getConnection();
        $stmt = $con->query($sql);
        while($row = $stmt->fetch_array(MYSQLI_ASSOC)) {
            array_push($results, $row);
        }

        return $results;
    }

    /**
     * @param {integer} $articleId
     * @return array
     */
    public function getArticleById($articleId)
    {
        $sql = "SELECT articlePK, articleId, title, html, tldr, createdDate, lastUpdate FROM Articles WHERE articleId=? and status='published' ORDER By lastUpdate DESC LIMIT 1";
        return $this->getArticle($sql, $articleId);
    }

    /**
     * @param {integer} $articlePK
     * @return array
     */
    public function getArticleByPK($articlePK)
    {
        $sql = "SELECT articlePK, articleId, title, html, tldr, createdDate, lastUpdate FROM Articles WHERE articlePK=?";
        return $this->getArticle($sql, $articlePK);
    }


    public function deleteArticle($articePK)
    {
        $sql = "delete from Articles where articlePK = ?";
        $con = getConnection();
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $articePK);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->affected_rows > 0;
    }

    private function getArticle($sql, $id)
    {
        $con = getConnection();
        $title='';
        $html='';
        $tldr='';
        $date='';
        $lastUpdate='';
        $tagHtml='';
        $refHtml='';

        $stmt = $con->prepare($sql);
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $stmt->bind_result($articlePK, $articleId, $title, $html, $tldr, $date, $lastUpdate);
        $stmt->fetch(); //only need to worry about one row

        if ($title) {
            $con2 = getConnection();
            $sql = "SELECT tagPK, tag FROM Tags WHERE articlePK=?";
            $stmt = $con2->prepare($sql);
            $stmt->bind_param("i",$articlePK);
            $stmt->execute();
            $stmt->bind_result($tagPK, $tag);
            while ($stmt->fetch()) {
                $tagHtml+='<a href="blog.php?tag=$tagPK">$tag</a>,';
            }
            //Remove the last comma as it isn't needed
            $tagHtml = substr($tagHtml, 0, strlen($tagHtml)-1);

            $sql = "SELECT url, description FROM Refs WHERE articlePK=$articlePK"; //Don't need to bind param as control $articePK within our code

            $con3 = getConnection();
            $stmt = $con3->prepare($sql);
            $stmt->execute();
            $stmt->bind_result($url, $description);
            while ($stmt->fetch()) {
                $refHtml.='<a href="'.$url.'">'.$url.'</a> - '.$description.'<br>';
            }
        }

        return array(
            'articleId' => $articleId,
            'articlePK' => $articlePK,
            'title' => $title,
            'html' => $html,
            'tldr' => $tldr,
            'date' => $date,
            'lastUpdate' => $lastUpdate,
            'tagHtml' => $tagHtml,
            'refHtml' => $refHtml
        );
    }

    /**
     * @param stdClass $article
     * @return string
     */
    public function saveArticle(stdClass $article)
    {
        $con = getConnection();
        // Insert into the database
        $sql = "INSERT INTO Articles (articleId,title,html,tldr,createdDate,lastUpdate) VALUES (?,?,?,?, DATE(NOW()), NOW())";
        $stmt = $con->prepare($sql);
        //$html = $con->real_escape_string($html);
        $stmt->bind_param(
            "dsss",
            $article->articleId,
            $article->title,
            $article->html,
            $article->tldr
        );
        $stmt->execute();
//        $stmt->close(); //close statement


        //Find primary key Id of last statement
        $artPK = mysqli_insert_id($con);

        // Insert into tags
        $sql = "INSERT INTO Tags (articlePK, tag) VALUES (?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ds",$artPK, $tag);
        for ($i=0; $i<count($article->tags); $i++)
        {
            $tag = $article->tags[$i];
            if (trim($tag) !== '') {
                $stmt->execute();
            }
        }
//        $stmt->close(); //close statement

        // Insert into References
        $sql = "INSERT INTO Refs (articlePK, url, description) VALUES (?, ?, ?)";
        //echo ($sql);
        $stmt = $con->prepare($sql);
        $stmt->bind_param("dss",$artPK, $url, $desc);
        //var_dump($refs);

        for ($i=0; $i<count($article->refs); $i++)
        {
            //var_dump($refs[$i]);
            $url = $article->refs[$i]->url;
            $desc = $article->refs[$i]->description;
            if (trim($desc) !== ''  && trim('$url') !== '') {
                $stmt->execute();
            }
        }
        $stmt->close(); //close statement


        //Send back date and lastupdated in JSON format
        $sql = "SELECT articleId, createdDate, lastUpdate FROM Articles WHERE articlePK=$artPK";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $stmt->bind_result($articleId, $date, $lastUpdate);
        $stmt->fetch(); //only need to worry about one row
        $stmt->close();

        //Package data up an array object for transfer
        $retData = array('articlePK'=>$artPK, 'articleId' => $articleId, 'date' => $date, 'lastUpdate'=>$lastUpdate);
        return json_encode($retData);  //Write json back
    }

    public function publishArticle($articlePK)
    {
        $con = getConnection();

        // Insert into the database
        $sql = "UPDATE Articles SET status='published' WHERE articlePK=?";
        $stmt = $con->prepare($sql);
        //$html = $con->real_escape_string($html);
        $stmt->bind_param("d",$articlePK);
        $stmt->execute();

        $sql = "SELECT articleId FROM Articles WHERE articlePK=?";
        $stmt=$con->prepare($sql);
        $stmt->bind_param("d",$articlePK);
        $stmt->execute();
        $stmt->bind_result($articleId);
        $stmt->fetch();
        $stmt->close();

        $con2 = getConnection();
        //remove previous published tags
        $sql = "UPDATE Articles SET status='pre-published' WHERE articleId=$articleId AND articlePK!=?";
        $stmt=$con2->prepare($sql);
        $stmt->bind_param("d",$articlePK);
        $stmt->execute();
    }

    public function getNewArticle()
    {
        $con = getConnection();
        $sql = "SELECT articleId FROM Articles ORDER BY articleId DESC LIMIT 1";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $stmt->bind_result($articleId);
        $stmt->fetch(); //only need to worry about one row
        $stmt->close();

        $data = array(
            'articleId' => $articleId + 1,
            'articlePK' => 0,
            'title' => '',
            'html' => '',
            'tldr' => '',
            'date' => '',
            'lastUpdate' => '',
            'tagHtml' => '',
            'refHtml' => '',
            'new' => true
        );

        return $data;

    }
}