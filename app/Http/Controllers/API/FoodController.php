<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Food;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Validator;

class FoodController extends Controller
{
    public function index(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $types = $request->input('types');

        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        $rate_from = $request->input('rate_from');
        $rate_to = $request->input('rate_to');

        if ($id) {
            $food = Food::find($id);

            if ($food) {
                return ResponseFormatter::success(
                    $food,
                    'Data produk berhasil diambil'
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'Data produk tidak ada',
                    404
                );
            }
        }

        $food = Food::query();

        if ($name) {
            $food->where('name', 'like', '%' . $name . '%');
        }
        if ($types) {
            $food->where('types', 'like', '%' . $types . '%');
        }
        if ($price_from) {
            $food->where('price', '>=', $price_from);
        }
        if ($price_to) {
            $food->where('price', '<=', $price_to);
        }
        if ($rate_from) {
            $food->where('rate', '>=', $rate_from);
        }
        if ($rate_to) {
            $food->where('rate', '<=', $rate_to);
        }

        return ResponseFormatter::success(
            $food->paginate($limit),
            'Data list produk berhasil diambil'
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'picturePath' => 'required|image',
            'description' => 'required',
            'ingredients' => 'required',
            'price' => 'required|integer',
            'rate' => 'required|integer',
            'types' => '',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $validator->errors(),
            ], 'Create Food Failed', 422);
        }

        $data = $request->all();
        $data['picturePath'] = $request->file('picturePath')->store('assets/food', 'public');
        $food = Food::create($data);

        return ResponseFormatter::success([
            'food' => $food
        ], 'Food Created');
    }

    public function update(Request $request, $id)
    {
        $food = Food::where('id', $id);
        $data = $request->all();
        if ($request->file('picturePath')) {
            $data['picturePath'] = $request->file('picturePath')->store('assets/food', 'public');
        }

        $food->update($data);

        return ResponseFormatter::success([
            'food' => $food->first()
        ], 'Food Updated');
    }

    public function destroy(Food $food)
    {
        $food->delete();
        return ResponseFormatter::success(null, 'Food Deleted');
    }
}
