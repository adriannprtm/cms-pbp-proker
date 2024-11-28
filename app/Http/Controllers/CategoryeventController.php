<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Storage;
use Alert;

class CategoryeventController extends Controller
{
    protected $firestore;
    protected $storage;
    protected $collection;
    protected $bucket;
    
    public function __construct()
    {
        $this->firestore = app('firebase.firestore');
        $this->storage = app('firebase.storage');
        $this->collection = $this->firestore->database()->collection('eventCategory');
        $this->bucket = $this->storage->getBucket();
    }

    public function index()
    {
        $documents = $this->collection->documents();
        $categoryEvents = [];
        
        foreach ($documents as $document) {
            if ($document->exists()) {
                $categoryEvent = $document->data();
                $categoryEvent['id'] = $document->id();
                $categoryEvents[] = $categoryEvent;
            }
        }
        // dd($categoryEvents);
        
        return view('Events.categoryEvent', compact('categoryEvents'));
    }

    public function store(Request $request){
        $request->validate([
            'categoryName' => 'required',
        ]);
        try {
            $document = $this->collection->newDocument();
            $document->set([
                'categoryName' => $request->categoryName,
            ]);
            Alert::success('Sukses', 'Data Berhasil Ditambahkan');
            return redirect()->route('categoryEvent.index');
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Data Gagal Ditambahkan');
            return redirect()->back();
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'categoryName' => 'required',
        ]);
    
        $document = $this->collection->document($id);
        $snapshot = $document->snapshot();
        
        // Cek apakah snapshot ada
        if ($snapshot->exists()) {
            $document->set([
                'categoryName' => $validatedData['categoryName'],
            ], ['merge' => true]); // 'merge' untuk mengupdate data tanpa menghapus field lain
            Alert::success('Sukses', 'Data Berhasil Diubah');
            return redirect()->route('categoryEvent.index');
        }
        Alert::error('Gagal', 'Data Gagal Diubah');
        return redirect()->back()->with('error', 'Warna tidak ditemukan');
    }

    public function destroy($id)
    {
        try {
            $document = $this->collection->document($id);
            $data = $document->snapshot();
            
            if ($data->exists()) {
                $document->delete();
            }
            
            Alert::success('Sukses', 'Data Berhasil Dihapus');
            return redirect()->route('categoryEvent.index');
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Data Gagal Dihapus');
            return redirect()->back();
        }
    }
}

