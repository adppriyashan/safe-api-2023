<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Disaster;
use App\Models\DisasterHasImage;
use App\Models\DisasterType;
use App\Models\Image;
use App\Traits\RespondsWithHttpStatus;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DisasterController extends Controller
{
    use RespondsWithHttpStatus;

    function getTypeData()
    {
        try {
            return $this->success('Data fetched successfully.', json_encode(DisasterType::select(['id', 'type'])->get()));
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->failure($e->getMessage(), status: 500);
        }
    }

    public function create(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'type' => 'required|exists:disaster_types,id',
                    'lng' => 'required',
                    'ltd' => 'required',
                    'moreinfo' => 'nullable'
                ]
            );

            if ($validateUser->fails()) {
                return $this->failure('Validation error', $validateUser->errors(), 401);
            }

            DB::beginTransaction();

            $disaster = Disaster::create([
                'user' => Auth::user()->id,
                'district' => Auth::user()->district,
                'type' => $request->type,
                'lng' => $request->lng,
                'ltd' => $request->ltd,
                'moreinfo' => $request->moreinfo,
            ]);

            for ($i = 1; $i < 4; $i++) {
                if ($request->has('image' . $i)) {
                    $this->uploadImage($request['image' . $i], $disaster, $i);
                }
            }

            DB::commit();

            return $this->success('Misfortune successfully informed.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->failure($e->getMessage(), status: 500);
        }
    }

    protected function uploadImage($encodedImage, $disaster, $index)
    {
        $fileName = $disaster->id . '_' . Carbon::now()->format('YmdHs') . '_' . $index . '.jpg';
        file_put_contents(public_path() . '/uploads/' . $fileName, base64_decode($encodedImage));

        DisasterHasImage::create([
            'disaster' => $disaster->id,
            'image' => Image::create([
                'path' => $fileName
            ])->id
        ]);
    }

    public function fetch(Request $request)
    {
        try {
            $data = Disaster::where('status', '!=', 4);
            return $this->success('Data fetched successfully.', $data->select(['id', 'type', 'lng', 'ltd', 'status', 'moreinfo'])->with('images')->orderBy('created_at', 'DESC')->get());
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->failure($e->getMessage(), status: 500);
        }
    }
}
