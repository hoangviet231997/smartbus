<?php

namespace App\Http\Controllers\Api1;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Api1\ApiController;

class AdminCategoriesApi extends ApiController
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function listCategory()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        if (empty($input['limit']) && $input['limit'] < 0) $input['limit'] = 10;

        $category = Category::where('parent_id', 0)->orderby('created_at', 'DESC')->paginate($input['limit'])->toArray();
        $category_arr = array();
        foreach ($category['data'] as $values) {

            $category_chil = Category::where('parent_id', $values['id'])->get()->toArray();
            $values['page_parent'] = $values['display_name'];
            $category_arr[] = $values;
            if(count($category_chil) > 0){
                foreach ($category_chil as $v_chil) {
                    $v_chil['page_parent'] = $values['display_name'];
                    $category_arr[] = $v_chil;
                }
            }
        }

        header("pagination-total: " . $category['total']);
        header("pagination-current: " . $category['current_page']);
        header("pagination-last: " . $category['last_page']);

        return $category_arr;
    }

    public function getCategoryById($categoryId)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($categoryId) || (int) $categoryId < 0)
            return response('Invalid ID supplied', 404);

        // get Category
        $category = Category::find($categoryId);

        if (empty($category)) return response('Category Not found', 404);

        return $category;
    }

    public function createCategory()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'display_name' => 'required',
            'parent_id' => 'required | integer',
            'key' => 'required',
            'type' => 'required'
        ]);

        $input = $this->request->all();
        //check exist
        $check = Category::where([
            ['key', '=', $input['key']],
            ['type', '=', $input['type']]
        ])->first();

        if ($check) return response('Category exist.', 404);

        $category = Category::create($input);
        if ($category)
            return $category;
        return response('Create Error', 404);
    }

    public function updateCategory()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'id' => 'bail|required|integer|min:1',
            'display_name' => 'required',
            'key' => 'required',
            'parent_id' => 'integer',
            'type' => 'required'
        ]);

        $input = $this->request->all();

        //check exist
        $check = Category::where([
            ['key', '=', $input['key']],
            ['type', '=', $input['type']],
            ['id', '!=', $input['id']]
        ])->first();

        if ($check) return response('Category exist.', 404);

        $category = Category::find($input['id']);

        if (empty($category)) return response('Category Not found', 404);
        $category->update($input);
        if ($category) return $category;

        return response('Update Error', 404);
    }

    public function deleteCategory($categoryId)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($categoryId) || (int) $categoryId < 0)
            return response('Invalid ID supplied', 404);

        $category = Category::find($categoryId);

        if (empty($category)) return response('Category Not found', 404);

        if ($category->delete()) return response('OK', 200);

        return response('Delete Error', 404);
    }

    /**
     * Operation managerListCategoryByInputAndByTypeSearch
     *
     * Search category by input.
     *
     *
     * @return Http response
     */
    public function managerListCategoryByInputAndByTypeSearch()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        $key_input = $input['key_input'];
        $style_search = $input['style_search'];

        $categories = Category::where('parent_id', 0);

        if ($style_search == 'display_name') $categories->where('display_name', 'like', "%$key_input%");

        if ($style_search == 'key') $categories->where('key', 'like', "%$key_input%");

        return $categories->orderby('created_at', 'DESC')->get()->toArray();
    }
}
