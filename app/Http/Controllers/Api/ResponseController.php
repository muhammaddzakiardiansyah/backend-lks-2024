<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AllowedDomain;
use App\Models\Answer;
use App\Models\Form;
use App\Models\Question;
use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ResponseController extends Controller
{
    // create response function
    public function createResponse(Request $request, $slug)
    {
        $form = Form::where('slug', $slug)->first();
        if (!$form) {
            return response()->json([
                'message' => 'Form not found',
            ], 404);
        }
        $validate = Validator::make($request->all(), [
            'answer' => ['required', 'array'],
            'answer.*.value' => [Rule::requiredIf(function () use ($form) {
                $question = Question::where('form_id', $form->id)->first();
                return $question->is_required === 1;
            })]
        ]);
        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validate->errors(),
            ], 402);
        }
        $allowedDomain = AllowedDomain::where('form_id', $form->id)->first();
        // if domain allowed set
        if ($allowedDomain->domain) {
            $userDomain = explode('@', Auth::user()->email);
            foreach($form->formatted_allowed_domains as $domain) {
                if(!in_array(end($userDomain), $domain)) {
                    return response()->json([
                        'message' => 'Forbidden access',
                    ], 403);
                }
            }
        }
        // if domain allowed not set
        // $response = Response::where('form_id', $form->id)->where('user_id', Auth::user()->id)->first();
        // if ($response && $form->limit_one_response == 1) {
        //     return response()->json([
        //         'message' => 'You cant not submit twice',
        //     ], 422);
        // }
        // $createResponse = Response::create([
        //     'form_id' => $form->id,
        //     'user_id' => Auth::user()->id,
        // ]);
        // foreach ($request->answer as $data) {
        //     $createAnswer = Answer::create([
        //         'response_id' => $createResponse->id,
        //         'question_id' => $data['question_id'],
        //         'value' => $data['value'],
        //     ]);
        // }
        // if ($createAnswer) {
        //     return response()->json([
        //         'message' => 'Create response success',
        //     ], 200);
        // }
        return response()->json(['message' => 'berhasil']);
    }

    // get all responses function
    public function getAllResponses($slug)
    {
        $form = Form::with('responses')->where('slug', $slug)->first();
        if (!$form) {
            return response()->json([
                'message' => 'Form not found',
            ], 404);
        }

        /** @var Collection */
        $responses = $form->responses()->with(['user', 'answers.question'])->get();
        $formatResponse = [];
        
        // $questions = Question::with('answers')->where('form_id', $form->id)->get();
        foreach($responses as $response) {
            $formatResponse[] = [
                'date' => $response->created_at,
                'user' => $response->user->toArray(),
                'answers' => $response->answers->reduce(function ($formated, Answer $answer) {
                    $formated[$answer->question->name] = $answer->value;
                    
                    return $formated;
                }, []),
            ];
        }
        return response()->json([
            'message' => 'Get all responses success',
            'responses' => $formatResponse,
        ], 200);
   
    }
}
