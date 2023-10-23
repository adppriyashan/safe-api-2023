<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Disaster;
use App\Models\DisasterHasImage;
use App\Models\DisasterType;
use App\Models\Image;
use App\Models\User;
use App\Traits\RespondsWithHttpStatus;
use App\Traits\SendSMS;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DisasterController extends Controller
{
    use RespondsWithHttpStatus;
    use SendSMS;

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

            //send sms
            foreach (User::where('is_admin', 1)->where('district', Auth::user()->district)->pluck('contact') as $key => $value) {
                $this->sendNow('New misfortune submitted, Check the location via : https://www.google.com/maps/search/?api=1&query=' . $request->ltd . ',' . $request->lng, $value);
            }

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
        $validateUser = Validator::make(
            $request->all(),
            [
                'start' => 'required',
                'count' => 'required'
            ]
        );

        if ($validateUser->fails()) {
            return $this->failure('Validation error', $validateUser->errors(), 401);
        }

        try {
            $dataQuery = Disaster::whereIn('status', (Auth::user()->isadmin == 0) ? [1, 2, 3] : [1, 2]);

            if (Auth::user()->isadmin == 0) {
                $dataQuery->where('user', Auth::user()->id);
            }

            $data = $dataQuery->select(['id', 'user', 'type', 'lng', 'ltd', 'status', 'moreinfo', 'created_at'])->orderBy('created_at', 'DESC')->skip($request->start)->take($request->count)->get();
            return $this->success('Data fetched successfully.', $data);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->failure($e->getMessage(), status: 500);
        }
    }

    public function fetchVerified(Request $request)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'start' => 'required',
                'count' => 'required'
            ]
        );

        if ($validateUser->fails()) {
            return $this->failure('Validation error', $validateUser->errors(), 401);
        }

        try {
            $dataQuery = Disaster::where('status', 2);

            if (Auth::user()->isadmin == 0) {
                $dataQuery->where('district', Auth::user()->district);
            }

            $data = $dataQuery->select(['id', 'user', 'type', 'lng', 'ltd', 'status', 'moreinfo', 'created_at'])->orderBy('created_at', 'DESC')->skip($request->start)->take($request->count)->get();
            return $this->success('Data fetched successfully.', $data);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->failure($e->getMessage(), status: 500);
        }
    }

    public function status(Request $request)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'id' => 'required|exists:disasters,id',
                'status' => 'required|in:' . implode(',', array_keys(Disaster::$status))
            ]
        );

        if ($validateUser->fails()) {
            return $this->failure('Validation error', $validateUser->errors(), 401);
        }

        try {
            $disaster = Disaster::where('id', $request->id)->first();

            //send sms
            foreach (User::where('is_admin', 0)->where('district', $disaster->district)->pluck('contact') as $key => $value) {
                $this->sendNow('Misfortune detected and verified in your district, Please make safe yourself, Please login to SAFE app to more information.', $value);
            }

            $disaster->update([
                'status' => $request->status
            ]);

            return $this->success('Data fetched successfully.');
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->failure($e->getMessage(), status: 500);
        }
    }
}
