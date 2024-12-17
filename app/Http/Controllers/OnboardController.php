<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Storage;
use Alert;

class OnboardController extends Controller
{
    protected $firestore;
    protected $storage;
    protected $collection;
    protected $bucket;
    
    public function __construct()
    {
        $this->firestore = app('firebase.firestore');
        $this->storage = app('firebase.storage');
        $this->warnaCollection = $this->firestore->database()->collection('warna');
        $this->onBoardCollection = $this->firestore->database()->collection('onBoard');
        $this->bucket = $this->storage->getBucket();
    }

    function index() {
        $documents = $this->warnaCollection->documents();
        $colors = [];

        foreach ($documents as $document) {
            if ($document->exists()) {
                $warna = $document->data();
                $warna['id'] =$document->id();
                $colors[] = $warna;
            }
        }
        // dd($colors);
        return view('Tampilan.warna', compact('colors'));
    }

    public function update(Request $request, $id)
    {
        // Validasi data yang masuk
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'code' => 'required|string|max:7', // kode warna dalam format hex
        ]);

        // Ambil 6 karakter pertama dari 'code' menggunakan substr
        $validatedData['code'] = substr($validatedData['code'], 1);
    
        // Temukan dokumen berdasarkan ID dan buat snapshot
        $document = $this->warnaCollection->document($id);
        $snapshot = $document->snapshot();
        
        // Cek apakah snapshot ada
        if ($snapshot->exists()) {
            $document->set([
                'nama' => $validatedData['nama'],
                'code' => $validatedData['code'],
            ], ['merge' => true]); // 'merge' untuk mengupdate data tanpa menghapus field lain
            Alert::success('Sukses', 'Data Berhasil Diubah');
            return redirect()->route('warna.index');
        }
        Alert::error('Gagal', 'Data Gagal Diubah');
        return redirect()->route('warna.index');
    }

    function indexOnBoard() {
        $documents = $this->onBoardCollection->documents();
        $onBoards = [];

        foreach ($documents as $document) {
            if ($document->exists()) {
                $onBoard = $document->data();
                $onBoard['id'] =$document->id();
                $onBoards[] = $onBoard;
            }
        }
        // dd($onBoards);
        return view('onBoard.onBoard', compact('onBoards'));
    }

    public function storeOnBoard(Request $request){
        $request->validate([
            'title' => 'required',
            'gambar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'required',
            'status' => 'required'
        ]);

        try {
            $image = $request->file('gambar');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = 'Image/OnBoarding/' . $imageName;

            $imageStream = fopen($image->getRealPath(), 'r');
            $object = $this->bucket->upload($imageStream, [
                'name' => $imagePath,
                'metadata' => [
                    'contentType' => $image->getMimeType(),
                ]
            ]);

            $publicUrl = sprintf(
                'https://firebasestorage.googleapis.com/v0/b/%s/o/%s?alt=media',
                $this->bucket->info()['name'],
                urlencode($imagePath)
            );
            // dd($publicUrl);

            // dd($request);
            $document = $this->onBoardCollection->newDocument();
            $document->set([
                'title' => $request->title,
                'gambar' => $publicUrl,
                'description' => $request->description,
                'status' => $request->status,
            ]);
            Alert::success('Sukses', 'Data Berhasil Ditambahkan');
            return redirect('/indexOnBoard');
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Data Gagal Ditambahkan');
            return redirect()->back();
        }
    }
    
    public function updateOnBoard(Request $request, $id)
    {
        try {
            $document = $this->onBoardCollection->document($id);
            $data = $document->snapshot();
            // dd($data);

            if (!$data->exists()) {
                Alert::error('Gagal', 'On Boarding Tidak Ditemukan');
                return redirect()->back();
            }

            // Validasi input
            $request->validate([
                'title' => 'required',
                'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Change 'required' to 'sometimes'
                'description' => 'required',
                'status' => 'required'
            ]);

            // Mendapatkan data saat ini
            $currentData = $data->data();
            // Menyiapkan data untuk diupdate
            $updateData = [
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Menghandle jika ada gambar baru yang diupload
            if ($request->hasFile('gambar')) {
                // Menghapus gambar lama jika ada
                if (isset($currentData['gambar'])) {
                    // Ekstrak path dari URL lama jika formatnya berupa public URL
                    $oldPath = parse_url($currentData['gambar'], PHP_URL_PATH);
                    $decodedPath = urldecode($oldPath); // Decoding untuk mendapatkan path aslinya
                    $oldObject = $this->bucket->object(ltrim($decodedPath, '/')); // Ltrim untuk menghapus karakter "/"
                    if ($oldObject->exists()) {
                        $oldObject->delete();
                    }
                }
                
                // Mengupload gambar baru
                $image = $request->file('gambar');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = 'Image/OnBoarding/' . $imageName;
                
                $imageStream = fopen($image->getRealPath(), 'r');
                $object = $this->bucket->upload($imageStream, [
                    'name' => $imagePath,
                    'metadata' => [
                        'contentType' => $image->getMimeType(),
                        ]
                    ]);
                    
                    // Generate public URL
                    $publicUrl = sprintf(
                        'https://firebasestorage.googleapis.com/v0/b/%s/o/%s?alt=media',
                        $this->bucket->info()['name'], // Nama bucket Firebase
                        urlencode($imagePath)         // Path gambar (di-encode)
                    );
                    
                    $updateData['gambar'] = $publicUrl; // Simpan URL publik ke Firestore
                }
                // dd($object);
                
                // Update dokumen di Firestore
                $document->set($updateData, ['merge' => true]);
                
            Alert::success('Sukses', 'Data Berhasil Diubah');
            return redirect('/onBoard');
        } catch (\Exception $e) {
            \Log::error('Banner update error: ' . $e->getMessage());
            Alert::error('Gagal', 'Data Gagal Diubah');
            return redirect()->back()
                ->withInput();
        }
    }

    public function destroyOnBoard($id)
    {
        try {
            $document = $this->onBoardCollection->document($id);
            $data = $document->snapshot();
            
            if ($data->exists()) {
                $bannerData = $data->data();
                
                // Hapus gambar dari storage jika ada
                if (isset($bannerData['gambar'])) {
                    try {
                        // Extract filename from the full URL
                        $imageUrl = $bannerData['gambar'];
                        $imagePath = 'Image/OnBoarding/' . basename(parse_url($imageUrl, PHP_URL_PATH));
                        
                        $object = $this->bucket->object($imagePath);
                        if ($object->exists()) {
                            $object->delete();
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error deleting image: ' . $e->getMessage());
                    }
                }
                
                // Hapus document dari Firestore
                $document->delete();
                
                Alert::success('Sukses', 'Data Berhasil Dihapus');
                return redirect('/onBoard');
            }
            
            Alert::error('Gagal', 'Data Tidak Ditemukan');
            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('Banner delete error: ' . $e->getMessage());
            Alert::error('Gagal', 'Data Gagal Dihapus');
            return redirect()->back();
        }
    }
}
