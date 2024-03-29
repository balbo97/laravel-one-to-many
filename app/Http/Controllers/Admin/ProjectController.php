<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;

use App\Models\Project;
use App\Models\Category;
use App\Http\Controllers\Controller;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
        // $projects = Project::all();
        $projects = Project::orderBy('id', 'desc')->get();

        return view('admin.projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //poichè voglio ciclare le categorie all'interno della view create, vado a recuperare tutte le categorie e le invio alla view 
        $categories = Category::all();

        return view('admin.projects.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreProjectRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProjectRequest $request)
    {
        
        
        // recupero i dati inviati dalla form
        $form_data = $request->all();

        // creo una nuova istanza del model Project
        $project = new Project();

        // riempio gli altri campi con la funzione fill()
        $project->fill($form_data);

        // verifico se la richiesta contiene l'immagine 
        if($request->hasFile('cover_image')){

            $path = Storage::disk('public')->put('project_image', $form_data['cover_image']);

            $form_data['cover_image'] = $path;
            
        };

        // salvo il record sul db
        $project->save();

        // effettuo il redirect alla view index
        return redirect()->route('admin.project.index');
       
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        return view('admin.projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        $categories = Category::all();
        return view('admin.projects.edit', compact('project', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProjectRequest  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {

       
        // recupero i dati inviati dalla form
        $form_data = $request->all();

        // controllo se il titolo è gia presente nll'elenco dei miei progetti 
        $exists = Post::where('title', 'LIKE', $form_data['title'])->where('id', '!=', $project->id)->get();

        if(count($exists) > 0){
            $error_message = 'Hai gia utilizzato questo titole in un altro progetto';
            return redirect()->route('admin.project.edit', compact('project', 'error_message'));
        }

        // controllo se nel form stanno mettendo il file image 
        if($request->hasFile('cover_image')){

            // controllo se il file aveva già un immagine in precedenza 
            if($project->cover_image != null){
                Storage::disk('public')->delete($project->cover_image);
            }
            $path = Storage::disk('public')->put('project_image', $form_data['cover_image']);
                
            $form_data['cover_image'] = $path;
        }


        // riempio gli altri campi con la funzione fill()
        $project->update($form_data);

        // effettuo il redirect alla view index
        return redirect()->route('admin.project.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        // controllo se il file aveva già un immagine in precedenza 
        if($project->cover_image != null){
            Storage::disk('public')->delete($project->cover_image);
        }

        $project -> delete();
        return redirect()->route('admin.project.index');
    }
}
