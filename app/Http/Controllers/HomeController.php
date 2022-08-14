<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function cloudinary(){
        // return view('cloudinary');
        // $upload = \Cloudinary::uploadVideo($request->file('file')->getRealPath())->getSecurePath();
        
        try {
            $upload = \Cloudinary::uploadVideo(env('CLOUDINARY_SAMPLE_FILE'), ['folder' => 'desafio-saudavel']);
            
            print $upload->getSecurePath();
            print "<br />";
            print $upload->getPublicId();
        } catch(Exception $e){
            throw('Erro');
        }

    }
}
