<?php

namespace App\Services;

use App\Models\CategoryNews;
use App\Models\News;

class CategoryNewsService
{
    public function __construct()
    {
    }

    public function createCategoryNews($data)
    {
        $data['description'] = $data['description'] ?? '';
        unset($data['company_id']);
        return CategoryNews::create($data);
    }

    public function listCategoryNews()
    {
        return CategoryNews::all();
    }

    public function updateCategoryNews($data)
    {
        $data['description'] = $data['description'] ?? '';
        unset($data['company_id']);

        $cate_news = CategoryNews::find($data['id']);
        $cate_news->name = $data['name'];
        $cate_news->description = $data['description'];
        $cate_news->weigth = $data['weigth'];
        if ($cate_news->save()) {
            return $cate_news;
        }
    }

    public function getCategoryNewsById($cateNewId)
    {
        return CategoryNews::find($cateNewId);
    }

    public function deleteCategoryNews($cateNewId)
    {
        $cate_news = CategoryNews::find($cateNewId);
        if ($cate_news->delete()) {
            $news = News::where('category_id', $cate_news->id)->delete();
            if ($news) return response('Delete Category News OK!', 200);
        }
    }
}
