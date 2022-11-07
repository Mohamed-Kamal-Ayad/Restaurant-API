<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MealResource;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use function PHPUnit\Framework\isEmpty;
use File;

class MealController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $meals = MealResource::collection(Meal::all());

        return $this->apiResponse($meals, 'Done successfuly', 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'title' => 'required|min:3|max:255',
            'description' => 'required|min:3',
            'image' => 'required|image',
            'rating' => 'required|max:255|numeric',
            'price' => 'required|max:255|numeric'
        ]
        );

        if($validator->fails())
        {
            return $this->apiResponse(null, $validator->errors(), 400);
        }
        $request->image = request()->file('image')->store('images');

        $meal = Meal::create([
           'title' => $request->title,
           'description' => $request->description,
           'image' => $request->image,
           'price' => $request->price,
           'rating' => $request->rating
        ]);

        if ($meal) {
            return $this->apiResponse(new MealResource($meal), 'Meal created', 201);
        }
    }

    public function show($id)
    {
        $meal = Meal::find($id);
        if ($meal) {
            return $this->apiResponse(new MealResource($meal), 'Done successfuly', 200);
        }
        return $this->apiResponse(null, 'Not found', 404);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),
            [
                'title' => 'required|min:3|max:255',
                'description' => 'required|min:3',
                'image' => 'required|image',
                'price' => 'required|max:255|numeric',
                'rating' => 'required|max:255|numeric',
            ]
        );

        if($validator->fails())
        {
            return $this->apiResponse(null, $validator->errors(), 400);
        }

        $meal = Meal::find($id);

        if(!$meal)
        {
            return $this->apiResponse(null, 'Not found', 404);
        }

        if(File::exists(public_path("storage/$meal->image")))
        {
        File::delete(public_path("storage/$meal->image"));
        }

        $request->image = request()->file('image')->store('images');

        $meal->title = $request->title;
        $meal->description = $request->description;
        $meal->image = $request->image;
        $meal->rating = $request->rating;
        $meal->price = $request->price;

        $meal->save();

        if ($meal) {
            return $this->apiResponse(new MealResource($meal), 'Meal updated', 200);
        }
    }

    public function destroy($id)
    {
        $meal = Meal::find($id);
        if(!$meal)
        {
            return $this->apiResponse(null, 'Not found', 404);
        }

        if(File::exists(public_path("storage/$meal->image")))
        {
            File::delete(public_path("storage/$meal->image"));
        }

        $meal->delete();
        if ($meal) {
            return $this->apiResponse(null, 'Meal deleted', 200);
        }
    }
}
