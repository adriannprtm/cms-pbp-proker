<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Storage;
use Kreait\Firebase\Exception\Auth\EmailExists;
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
            dd($userAuth);

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

            $userData = [
                'createdAt' => date('Y-m-d H:i:s'),
                'email' => $request->email,
                'firstName' => $request->firstName,
                'imageUrl' => $imageUrl,
                'lastName' => $request->lastName,
                'lastSeen' => null,
                'name' => $request->firstName . ' ' . $request->lastName,
                'role' => 'user',
                'updatedAt' => date('Y-m-d H:i:s')
            ];

            // 4. Simpan ke Firestore menggunakan UID dari Auth
            $this->collection->document($userAuth->uid)->set($userData);

            return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil ditambahkan');
        } catch (EmailExists $e) {
            return redirect()->back()->with('error', 'Email sudah terdaftar');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            // Hapus foto profil dari Storage jika ada
            $document = $this->collection->document($id)->snapshot();
            if ($document->exists() && !empty($document->data()['imageUrl'])) {
                $path = parse_url($document->data()['imageUrl'], PHP_URL_PATH);
                $this->bucket->object(ltrim($path, '/'))->delete();
            }

            // Hapus user dari Authentication
            $this->auth->deleteUser($id);
            
            // Hapus document dari Firestore
            $this->collection->document($id)->delete();

            return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil dihapus');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'firstName' => 'required',
                'lastName' => 'required',
                'email' => 'required|email',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            // Update email di Authentication jika berubah
            $userRecord = $this->auth->getUser($id);
            if ($userRecord->email !== $request->email) {
                $this->auth->updateUser($id, ['email' => $request->email]);
            }

            // Update password jika ada
            if ($request->filled('password')) {
                $this->auth->updateUser($id, ['password' => $request->password]);
            }

            // Handle image update
            $userData = [
                'email' => $request->email,
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'name' => $request->firstName . ' ' . $request->lastName,
                'updatedAt' => round(microtime(true) * 1000)
            ];

            if ($request->hasFile('image')) {
                // Hapus foto lama jika ada
                $document = $this->collection->document($id)->snapshot();
                if ($document->exists() && !empty($document->data()['imageUrl'])) {
                    $path = parse_url($document->data()['imageUrl'], PHP_URL_PATH);
                    $this->bucket->object(ltrim($path, '/'))->delete();
                }

                // Upload foto baru
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $path = 'profile_images/' . $id . '/' . $imageName;
                
                $imageObject = $this->bucket->upload(
                    file_get_contents($image->getRealPath()),
                    ['name' => $path]
                );

                $userData['imageUrl'] = 'https://firebasestorage.googleapis.com/v0/b/%s/o/%s?alt=media' . $this->bucket->name() . '/' . $path;
            }

            // Update data di Firestore
            $this->collection->document($id)->update($userData);

            return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil diperbarui');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}