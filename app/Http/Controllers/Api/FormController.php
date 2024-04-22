<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AllowedDomain;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FormController extends Controller
{
    // create form function
    public function createForm(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:forms,slug|regex:/^[a-zA-Z0-10\-.]+$/',
            'allowed_domains' => 'array',
            'allowed_domains.*' => 'string'
        ]);
        if($validate->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validate->errors(),
            ]);
        }
        $form = Form::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'limit_one_response' => $request->limit_one_response,
            'creator_id' => Auth::user()->id,
        ]);
        AllowedDomain::create([
            'form_id' => $form->id,
            'domain' => implode(',', $request->allowed_domains),
        ]);
        return response()->json([
            'message' => 'Create form success',
            'form' => [
                'name' => $form->name,
                'slug' => $form->slug,
                'description' => $form->description,
                'limit_one_response' => $form->limit_one_response,
                'creator_id' => $form->creator_id,
                'id' => $form->id,
            ],
        ], 200);
    }

    // get all forms function
    public function getAllForms()
    {
        $forms = Form::select('id', 'name', 'slug', 'description', 'limit_one_response', 'creator_id')->where('creator_id', Auth::user()->id)->get();
        return response()->json([
            'message' => 'Get all forms success',
            'forms' => $forms,
        ], 200);
    }

    // function get detail form by slug
    public function getDetailForm($slug)
    {
        $form = Form::with(['allowedDomains', 'questions'])->where('slug', $slug)->first();
        if(!$form) {
            return response()->json([
                'message' => 'Form not found'
            ], 404);
        }
        $user = explode('@', Auth::user()->email);
        foreach($form->formatted_allowed_domains as $domain) {
            if(end($user) !== $domain) {
                return response()->json([
                    'message' => 'Forbidden access',
                ]);
            }
        }
        return response()->json([
            'message'=> 'Get form success',
            'form' => [
                'name'=> $form->name,
                'slug'=> $form->slug,
                'description'=> $form->description,
                'limit_one_response'=> $form->limit_one_response,
                'creator_id'=> $form->creator_id,
                'allowed_domains' => $form->formatted_allowed_domains,
                'questions' => $form->formatted_questions,
            ]
        ], 200);
    }
}
