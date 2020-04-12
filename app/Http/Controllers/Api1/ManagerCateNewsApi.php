<?php

namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\UsersService;
use App\Services\CategoryNewsService;

class ManagerCateNewsApi extends ApiController
{
    /**
     * Constructor
     */
    protected $request;
    protected $cate_news;
    protected $user;

    public function __construct(Request $request, CategoryNewsService $cate_news, UsersService $user)
    {
        $this->request = $request;
        $this->cate_news = $cate_news;
        $this->user = $user;
    }

    public function managerCreateCategoryNews()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        //path params validation
        $this->validate($this->request, [
            'name' => 'required',
            'weigth' => 'required',
        ]);

        $input['company_id'] = $user->company_id;

        return $this->cate_news->createCategoryNews($input);
    }
    /**
     * Operation managerListNews
     *
     * get all news.
     *
     *
     * @return Http response
     */
    public function managerListCategoryNews()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        $input['company_id'] = $user->company_id;

        return $this->cate_news->listCategoryNews();
    }
    /**
     * Operation managerUpdateNews
     *
     * update news.
     *
     *
     * @return Http response
     */
    public function managerUpdateCategoryNews()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->cate_news->updateCategoryNews($input);
    }
    /**
     * Operation managerDeleteNews
     *
     * delete news.
     *
     * @param int $cateNewId  (required)
     *
     * @return Http response
     */
    public function managerDeleteCategoryNews($cateNewId)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        $input['company_id'] = $user->company_id;

        return $this->cate_news->deleteCategoryNews($cateNewId);
    }
    /**
     * Operation managerGetNewsById
     *
     * Find by ID.
     *
     * @param int $cateNewId  (required)
     *
     * @return Http response
     */
    public function managerGetCategoryNewsById($cateNewId)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        $input['company_id'] = $user->company_id;

        return $this->cate_news->getCategoryNewsById($cateNewId);
    }
}
