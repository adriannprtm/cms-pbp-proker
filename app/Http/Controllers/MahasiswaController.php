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

class MahasiswaController extends Controller
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
        $mahasiswas = [];

        foreach ($documents as $document) {
            if ($document->exists()) {
                $mahasiswa = $document->data();
                $mahasiswa['id'] = $document->id();
                if (isset($mahasiswa['role']) && $mahasiswa['role'] === 'user') {
                    $mahasiswas[] = $mahasiswa;
                }
            }
        }

        return view('Mahasiswa.mahasiswa', compact('mahasiswas'));
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
                'role' => 'user',
                'updatedAt' => new Timestamp(new \DateTime())
            ];
            // dd($userData);

            // 4. Simpan ke Firestore menggunakan UID dari Auth
            $this->collection->document($userAuth->uid)->set($userData);

            return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil ditambahkan');
        } catch (EmailExists $e) {
            return redirect()->back()->with('error', 'Email sudah terdaftar');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($uid)
    {
        try {
            // 1. Dapatkan dokumen pengguna dari Firestore berdasarkan UID
            $userDocument = $this->collection->document($uid);
            $existingData = $userDocument->snapshot();

            if (!$existingData->exists()) {
                return redirect()->back()->with('error', 'Pengguna tidak ditemukan');
            }

            // 2. Hapus gambar profil dari Firebase Storage jika ada
            if (!empty($existingData['imageUrl'])) {
                $imagePath = parse_url($existingData['imageUrl'], PHP_URL_PATH);
                $bucketPath = urldecode(substr($imagePath, strpos($imagePath, '/o/') + 3));
                
                $this->bucket->object($bucketPath)->delete(); // Hapus file gambar dari bucket
            }

            // 3. Hapus dokumen pengguna dari Firestore
            $userDocument->delete();

            // 4. Hapus pengguna dari Firebase Authentication
            $userAuth = $this->auth->getUser($uid);  // Mendapatkan user berdasarkan UID
            if ($userAuth) {
                $this->auth->deleteUser($uid);  // Menghapus user dari Firebase Auth
            }

            return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil dihapus');
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

            return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil diperbarui');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

}