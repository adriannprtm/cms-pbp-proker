<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Storage;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Google\Cloud\Core\Timestamp;
use Exception;
use Alert;

class PengelolaController extends Controller
{
    protected $firestore;
    protected $storage;
    protected $collection;
    protected $bucket;
    protected $auth;
    
    public function __construct(){
        $this->firestore = app('firebase.firestore');
        $this->storage = app('firebase.storage');
        $this->auth = app('firebase.auth');
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

    public function store(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:6',
                'firstName' => 'required',
                'lastName' => 'nullable',
                'image' => 'image|mimes:jpeg,png,jpg|max:2048' // Validasi untuk gambar
            ]);
            // dd($request);
            // 1. Buat user di Firebase Authentication
            $userAuth = $this->auth->createUser([
                'email' => $request->email,
                'password' => $request->password,
                'emailVerified' => false,
            ]);
            // dd($userAuth);

            // 2. Upload gambar jika ada
            $imageUrl = '';
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $path = 'profile_images/' . $userAuth->uid . '/' . $imageName;
                
                $imageObject = $this->bucket->upload(
                    file_get_contents($image->getRealPath()),
                    ['name' => $path]
                );

                // Dapatkan URL gambar
                // $imageUrl = 'https://storage.googleapis.com/' . $this->bucket->name() . '/' . $path;

                $imageUrl = sprintf(
                    'https://firebasestorage.googleapis.com/v0/b/%s/o/%s?alt=media',
                    $this->bucket->info()['name'],
                    urlencode($path)
                );
            }

            $lastName = $request->lastName ?? '';

            $userData = [
                'createdAt' => new Timestamp(new \DateTime()),
                'email' => $request->email,
                'firstName' => $request->firstName,
                'imageUrl' => $imageUrl,
                'lastName' => $lastName,
                'lastSeen' => new Timestamp(new \DateTime()),
                'name' => $request->firstName . ' ' . $request->lastName,
                'role' => 'pengelola',
                'updatedAt' => new Timestamp(new \DateTime())
            ];
            // dd($userData);

            // 4. Simpan ke Firestore menggunakan UID dari Auth
            $this->collection->document($userAuth->uid)->set($userData);

            return redirect()->route('pengelola.index')->with('success', 'Mahasiswa berhasil ditambahkan');
        } catch (EmailExists $e) {
            return redirect()->back()->with('error', 'Email sudah terdaftar');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $uid)
    {
        try {
            // Validasi input
            $request->validate([
                'firstName' => 'required',
                'lastName' => 'nullable',
                'image' => 'image|mimes:jpeg,png,jpg|max:2048' // Validasi untuk gambar
            ]);

            // 1. Dapatkan dokumen pengguna dari Firestore berdasarkan UID
            $userDocument = $this->collection->document($uid);
            $existingData = $userDocument->snapshot();

            if (!$existingData->exists()) {
                return redirect()->back()->with('error', 'Pengguna tidak ditemukan');
            }

            // 2. Perbarui gambar jika ada
            $imageUrl = $existingData['imageUrl'] ?? ''; // Gunakan URL gambar lama jika tidak ada penggantian
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $path = 'profile_images/' . $uid . '/' . $imageName;

                $imageObject = $this->bucket->upload(
                    file_get_contents($image->getRealPath()),
                    ['name' => $path]
                );

                // Dapatkan URL gambar
                $imageUrl = sprintf(
                    'https://firebasestorage.googleapis.com/v0/b/%s/o/%s?alt=media',
                    $this->bucket->info()['name'],
                    urlencode($path)
                );
            }

            // Set nilai default untuk `lastName` jika tidak diisi
            $lastName = $request->lastName ?? '';

            // 3. Siapkan data yang akan diperbarui
            $updatedData = [
                'firstName' => $request->firstName,
                'imageUrl' => $imageUrl,
                'lastName' => $lastName,
                'name' => $request->firstName . ' ' . $lastName,
                'updatedAt' => new Timestamp(new \DateTime()), // Perbarui waktu
            ];

            // 4. Perbarui dokumen di Firestore
            $userDocument->update(
                array_map(
                    fn($key, $value) => ['path' => $key, 'value' => $value],
                    array_keys($updatedData),
                    $updatedData
                )
            );

            return redirect()->route('pengelola.index')->with('success', 'Pengelola berhasil diperbarui');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function destroy($id)
    {
        try {
            $document = $this->collection->document($id);
            $data = $document->snapshot();
            
            if ($data->exists()) {
                $bannerData = $data->data();
                
                // Hapus gambar dari storage jika ada
                if (isset($bannerData['imageUrl'])) {
                    try {
                        // Extract filename from the full URL
                        $imageUrl = $bannerData['imageUrl'];
                        $imagePath = 'profile_images/' . basename(parse_url($imageUrl, PHP_URL_PATH));
                        
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
                return redirect('/mahasiswa');
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
