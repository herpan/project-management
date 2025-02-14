<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProjectController extends Controller
{
       
    
    public function index()
    {
        $user = JWTAuth::user();
        $projects = Project::where('user_id', $user->id)->get();
        return response()->json($projects);
    }

    public function store(Request $request)
    {
        $user = JWTAuth::user();
        $project = Project::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json($project, 201);
    }

    public function update(Request $request, $id)
    {
        $user = JWTAuth::user();
        $project = Project::where('user_id', $user->id)->findOrFail($id);
        $project->update($request->all());
        return response()->json($project);
    }

    public function destroy($id)
    {
        $user = JWTAuth::user();
        $project = Project::where('user_id', $user->id)->findOrFail($id);
        $project->delete();
        return response()->json(null, 204);
    }
}
