<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PengelolaController extends Controller
{
    protected $firestore;
    protected $storage;
    protected $collection;
    protected $bucket;
    
    public function __construct(){
        $this->firestore = app('firebase.firestore');
        $this->storage = app('firebase.storage');
        $this->collection = $this->firestore->database()->collection('users');
        $this->bucket = $this->storage->getBucket();
    }

    public function index(){
        $documents = $this->collection->documents();
        $pengelolas = [];

        foreach ($documents as $document) {
            if ($document->exists()) {
                $pengelola = $document->data();
                $pengelola['id'] = $document->id();
                if (isset($pengelola['role']) && $pengelola['role'] === 'pengelola') {
                    $pengelolas[] = $pengelola;
                }
            }
            // dd($mahasiswas);
        }

        return view('Pengelola.pengelola', compact('pengelolas'));
    }
}
