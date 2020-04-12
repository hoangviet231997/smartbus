<?php

namespace App\Services;

use App\Models\News;
use App\Services\PublicFunctionService;

class NewsService
{

    protected $public_function;

    public function __construct(PublicFunctionService $public_function)
    {
        $this->public_function = $public_function;
    }

    public function createNews($data)
    {
        if ($data) {

            $category_id = $data['category_id'];
            $name = $data['name'];
            $url_img = $data['url_img'];
            $description = $data['description'];
            $content = $data['content'] ?? '';
            $weigth = $data['weigth'];
            $company_id = $data['company_id'];

            $path = "/img/news/";
            $format_img = $this->public_function->saveImgBase64($url_img, $company_id, $path, 100, 100);
            $news = new News();
            $news->category_id = $category_id;
            $news->name = $name;
            $news->url_img = $format_img;
            $news->description = $description;
            $news->content = $content;
            $news->weigth = $weigth;
            if ($news->save()) {
                return $news;
            }
        }
    }

    public function updateNews($data)
    {
        if ($data) {

            $category_id = $data['category_id'] ?? null;
            $name = $data['name'] ?? '';
            $url_img = $data['url_img'] ?? null;
            $description = $data['description'] ?? '';
            $content = $data['content'] ?? '';
            $weigth = $data['weigth'] ?? null;
            $company_id = $data['company_id'];
            $id = $data['id'];

            $news = News::find($id);
            if ($news) {

                if ($url_img !== null) {
                    $path = "/img/news/";
                    $this->public_function->removeImageBase64($news->url_img, $path);
                    $news->url_img = $this->public_function->saveImgBase64($url_img, $company_id, $path, 100, 100);
                }

                $news->category_id = $category_id;
                $news->name = $name;
                $news->description = $description;
                $news->content = $content;
                $news->weigth = $weigth;

                if ($news->save()) return $this->getNewsById($id);
            }
            return response('News not found',404);           
        }
    }

    public function listNews()
    {
        return News::join('category_news','news.category_id','=','category_news.id')
        ->select('news.*','category_news.name as category_news_name')
        ->orderBy('news.updated_at', 'desc')->get();
    }

    public function getNewsById($news_id)
    {
        return News::join('category_news','news.category_id','=','category_news.id')
        ->where('news.id',$news_id)
        ->select('news.*','category_news.name as category_news_name')
        ->first();
    }

    public function deleteNews($news_id)
    {
        $del_news = News::find($news_id)->delete();
        if ($del_news) return response('Delete News OK!', 200);
    }
}
