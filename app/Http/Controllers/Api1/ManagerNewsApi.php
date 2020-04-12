<?php

namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\NewsService;
use App\Services\UsersService;

class ManagerNewsApi extends ApiController
{
    /**
     * Constructor
     */
    protected $request;
    protected $news;
    protected $user;

    public function __construct(Request $request, NewsService $news, UsersService $user)
    {
        $this->request = $request;
        $this->news = $news;
        $this->user = $user;
    }

    public function managerCreateNews()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        //path params validation
        $this->validate($this->request, [
            'category_id' => 'required',
            'name' => 'required',
            'url_img' => 'required',
            'description' => 'required | max:256',
            'weigth' => 'required',
        ]);

        $input['company_id'] = $user->company_id;

        return $this->news->createNews($input);
    }
    /**
     * Operation managerListNews
     *
     * get all news.
     *
     *
     * @return Http response
     */
    public function managerListNews()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        $input['company_id'] = $user->company_id;

        return $this->news->listNews();
    }
    /**
     * Operation managerUpdateNews
     *
     * update news.
     *
     *
     * @return Http response
     */
    public function managerUpdateNews()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->news->updateNews($input);
    }
    /**
     * Operation managerDeleteNews
     *
     * delete news.
     *
     * @param int $news_id  (required)
     *
     * @return Http response
     */
    public function managerDeleteNews($news_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        $input['company_id'] = $user->company_id;

        return $this->news->deleteNews($news_id);
    }
    /**
     * Operation managerGetNewsById
     *
     * Find by ID.
     *
     * @param int $news_id  (required)
     *
     * @return Http response
     */
    public function managerGetNewsById($news_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        $input['company_id'] = $user->company_id;

        return $this->news->getNewsById($news_id);
    }
}
