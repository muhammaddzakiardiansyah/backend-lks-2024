<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule as ValidationRule;
use Psr\Http\Message\ResponseInterface;

use function PHPUnit\Framework\returnSelf;

class QuestionController extends Controller
{
    // function create question
    public function createQuestion(Request $request, $slug)
    {
        $validChoises = [
            'short answer',
            'paragraph',
            'date',
            'multiple choice',
            'dropdown',
            'checkboxes'
        ];

        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'choice_type' => [
                'required',
                ValidationRule::in($validChoises)
            ],
            'choices' => [
                ValidationRule::requiredIf(function () use ($request) {
                    return in_array($request->choice_type, [
                        'checkboxes',
                        'multiple choice',
                        'dropdown',
                    ]);
                }),
                ValidationRule::excludeIf(function() use ($request) {
                    return in_array($request->choice_type, [
                        'short answer',
                        'paragraph',
                        'date',
                    ]);
                }),
                'array',
            ],
            'choices.*' => 'string',
        ]);
        if($validate->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validate->errors(),
            ], 402);
        }
        $form = Form::where('slug', $slug)->first();
        if(!$form) {
            return response()->json([
                'message' => 'Form not found',
            ], 404);
        } else if($form->creator_id !== Auth::user()->id) {
            return response()->json([
                'message' => 'Forbidden access',
            ], 403);
        }
        $question = Question::create([
            'form_id' => $form->id,
            'name' => $request->name,
            'choice_type' => $request->choice_type,
            'choices' => implode(',', $request->choices),
            'is_required' => $request->is_required,
        ]);
        return response()->json([
            'message' => 'Add question success',
            'question' => [
                'name' => $question->name,
                'choice_type' => $question->choice_type,
                'is_required' => $question->is_required,
                'choices' => explode(',', $question->choices),
                'form_id' => $question->form_id,
                'id' => $question->id,
            ],
        ]);
    }

    // delete question
    public function deleteQuestion($slug, $id)
    {
        $form = Form::where('slug', $slug)->first();
        if(!$form) {
            return response()->json([
                'message' => 'Form not found',
            ], 404);
        }
        $question = Question::where('id', $id)->where('form_id', $form->id)->first();
        if(!$question) {
            return response()->json([
                'message' => 'Question not found',
            ], 404);
        }
        if($form->creator_id !== Auth::user()->id) {
            return response()->json([
                'message' => 'Forbidden access',
            ], 403);
        }
        Question::destroy($id);
        return response()->json([
            'message' => 'Remove question success',
        ], 200);
    }
}
