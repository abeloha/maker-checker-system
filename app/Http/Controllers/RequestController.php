<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminResource;
use App\Http\Resources\ChangeRequestResource;
use App\Http\Resources\UserResource;
use App\Jobs\NotifyAdmin;
use App\Mail\PendingRequestEmail;
use App\Models\Admin;
use App\Models\ChangeRequest;
use App\Models\User;
use App\Notifications\PendingRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = ChangeRequest::where('admin_id', '!=', $request->user()->id)->get();
        return response()->json(
            [
                'message' => 'Success',
                'data' => ChangeRequestResource::collection($requests)
            ]
        );
    }

    public function myRequests(Request $request)
    {
        $requests = ChangeRequest::where('admin_id', $request->user()->id)->get();
        return response()->json(
            [
                'message' => 'Success',
                'data' => ChangeRequestResource::collection($requests)
            ]
        );
    }

    public function users()
    {
        return response()->json(
            [
                'message' => 'Success',
                'data' => UserResource::collection(User::all())
            ]
        );
    }

    public function create(Request $request)
    {

        // Validate the incoming request
        $validatedData = $request->validate([
            'type' => [
                'required',
                Rule::in(['create', 'update', 'delete']),
            ],
            'data' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->type === 'update' || $request->type === 'create';
                }),
                'json',
                'min:1',
                //'regex:/^(?=.*\bfirst_name\b|\blast_name\b|\bemail\b).+$/'
                function ($attribute, $value, $fail) use ($request)  {
                    if ($request->type == 'delete') {
                        return;
                    }
                    $json = json_decode($value, true);
                    if (!isset($json['first_name']) || !isset($json['last_name']) || !isset($json['email'])) {
                        $fail('The data field must include first_name, last_name, and email.');
                    }
                },
            ],
            'user_id' => [
                'nullable',
                Rule::requiredIf(function () use ($request) {
                    return $request->type === 'update' || $request->type === 'delete';
                }),
                Rule::exists('users', 'id')->where(function ($query) use ($request) {
                    $query->where('id', $request->user_id);
                }),
            ],
        ]);

        $admin = $request->user();
        $validatedData['admin_id'] = $admin->id;
        $record = ChangeRequest::create($validatedData);



        NotifyAdmin::dispatchAfterResponse($record);



        return response()->json(
            [
                'message' => 'Request submitted successfully',
                'data' => new ChangeRequestResource($record)
            ],
            201
        );
    }


    public function approve(Request $request, ChangeRequest $record)
    {
        if ($record->admin_id == $request->user()->id) {
            return response()->json(['message' => 'You cannot authorize this change'], 403);
        }

        $success =  false;

        if($record->type == 'create'){
            $success = $this->createUser(json_decode($record->data, true));
        }elseif($record->type == 'update'){
            $success = $this->updateUser($record->user, json_decode($record->data, true));
        }elseif($record->type == 'delete'){
            $success = $record->user->delete();
        }

        if(!$success){
            $logData = [
                'action' => 'APPROVE',
                'request' => new ChangeRequestResource($record),
                'action_by' => new AdminResource($request->user()),
                'error' => 'The action failed due to an unexpected error.',
            ];

            Log::channel('requests_errors')->info(json_encode($logData, JSON_PRETTY_PRINT));

            return response()->json(['message' => 'The action failed due to an unexpected error.'], 500);
        }

        $this->logAction($record, 'APPROVE', $request->user());

        $record->delete();
        return response()->json(['message' => 'Successfully approved']);

    }

    private function createUser(array $data): bool
    {
        User::create($data);
        return true;
    }

    private function updateUser(User $user, array $data): bool
    {
        $user->update($data);
        return true;
    }

    private function logAction(ChangeRequest $record, String $action, Admin $admin)
    {
        $logData = [
            'action' => $action,
            'request' => new ChangeRequestResource($record),
            'action_by' => new AdminResource($admin),
        ];
        Log::channel('requests_action')->info(json_encode($logData, JSON_PRETTY_PRINT));
    }

    public function decline(Request $request, ChangeRequest $record)
    {

        if ($record->admin_id == $request->user()->id) {
            return response()->json(['message' => 'You cannot authorize this change'], 403);
        }

        $this->logAction($record, 'DECLINE', $request->user());

        $record->delete();
        return response()->json(['message' => 'Request deleted']);
    }


}
