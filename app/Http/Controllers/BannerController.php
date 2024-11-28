<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Storage;
use Alert;

class BannerController extends Controller
{
    protected $firestore;
    protected $storage;
    protected $collection;
    protected $bucket;
    
    public function __construct()
    {
        $this->firestore = app('firebase.firestore');
        $this->storage = app('firebase.storage');
        $this->collection = $this->firestore->database()->collection('banners');
        $this->bucket = $this->storage->getBucket();
    }
    
    public function index()
    {
        $documents = $this->collection->documents();
        // dd($documents);
        $banners = [];
        
        foreach ($documents as $document) {
            if ($document->exists()) {
                $banner = $document->data();
                $banner['id'] = $document->id();
                // Mendapatkan URL gambar jika ada
                // if (isset($banner['gambar'])) {
                //     $banner['gambar_url'] = $this->getImageUrl($banner['gambar']);
                // }
                $banners[] = $banner;
            }
        }
        // dd($banners);
        
        return view('Banner.banner', compact('banners'));
    }

    public function store(Request $request)
    {   
        // Validasi input
        $request->validate([
            'nama' => 'required',
            'gambar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required'
        ]);

        try {
            // Upload gambar ke Firebase Storage
            $image = $request->file('gambar');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = 'Image/Banner/' . $imageName;
            
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

            // Simpan data ke Firestore
            $document = $this->collection->newDocument();
            $document->set([
                'nama' => $request->nama,
                'gambar' => $publicUrl, // Simpan URL publik langsung
                'status' => $request->status,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            Alert::success('Sukses', 'Data Berhasil Ditambahkan');
            return redirect('/banner');
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Data Gagal Ditambahkan');
            return redirect()->back();
        }
    }

    private function getImageUrl($path)
    {
        try {
            // Generate signed URL yang berlaku selama 1 jam
            $expiration = new \DateTime('tomorrow');
            $object = $this->bucket->object($path);
            if ($object->exists()) {
                return $object->signedUrl($expiration);
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $document = $this->collection->document($id);
            $data = $document->snapshot();

            if (!$data->exists()) {
                return redirect()->back()->with('error', 'Banner tidak ditemukan');
            }

            // Validasi input
            $request->validate([
                'nama' => 'required',
                'status' => 'required',
                'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Mendapatkan data saat ini
            $currentData = $data->data();

            // Menyiapkan data untuk diupdate
            $updateData = [
                'nama' => $request->nama,
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
                $imagePath = 'Image/Banner/' . $imageName;
                
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

            // Update dokumen di Firestore
            $document->set($updateData, ['merge' => true]);

            Alert::success('Sukses', 'Data Berhasil Diubah');
            return redirect('/banner');
        } catch (\Exception $e) {
            \Log::error('Banner update error: ' . $e->getMessage());
            Alert::error('Gagal', 'Data Gagal Diubah');
            return redirect()->back()
                ->withInput();
        }
    }

    // Tambahan method untuk menghapus banner
    public function destroy($id)
    {
        try {
            $document = $this->collection->document($id);
            $data = $document->snapshot();
            
            if ($data->exists()) {
                // Hapus gambar dari storage jika ada
                if (isset($data['gambar'])) {
                    $object = $this->bucket->object($data['gambar']);
                    if ($object->exists()) {
                        $object->delete();
                    }
                }
                
                // Hapus document dari Firestore
                $document->delete();
            }
            
            Alert::success('Sukses', 'Data Berhasil Dihapus');
            return redirect('/banner');
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Data Gagal Dihapus');
            return redirect()->back();
        }
    }
}